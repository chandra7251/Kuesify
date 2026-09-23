<script setup lang="ts">
import TopNavBar from '@/Components/TopNavBar.vue';
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
        color: 'text-[#3154D5]',
    },
    {
        label: 'Soal Aktif',
        value: props.stats.questions,
        color: 'text-[#527A12]',
    },
    {
        label: 'Live Aktif',
        value: props.stats.liveSessions,
        color: 'text-[#3154D5]',
    },
    {
        label: 'Attempt Saya',
        value: props.stats.attempts,
        color: 'text-[#527A12]',
    },
]);

const shortcuts = [
    {
        title: 'Question Bank',
        description: 'Buat dan kelola koleksi soal',
        href: '/questions',
    },
    {
        title: 'Quiz Builder',
        description: 'Susun kuis untuk peserta',
        href: '/quizzes',
    },
    {
        title: 'Live Quiz',
        description: 'Mulai sesi interaktif',
        href: '/live-sessions',
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
        <TopNavBar />
        <div class="min-h-[calc(100vh-4rem)] bg-[#E6F1F5]">
            <main
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <section
                    aria-labelledby="dashboard-title"
                    class="overflow-hidden rounded-2xl bg-[#3b5fe1] px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7"
                >
                    <div
                        class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-brand-secondary"
                                    aria-hidden="true"
                                ></span>
                                {{ roleLabel(organization.role) }} ·
                                {{ organization.name }}
                            </p>
                            <h1
                                id="dashboard-title"
                                class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl"
                            >
                                Selamat datang, {{ $page.props.auth.user.name }}
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base"
                            >
                                Kelola soal, siapkan kuis, dan pantau aktivitas
                                belajar dari satu workspace.
                            </p>
                        </div>
                        <div class="grid shrink-0 gap-3 sm:flex">
                            <Link
                                href="/quizzes"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-secondary px-5 text-sm font-bold text-[#ffffff] transition hover:brightness-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            >
                                Buat kuis baru
                            </Link>
                            <Link
                                href="/live-sessions"
                                class="hover:brand-secondary inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-secondary px-5 text-sm font-semibold text-brand-secondary transition hover:bg-brand-secondary/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                            >
                                Mulai sesi live
                            </Link>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="workspace-summary">
                    <div class="mb-3">
                        <h2
                            id="workspace-summary"
                            class="text-lg font-bold text-slate-950"
                        >
                            Ringkasan organisasi
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Aktivitas utama di workspace ini.
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4"
                    >
                        <article
                            v-for="(item, index) in statCards"
                            :key="item.label"
                            :class="[
                                'border-slate-200 p-4 even:border-l sm:border-b-0 sm:border-l sm:first:border-l-0',
                                index < 2 ? 'border-b' : '',
                            ]"
                        >
                            <div
                                :class="[
                                    'mb-4 h-1 w-8 rounded-full',
                                    index % 2 === 0
                                        ? 'bg-[#3154D5]'
                                        : 'bg-brand-secondary',
                                ]"
                                aria-hidden="true"
                            ></div>
                            <p class="text-sm font-medium text-slate-600">
                                {{ item.label }}
                            </p>
                            <p
                                :class="[
                                    'mt-1 text-2xl font-bold tabular-nums',
                                    item.color,
                                ]"
                            >
                                {{ item.value }}
                            </p>
                        </article>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        aria-labelledby="recent-activity"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2"
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
                                class="rounded-md px-2 py-1 text-sm font-semibold text-[#3154D5] transition hover:bg-[#E6F1F5] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3154D5]"
                            >
                                Lihat semua
                            </Link>
                        </div>

                        <div
                            v-if="recentQuizzes.length"
                            class="divide-y divide-slate-100"
                        >
                            <div
                                v-for="quiz in recentQuizzes"
                                :key="quiz.id"
                                class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#E6F1F5]/60"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate font-semibold text-slate-900"
                                    >
                                        {{ quiz.title }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Diperbarui
                                        {{ formatDate(quiz.updated_at) }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-semibold text-[#527A12]"
                                    >{{ quiz.status }}</span
                                >
                            </div>
                        </div>
                        <div v-else class="px-5 py-10 text-center">
                            <p class="font-semibold text-slate-900">
                                Belum ada aktivitas kuis.
                            </p>
                            <Link
                                href="/quizzes"
                                class="mt-2 inline-flex text-xs font-bold text-[#3154D5] hover:underline"
                            >
                                Buat kuis pertama
                            </Link>
                        </div>
                    </section>

                    <section
                        aria-labelledby="learning-progress"
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
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
                        <dl
                            class="mt-5 grid grid-cols-3 overflow-hidden rounded-lg bg-[#E6F1F5] text-center"
                        >
                            <div class="px-2 py-4">
                                <dt class="text-xs font-medium text-slate-500">
                                    XP
                                </dt>
                                <dd
                                    class="mt-1 text-xl font-bold text-[#3154D5]"
                                >
                                    {{ stats.xp }}
                                </dd>
                            </div>
                            <div class="border-x border-white px-2 py-4">
                                <dt class="text-xs font-medium text-slate-500">
                                    Level
                                </dt>
                                <dd
                                    class="mt-1 text-xl font-bold text-[#527A12]"
                                >
                                    {{ stats.level }}
                                </dd>
                            </div>
                            <div class="px-2 py-4">
                                <dt class="text-xs font-medium text-slate-500">
                                    Streak
                                </dt>
                                <dd
                                    class="mt-1 text-xl font-bold text-[#3154D5]"
                                >
                                    {{ stats.streak }}
                                </dd>
                            </div>
                        </dl>
                        <Link
                            href="/attempts"
                            class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-[#3154D5] px-4 text-sm font-semibold text-[#3154D5] transition hover:bg-[#3154D5] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3154D5]"
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
                            v-for="{ title, description, href } in shortcuts"
                            :key="href"
                            :href="href"
                            class="group flex min-h-20 items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3154D5] motion-reduce:hover:translate-y-0"
                        >
                            <span>
                                <span
                                    class="block font-semibold text-slate-950"
                                    >{{ title }}</span
                                >
                                <span
                                    class="mt-1 block text-sm text-slate-600"
                                    >{{ description }}</span
                                >
                            </span>
                            <svg
                                class="h-5 w-5 shrink-0 text-[#3154D5] transition group-hover:translate-x-0.5 group-hover:text-[#527A12] motion-reduce:transform-none"
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
        </div>
    </AuthenticatedLayout>
</template>
