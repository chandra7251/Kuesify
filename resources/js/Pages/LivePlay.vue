<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import QRCode from 'qrcode';
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';

interface LiveState {
    id: number;
    title: string;
    status: string;
    pin: string;
    broadcastToken: string;
    lobbyLocked: boolean;
    questionDuration: number;
    questionStartedAt: string | null;
    question: {
        id: number;
        type: string;
        prompt: string;
        options: string[] | null;
    } | null;
    participants: { id: number; alias: string; score: number }[];
    leaderboard: { id: number; alias: string; score: number }[];
}
const props = defineProps<{ session: LiveState; isHost: boolean }>();
const state = ref<LiveState>(props.session);
const answer = useForm({ answer: '' });
const now = ref(Date.now());
const clock = window.setInterval(() => {
    now.value = Date.now();
}, 1000);
onBeforeUnmount(() => window.clearInterval(clock));
useEchoPublic<LiveState>(
    `live-session.${props.session.broadcastToken}`,
    '.live.session.updated',
    (payload) => {
        state.value = payload;
        answer.reset();
    },
);
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
    answer.answer = value;
    answer.post(route('live-sessions.answers.store', state.value.id));
}
function hostAction(name: 'lock' | 'start' | 'next' | 'end'): void {
    useForm({}).post(route(`live-sessions.${name}`, state.value.id));
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
                                class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700"
                            >
                                Sesi live
                            </p>
                            <span
                                class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold capitalize text-teal-800"
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
                        class="rounded-lg border border-teal-200 bg-teal-50 p-4 lg:min-w-72"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-teal-700"
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
                            class="mt-4 min-h-11 rounded-lg border border-teal-700 bg-white px-4 text-sm font-bold text-teal-800 transition hover:bg-teal-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
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
                        class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                        @click="hostAction('lock')"
                    >
                        {{
                            state.lobbyLocked ? 'Lobby terkunci' : 'Kunci lobby'
                        }}
                    </button>
                    <button
                        v-if="state.status === 'lobby'"
                        type="button"
                        class="min-h-11 rounded-lg bg-[#3451b5] px-5 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5]"
                        @click="hostAction('start')"
                    >
                        Mulai kuis
                    </button>
                    <button
                        v-if="state.status === 'live'"
                        type="button"
                        class="min-h-11 rounded-lg bg-[#3451b5] px-5 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5]"
                        @click="hostAction('next')"
                    >
                        Soal berikutnya
                    </button>
                    <button
                        type="button"
                        class="min-h-11 rounded-lg px-3 text-sm font-bold text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                        @click="hostAction('end')"
                    >
                        Akhiri sesi
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
                            class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold tabular-nums text-teal-800"
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
                            class="flex items-center justify-between gap-4 px-5 py-4 text-sm"
                        >
                            <span class="font-semibold text-slate-800">{{
                                participant.alias
                            }}</span>
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
                            class="flex items-center gap-3 px-5 py-4 text-sm"
                        >
                            <span
                                class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-extrabold tabular-nums text-slate-600"
                            >
                                {{ index + 1 }}
                            </span>
                            <span
                                class="min-w-0 flex-1 truncate font-semibold text-slate-800"
                                >{{ participant.alias }}</span
                            >
                            <strong class="tabular-nums text-teal-800"
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
                    class="absolute right-3 top-3 grid h-11 w-11 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
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
                    class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700"
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

                <div class="mt-5 rounded-lg bg-teal-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase text-teal-700">
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
    </AuthenticatedLayout>
    <main v-else class="min-h-screen bg-[#f4fbfa] px-4 py-8">
        <div class="mx-auto max-w-xl">
            <Link href="/join" class="text-sm font-extrabold text-teal-700"
                >Kuesify Live</Link
            >
            <button
                class="ml-4 text-sm font-bold text-teal-700 underline"
                @click="loadQr"
            >
                QR Reconnect
            </button>
            <div
                v-if="showQr"
                class="mt-3 inline-block rounded-2xl bg-white p-4 shadow-sm"
            >
                <canvas ref="qrCanvas" />
            </div>
            <section class="mt-5 rounded-3xl bg-teal-800 p-6 text-white">
                <p
                    class="text-sm font-bold uppercase tracking-wide text-teal-100"
                >
                    {{ state.status }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">{{ state.title }}</h1>
                <p v-if="state.status === 'lobby'" class="mt-3 text-teal-50">
                    Tunggu host memulai sesi.
                </p>
                <p
                    v-else-if="state.status === 'ended'"
                    class="mt-3 text-teal-50"
                >
                    Sesi selesai. Lihat podium di bawah.
                </p>
                <p v-else class="mt-4 text-5xl font-extrabold">
                    {{ secondsLeft }}<span class="ml-2 text-lg">detik</span>
                </p>
            </section>
            <section
                v-if="state.question && state.status === 'live'"
                class="mt-5 rounded-3xl bg-white p-6 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700"
                >
                    {{ state.question.type }}
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">
                    {{ state.question.prompt }}
                </h2>
                <div v-if="state.question.options" class="mt-6 grid gap-3">
                    <button
                        v-for="option in state.question.options"
                        :key="option"
                        class="min-h-14 rounded-2xl border border-teal-100 px-4 text-left font-bold text-slate-800 transition hover:border-teal-500 hover:bg-teal-50"
                        :disabled="answer.processing || secondsLeft === 0"
                        @click="choose(option)"
                    >
                        {{ option }}
                    </button>
                </div>
                <form
                    v-else
                    class="mt-6 space-y-3"
                    @submit.prevent="choose(answer.answer)"
                >
                    <input
                        v-model="answer.answer"
                        class="min-h-12 w-full rounded-xl border-slate-200 text-slate-900 focus:border-teal-600 focus:ring-teal-600"
                        placeholder="Ketik jawaban"
                    /><button
                        class="min-h-12 w-full rounded-xl bg-teal-700 text-sm font-extrabold text-white"
                    >
                        Kirim jawaban
                    </button>
                </form>
            </section>
            <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="font-extrabold text-slate-900">Podium sementara</h3>
                <ol class="mt-4 space-y-3">
                    <li
                        v-for="(participant, index) in state.leaderboard.slice(
                            0,
                            3,
                        )"
                        :key="participant.id"
                        class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"
                    >
                        <span class="font-bold text-slate-800"
                            >{{ index + 1 }}. {{ participant.alias }}</span
                        ><strong class="text-teal-800">{{
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
</style>
