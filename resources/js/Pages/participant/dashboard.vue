<script setup lang="ts">
import ActivityFeed from '@/Components/ActivityFeed.vue';
import BadgeGrid from '@/Components/BadgeGrid.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import QuizCard from '@/Components/QuizCard.vue';
import StatCard from '@/Components/StatCard.vue';
import StreakCalendar from '@/Components/StreakCalendar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type QuizSummary = {
    id: number;
    title: string;
    description: string | null;
    questions_count: number;
    deadline_at: string | null;
    max_attempts: number | null;
};

type AttemptSummary = {
    id: number;
    quiz_title: string;
    status: string;
    score: number;
    updated_at: string | null;
};

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        availableQuizzes: number;
        attempts: number;
        completedAttempts: number;
        xp: number;
        streak: number;
        level: number;
        badges: number;
    };
    badges: { key: string; name: string }[];
    recentAttempts: AttemptSummary[];
    availableQuizzes: QuizSummary[];
    quizOfTheDay: QuizSummary | null;
    streakHeatmap: { date: string; count: number }[];
}>();

const nextLevelXp = computed(() => props.stats.level * 100);
const currentLevelXp = computed(() => Math.min(props.stats.xp % 100, 100));

const statCards = computed(() => [
    { label: 'Kuis tersedia', value: props.stats.availableQuizzes, accent: 'bg-brand-primary' },
    { label: 'Attempt saya', value: props.stats.attempts, accent: 'bg-brand-secondary' },
    { label: 'Selesai', value: props.stats.completedAttempts, accent: 'bg-support-1' },
    { label: 'Badge', value: props.stats.badges, accent: 'bg-support-3' },
]);

const attemptItems = computed(() =>
    props.recentAttempts.map((attempt) => ({
        id: attempt.id,
        title: attempt.quiz_title,
        subtitle: attempt.status,
        meta: `${attempt.score} poin`,
    })),
);

const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' });
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <section class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                        <span class="h-2 w-2 rounded-full bg-brand-secondary" aria-hidden="true"></span>
                        Siswa · {{ organization.name }}
                    </p>
                    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_18rem] lg:items-end">
                        <div>
                            <h1 class="max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">Ruang belajar kamu hari ini</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                                Lanjutkan kuis, kumpulkan XP, dan pantau streak belajar tanpa masuk ke area guru.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Level {{ stats.level }}</p>
                            <div class="mt-3 flex items-end justify-between gap-3">
                                <p class="text-3xl font-black">{{ stats.xp }} XP</p>
                                <p class="text-xs text-white/70">Target {{ nextLevelXp }} XP</p>
                            </div>
                            <ProgressBar class="mt-3" :current="currentLevelXp" :max="100" />
                        </div>
                    </div>
                </section>

                <section aria-labelledby="student-kpi" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <h2 id="student-kpi" class="sr-only">Ringkasan belajar siswa</h2>
                    <StatCard v-for="card in statCards" :key="card.label" :label="card.label" :value="card.value" :accent="card.accent" />
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Quiz of the Day</h2>
                                <p class="mt-1 text-xs text-slate-500">Rekomendasi cepat supaya belajar tetap konsisten.</p>
                            </div>
                            <Link href="/attempts" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-primary hover:bg-brand-accent">Lihat semua</Link>
                        </div>

                        <div v-if="quizOfTheDay" class="mt-4 rounded-2xl border border-brand-secondary/40 bg-brand-secondary/10 p-5">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#527A12]">Mulai dari sini</p>
                            <h3 class="mt-2 text-xl font-black text-slate-950">{{ quizOfTheDay.title }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                                {{ quizOfTheDay.description ?? 'Kuis pilihan dari organisasi untuk latihan hari ini.' }}
                            </p>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-600">
                                <span>{{ quizOfTheDay.questions_count }} soal</span>
                                <span v-if="quizOfTheDay.deadline_at">Deadline {{ dateFormatter.format(new Date(quizOfTheDay.deadline_at)) }}</span>
                                <span v-if="quizOfTheDay.max_attempts">Maks {{ quizOfTheDay.max_attempts }} attempt</span>
                            </div>
                        </div>

                        <p v-else class="mt-4 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600">
                            Belum ada kuis published di organisasi ini.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                        <h2 class="text-base font-bold text-slate-900">Streak 30 hari</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ stats.streak }} hari streak aktif.</p>
                        <StreakCalendar class="mt-4" :streaks="streakHeatmap" />
                    </section>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Kuis tersedia</h2>
                                <p class="mt-1 text-xs text-slate-500">Ambil latihan sesuai materi organisasi.</p>
                            </div>
                            <Link href="/attempts" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-primary hover:bg-brand-accent">Buka</Link>
                        </div>
                        <div v-if="availableQuizzes.length" class="mt-4 grid gap-3 sm:grid-cols-2">
                            <QuizCard
                                v-for="quiz in availableQuizzes"
                                :key="quiz.id"
                                :title="quiz.title"
                                :description="quiz.description"
                                :questions-count="quiz.questions_count"
                            />
                        </div>
                        <p v-else class="mt-6 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600">Belum ada kuis tersedia.</p>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                        <h2 class="text-base font-bold text-slate-900">Badge saya</h2>
                        <BadgeGrid class="mt-4" :badges="badges" empty-text="Selesaikan kuis pertama untuk membuka badge." />
                    </section>
                </div>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                    <h2 class="text-base font-bold text-slate-900">Attempt terbaru</h2>
                    <ActivityFeed class="mt-4" :items="attemptItems" empty-text="Belum ada attempt. Mulai dari Quiz of the Day." />
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
