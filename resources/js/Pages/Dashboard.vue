<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
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

const shortcuts = [
    ['Question Bank', 'Buat dan kelola soal', '/questions'],
    ['Quiz Builder', 'Susun kuis untuk peserta', '/quizzes'],
    ['Live Quiz', 'Mulai sesi interaktif', '/live-sessions'],
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
            <section
                aria-labelledby="dashboard-title"
                class="border-b border-slate-200 pb-6"
            >
                <p class="text-sm font-semibold text-teal-700">
                    {{ roleLabel(organization.role) }} · {{ organization.name }}
                </p>
                <h1
                    id="dashboard-title"
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-950"
                >
                    Selamat datang, {{ $page.props.auth.user.name }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Kelola soal, siapkan kuis, dan pantau aktivitas belajar dari
                    satu workspace.
                </p>
                <div class="mt-5 grid gap-3 sm:flex">
                    <Link
                        href="/quizzes"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                    >
                        Buat kuis baru
                    </Link>
                    <Link
                        href="/live-sessions"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-teal-700 bg-white px-4 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                    >
                        Mulai sesi live
                    </Link>
                </div>
            </section>

            <section aria-labelledby="workspace-summary">
                <div class="mb-3 flex items-end justify-between gap-4">
                    <div>
                        <h2
                            id="workspace-summary"
                            class="text-lg font-bold text-slate-950"
                        >
                            Ringkasan workspace
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Aktivitas utama di organisasi ini.
                        </p>
                    </div>
                </div>
                <div
                    class="grid grid-cols-2 divide-x divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-4"
                >
                    <article
                        v-for="item in [
                            { label: 'Kuis', value: stats.quizzes },
                            { label: 'Soal aktif', value: stats.questions },
                            { label: 'Live aktif', value: stats.liveSessions },
                            { label: 'Attempt', value: stats.attempts },
                        ]"
                        :key="item.label"
                        class="p-4"
                    >
                        <p class="text-sm font-medium text-slate-600">
                            {{ item.label }}
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold tabular-nums text-slate-950"
                        >
                            {{ item.value }}
                        </p>
                    </article>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-3">
                <section
                    aria-labelledby="recent-activity"
                    class="rounded-xl border border-slate-200 bg-white lg:col-span-2"
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4"
                    >
                        <div>
                            <h2
                                id="recent-activity"
                                class="text-lg font-bold text-slate-950"
                            >
                                Aktivitas terbaru
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Kuis yang terakhir diperbarui.
                            </p>
                        </div>
                        <Link
                            href="/quizzes"
                            class="text-sm font-semibold text-teal-700 hover:text-teal-900"
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
                            class="flex items-center justify-between gap-4 px-5 py-4"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate font-semibold text-slate-900"
                                >
                                    {{ quiz.title }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Diperbarui {{ quiz.updated_at }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
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

                <section
                    aria-labelledby="learning-progress"
                    class="rounded-xl border border-slate-200 bg-white p-5"
                >
                    <h2
                        id="learning-progress"
                        class="text-lg font-bold text-slate-950"
                    >
                        Perkembangan belajar
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Ringkasan progres pribadi.
                    </p>
                    <dl class="mt-5 grid grid-cols-3 gap-3 text-center">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">
                                XP
                            </dt>
                            <dd class="mt-1 text-xl font-bold text-slate-950">
                                {{ stats.xp }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">
                                Level
                            </dt>
                            <dd class="mt-1 text-xl font-bold text-slate-950">
                                {{ stats.level }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">
                                Streak
                            </dt>
                            <dd class="mt-1 text-xl font-bold text-slate-950">
                                {{ stats.streak }}
                            </dd>
                        </div>
                    </dl>
                    <Link
                        href="/attempts"
                        class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-800 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                        >Lihat hasil belajar</Link
                    >
                </section>
            </div>

            <section aria-labelledby="quick-actions">
                <div class="mb-3">
                    <h2
                        id="quick-actions"
                        class="text-lg font-bold text-slate-950"
                    >
                        Aksi cepat
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Pilih tugas yang ingin dikerjakan.
                    </p>
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    <Link
                        v-for="[title, description, href] in shortcuts"
                        :key="href"
                        :href="href"
                        class="group flex min-h-20 items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-teal-300 hover:bg-teal-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                    >
                        <span>
                            <span class="block font-semibold text-slate-950">{{
                                title
                            }}</span>
                            <span class="mt-1 block text-sm text-slate-600">{{
                                description
                            }}</span>
                        </span>
                        <svg
                            class="h-5 w-5 shrink-0 text-teal-700"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                d="M5 12h14m-6-6 6 6-6 6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </Link>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
