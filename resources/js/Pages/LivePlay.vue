<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import QRCode from 'qrcode';
import { computed, onBeforeUnmount, ref } from 'vue';

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
const hostQrCanvas = ref<HTMLCanvasElement | null>(null);
const hostQrVisible = ref(false);
async function toggleHostQr(): Promise<void> {
    hostQrVisible.value = !hostQrVisible.value;
    if (hostQrVisible.value) {
        await new Promise<void>((resolve) => setTimeout(resolve, 50));
        if (hostQrCanvas.value)
            QRCode.toCanvas(hostQrCanvas.value, hostJoinUrl, { width: 180 });
    }
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
    <AuthenticatedLayout v-if="isHost"
        ><template #header
            ><h2 class="text-xl font-extrabold text-slate-950">
                Host: {{ state.title }}
            </h2></template
        >
        <main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6">
            <section class="rounded-3xl bg-orange-700 p-6 text-white">
                <p class="text-sm font-bold">
                    PIN
                    <span class="ml-2 text-2xl tracking-[0.28em]">{{
                        state.pin
                    }}</span>
                </p>
                <div class="mt-3 flex items-start gap-4">
                    <div>
                        <button
                            class="rounded-lg bg-white/20 px-3 py-2 text-xs font-extrabold"
                            @click="toggleHostQr"
                        >
                            {{
                                hostQrVisible ? 'Tutup QR' : 'Tampilkan QR Join'
                            }}
                        </button>
                        <div
                            v-if="hostQrVisible"
                            class="mt-3 rounded-2xl bg-white p-2"
                        >
                            <canvas ref="hostQrCanvas" />
                        </div>
                    </div>
                </div>
                <h1 class="mt-4 text-3xl font-extrabold">{{ state.status }}</h1>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button
                        v-if="state.status === 'lobby'"
                        class="min-h-11 rounded-xl bg-white px-4 text-sm font-extrabold text-orange-800"
                        @click="hostAction('lock')"
                    >
                        {{
                            state.lobbyLocked ? 'Lobby terkunci' : 'Kunci lobby'
                        }}</button
                    ><button
                        v-if="state.status === 'lobby'"
                        class="min-h-11 rounded-xl bg-amber-400 px-4 text-sm font-extrabold text-amber-950"
                        @click="hostAction('start')"
                    >
                        Mulai</button
                    ><button
                        v-if="state.status === 'live'"
                        class="min-h-11 rounded-xl bg-white px-4 text-sm font-extrabold text-orange-800"
                        @click="hostAction('next')"
                    >
                        Soal berikut</button
                    ><button
                        v-if="state.status !== 'ended'"
                        class="min-h-11 rounded-xl border border-orange-200 px-4 text-sm font-extrabold"
                        @click="hostAction('end')"
                    >
                        Akhiri
                    </button>
                </div>
            </section>
            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="font-extrabold">Peserta</h3>
                    <p
                        v-for="participant in state.participants"
                        :key="participant.id"
                        class="mt-3 flex justify-between text-sm"
                    >
                        <span>{{ participant.alias }}</span
                        ><strong>{{ participant.score }}</strong>
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="font-extrabold">Leaderboard</h3>
                    <p
                        v-for="(participant, index) in state.leaderboard"
                        :key="participant.id"
                        class="mt-3 flex justify-between text-sm"
                    >
                        <span>{{ index + 1 }}. {{ participant.alias }}</span
                        ><strong>{{ participant.score }}</strong>
                    </p>
                </div>
            </section>
        </main></AuthenticatedLayout
    >
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
