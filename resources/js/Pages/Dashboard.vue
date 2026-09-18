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
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- Hero Card (Mockup Reference) -->
            <section
                aria-labelledby="dashboard-title"
                class="relative overflow-hidden rounded-xl border border-slate-100/80 bg-white p-6 shadow-figma sm:p-8"
            >
                <div class="relative z-10 max-w-3xl">
                    <h2 class="sr-only">
                        Selamat datang, {{ $page.props.auth.user.name }}
                    </h2>
                    <h1
                        id="dashboard-title"
                        class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Belajar Lebih Hidup di {{ organization.name }} .
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base">
                        Buat Soal, jalankan kuis, dan pantau perkembangan peserta dalam satu ruang kerja
                    </p>

                    <!-- Primary Action Buttons -->
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            href="/quizzes"
                            aria-label="Buat kuis baru"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-primary px-6 text-sm font-bold text-white shadow-md shadow-brand-primary/20 transition hover:bg-brand-hover hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
                        >
                            Buat Kuis
                        </Link>
                        <Link
                            href="/live-sessions"
                            aria-label="Mulai sesi live"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-secondary px-6 text-sm font-bold text-slate-900 shadow-md shadow-brand-secondary/20 transition hover:bg-emerald-500 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                        >
                            Buka Live Quiz
                        </Link>
                    </div>
                </div>
            </section>

            <!-- 4 Stat Cards (Mockup Reference) -->
            <section aria-labelledby="workspace-summary">
                <h2 id="workspace-summary" class="sr-only">
                    Ringkasan workspace
                </h2>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <!-- Stat 1: Quiz -->
                    <article
                        class="rounded-xl border border-slate-100/80 bg-white p-5 shadow-figma transition hover:shadow-figma-hover"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-brand-secondary"
                        >
                            Quiz
                        </p>
                        <p
                            class="mt-2 text-4xl font-black tabular-nums text-brand-secondary sm:text-5xl"
                        >
                            {{ stats.quizzes }}
                        </p>
                    </article>

                    <!-- Stat 2: Soal Aktif -->
                    <article
                        class="rounded-xl border border-slate-100/80 bg-white p-5 shadow-figma transition hover:shadow-figma-hover"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-support-1"
                        >
                            Soal Aktif
                        </p>
                        <p
                            class="mt-2 text-4xl font-black tabular-nums text-support-1 sm:text-5xl"
                        >
                            {{ stats.questions }}
                        </p>
                    </article>

                    <!-- Stat 3: Live Aktif -->
                    <article
                        class="rounded-xl border border-slate-100/80 bg-white p-5 shadow-figma transition hover:shadow-figma-hover"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-support-3"
                        >
                            Live Aktif
                        </p>
                        <p
                            class="mt-2 text-4xl font-black tabular-nums text-support-3 sm:text-5xl"
                        >
                            {{ stats.liveSessions }}
                        </p>
                    </article>

                    <!-- Stat 4: Attempt Saya -->
                    <article
                        class="rounded-xl border border-slate-100/80 bg-white p-5 shadow-figma transition hover:shadow-figma-hover"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500"
                        >
                            Attempt Saya
                        </p>
                        <p
                            class="mt-2 text-4xl font-black tabular-nums text-brand-secondary sm:text-5xl"
                        >
                            {{ stats.attempts }}
                        </p>
                    </article>
                </div>
            </section>

            <!-- Level Up Gamification Banner (Mockup Reference) -->
            <section
                class="flex items-center justify-between rounded-xl bg-brand-secondary px-6 py-5 text-slate-900 shadow-figma"
            >
                <div>
                    <h3 class="text-xl font-black tracking-tight text-slate-900">
                        Level Up!
                    </h3>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">
                        {{ stats.xp }} Xp
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-white/30 px-3.5 py-1 text-xs font-bold text-slate-900">
                        Level {{ stats.level }}
                    </span>
                    <Link
                        href="/reports"
                        class="rounded-lg bg-white px-3.5 py-1.5 text-xs font-bold text-slate-900 shadow-sm transition hover:bg-emerald-50"
                    >
                        Hasil
                    </Link>
                </div>
            </section>

            <!-- Two-Column Lower Section: Kuis Terbaru & Aksi Cepat + Streak -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Kuis Terbaru (lg:col-span-6) -->
                <section
                    aria-labelledby="recent-activity"
                    class="rounded-xl border border-slate-100/80 bg-white p-6 shadow-figma lg:col-span-6 flex flex-col"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-4"
                    >
                        <div>
                            <h2
                                id="recent-activity"
                                class="text-base font-black text-slate-900 sm:text-lg"
                            >
                                Kuis Terbaru
                                <span class="sr-only">· Aktivitas terbaru</span>
                            </h2>
                            <p
                                class="mt-0.5 text-xs font-medium text-slate-400"
                            >
                                Lanjutkan dari Aktivitas Terakhir
                            </p>
                        </div>
                        <Link
                            href="/quizzes"
                            class="text-xs font-bold text-brand-secondary hover:underline"
                        >
                            Lihat semua
                        </Link>
                    </div>

                    <!-- Quizzes List (clean single-line items like Image 1) -->
                    <div
                        v-if="recentQuizzes.length"
                        class="mt-3 divide-y divide-slate-100"
                    >
                        <div
                            v-for="quiz in recentQuizzes"
                            :key="quiz.id"
                            class="flex items-center justify-between py-3 px-2 rounded-lg transition hover:bg-slate-50/70"
                        >
                            <p
                                class="truncate text-xs sm:text-sm font-semibold text-slate-800 min-w-0 pr-4"
                            >
                                {{ quiz.title }}
                            </p>
                            <span
                                class="shrink-0 rounded-full bg-slate-200 px-3 py-0.5 text-[11px] font-semibold text-slate-600 capitalize"
                            >
                                {{ quiz.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-10 text-center">
                        <p class="text-sm font-bold text-slate-800">
                            Belum ada aktivitas kuis.
                        </p>
                        <Link
                            href="/quizzes"
                            class="mt-2 inline-flex text-xs font-bold text-brand-secondary hover:underline"
                        >
                            Buat kuis pertama
                        </Link>
                    </div>
                </section>

                <!-- Right Column: Aksi Cepat & Streak Card (lg:col-span-6) -->
                <div class="flex flex-col gap-6 lg:col-span-6">
                    <!-- Aksi Cepat - Pilih Aktivitas -->
                    <section
                        aria-labelledby="quick-actions"
                    >
                        <div class="mb-3">
                            <h2
                                id="quick-actions"
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Aksi cepat
                            </h2>
                            <p
                                class="text-base font-black text-slate-900 sm:text-lg"
                            >
                                Pilih Aktivitas
                            </p>
                        </div>

                        <div class="space-y-3">
                            <!-- Card 1: Kategori -->
                            <Link
                                href="/questions"
                                class="group flex items-center justify-between rounded-xl border border-slate-100/80 bg-white p-4 shadow-figma-sm transition hover:shadow-figma"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-accent text-base font-black text-brand-secondary"
                                    >
                                        C
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900">
                                            Kategori
                                        </p>
                                        <p class="truncate text-xs text-slate-400">
                                            Buat dan Kelola Kategori
                                        </p>
                                    </div>
                                </div>
                                <span class="text-slate-400 font-bold text-sm">></span>
                            </Link>

                            <!-- Card 2: Tenant -->
                            <Link
                                href="/organization"
                                class="group flex items-center justify-between rounded-xl border border-slate-100/80 bg-white p-4 shadow-figma-sm transition hover:shadow-figma"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-accent text-base font-black text-brand-secondary"
                                    >
                                        T
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900">
                                            Tenant
                                        </p>
                                        <p class="truncate text-xs text-slate-400">
                                            Mulai Membuat Tenant
                                        </p>
                                    </div>
                                </div>
                                <span class="text-slate-400 font-bold text-sm">></span>
                            </Link>
                        </div>
                    </section>

                    <!-- Streak Card (Mockup Reference) -->
                    <section
                        class="flex flex-1 flex-col items-center justify-center rounded-xl border border-slate-100/80 bg-white p-8 text-center shadow-figma min-h-[200px]"
                    >
                        <h3
                            class="text-lg sm:text-xl font-bold text-slate-800"
                        >
                            Streak :
                        </h3>
                        <p
                            class="my-2 text-7xl sm:text-8xl font-black tabular-nums text-slate-800"
                        >
                            {{ stats.streak }}
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
