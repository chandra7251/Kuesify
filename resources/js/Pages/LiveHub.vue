<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    sessions: {
        data: {
            id: number;
            pin: string;
            status: string;
            lobby_locked: boolean;
            quiz: { title: string };
            participants: unknown[];
        }[];
    };
    quizzes: { id: number; title: string }[];
}>();

const form = useForm({
    quiz_id: '',
    question_duration: 30,
    speed_multiplier: 10,
});

const lobbyCount = computed(
    () => props.sessions.data.filter((s) => s.status === 'lobby').length,
);

const liveCount = computed(
    () => props.sessions.data.filter((s) => s.status === 'live').length,
);

const endedCount = computed(
    () => props.sessions.data.filter((s) => s.status === 'ended').length,
);

const totalParticipantsCount = computed(() => {
    return props.sessions.data.reduce((total, session) => {
        return (
            total +
            (Array.isArray(session.participants)
                ? session.participants.length
                : 0)
        );
    }, 0);
});

function openSession(): void {
    form.post(route('live-sessions.store'));
}
</script>

<template>
    <Head title="Live Quiz" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <!-- 3 Stat Cards (Persis dengan Card Types, Categories, Tags Question Bank) -->
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Card 1: Status Sesi -->
                <article
                    class="min-h-52 rounded-xl border border-t-4 border-slate-200 border-t-[#2dd4bf] bg-white p-5 shadow-[0_2px_6px_rgba(15,23,42,0.08)]"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            STATUS SESI
                        </p>
                        <span
                            class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold tabular-nums text-teal-800"
                        >
                            {{ sessions.data.length }}
                        </span>
                    </div>

                    <div
                        v-if="sessions.data.length"
                        class="mt-3 space-y-2 text-xs text-slate-700"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Lobby</span
                            >
                            <span class="font-bold text-slate-900">{{
                                lobbyCount
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Sedang berlangsung</span
                            >
                            <span class="font-bold text-slate-900">{{
                                liveCount
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Selesai</span
                            >
                            <span class="font-bold text-slate-900">{{
                                endedCount
                            }}</span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-slate-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-slate-500"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Card 2: Kuis Published -->
                <article
                    class="min-h-52 rounded-xl border border-t-4 border-slate-200 border-t-[#2dd4bf] bg-white p-5 shadow-[0_2px_6px_rgba(15,23,42,0.08)]"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            KUIS PUBLISHED
                        </p>
                        <span
                            class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold tabular-nums text-teal-800"
                        >
                            {{ quizzes.length }}
                        </span>
                    </div>

                    <div
                        v-if="quizzes.length"
                        class="mt-3 space-y-2 text-xs text-slate-700"
                    >
                        <div
                            v-for="quiz in quizzes.slice(0, 3)"
                            :key="quiz.id"
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span
                                class="truncate pr-2 font-medium text-slate-700"
                                >{{ quiz.title }}</span
                            >
                            <span
                                class="shrink-0 rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-bold text-teal-800"
                            >
                                published
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-slate-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-slate-500"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Card 3: Peserta Terdaftar -->
                <article
                    class="min-h-52 rounded-xl border border-t-4 border-slate-200 border-t-[#2dd4bf] bg-white p-5 shadow-[0_2px_6px_rgba(15,23,42,0.08)] sm:col-span-2 lg:col-span-1"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            PARTISIPASI
                        </p>
                        <span
                            class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold tabular-nums text-teal-800"
                        >
                            {{ totalParticipantsCount }}
                        </span>
                    </div>

                    <div
                        v-if="sessions.data.length"
                        class="mt-3 space-y-2 text-xs text-slate-700"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Total peserta</span
                            >
                            <span class="font-bold text-slate-900">{{
                                totalParticipantsCount
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Rata-rata / sesi</span
                            >
                            <span class="font-bold text-slate-900">
                                {{
                                    sessions.data.length
                                        ? Math.round(
                                              totalParticipantsCount /
                                                  sessions.data.length,
                                          )
                                        : 0
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                        >
                            <span class="font-medium text-slate-500"
                                >Akses gabung</span
                            >
                            <span class="font-bold text-slate-900"
                                >PIN 6 digit</span
                            >
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-slate-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-slate-500"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Card Host Control: Buka Sesi Baru (Style Card Question Bank) -->
            <section
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_2px_7px_rgba(15,23,42,0.09)] sm:p-7"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5"
                >
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">
                            Buka sesi baru
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Pilih kuis published untuk memulai lobby live bagi
                            peserta.
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800"
                    >
                        host-control
                    </span>
                </div>

                <form class="mt-6 space-y-5" @submit.prevent="openSession">
                    <div class="grid gap-4 md:grid-cols-12">
                        <div class="md:col-span-6">
                            <InputLabel
                                for="quiz_id"
                                value="Pilih Kuis Published"
                                class="font-bold text-slate-800"
                            />
                            <select
                                id="quiz_id"
                                v-model="form.quiz_id"
                                class="mt-2 block min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-[#3451b5] focus:outline-none focus:ring-1 focus:ring-[#3451b5]"
                                required
                            >
                                <option value="">Pilih kuis published</option>
                                <option
                                    v-for="quiz in quizzes"
                                    :key="quiz.id"
                                    :value="quiz.id"
                                >
                                    {{ quiz.title }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.quiz_id"
                                class="mt-1.5"
                            />
                        </div>

                        <div class="md:col-span-3">
                            <InputLabel
                                for="question_duration"
                                value="Durasi / Soal (detik)"
                                class="font-bold text-slate-800"
                            />
                            <input
                                id="question_duration"
                                v-model.number="form.question_duration"
                                min="5"
                                max="300"
                                type="number"
                                aria-label="Durasi per soal"
                                class="mt-2 block min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-[#3451b5] focus:outline-none focus:ring-1 focus:ring-[#3451b5]"
                                required
                            />
                            <InputError
                                :message="form.errors.question_duration"
                                class="mt-1.5"
                            />
                        </div>

                        <div class="md:col-span-3">
                            <InputLabel
                                for="speed_multiplier"
                                value="Bonus Kecepatan"
                                class="font-bold text-slate-800"
                            />
                            <input
                                id="speed_multiplier"
                                v-model.number="form.speed_multiplier"
                                min="0"
                                max="1000"
                                type="number"
                                aria-label="Speed multiplier"
                                class="mt-2 block min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-sm transition focus:border-[#3451b5] focus:outline-none focus:ring-1 focus:ring-[#3451b5]"
                                required
                            />
                            <InputError
                                :message="form.errors.speed_multiplier"
                                class="mt-1.5"
                            />
                        </div>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-between gap-4 pt-2"
                    >
                        <p class="text-xs text-slate-500">
                            PIN 6 digit unik akan dibuat otomatis setelah lobby
                            dibuka.
                        </p>
                        <button
                            type="submit"
                            class="inline-flex min-h-11 items-center justify-center rounded-md bg-[#3451b5] px-5 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5] disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Membuka...' : 'Buka lobby' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Card Sesi Terbaru (Persis dengan Card Data Workspace Question Bank) -->
            <section
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_2px_7px_rgba(15,23,42,0.09)]"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-white px-6 py-5"
                >
                    <div>
                        <h2 class="font-extrabold text-slate-900">
                            Data workspace
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ sessions.data.length }} item pada halaman ini.
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800"
                    >
                        live-sessions
                    </span>
                </div>

                <div
                    v-if="sessions.data.length"
                    class="divide-y divide-slate-100 bg-white"
                >
                    <article
                        v-for="session in sessions.data"
                        :key="session.id"
                        class="group flex min-h-20 items-center justify-between gap-4 border-l-4 border-l-transparent bg-white px-6 py-5 transition-colors duration-150 hover:bg-slate-50"
                    >
                        <div
                            class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between sm:gap-6"
                        >
                            <div>
                                <p
                                    class="truncate font-extrabold text-slate-900"
                                >
                                    {{ session.quiz.title }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    PIN
                                    <strong
                                        class="tracking-widest text-slate-800"
                                        >{{ session.pin }}</strong
                                    >
                                    · {{ session.participants.length }} peserta
                                    <span
                                        v-if="session.lobby_locked"
                                        class="ml-1 font-semibold text-amber-700"
                                    >
                                        · Lobby terkunci
                                    </span>
                                </p>
                            </div>

                            <p
                                class="mt-2 inline-flex shrink-0 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800 sm:mt-0"
                            >
                                {{ session.status }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('live-sessions.play', session.id)"
                                class="inline-flex min-h-9 items-center justify-center rounded-md bg-[#3451b5] px-4 text-xs font-bold text-white transition hover:bg-[#29439d]"
                            >
                                Kelola
                            </Link>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="grid min-h-36 place-items-center py-12 text-center"
                >
                    <div>
                        <svg
                            class="mx-auto h-8 w-8 text-slate-300"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path
                                d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <p class="mt-2 text-sm font-semibold text-slate-500">
                            Belum ada data
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
