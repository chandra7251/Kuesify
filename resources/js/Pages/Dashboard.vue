<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        quizzes: number;
        questions: number;
        liveSessions: number;
        attempts: number;
        xp: number;
        streak: number;
        level: number;
    };
    recentQuizzes: {
        id: number;
        title: string;
        status: string;
        updated_at: string;
    }[];
    badges: { key: string; name: string }[];
}>();

const statCards = computed(() => [
    {
        label: 'Kuis',
        value: props.stats.quizzes,
        color: 'text-teal-700',
    },
    {
        label: 'Soal Aktif',
        value: props.stats.questions,
        color: 'text-amber-700',
    },
    {
        label: 'Live Aktif',
        value: props.stats.liveSessions,
        color: 'text-[#4b3f63]',
    },
    {
        label: 'Attempt Saya',
        value: props.stats.attempts,
        color: 'text-teal-700',
    },
]);

const shortcuts = [
    {
        title: 'Question Bank',
        description: 'Buat dan kelola koleksi soal',
        href: '/questions',
        icon: 'Q',
    },
    {
        title: 'Quiz Builder',
        description: 'Susun kuis untuk peserta',
        href: '/quizzes',
        icon: 'K',
    },
    {
        title: 'Live Quiz',
        description: 'Mulai sesi interaktif',
        href: '/live-sessions',
        icon: 'L',
    },
];

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const formatDate = (value: string) => dateFormatter.format(new Date(value));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <main
            class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"
        >
            <section
                aria-labelledby="dashboard-title"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_2px_8px_rgba(15,23,42,0.10)] sm:p-6"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider text-teal-700"
                >
                    {{ roleLabel(organization.role) }} · {{ organization.name }}
                </p>
                <h1
                    id="dashboard-title"
                    class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl"
                >
                    Belajar Lebih Hidup di {{ organization.name }}.
                </h1>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                    Buat soal, jalankan kuis, dan pantau perkembangan peserta
                    dalam satu ruang kerja.
                </p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <Link
                        href="/quizzes"
                        class="inline-flex min-h-11 items-center justify-center rounded-md bg-[#3451b5] px-4 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5]"
                    >
                        Buat Kuis
                    </Link>
                    <Link
                        href="/live-sessions"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-[#3451b5] bg-[#2dd4bf] px-4 text-sm font-bold text-slate-900 transition hover:bg-[#5eead4] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5]"
                    >
                        Buka Live Quiz
                    </Link>
                </div>
            </section>

            <section aria-labelledby="workspace-summary">
                <h2 id="workspace-summary" class="sr-only">
                    Ringkasan workspace
                </h2>
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <div
                        v-for="item in statCards"
                        :key="item.label"
                        class="rounded-md border border-slate-200 bg-white p-4 shadow-[0_2px_5px_rgba(15,23,42,0.08)]"
                    >
                        <dt class="text-xs font-bold" :class="item.color">
                            {{ item.label }}
                        </dt>
                        <dd
                            class="mt-1 text-2xl font-extrabold tabular-nums"
                            :class="item.color"
                        >
                            {{ item.value }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section
                aria-label="Progres level"
                class="flex items-center justify-between gap-4 rounded-md border border-teal-500 bg-[#2dd4bf] px-5 py-3 text-slate-900 shadow-[0_2px_5px_rgba(15,23,42,0.12)]"
            >
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider">
                        Progress
                    </p>
                    <p class="font-extrabold">Level {{ stats.level }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold">XP terkumpul</p>
                    <p class="text-lg font-extrabold tabular-nums">
                        {{ stats.xp }} XP
                    </p>
                </div>
            </section>

            <div
                class="grid items-stretch gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.5fr)]"
            >
                <section
                    aria-labelledby="recent-quizzes"
                    class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-[0_2px_7px_rgba(15,23,42,0.09)]"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4"
                    >
                        <div>
                            <h2
                                id="recent-quizzes"
                                class="font-extrabold text-[#4b3f63]"
                            >
                                Kuis Terbaru
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Lanjutkan dari aktivitas terakhir
                            </p>
                        </div>
                        <Link
                            href="/quizzes"
                            class="shrink-0 rounded text-xs font-bold text-teal-700 hover:text-teal-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                        >
                            Lihat semua
                        </Link>
                    </div>
                    <ul
                        v-if="recentQuizzes.length"
                        class="divide-y divide-slate-100"
                    >
                        <li
                            v-for="quiz in recentQuizzes"
                            :key="quiz.id"
                            class="flex items-center justify-between gap-3 px-4 py-3"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-semibold text-slate-800"
                                >
                                    {{ quiz.title }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ formatDate(quiz.updated_at) }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold capitalize text-slate-600"
                                >{{ quiz.status }}</span
                            >
                        </li>
                    </ul>
                    <div v-else class="px-5 py-10 text-center">
                        <p class="font-semibold text-slate-900">
                            Belum ada aktivitas kuis.
                        </p>
                        <Link
                            href="/quizzes"
                            class="mt-2 inline-flex text-sm font-semibold text-teal-700 hover:text-teal-900"
                            >Buat kuis pertama</Link
                        >
                    </div>
                </section>

                <div class="grid gap-5">
                    <section aria-labelledby="quick-actions">
                        <div class="mb-2">
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                Aksi Cepat
                            </p>
                            <h2
                                id="quick-actions"
                                class="text-lg font-extrabold leading-none text-[#4b3f63]"
                            >
                                Pilih Aktivitas
                            </h2>
                        </div>
                        <div class="grid gap-2">
                            <Link
                                v-for="item in shortcuts"
                                :key="item.href"
                                :href="item.href"
                                class="group flex min-h-14 items-center gap-3 rounded-md border border-slate-200 bg-white px-4 py-2.5 shadow-[0_2px_5px_rgba(15,23,42,0.08)] transition hover:border-teal-300 hover:bg-teal-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                            >
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-teal-100 text-xs font-extrabold text-teal-900"
                                    aria-hidden="true"
                                >
                                    {{ item.icon }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-sm font-bold text-slate-800"
                                    >
                                        {{ item.title }}
                                    </span>
                                    <span
                                        class="block truncate text-xs text-slate-500"
                                    >
                                        {{ item.description }}
                                    </span>
                                </span>
                                <svg
                                    class="h-5 w-5 shrink-0 text-slate-500 transition group-hover:translate-x-0.5 group-hover:text-teal-700"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m9 18 6-6-6-6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </Link>
                        </div>
                    </section>

                    <section
                        aria-labelledby="streak-title"
                        class="grid min-h-52 place-items-center rounded-md border border-slate-200 bg-white p-6 text-center shadow-[0_2px_7px_rgba(15,23,42,0.09)]"
                    >
                        <div>
                            <h2
                                id="streak-title"
                                class="text-lg font-extrabold text-[#4b3f63]"
                            >
                                Streak
                            </h2>
                            <p
                                class="mt-2 text-6xl font-extrabold tabular-nums text-[#4b3f63]"
                            >
                                {{ stats.streak }}
                            </p>
                            <p class="mt-2 text-xs font-medium text-slate-500">
                                hari belajar berturut-turut
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
