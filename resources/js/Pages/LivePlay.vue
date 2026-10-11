<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import { Chart, registerables } from 'chart.js';
import QRCode from 'qrcode';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

Chart.register(...registerables);

interface LiveState {
    id: number;
    title: string;
    status: 'lobby' | 'live' | 'ended';
    intermission: boolean;
    isLastQuestion: boolean;
    questionIndex: number;
    totalQuestions: number;
    pin: string;
    broadcastToken: string;
    lobbyLocked: boolean;
    questionDuration: number;
    questionStartedAt: string | null;
    question: {
        id: number;
        type: string;
        prompt: string;
        options: string[];
        correct_answer: string;
    } | null;
    participants: {
        id: number;
        alias: string;
        avatar_key: string | null;
        score: number;
    }[];
    leaderboard: {
        id: number;
        alias: string;
        avatar_key: string | null;
        score: number;
        correct_count?: number;
        wrong_count?: number;
        total_answered?: number;
    }[];
    myStats?:
        | { correctCount: number; wrongCount: number; totalAnswered: number }
        | null
        | undefined;
    currentParticipant?: {
        id: number;
        alias: string;
        avatar_key: string | null;
        score: number;
    } | null;
}
const props = defineProps<{ session: LiveState; isHost: boolean }>();
const state = ref<LiveState>(props.session);
const answer = useForm({ answer: '' });
const hasAnswered = ref(false);
const answerResult = ref<{
    is_correct: boolean;
    correct_answer: string;
    points_awarded: number;
} | null>(null);

// Dynamic Participant Performance computed directly from live WebSocket state
const myLeaderboardEntry = computed(() => {
    return (
        state.value.leaderboard?.find(
            (p) => p.id === state.value.currentParticipant?.id,
        ) || state.value.leaderboard?.[0]
    );
});

const myRank = computed(() => {
    const participantId = state.value.currentParticipant?.id;
    if (!participantId) return null;

    const index = state.value.leaderboard.findIndex(
        (participant) => participant.id === participantId,
    );

    return index >= 0 ? index + 1 : null;
});

const accuracyPercent = computed(() =>
    participantStats.value.totalAnswered > 0
        ? Math.round(
              (participantStats.value.correctCount /
                  participantStats.value.totalAnswered) *
                  100,
          )
        : 0,
);

const completionText = computed(() => {
    if (participantStats.value.totalAnswered >= state.value.totalQuestions) {
        return 'Semua soal selesai dijawab.';
    }

    return `${participantStats.value.totalAnswered} dari ${state.value.totalQuestions} soal dijawab.`;
});

const hostAnsweredParticipants = computed(() =>
    state.value.leaderboard.filter(
        (participant) => (participant.total_answered ?? 0) > 0,
    ),
);

const hostAverageAccuracy = computed(() => {
    const answered = hostAnsweredParticipants.value;
    const totalAnswered = answered.reduce(
        (sum, participant) => sum + (participant.total_answered ?? 0),
        0,
    );
    if (totalAnswered === 0) return 0;

    const totalCorrect = answered.reduce(
        (sum, participant) => sum + (participant.correct_count ?? 0),
        0,
    );

    return Math.round((totalCorrect / totalAnswered) * 100);
});

const hostTopParticipant = computed(() => state.value.leaderboard[0] ?? null);

const hostNeedsFollowUp = computed(() =>
    state.value.leaderboard
        .filter((participant) => {
            const totalAnswered = participant.total_answered ?? 0;
            if (totalAnswered === 0) return true;

            const accuracy =
                ((participant.correct_count ?? 0) / totalAnswered) * 100;

            return accuracy < 60;
        })
        .slice(0, 5),
);

// Dynamic reactive stats reading directly from WebSocket payload state
const participantStats = computed(() => {
    // 1. Ambil dari broadcast leaderboard item jika server menyediakan count-nya
    if (
        myLeaderboardEntry.value &&
        typeof myLeaderboardEntry.value.correct_count === 'number'
    ) {
        return {
            correctCount: myLeaderboardEntry.value.correct_count ?? 0,
            wrongCount: myLeaderboardEntry.value.wrong_count ?? 0,
            totalAnswered: myLeaderboardEntry.value.total_answered ?? 0,
        };
    }

    // 2. Fallback ke myStats dari session presenter
    if (state.value.myStats) {
        return {
            correctCount: state.value.myStats.correctCount,
            wrongCount: state.value.myStats.wrongCount,
            totalAnswered: state.value.myStats.totalAnswered,
        };
    }

    // 3. Counter in-memory lokal
    return localAnswerStats.value;
});

const localAnswerStats = ref({
    correctCount: props.session.myStats?.correctCount ?? 0,
    wrongCount: props.session.myStats?.wrongCount ?? 0,
    totalAnswered: props.session.myStats?.totalAnswered ?? 0,
});
const donutCanvas = ref<HTMLCanvasElement | null>(null);
let doughnutChartInstance: Chart | null = null;
const now = ref(Date.now());
const clock = window.setInterval(() => {
    now.value = Date.now();
}, 1000);
const isBrowserOnline = ref(
    typeof navigator === 'undefined' ? true : navigator.onLine,
);

function updateOnlineStatus(): void {
    isBrowserOnline.value = navigator.onLine;
}

// Intermission countdown — 3 detik setelah podium muncul, tombol "Lanjut Soal" aktif
const intermissionCountdown = ref(0);
let intermissionTimer: ReturnType<typeof setInterval> | number | null = null;

function startIntermissionCountdown(): void {
    intermissionCountdown.value = 3;
    if (intermissionTimer) window.clearInterval(intermissionTimer);
    intermissionTimer = window.setInterval(() => {
        intermissionCountdown.value = Math.max(
            0,
            intermissionCountdown.value - 1,
        );
        if (intermissionCountdown.value === 0 && intermissionTimer) {
            window.clearInterval(intermissionTimer);
            intermissionTimer = null;
        }
    }, 1000);
}

function renderDoughnutChart(): void {
    if (!donutCanvas.value) return;
    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
        doughnutChartInstance = null;
    }

    const { correctCount, wrongCount } = participantStats.value;
    const hasData = correctCount > 0 || wrongCount > 0;

    doughnutChartInstance = new Chart(donutCanvas.value, {
        type: 'doughnut',
        data: {
            labels: ['Benar', 'Salah'],
            datasets: [
                {
                    data: hasData ? [correctCount, wrongCount] : [0, 1],
                    backgroundColor: hasData
                        ? wrongCount === 0
                            ? ['#10B981', '#10B981']
                            : ['#10B981', '#EF4444']
                        : ['#CBD5E1'],
                    borderWidth: 3,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: hasData,
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.raw} Soal`,
                    },
                },
            },
        },
    });
}

watch(
    () => state.value.status,
    async (newStatus) => {
        if (newStatus === 'ended' && !props.isHost) {
            await nextTick();
            renderDoughnutChart();
            setTimeout(() => {
                renderDoughnutChart();
            }, 200);
        }
    },
    { immediate: true },
);
onMounted(() => {
    window.addEventListener('keydown', handleShortcut);
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    if (state.value.intermission) startIntermissionCountdown();
    if (state.value.status === 'ended' && !props.isHost) {
        nextTick(() => renderDoughnutChart());
    }
});
onBeforeUnmount(() => {
    window.clearInterval(clock);
    if (intermissionTimer) window.clearInterval(intermissionTimer);
    if (doughnutChartInstance) doughnutChartInstance.destroy();
    window.removeEventListener('keydown', handleShortcut);
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});
useEchoPublic<LiveState>(
    `live-session.${props.session?.broadcastToken || ''}`,
    '.live.session.updated',
    (payload) => {
        if (payload) {
            const wasIntermission = state.value.intermission;
            lastPingAt.value = Date.now();
            connectionLost.value = false;
            reconnectAttempts.value = 0;
            const currentQId = state.value.question?.id;

            // Pertahankan myStats jika payload broadcast dari server tidak membawanya
            const existingMyStats = state.value.myStats;
            state.value = {
                ...payload,
                myStats: payload.myStats ?? existingMyStats,
            };

            // Trigger auto-render doughnut chart secara otomatis pas status berpindah ke ended via WebSocket!
            if (payload.status === 'ended' && !props.isHost) {
                nextTick(() => {
                    renderDoughnutChart();
                });
                setTimeout(() => {
                    renderDoughnutChart();
                }, 300);
            }

            // Reset status jawaban saat berpindah ke pertanyaan baru
            if (payload.question?.id !== currentQId) {
                answer.reset();
                hasAnswered.value = false;
                answerResult.value = null;
            }

            // Mulai countdown saat baru masuk intermission
            if (!wasIntermission && payload.intermission) {
                startIntermissionCountdown();
            }
        }
    },
);
const selectedOptionIndex = computed(
    () =>
        state.value.question?.options?.findIndex(
            (option) => option === answer.answer,
        ) ?? -1,
);

// Recovery UX — warn if no WS event in 20s while live
const connectionLost = ref(false);
const reconnectAttempts = ref(0);
const lastPingAt = ref(Date.now());
const connectionWarning = computed(
    () =>
        !props.isHost &&
        state.value.status === 'live' &&
        (!isBrowserOnline.value || now.value - lastPingAt.value > 20_000),
);
const connectionStatus = computed(() => {
    if (!isBrowserOnline.value) {
        return {
            label: 'Offline',
            detail: 'Internet terputus. Jangan tutup halaman.',
            className: 'border-red-200 bg-red-50 text-red-700',
            dotClass: 'bg-red-500',
        };
    }

    if (connectionWarning.value) {
        return {
            label: 'Koneksi lambat',
            detail: 'Menunggu update dari server.',
            className: 'border-amber-200 bg-amber-50 text-amber-700',
            dotClass: 'bg-amber-500',
        };
    }

    return {
        label: 'Tersambung',
        detail: 'Update soal dan skor aktif.',
        className: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        dotClass: 'bg-emerald-500',
    };
});

const secondsLeft = computed(() => {
    if (!state.value.questionStartedAt) return state.value.questionDuration;
    return Math.max(
        0,
        state.value.questionDuration -
            Math.floor(
                (now.value -
                    new Date(state.value.questionStartedAt).getTime()) /
                    1000,
            ),
    );
});
function choose(value: string): void {
    if (answer.processing || secondsLeft.value === 0 || hasAnswered.value)
        return;
    answer.answer = value;
    hasAnswered.value = true;

    answer.post(route('live-sessions.answers.store', state.value.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            const flash = (page.props as any).flash;
            if (flash?.answerResult) {
                answerResult.value = flash.answerResult;

                // Update localAnswerStats secara in-memory begitu server mengonfirmasi jawaban
                localAnswerStats.value.totalAnswered += 1;
                if (flash.answerResult.is_correct) {
                    localAnswerStats.value.correctCount += 1;
                } else {
                    localAnswerStats.value.wrongCount += 1;
                }
            }
        },
        onError: () => {
            hasAnswered.value = false;
            answerResult.value = null;
        },
    });
}
function moveOption(delta: number): void {
    const options = state.value.question?.options;
    if (!options?.length || state.value.status !== 'live') return;
    const nextIndex =
        (selectedOptionIndex.value + delta + options.length) % options.length;
    answer.answer = options[nextIndex];
}
function handleShortcut(event: KeyboardEvent): void {
    const target = event.target as HTMLElement | null;
    if (props.isHost || target?.tagName === 'INPUT') return;
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        moveOption(1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        moveOption(-1);
    } else if (event.key === 'Enter' && answer.answer) {
        event.preventDefault();
        choose(answer.answer);
    }
}
const hostActionProcessing = ref(false);
const showEndConfirm = ref(false);

function confirmEndSession(): void {
    showEndConfirm.value = false;
    hostAction('end');
}

function hostAction(name: 'lock' | 'start' | 'next' | 'end'): void {
    if (hostActionProcessing.value) return;
    hostActionProcessing.value = true;
    useForm({}).post(route(`live-sessions.${name}`, state.value.id), {
        preserveScroll: true,
        onFinish: () => {
            hostActionProcessing.value = false;
        },
    });
}

const hostJoinUrl =
    typeof window !== 'undefined' ? window.location.origin + '/join' : '/join';
const hostQrDialog = ref<HTMLDialogElement | null>(null);
const hostQrCanvas = ref<HTMLCanvasElement | null>(null);
const hostQrVisible = ref(false);
async function openHostQr(): Promise<void> {
    if (!hostQrDialog.value || hostQrDialog.value.open) return;

    hostQrDialog.value.showModal();
    hostQrVisible.value = true;
    await nextTick();
    if (hostQrCanvas.value)
        await QRCode.toCanvas(hostQrCanvas.value, hostJoinUrl, {
            width: 240,
            margin: 1,
        });
}
function closeHostQr(): void {
    hostQrDialog.value?.close();
}

const qrCanvas = ref<HTMLCanvasElement | null>(null);
const qrUrl = ref<string | null>(null);
const showQr = ref(false);

async function loadQr(): Promise<void> {
    if (qrUrl.value) {
        showQr.value = !showQr.value;
        return;
    }
    const data = await fetch(
        route('live-sessions.reconnect-qr', state.value.id),
    ).then((response) => response.json() as Promise<{ url: string }>);
    qrUrl.value = data.url;
    showQr.value = true;
    await new Promise<void>((resolve) => setTimeout(resolve, 50));
    if (qrCanvas.value)
        QRCode.toCanvas(qrCanvas.value, data.url, { width: 200 });
}
</script>

<template>
    <Head :title="state.title" />
    <AuthenticatedLayout v-if="isHost">
        <main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <Link
                :href="route('live-sessions.index')"
                class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-brand-primary transition hover:border-brand-secondary hover:bg-brand-secondary/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path
                        d="m15 18-6-6 6-6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                Kembali ke Live Quiz
            </Link>

            <section
                aria-labelledby="live-session-title"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start"
                >
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p
                                class="text-xs font-bold uppercase tracking-[0.16em] text-[#527A12]"
                            >
                                Sesi live
                            </p>
                            <span
                                class="rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold capitalize text-[#527A12]"
                            >
                                {{ state.status }}
                            </span>
                        </div>
                        <h1
                            id="live-session-title"
                            class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl"
                        >
                            {{ state.title }}
                        </h1>
                        <p class="mt-2 max-w-xl text-sm text-slate-600">
                            Bagikan PIN kepada peserta, lalu mulai kuis saat
                            semua sudah bergabung.
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-brand-secondary/40 bg-brand-secondary/10 p-4 lg:min-w-72"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-[#527A12]"
                        >
                            PIN peserta
                        </p>
                        <p
                            class="mt-1 font-mono text-3xl font-extrabold tracking-[0.2em] text-slate-950"
                        >
                            {{ state.pin }}
                        </p>
                        <button
                            type="button"
                            class="mt-4 min-h-11 rounded-lg border border-brand-secondary bg-white px-4 text-sm font-bold text-[#527A12] transition hover:bg-brand-secondary/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                            aria-haspopup="dialog"
                            :aria-expanded="hostQrVisible"
                            @click="openHostQr"
                        >
                            Tampilkan QR join
                        </button>
                    </div>
                </div>

                <div
                    v-if="state.status !== 'ended'"
                    class="flex flex-wrap items-center gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6"
                >
                    <button
                        v-if="state.status === 'lobby'"
                        type="button"
                        class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                        @click="hostAction('lock')"
                    >
                        {{
                            state.lobbyLocked ? 'Lobby terkunci' : 'Kunci lobby'
                        }}
                    </button>
                    <button
                        v-if="state.status === 'lobby'"
                        type="button"
                        class="min-h-11 rounded-lg bg-brand-primary px-5 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
                        @click="hostAction('start')"
                    >
                        Mulai kuis
                    </button>
                    <button
                        v-if="state.status !== 'live'"
                        type="button"
                        class="min-h-11 rounded-lg px-3 text-sm font-bold text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                        @click="showEndConfirm = true"
                    >
                        Akhiri sesi
                    </button>
                </div>
            </section>

            <section
                v-if="state.status === 'ended'"
                class="overflow-hidden rounded-3xl border border-brand-primary/15 bg-white shadow-figma-sm"
                aria-labelledby="host-final-summary-title"
            >
                <div class="bg-brand-primary p-6 text-white sm:p-7">
                    <span
                        class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-wide"
                    >
                        Sesi selesai
                    </span>
                    <div
                        class="mt-3 grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end"
                    >
                        <div>
                            <h2
                                id="host-final-summary-title"
                                class="text-3xl font-black tracking-tight"
                            >
                                Ringkasan kelas sudah siap.
                            </h2>
                            <p
                                class="mt-2 max-w-2xl text-sm font-semibold leading-relaxed text-white/80"
                            >
                                Gunakan hasil ini untuk menentukan pembahasan,
                                remedial, atau sesi ulang berikutnya.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                :href="route('attempts.index')"
                                class="inline-flex min-h-11 items-center rounded-2xl bg-white px-4 text-sm font-black text-brand-primary transition hover:bg-brand-accent active:scale-95"
                            >
                                Lihat laporan
                            </Link>
                            <Link
                                :href="route('live-sessions.index')"
                                class="inline-flex min-h-11 items-center rounded-2xl border border-white/25 px-4 text-sm font-black text-white transition hover:bg-white/10 active:scale-95"
                            >
                                Buat sesi baru
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4 lg:p-7"
                >
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Peserta
                        </p>
                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ state.participants.length }}
                        </p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            bergabung di sesi ini
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-emerald-700"
                        >
                            Partisipasi
                        </p>
                        <p class="mt-2 text-3xl font-black text-emerald-950">
                            {{ hostAnsweredParticipants.length }}
                        </p>
                        <p class="mt-1 text-xs font-semibold text-emerald-700">
                            peserta menjawab minimal 1 soal
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-brand-secondary/40 bg-brand-accent p-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-brand-dark/70"
                        >
                            Akurasi kelas
                        </p>
                        <p class="mt-2 text-3xl font-black text-brand-dark">
                            {{ hostAverageAccuracy }}%
                        </p>
                        <p
                            class="mt-1 text-xs font-semibold text-brand-dark/70"
                        >
                            rata-rata jawaban benar
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Peringkat 1
                        </p>
                        <p
                            class="mt-2 truncate text-2xl font-black text-slate-950"
                        >
                            {{ hostTopParticipant?.alias ?? '-' }}
                        </p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            {{ hostTopParticipant?.score ?? 0 }} poin
                        </p>
                    </div>
                </div>

                <div
                    class="grid gap-4 border-t border-slate-100 p-6 lg:grid-cols-2 lg:p-7"
                >
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5"
                    >
                        <h3 class="text-sm font-black text-slate-950">
                            Top leaderboard
                        </h3>
                        <ol
                            v-if="state.leaderboard.length"
                            class="mt-4 space-y-3"
                        >
                            <li
                                v-for="(
                                    participant, index
                                ) in state.leaderboard.slice(0, 5)"
                                :key="participant.id"
                                class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3"
                            >
                                <span class="flex min-w-0 items-center gap-3">
                                    <span
                                        class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-brand-primary text-xs font-black text-white"
                                        >{{ index + 1 }}</span
                                    >
                                    <span
                                        class="min-w-0 truncate text-sm font-bold text-slate-800"
                                        >{{ participant.alias }}</span
                                    >
                                </span>
                                <strong
                                    class="shrink-0 text-sm font-black text-brand-primary"
                                    >{{ participant.score }} poin</strong
                                >
                            </li>
                        </ol>
                        <p
                            v-else
                            class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500"
                        >
                            Belum ada data leaderboard.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-5"
                    >
                        <h3 class="text-sm font-black text-amber-950">
                            Perlu follow-up
                        </h3>
                        <p
                            class="mt-1 text-xs font-semibold leading-relaxed text-amber-800"
                        >
                            Peserta dengan akurasi rendah atau belum menjawab.
                            Cocok untuk pembahasan ulang.
                        </p>
                        <ul
                            v-if="hostNeedsFollowUp.length"
                            class="mt-4 space-y-3"
                        >
                            <li
                                v-for="participant in hostNeedsFollowUp"
                                :key="participant.id"
                                class="flex items-center justify-between gap-3 rounded-2xl bg-white/80 px-4 py-3"
                            >
                                <span
                                    class="min-w-0 truncate text-sm font-bold text-amber-950"
                                    >{{ participant.alias }}</span
                                >
                                <span
                                    class="shrink-0 text-xs font-black text-amber-700"
                                >
                                    {{ participant.correct_count ?? 0 }}/{{
                                        participant.total_answered ?? 0
                                    }}
                                    benar
                                </span>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="mt-4 rounded-2xl bg-white/80 p-4 text-sm font-semibold text-amber-800"
                        >
                            Tidak ada peserta prioritas remedial dari sesi ini.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Host Question Display & Timer Card (Hanya muncul jika live dan TIDAK intermission) -->
            <section
                v-if="
                    state.status === 'live' &&
                    state.question &&
                    !state.intermission
                "
                class="overflow-hidden rounded-2xl border border-brand-secondary/40 bg-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-extrabold uppercase tracking-wider text-brand-primary"
                            >
                                Pertanyaan Berlangsung •
                                {{ state.question.type }}
                            </span>
                            <!-- Badge nomor soal dari total soal -->
                            <span
                                v-if="state.totalQuestions > 0"
                                class="rounded-full bg-brand-primary px-2.5 py-0.5 text-xs font-black text-white"
                            >
                                {{ state.questionIndex }} /
                                {{ state.totalQuestions }}
                            </span>
                        </div>
                        <h2
                            class="mt-1 text-xl font-black text-slate-900 sm:text-2xl"
                        >
                            {{ state.question.prompt }}
                        </h2>
                    </div>
                    <!-- Countdown Timer Ring/Box -->
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-brand-secondary/30 bg-brand-accent px-5 py-3"
                    >
                        <svg
                            class="h-6 w-6 animate-pulse text-brand-primary"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <div class="text-right">
                            <span
                                class="block text-[10px] font-black uppercase text-slate-500"
                                >Sisa Waktu</span
                            >
                            <span
                                class="font-mono text-2xl font-black text-slate-900"
                                :class="{
                                    'animate-bounce text-red-600':
                                        secondsLeft <= 5,
                                }"
                            >
                                {{ secondsLeft }}s
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Options & Correct Answer Reveal -->
                <div
                    v-if="state.question.options"
                    class="mt-5 grid gap-3 sm:grid-cols-2"
                >
                    <div
                        v-for="(option, idx) in state.question.options"
                        :key="idx"
                        class="flex items-center gap-3 rounded-xl border p-4 font-bold transition-all"
                        :class="[
                            secondsLeft === 0 &&
                            option === state.question.correct_answer
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-900 ring-2 ring-emerald-400'
                                : secondsLeft === 0
                                  ? 'border-slate-200 bg-slate-50 text-slate-400 opacity-60'
                                  : 'border-slate-200 bg-white text-slate-800',
                        ]"
                    >
                        <span
                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-xs font-black"
                            :class="[
                                secondsLeft === 0 &&
                                option === state.question.correct_answer
                                    ? 'bg-emerald-500 text-white'
                                    : 'bg-slate-100 text-slate-600',
                            ]"
                        >
                            {{ ['A', 'B', 'C', 'D', 'E'][idx] || idx + 1 }}
                        </span>
                        <span class="flex-1 text-sm sm:text-base">{{
                            option
                        }}</span>
                        <span
                            v-if="
                                secondsLeft === 0 &&
                                option === state.question.correct_answer
                            "
                            class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-extrabold text-emerald-700"
                        >
                            ✓ Jawaban Benar
                        </span>
                    </div>
                </div>

                <!-- Essay / Fill in text correct answer reveal -->
                <div
                    v-else-if="
                        secondsLeft === 0 && state.question.correct_answer
                    "
                    class="mt-5 rounded-xl border border-emerald-300 bg-emerald-50 p-4"
                >
                    <span
                        class="block text-xs font-bold uppercase text-emerald-700"
                        >Jawaban Benar:</span
                    >
                    <span class="text-lg font-black text-emerald-900">{{
                        state.question.correct_answer
                    }}</span>
                </div>

                <!-- Time Up Alert Banner for Host -->
                <div
                    v-if="secondsLeft === 0"
                    class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-amber-900"
                >
                    <div
                        class="flex items-center gap-2.5 text-sm font-extrabold sm:text-base"
                    >
                        <span>⏰ Waktu Pertanyaan Habis!</span>
                        <span
                            class="hidden text-xs font-medium text-amber-700 sm:inline"
                        >
                            {{
                                state.isLastQuestion
                                    ? 'Ini pertanyaan terakhir!'
                                    : 'Lanjutkan atau akhiri sesi.'
                            }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-red-300 bg-white px-4 py-2 text-xs font-extrabold text-red-700 shadow-sm hover:bg-red-50"
                            @click="showEndConfirm = true"
                        >
                            Akhiri Sesi
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-extrabold text-white shadow-sm hover:bg-brand-hover"
                            @click="hostAction('next')"
                        >
                            {{
                                state.isLastQuestion
                                    ? 'Tampilkan Hasil Akhir / Podium →'
                                    : 'Tampilkan Podium Sementara →'
                            }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- INTERMISSION SCREEN HOST -->
            <section
                v-if="state.intermission"
                class="overflow-hidden rounded-2xl border border-brand-secondary/40 bg-white shadow-sm"
            >
                <div
                    class="bg-gradient-to-br from-brand-primary to-[#1a3a8f] px-6 py-8 text-center text-white"
                >
                    <p
                        class="text-xs font-extrabold uppercase tracking-[0.2em] text-white/70"
                    >
                        Podium Sementara
                    </p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight">
                        🏆 Top 3 Saat Ini
                    </h2>
                    <p class="mt-1 text-sm text-white/70">
                        Klik "Lanjut Soal Berikutnya" untuk melanjutkan
                    </p>
                </div>

                <!-- Podium Visual -->
                <div
                    class="flex items-end justify-center gap-3 px-6 pb-4 pt-8 sm:gap-6"
                >
                    <!-- 2nd -->
                    <div
                        v-if="state.leaderboard[1]"
                        class="flex flex-col items-center gap-2"
                        style="animation: podiumRise 0.6s ease 0.15s both"
                    >
                        <div class="relative">
                            <img
                                v-if="state.leaderboard[1].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[1].avatar_key}.png`"
                                :alt="state.leaderboard[1].alias"
                                class="h-14 w-14 rounded-full border-4 border-slate-300 object-cover shadow-lg sm:h-16 sm:w-16"
                            />
                            <span
                                v-else
                                class="flex h-14 w-14 items-center justify-center rounded-full border-4 border-slate-300 bg-slate-100 text-xl font-black text-slate-600 shadow-lg sm:h-16 sm:w-16"
                            >
                                {{
                                    state.leaderboard[1].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}
                            </span>
                            <span
                                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-slate-400 text-xs font-black text-white shadow"
                                >2</span
                            >
                        </div>
                        <div
                            class="w-20 rounded-t-xl bg-slate-200 px-2 py-3 text-center sm:w-24"
                            style="height: 80px"
                        >
                            <p
                                class="truncate text-xs font-extrabold text-slate-700"
                            >
                                {{ state.leaderboard[1].alias }}
                            </p>
                            <p class="mt-1 text-sm font-black text-slate-900">
                                {{ state.leaderboard[1].score }}
                            </p>
                        </div>
                    </div>
                    <!-- 1st -->
                    <div
                        v-if="state.leaderboard[0]"
                        class="flex flex-col items-center gap-2"
                        style="animation: podiumRise 0.5s ease 0s both"
                    >
                        <div class="relative">
                            <span
                                class="absolute -top-5 left-1/2 -translate-x-1/2 text-2xl"
                                >👑</span
                            >
                            <img
                                v-if="state.leaderboard[0].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[0].avatar_key}.png`"
                                :alt="state.leaderboard[0].alias"
                                class="h-20 w-20 rounded-full border-4 border-yellow-400 object-cover shadow-xl ring-4 ring-yellow-200 sm:h-24 sm:w-24"
                            />
                            <span
                                v-else
                                class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-yellow-400 bg-yellow-50 text-2xl font-black text-yellow-700 shadow-xl ring-4 ring-yellow-200 sm:h-24 sm:w-24"
                            >
                                {{
                                    state.leaderboard[0].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}
                            </span>
                            <span
                                class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-xs font-black text-yellow-900 shadow"
                                >1</span
                            >
                        </div>
                        <div
                            class="w-24 rounded-t-xl bg-yellow-400 px-2 py-3 text-center sm:w-28"
                            style="height: 110px"
                        >
                            <p
                                class="truncate text-xs font-extrabold text-yellow-900"
                            >
                                {{ state.leaderboard[0].alias }}
                            </p>
                            <p
                                class="mt-1 text-base font-black text-yellow-950"
                            >
                                {{ state.leaderboard[0].score }}
                            </p>
                        </div>
                    </div>
                    <!-- 3rd -->
                    <div
                        v-if="state.leaderboard[2]"
                        class="flex flex-col items-center gap-2"
                        style="animation: podiumRise 0.6s ease 0.3s both"
                    >
                        <div class="relative">
                            <img
                                v-if="state.leaderboard[2].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[2].avatar_key}.png`"
                                :alt="state.leaderboard[2].alias"
                                class="h-12 w-12 rounded-full border-4 border-amber-600 object-cover shadow-lg sm:h-14 sm:w-14"
                            />
                            <span
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded-full border-4 border-amber-600 bg-amber-50 text-lg font-black text-amber-700 shadow-lg sm:h-14 sm:w-14"
                            >
                                {{
                                    state.leaderboard[2].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}
                            </span>
                            <span
                                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-600 text-xs font-black text-white shadow"
                                >3</span
                            >
                        </div>
                        <div
                            class="w-20 rounded-t-xl bg-amber-200 px-2 py-3 text-center sm:w-24"
                            style="height: 60px"
                        >
                            <p
                                class="truncate text-xs font-extrabold text-amber-900"
                            >
                                {{ state.leaderboard[2].alias }}
                            </p>
                            <p class="mt-1 text-sm font-black text-amber-950">
                                {{ state.leaderboard[2].score }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Full leaderboard list under podium -->
                <ol
                    v-if="state.leaderboard.length > 3"
                    class="divide-y divide-slate-100 px-6 pb-2"
                >
                    <li
                        v-for="(p, idx) in state.leaderboard.slice(3)"
                        :key="p.id"
                        class="flex items-center gap-3 py-3 text-sm"
                    >
                        <span
                            class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-extrabold text-slate-600"
                            >{{ idx + 4 }}</span
                        >
                        <img
                            v-if="p.avatar_key"
                            :src="`/images/photo_profile/${p.avatar_key}.png`"
                            :alt="p.alias"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        />
                        <span
                            v-else
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-xs font-black text-brand-primary"
                            >{{ p.alias.charAt(0).toUpperCase() }}</span
                        >
                        <span
                            class="flex-1 truncate font-semibold text-slate-800"
                            >{{ p.alias }}</span
                        >
                        <strong class="tabular-nums text-[#527A12]">{{
                            p.score
                        }}</strong>
                    </li>
                </ol>

                <!-- CTA Host -->
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-extrabold text-red-700 hover:bg-red-50"
                        @click="showEndConfirm = true"
                    >
                        Akhiri Sesi
                    </button>
                    <!-- Tombol Lanjut Soal hanya muncul jika BUKAN soal terakhir -->
                    <button
                        v-if="!state.isLastQuestion"
                        type="button"
                        class="rounded-lg px-5 py-2.5 text-sm font-extrabold text-white shadow-sm transition-all"
                        :class="
                            intermissionCountdown > 0
                                ? 'cursor-not-allowed bg-slate-400'
                                : 'bg-brand-primary hover:bg-brand-hover'
                        "
                        :disabled="
                            intermissionCountdown > 0 || hostActionProcessing
                        "
                        @click="hostAction('next')"
                    >
                        <span v-if="intermissionCountdown > 0">
                            Lanjut Soal dalam {{ intermissionCountdown }}...
                        </span>
                        <span v-else>Lanjut Soal Berikutnya →</span>
                    </button>
                    <!-- Sinyal Kuis Selesai untuk Host jika sudah soal terakhir -->
                    <button
                        v-else
                        type="button"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700"
                        @click="showEndConfirm = true"
                    >
                        🏆 Kuis Selesai! Klik untuk Lihat Podium Akhir
                    </button>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <article
                    aria-labelledby="participants-title"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4"
                    >
                        <div>
                            <h2
                                id="participants-title"
                                class="font-extrabold text-slate-950"
                            >
                                Peserta
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Peserta yang sudah bergabung.
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold tabular-nums text-[#527A12]"
                        >
                            {{ state.participants.length }}
                        </span>
                    </div>
                    <ul
                        v-if="state.participants.length"
                        class="divide-y divide-slate-100"
                    >
                        <li
                            v-for="participant in state.participants"
                            :key="participant.id"
                            class="flex items-center justify-between gap-4 px-5 py-3 text-sm"
                        >
                            <span class="flex items-center gap-3">
                                <img
                                    v-if="participant.avatar_key"
                                    :src="`/images/photo_profile/${participant.avatar_key}.png`"
                                    :alt="participant.alias"
                                    class="h-8 w-8 shrink-0 rounded-full border border-slate-200 object-cover"
                                />
                                <span
                                    v-else
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-xs font-black text-brand-primary"
                                >
                                    {{
                                        participant.alias
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </span>
                                <span class="font-semibold text-slate-800">{{
                                    participant.alias
                                }}</span>
                            </span>
                            <strong class="tabular-nums text-slate-950"
                                >{{ participant.score }} poin</strong
                            >
                        </li>
                    </ul>
                    <p
                        v-else
                        class="px-5 py-10 text-center text-sm text-slate-500"
                    >
                        Belum ada peserta yang bergabung.
                    </p>
                </article>

                <article
                    aria-labelledby="leaderboard-title"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2
                            id="leaderboard-title"
                            class="font-extrabold text-slate-950"
                        >
                            Leaderboard
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Peringkat berdasarkan poin terkini.
                        </p>
                    </div>
                    <ol
                        v-if="state.leaderboard.length"
                        class="divide-y divide-slate-100"
                    >
                        <li
                            v-for="(participant, index) in state.leaderboard"
                            :key="participant.id"
                            class="flex items-center gap-3 px-5 py-3 text-sm"
                        >
                            <span
                                class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-extrabold tabular-nums text-slate-600"
                            >
                                {{ index + 1 }}
                            </span>
                            <img
                                v-if="participant.avatar_key"
                                :src="`/images/photo_profile/${participant.avatar_key}.png`"
                                :alt="participant.alias"
                                class="h-8 w-8 shrink-0 rounded-full border border-slate-200 object-cover"
                            />
                            <span
                                v-else
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-xs font-black text-brand-primary"
                            >
                                {{ participant.alias.charAt(0).toUpperCase() }}
                            </span>
                            <span
                                class="min-w-0 flex-1 truncate font-semibold text-slate-800"
                                >{{ participant.alias }}</span
                            >
                            <strong class="tabular-nums text-[#527A12]"
                                >{{ participant.score }} poin</strong
                            >
                        </li>
                    </ol>
                    <p
                        v-else
                        class="px-5 py-10 text-center text-sm text-slate-500"
                    >
                        Peringkat akan muncul setelah kuis dimulai.
                    </p>
                </article>
            </section>
        </main>

        <dialog
            ref="hostQrDialog"
            aria-labelledby="host-qr-title"
            aria-describedby="host-qr-description"
            class="qr-dialog m-auto w-[calc(100%-2rem)] max-w-sm overflow-hidden rounded-2xl border-0 bg-white p-0 text-slate-950 shadow-2xl backdrop:bg-slate-950/60 backdrop:backdrop-blur-sm"
            @click.self="closeHostQr"
            @close="hostQrVisible = false"
        >
            <div class="relative p-6 pt-14 text-center sm:p-7 sm:pt-14">
                <button
                    type="button"
                    class="absolute right-3 top-3 grid h-11 w-11 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                    aria-label="Tutup QR code"
                    @click="closeHostQr"
                >
                    <svg
                        aria-hidden="true"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>

                <p
                    class="text-xs font-bold uppercase tracking-[0.16em] text-[#527A12]"
                >
                    Gabung sesi
                </p>
                <h2 id="host-qr-title" class="mt-2 text-2xl font-extrabold">
                    Scan QR code
                </h2>
                <p id="host-qr-description" class="mt-2 text-sm text-slate-600">
                    Arahkan kamera peserta ke kode berikut, lalu masukkan PIN
                    sesi.
                </p>

                <div
                    class="mx-auto mt-5 w-fit rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                >
                    <canvas ref="hostQrCanvas" class="max-w-full" />
                </div>

                <div class="mt-5 rounded-lg bg-brand-secondary/15 px-4 py-3">
                    <p class="text-xs font-bold uppercase text-[#527A12]">
                        PIN peserta
                    </p>
                    <p
                        class="mt-1 font-mono text-2xl font-extrabold tracking-[0.2em] text-slate-950"
                    >
                        {{ state.pin }}
                    </p>
                </div>
            </div>
        </dialog>

        <!-- Host End Session Confirmation Modal -->
        <Modal
            :show="showEndConfirm"
            title="Akhiri Sesi Live Quiz"
            @close="showEndConfirm = false"
        >
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-600"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">
                            Konfirmasi Akhiri Sesi
                        </h3>
                        <p class="text-sm text-slate-500">
                            Semua peserta akan diarahkan ke layar hasil akhir
                            berisi ranking, skor, dan akurasi.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        @click="showEndConfirm = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-black text-white hover:bg-red-700"
                        :disabled="hostActionProcessing"
                        @click="confirmEndSession"
                    >
                        Ya, Akhiri Sesi Sekarang
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
    <main v-else class="min-h-screen bg-[#f4fbfa] px-4 py-8">
        <div class="mx-auto max-w-xl">
            <Link href="/join" class="text-sm font-extrabold text-[#527A12]"
                >Kuesify Live</Link
            >
            <button
                class="ml-4 text-sm font-bold text-[#527A12] underline"
                @click="loadQr"
            >
                QR Reconnect
            </button>
            <div
                class="mt-4 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold"
                :class="connectionStatus.className"
                aria-live="polite"
            >
                <span
                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full"
                    :class="connectionStatus.dotClass"
                    aria-hidden="true"
                ></span>
                <div>
                    <p class="font-black">{{ connectionStatus.label }}</p>
                    <p class="mt-0.5 text-xs opacity-80">
                        {{ connectionStatus.detail }}
                    </p>
                </div>
            </div>
            <!-- Connection warning banner -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="connectionWarning"
                    class="mt-4 flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900"
                    role="alert"
                >
                    <svg
                        class="h-5 w-5 shrink-0 text-amber-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M12 9v4m0 4h.01" />
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                        />
                    </svg>
                    <div>
                        <p>Koneksi tidak stabil atau lambat.</p>
                        <p class="mt-0.5 text-xs text-amber-800">
                            Kalau jawaban belum tersimpan, tunggu tersambung
                            lagi lalu kirim ulang.
                        </p>
                        <button
                            class="mt-1 text-xs font-black text-amber-700 underline"
                            @click="loadQr"
                        >
                            Gunakan QR reconnect untuk pindah perangkat atau
                            refresh halaman ini.
                        </button>
                    </div>
                </div>
            </transition>
            <div
                v-if="showQr"
                class="mt-3 inline-block rounded-2xl bg-white p-4 shadow-sm"
            >
                <canvas ref="qrCanvas" />
            </div>
            <section
                class="mt-5 rounded-3xl bg-brand-secondary p-6 text-[#102449]"
            >
                <p
                    class="text-sm font-bold uppercase tracking-wide text-[#102449]/70"
                >
                    {{ state.status }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">{{ state.title }}</h1>
                <p
                    v-if="state.status === 'lobby'"
                    class="mt-3 text-[#102449]/80"
                >
                    Tunggu host memulai sesi.
                </p>
                <p
                    v-else-if="state.status === 'ended'"
                    class="mt-3 text-[#102449]/80"
                >
                    Sesi selesai. Lihat podium di bawah.
                </p>
                <p
                    v-else-if="state.intermission"
                    class="mt-3 text-[#102449]/80"
                >
                    Tunggu host melanjutkan ke soal berikutnya...
                </p>
                <p v-else class="mt-4 text-5xl font-extrabold">
                    {{ secondsLeft }}<span class="ml-2 text-lg">detik</span>
                </p>
            </section>

            <!-- INTERMISSION SCREEN PESERTA -->
            <section
                v-if="state.intermission"
                class="mt-5 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary to-[#1a3a8f] text-white shadow-xl"
            >
                <div class="px-5 pb-3 pt-8 text-center">
                    <p
                        class="text-xs font-extrabold uppercase tracking-[0.2em] text-white/60"
                    >
                        Podium Sementara
                    </p>
                    <h2 class="mt-1 text-2xl font-black">🏆 Top 3 Saat Ini</h2>
                </div>

                <!-- Podium visual peserta -->
                <div class="flex items-end justify-center gap-3 px-4 pb-4 pt-6">
                    <!-- 2nd -->
                    <div
                        v-if="state.leaderboard[1]"
                        class="flex flex-col items-center gap-1.5"
                        style="animation: podiumRise 0.6s ease 0.15s both"
                    >
                        <div class="relative">
                            <img
                                v-if="state.leaderboard[1].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[1].avatar_key}.png`"
                                :alt="state.leaderboard[1].alias"
                                class="h-14 w-14 rounded-full border-4 border-white/40 object-cover shadow-lg"
                            />
                            <span
                                v-else
                                class="flex h-14 w-14 items-center justify-center rounded-full border-4 border-white/40 bg-white/20 text-lg font-black text-white shadow-lg"
                                >{{
                                    state.leaderboard[1].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}</span
                            >
                            <span
                                class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-[10px] font-black text-slate-800 shadow"
                                >2</span
                            >
                        </div>
                        <div
                            class="w-20 rounded-t-xl bg-white/20 px-2 py-2 text-center"
                            style="height: 70px"
                        >
                            <p
                                class="truncate text-[11px] font-extrabold text-white"
                            >
                                {{ state.leaderboard[1].alias }}
                            </p>
                            <p class="mt-0.5 text-sm font-black text-white">
                                {{ state.leaderboard[1].score }}
                            </p>
                        </div>
                    </div>
                    <!-- 1st -->
                    <div
                        v-if="state.leaderboard[0]"
                        class="flex flex-col items-center gap-1.5"
                        style="animation: podiumRise 0.5s ease 0s both"
                    >
                        <div class="relative">
                            <span
                                class="absolute -top-5 left-1/2 -translate-x-1/2 text-2xl"
                                >👑</span
                            >
                            <img
                                v-if="state.leaderboard[0].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[0].avatar_key}.png`"
                                :alt="state.leaderboard[0].alias"
                                class="h-20 w-20 rounded-full border-4 border-yellow-400 object-cover shadow-xl ring-4 ring-yellow-300/50"
                            />
                            <span
                                v-else
                                class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-yellow-400 bg-yellow-300/20 text-2xl font-black text-yellow-300 shadow-xl ring-4 ring-yellow-300/50"
                                >{{
                                    state.leaderboard[0].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}</span
                            >
                            <span
                                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-black text-yellow-900 shadow"
                                >1</span
                            >
                        </div>
                        <div
                            class="w-24 rounded-t-xl border-t-2 border-yellow-400 bg-yellow-400/30 px-2 py-2 text-center"
                            style="height: 95px"
                        >
                            <p
                                class="truncate text-xs font-extrabold text-yellow-200"
                            >
                                {{ state.leaderboard[0].alias }}
                            </p>
                            <p class="mt-0.5 text-base font-black text-white">
                                {{ state.leaderboard[0].score }}
                            </p>
                        </div>
                    </div>
                    <!-- 3rd -->
                    <div
                        v-if="state.leaderboard[2]"
                        class="flex flex-col items-center gap-1.5"
                        style="animation: podiumRise 0.6s ease 0.3s both"
                    >
                        <div class="relative">
                            <img
                                v-if="state.leaderboard[2].avatar_key"
                                :src="`/images/photo_profile/${state.leaderboard[2].avatar_key}.png`"
                                :alt="state.leaderboard[2].alias"
                                class="h-12 w-12 rounded-full border-4 border-amber-400 object-cover shadow-lg"
                            />
                            <span
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded-full border-4 border-amber-400 bg-amber-300/20 text-base font-black text-amber-300 shadow-lg"
                                >{{
                                    state.leaderboard[2].alias
                                        .charAt(0)
                                        .toUpperCase()
                                }}</span
                            >
                            <span
                                class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-white shadow"
                                >3</span
                            >
                        </div>
                        <div
                            class="w-20 rounded-t-xl bg-white/10 px-2 py-2 text-center"
                            style="height: 55px"
                        >
                            <p
                                class="truncate text-[11px] font-extrabold text-amber-200"
                            >
                                {{ state.leaderboard[2].alias }}
                            </p>
                            <p class="mt-0.5 text-sm font-black text-white">
                                {{ state.leaderboard[2].score }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-5 text-center text-xs text-white/50">
                    Tunggu host melanjutkan ke soal berikutnya...
                </div>
            </section>

            <section
                v-if="
                    state.question &&
                    state.status === 'live' &&
                    !state.intermission
                "
                class="mt-5 rounded-3xl bg-white p-6 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em] text-[#527A12]"
                >
                    {{ state.question.type }}
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">
                    {{ state.question.prompt }}
                </h2>

                <!-- Banner waktu habis untuk peserta -->
                <div
                    v-if="secondsLeft === 0"
                    class="mt-5 flex items-center gap-3 rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3"
                >
                    <span class="text-xl">⏰</span>
                    <div>
                        <p class="text-sm font-extrabold text-amber-900">
                            Waktu habis!
                        </p>
                        <p class="text-xs font-medium text-amber-700">
                            Tunggu host melanjutkan ke soal berikutnya.
                        </p>
                    </div>
                </div>

                <!-- Banner Status Jawaban (Benar/Salah/Terkunci) -->
                <div
                    v-else-if="hasAnswered && answerResult"
                    class="animate-fade-in mt-5 flex items-start gap-3 rounded-2xl px-4 py-3 transition-all"
                    :class="
                        answerResult.is_correct
                            ? 'border border-emerald-500/30 bg-emerald-50'
                            : 'border border-red-400/30 bg-red-50'
                    "
                >
                    <!-- Icon benar/salah -->
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-black text-white shadow-sm"
                        :class="
                            answerResult.is_correct
                                ? 'bg-emerald-500'
                                : 'bg-red-500'
                        "
                    >
                        {{ answerResult.is_correct ? '✓' : '✕' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-extrabold"
                            :class="
                                answerResult.is_correct
                                    ? 'text-emerald-950'
                                    : 'text-red-950'
                            "
                        >
                            {{
                                answerResult.is_correct
                                    ? 'Jawaban Benar! 🎉'
                                    : 'Jawaban Salah 😔'
                            }}
                        </p>
                        <p
                            v-if="!answerResult.is_correct"
                            class="mt-0.5 text-xs font-medium text-red-700"
                        >
                            Jawaban yang benar:
                            <strong>{{ answerResult.correct_answer }}</strong>
                        </p>
                        <p
                            v-if="
                                answerResult.is_correct &&
                                answerResult.points_awarded > 0
                            "
                            class="mt-0.5 text-xs font-medium text-emerald-700"
                        >
                            +{{ answerResult.points_awarded }} poin
                        </p>
                    </div>
                </div>
                <!-- Banner terkunci (jawaban belum balik dari server) -->
                <div
                    v-else-if="hasAnswered"
                    class="animate-fade-in mt-5 flex items-center gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-50 px-4 py-3 transition-all"
                >
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs font-black text-white"
                        >✓</span
                    >
                    <div>
                        <p class="text-sm font-extrabold text-emerald-950">
                            Jawabanmu sudah terkunci!
                        </p>
                        <p class="text-xs font-medium text-emerald-700">
                            Mengirim ke server. Tunggu sampai hasil muncul,
                            jangan tutup halaman.
                        </p>
                    </div>
                </div>

                <div v-if="state.question.options" class="mt-5 grid gap-3">
                    <button
                        v-for="option in state.question.options"
                        :key="option"
                        class="relative flex min-h-14 items-center justify-between rounded-2xl border px-4 text-left font-bold transition-all"
                        :class="[
                            secondsLeft === 0
                                ? answer.answer === option
                                    ? 'cursor-not-allowed border-brand-secondary/40 bg-brand-secondary/10 text-slate-700'
                                    : 'cursor-not-allowed border-brand-secondary/20 bg-slate-50 text-slate-400'
                                : hasAnswered && answerResult
                                  ? answer.answer === option
                                      ? answerResult.is_correct
                                          ? 'cursor-not-allowed border-emerald-500 bg-emerald-50 text-emerald-950 shadow-sm ring-2 ring-emerald-500/50'
                                          : 'cursor-not-allowed border-red-400 bg-red-50 text-red-950 shadow-sm ring-2 ring-red-400/50'
                                      : 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400 opacity-60'
                                  : hasAnswered
                                    ? answer.answer === option
                                        ? 'cursor-not-allowed border-emerald-500 bg-emerald-50 text-emerald-950 shadow-sm ring-2 ring-emerald-500/50'
                                        : 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400 opacity-60'
                                    : 'border-brand-secondary/30 text-slate-800 hover:border-brand-secondary hover:bg-brand-secondary/10',
                        ]"
                        :disabled="
                            answer.processing ||
                            secondsLeft === 0 ||
                            hasAnswered
                        "
                        @click="choose(option)"
                    >
                        <span>{{ option }}</span>
                        <!-- Badge Dipilih: centang hijau atau silang merah -->
                        <span
                            v-if="answer.answer === option && hasAnswered"
                            class="ml-2 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-black text-white shadow-sm"
                            :class="
                                answerResult
                                    ? answerResult.is_correct
                                        ? 'bg-emerald-500'
                                        : 'bg-red-500'
                                    : 'bg-emerald-500'
                            "
                        >
                            {{
                                answerResult && !answerResult.is_correct
                                    ? '✕'
                                    : '✓'
                            }}
                        </span>
                    </button>
                </div>
                <form
                    v-else
                    class="mt-6 space-y-3"
                    @submit.prevent="choose(answer.answer)"
                >
                    <input
                        v-model="answer.answer"
                        class="min-h-12 w-full rounded-xl border-slate-200 text-slate-900 focus:border-brand-secondary focus:ring-brand-secondary disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Ketik jawaban"
                        :disabled="secondsLeft === 0 || hasAnswered"
                    /><button
                        class="min-h-12 w-full rounded-xl bg-brand-secondary text-sm font-extrabold text-[#102449] disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="secondsLeft === 0 || hasAnswered"
                    >
                        Kirim jawaban
                    </button>
                </form>
            </section>
            <!-- Ringkasan Performa Peserta (Chart.js Circular Donut & Stat Cards) saat Sesi Selesai -->
            <section
                v-if="state.status === 'ended' && !isHost"
                class="mt-5 overflow-hidden rounded-3xl border border-brand-primary/15 bg-white shadow-figma-sm"
            >
                <div class="bg-brand-primary p-6 text-white">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-black"
                    >
                        Sesi selesai
                    </span>
                    <h2 class="mt-3 text-3xl font-black">
                        Hasil akhir kamu sudah keluar.
                    </h2>
                    <p class="mt-2 text-sm font-semibold text-white/80">
                        {{ completionText }} Cek ranking, skor, dan akurasi
                        sebelum lanjut.
                    </p>
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <div
                            class="bg-white/12 rounded-2xl p-3 text-center ring-1 ring-white/15"
                        >
                            <p
                                class="text-[11px] font-bold uppercase tracking-wide text-white/70"
                            >
                                Rank
                            </p>
                            <p class="mt-1 text-2xl font-black">
                                #{{ myRank ?? '-' }}
                            </p>
                        </div>
                        <div
                            class="bg-white/12 rounded-2xl p-3 text-center ring-1 ring-white/15"
                        >
                            <p
                                class="text-[11px] font-bold uppercase tracking-wide text-white/70"
                            >
                                Skor
                            </p>
                            <p class="mt-1 text-2xl font-black">
                                {{
                                    myLeaderboardEntry?.score ??
                                    state.currentParticipant?.score ??
                                    0
                                }}
                            </p>
                        </div>
                        <div
                            class="bg-white/12 rounded-2xl p-3 text-center ring-1 ring-white/15"
                        >
                            <p
                                class="text-[11px] font-bold uppercase tracking-wide text-white/70"
                            >
                                Akurasi
                            </p>
                            <p class="mt-1 text-2xl font-black">
                                {{ accuracyPercent }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div
                        class="rounded-2xl border border-brand-secondary/40 bg-brand-accent p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-secondary text-lg font-black text-brand-dark"
                            >
                                ✓
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-brand-dark">
                                    Jawaban kamu tersimpan.
                                </h3>
                                <p
                                    class="mt-1 text-xs font-semibold leading-relaxed text-brand-dark/70"
                                >
                                    Hasil ini dihitung dari sesi live.
                                    Pembahasan dan tindak lanjut tetap menunggu
                                    arahan guru.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Donut Chart & Center Metric -->
                    <div class="relative my-6 flex items-center justify-center">
                        <div class="relative h-48 w-48">
                            <canvas ref="donutCanvas" />
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center text-center"
                            >
                                <span
                                    class="text-3xl font-black text-slate-900"
                                >
                                    {{ accuracyPercent }}%
                                </span>
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Akurasi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Cards Breakdown (Benar / Salah / Total) -->
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            class="rounded-2xl border border-emerald-200/80 bg-emerald-50/70 p-3.5 text-center"
                        >
                            <div
                                class="mx-auto flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-xs font-black text-white"
                            >
                                ✓
                            </div>
                            <p class="mt-2 text-xl font-black text-emerald-950">
                                {{ participantStats.correctCount }}
                            </p>
                            <p class="text-[11px] font-bold text-emerald-700">
                                Benar
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-rose-200/80 bg-rose-50/70 p-3.5 text-center"
                        >
                            <div
                                class="mx-auto flex h-7 w-7 items-center justify-center rounded-full bg-rose-500 text-xs font-black text-white"
                            >
                                ✕
                            </div>
                            <p class="mt-2 text-xl font-black text-rose-950">
                                {{ participantStats.wrongCount }}
                            </p>
                            <p class="text-[11px] font-bold text-rose-700">
                                Salah
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50 p-3.5 text-center"
                        >
                            <div
                                class="mx-auto flex h-7 w-7 items-center justify-center rounded-full bg-brand-primary text-xs text-white"
                            >
                                {{ state.totalQuestions }}
                            </div>
                            <p class="mt-2 text-xl font-black text-slate-900">
                                {{ participantStats.totalAnswered }}
                            </p>
                            <p class="text-[11px] font-bold text-slate-500">
                                Dijawab
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <a
                            href="/join"
                            class="flex min-h-12 items-center justify-center rounded-2xl bg-brand-primary px-4 text-sm font-black text-white transition hover:bg-brand-hover active:scale-95"
                        >
                            Ikut live quiz lain
                        </a>
                        <a
                            href="/"
                            class="flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50 active:scale-95"
                        >
                            Kembali ke beranda
                        </a>
                    </div>
                </div>
            </section>

            <!-- Podium sementara hanya tampil saat intermission atau sesi selesai -->
            <section
                v-if="state.intermission || state.status === 'ended'"
                class="mt-5 rounded-2xl bg-white p-5 shadow-sm"
            >
                <h3 class="font-extrabold text-slate-900">
                    {{
                        state.status === 'ended'
                            ? '🏁 Hasil Akhir'
                            : '📊 Podium Sementara'
                    }}
                </h3>
                <ol class="mt-4 space-y-3">
                    <li
                        v-for="(participant, index) in state.leaderboard.slice(
                            0,
                            3,
                        )"
                        :key="participant.id"
                        class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"
                    >
                        <span
                            class="flex items-center gap-2.5 font-bold text-slate-800"
                        >
                            <span>{{ index + 1 }}.</span>
                            <img
                                v-if="participant.avatar_key"
                                :src="`/images/photo_profile/${participant.avatar_key}.png`"
                                :alt="participant.alias"
                                class="h-7 w-7 shrink-0 rounded-full border border-slate-200 object-cover"
                            />
                            <span
                                v-else
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 text-xs font-black text-brand-primary"
                            >
                                {{ participant.alias.charAt(0).toUpperCase() }}
                            </span>
                            <span>{{ participant.alias }}</span>
                        </span>
                        <strong class="text-[#527A12]">{{
                            participant.score
                        }}</strong>
                    </li>
                </ol>
            </section>
        </div>
    </main>
</template>

<style scoped>
.qr-dialog[open] {
    animation: qr-dialog-enter 220ms cubic-bezier(0.16, 1, 0.3, 1);
}

.qr-dialog[open]::backdrop {
    animation: qr-backdrop-enter 180ms ease-out;
}

@keyframes qr-dialog-enter {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.96);
    }
}

@keyframes qr-backdrop-enter {
    from {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .qr-dialog[open],
    .qr-dialog[open]::backdrop {
        animation: none;
    }
}

@keyframes podiumRise {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
