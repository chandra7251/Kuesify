<script setup lang="ts">
import ActivityFeed from '@/Components/ActivityFeed.vue';
import AppIcon from '@/Components/AppIcon.vue';
import BadgeGrid from '@/Components/BadgeGrid.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import QuizCard from '@/Components/QuizCard.vue';
import StatCard from '@/Components/StatCard.vue';
import StreakCalendar from '@/Components/StreakCalendar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { computed, onMounted, onUnmounted, ref } from 'vue';

gsap.registerPlugin(ScrollTrigger);

type QuizSummary = {
    id: number;
    title: string;
    description: string | null;
    questions_count: number;
    deadline_at: string | null;
    max_attempts: number | null;
    allow_retry: boolean;
    my_attempts_count: number;
    best_score: number | null;
};

type Mission = {
    key: string;
    kind: string;
    title: string;
    description: string;
    goal: number;
    progress: number;
    reward_xp: number;
    completed_at: string | null;
};

type AttemptSummary = {
    id: number;
    quiz_title: string;
    status: string;
    score: number;
    updated_at: string | null;
};

const activeMissionTab = ref<'all' | 'daily' | 'weekly' | 'campaign'>('all');
const equippedTitle = ref('');

const missionBadgeLink: Record<string, string> = {
    learning_quiz_5: 'Trial Challenger',
    campaign_dungeon_15: 'Dungeon Conqueror',
    campaign_dungeon_25: 'Dungeon Grandmaster',
    campaign_flawless: 'Flawless Mastery',
    campaign_titan_30: 'Eternal Titan',
};

const filteredMissions = computed(() => {
    if (activeMissionTab.value === 'daily') return props.missions.filter((m) => m.kind === 'daily');
    if (activeMissionTab.value === 'weekly') return props.missions.filter((m) => m.kind === 'weekly');
    if (activeMissionTab.value === 'campaign') return props.missions.filter((m) => m.kind === 'campaign' || m.kind === 'learning_path');
    return props.missions;
});

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
    badges: {
        key: string;
        name: string;
        description: string | null;
        rarity: string;
        progress: number;
        criteria_value: number;
        progress_percent: number;
        earned: boolean;
        earned_at: string | null;
    }[];
    recentAttempts: AttemptSummary[];
    availableQuizzes: QuizSummary[];
    quizOfTheDay: QuizSummary | null;
    streakHeatmap: { date: string; count: number }[];
    missions: Mission[];
}>();

const levelStartXp = computed(
    () => 1000 * (((props.stats.level - 1) * props.stats.level) / 2),
);
const nextLevelXp = computed(
    () => levelStartXp.value + props.stats.level * 1000,
);
const currentLevelXp = computed(() =>
    Math.max(0, props.stats.xp - levelStartXp.value),
);
const levelTargetXp = computed(() => props.stats.level * 1000);

const statCards = computed(() => [
    {
        label: 'Kuis tersedia',
        value: props.stats.availableQuizzes,
        accent: 'bg-brand-primary',
        icon: 'quiz',
    },
    {
        label: 'Attempt saya',
        value: props.stats.attempts,
        accent: 'bg-brand-secondary',
        icon: 'results',
    },
    {
        label: 'Selesai',
        value: props.stats.completedAttempts,
        accent: 'bg-support-1',
        icon: 'learn',
    },
    {
        label: 'Badge',
        value: props.stats.badges,
        accent: 'bg-support-3',
        icon: 'badge',
    },
]);

const attemptItems = computed(() =>
    props.recentAttempts.map((attempt) => ({
        id: attempt.id,
        title: attempt.quiz_title,
        subtitle: attempt.status,
        meta: `${attempt.score} poin`,
    })),
);

const quizFilter = ref<'all' | 'unattempted' | 'retry' | 'done'>('all');

const filteredAvailableQuizzes = computed(() => {
    if (quizFilter.value === 'unattempted') {
        return props.availableQuizzes.filter((q) => q.my_attempts_count === 0);
    }
    if (quizFilter.value === 'retry') {
        return props.availableQuizzes.filter((q) => q.my_attempts_count > 0 && canStart(q));
    }
    if (quizFilter.value === 'done') {
        return props.availableQuizzes.filter((q) => !canStart(q));
    }
    return props.availableQuizzes;
});

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
});

function startQuiz(quiz: QuizSummary): void {
    if (!canStart(quiz)) return;
    router.post(route('attempts.store', quiz.id));
}

function canStart(quiz: QuizSummary): boolean {
    if (!quiz.allow_retry && quiz.my_attempts_count > 0) return false;
    return (
        quiz.max_attempts === null || quiz.my_attempts_count < quiz.max_attempts
    );
}

function quizState(quiz: QuizSummary): 'new' | 'retry' | 'done' | 'locked' {
    if (!canStart(quiz)) return 'locked';
    return quiz.my_attempts_count > 0 ? 'retry' : 'new';
}

const motionRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;
let motionMedia: gsap.MatchMedia | null = null;

onMounted(() => {
    equippedTitle.value = localStorage.getItem('kuesify_equipped_title') || '';
    window.addEventListener('kuesify-title-equipped', ((e: CustomEvent) => {
        equippedTitle.value = e.detail?.title || '';
    }) as EventListener);
    motionMedia = gsap.matchMedia();
    motionMedia.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            const timeline = gsap.timeline({
                defaults: { ease: 'power3.out' },
            });
            const hero = motionRoot.value?.querySelector('[data-motion-hero]');
            const items = motionRoot.value?.querySelectorAll('[data-motion-item]:not([data-motion-hero])');
            const sections = motionRoot.value?.querySelectorAll(
                '[data-motion-section]',
            );

            if (hero) timeline.from(hero, { autoAlpha: 0, y: 16, duration: 0.45, clearProps: 'all' });
            if (items?.length)
                timeline.from(items, { autoAlpha: 0, y: 14, duration: 0.35, stagger: 0.05, clearProps: 'all' }, '-=0.2');
            if (sections?.length) {
                gsap.from(sections, {
                    autoAlpha: 0,
                    y: 20,
                    duration: 0.45,
                    stagger: 0.06,
                    ease: 'power2.out',
                    clearProps: 'all',
                    scrollTrigger: {
                        trigger: sections[0],
                        start: 'top 88%',
                        once: true,
                    },
                });
            }

            return () => timeline.kill();
        }, motionRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    motionMedia?.revert();
    gsapCtx?.revert();
    ScrollTrigger.getAll().forEach((st) => {
        if (st.trigger && motionRoot.value?.contains(st.trigger)) st.kill();
    });
});
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main
                ref="motionRoot"
                data-motion="participant-dashboard"
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <!-- Hero Section: Clean, Purposeful, Educational -->
                <section
                    data-motion-hero
                    class="relative overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-sm sm:px-8"
                >
                    <div class="flex items-center justify-between">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm">
                            <span class="h-2 w-2 rounded-full bg-brand-secondary" aria-hidden="true"></span>
                            <span>{{ organization.name }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm">
                            <AppIcon name="flame" :size="14" class="text-brand-secondary" />
                            <span>{{ stats.streak }} Hari Streak</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                                    Halo, {{ $page.props.auth.user.name }}
                                </h1>
                                <span
                                    v-if="equippedTitle"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-brand-secondary/40 bg-brand-secondary/20 px-3 py-0.5 text-xs font-black text-brand-secondary shadow-xs backdrop-blur-sm"
                                >
                                    <AppIcon name="badge" :size="13" />
                                    <span>Gelar: [{{ equippedTitle }}]</span>
                                </span>
                            </div>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/80">
                                Lanjutkan pembelajaran mandiri, selesaikan misi aktif, dan kumpulkan poin pengalaman untuk menaikkan level akunmu.
                            </p>
                        </div>

                        <!-- Level Progress Card -->
                        <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                            <div class="flex items-center justify-between text-xs font-semibold text-white/80">
                                <span class="inline-flex items-center gap-1.5 text-brand-secondary font-bold">
                                    <AppIcon name="level" :size="15" />
                                    <span>Level {{ stats.level }}</span>
                                </span>
                                <span>{{ Math.round((currentLevelXp / levelTargetXp) * 100) }}% Progres</span>
                            </div>
                            <div class="mt-2.5 flex items-baseline justify-between">
                                <p class="text-2xl font-black text-white">
                                    {{ stats.xp }} <span class="text-xs font-bold text-white/60">XP</span>
                                </p>
                                <p class="text-xs text-white/70">
                                    Target {{ nextLevelXp }} XP
                                </p>
                            </div>
                            <ProgressBar
                                class="mt-3"
                                :current="currentLevelXp"
                                :max="levelTargetXp"
                            />
                        </div>
                    </div>
                </section>

                <!-- KPI Stats -->
                <section
                    data-motion-item
                    aria-labelledby="student-kpi"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                >
                    <h2 id="student-kpi" class="sr-only">
                        Ringkasan belajar siswa
                    </h2>
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        :label="card.label"
                        :value="card.value"
                        :accent="card.accent"
                        :icon="card.icon"
                    />
                </section>

                <!-- Quiz of the Day & Streak Heatmap -->
                <div data-motion-section class="grid gap-6 lg:grid-cols-3">
                    <!-- Quiz of the Day Card -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <AppIcon name="star" :size="18" class="text-brand-primary dark:text-brand-secondary" />
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                    Quiz of the Day
                                </h2>
                            </div>
                            <Link
                                href="/participant/quizzes"
                                class="text-xs font-semibold text-brand-primary hover:underline dark:text-brand-secondary"
                            >
                                Lihat Semua Kuis &rarr;
                            </Link>
                        </div>

                        <div
                            v-if="quizOfTheDay"
                            class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-brand-secondary/20 px-2.5 py-0.5 text-xs font-bold text-brand-dark dark:text-brand-secondary">
                                    Rekomendasi Hari Ini
                                </span>
                                <span class="text-xs font-semibold text-brand-primary dark:text-brand-secondary">
                                    +50 XP
                                </span>
                            </div>

                            <h3 class="mt-3 text-lg font-black text-slate-900 dark:text-white">
                                {{ quizOfTheDay.title }}
                            </h3>
                            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                                {{ quizOfTheDay.description ?? 'Kuis pilihan dari organisasi untuk latihan mandiri.' }}
                            </p>

                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                <span class="inline-flex items-center gap-1.5">
                                    <AppIcon name="quiz" :size="14" />
                                    <span>{{ quizOfTheDay.questions_count }} Soal</span>
                                </span>
                                <span v-if="quizOfTheDay.deadline_at" class="inline-flex items-center gap-1.5">
                                    <AppIcon name="clock" :size="14" />
                                    <span>Batas: {{ dateFormatter.format(new Date(quizOfTheDay.deadline_at)) }}</span>
                                </span>
                                <span v-if="quizOfTheDay.best_score !== null" class="inline-flex items-center gap-1.5 text-brand-primary font-bold dark:text-brand-secondary">
                                    <AppIcon name="trophy" :size="14" />
                                    <span>Skor Terbaik: {{ quizOfTheDay.best_score }}</span>
                                </span>
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-200/80 pt-3 dark:border-slate-700">
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ quizOfTheDay.my_attempts_count ? `${quizOfTheDay.my_attempts_count} kali dikerjakan` : 'Belum pernah dicoba' }}
                                </span>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-brand-hover active:scale-95 disabled:opacity-50"
                                    :disabled="!canStart(quizOfTheDay)"
                                    @click="startQuiz(quizOfTheDay)"
                                >
                                    <AppIcon :name="quizOfTheDay.my_attempts_count ? 'repeat' : 'arrowRight'" :size="14" />
                                    <span>{{ canStart(quizOfTheDay) ? (quizOfTheDay.my_attempts_count ? 'Ulangi Kuis' : 'Mulai Sekarang') : 'Batas Selesai' }}</span>
                                </button>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-4 rounded-xl bg-slate-50 px-4 py-6 text-center text-xs text-slate-500 dark:bg-slate-800"
                        >
                            Belum ada kuis yang dipublikasikan saat ini.
                        </p>
                    </section>

                    <!-- Streak 30 Hari Card -->
                    <section
                        class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <AppIcon name="flame" :size="18" class="text-brand-secondary" />
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                        Streak Belajar
                                    </h2>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    {{ stats.streak }} Hari
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Pertahankan konsistensi belajar harianmu.
                            </p>
                            <StreakCalendar class="mt-4" :streaks="streakHeatmap" />
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                            <span class="font-medium">Total Hari Aktif (30 Hari)</span>
                            <span class="font-bold text-brand-primary dark:text-brand-secondary tabular-nums">
                                {{ streakHeatmap.filter(s => s.count > 0).length }} / 30
                            </span>
                        </div>
                    </section>
                </div>

                <!-- Kuis Tersedia & Badge Grid -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Kuis Tersedia -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                    Kuis Tersedia
                                </h2>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Pilih materi kuis untuk mengasah pemahaman.
                                </p>
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                                <button
                                    type="button"
                                    @click="quizFilter = 'all'"
                                    class="rounded-lg px-2.5 py-1 transition"
                                    :class="quizFilter === 'all' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'"
                                >
                                    Semua ({{ availableQuizzes.length }})
                                </button>
                                <button
                                    type="button"
                                    @click="quizFilter = 'unattempted'"
                                    class="rounded-lg px-2.5 py-1 transition"
                                    :class="quizFilter === 'unattempted' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'"
                                >
                                    Belum Dicoba
                                </button>
                                <button
                                    type="button"
                                    @click="quizFilter = 'retry'"
                                    class="rounded-lg px-2.5 py-1 transition"
                                    :class="quizFilter === 'retry' ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'"
                                >
                                    Bisa Diulang
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="filteredAvailableQuizzes.length"
                            class="mt-4 grid gap-3 sm:grid-cols-2"
                        >
                            <QuizCard
                                v-for="quiz in filteredAvailableQuizzes"
                                :key="quiz.id"
                                :title="quiz.title"
                                :description="quiz.description"
                                :questions-count="quiz.questions_count"
                                :state="quizState(quiz)"
                                :score="quiz.best_score"
                            >
                                <template #actions>
                                    <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-slate-700">
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            {{ quiz.my_attempts_count ? `${quiz.my_attempts_count}x dikerjakan` : 'Belum dikerjakan' }}
                                        </span>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-brand-hover active:scale-95 disabled:bg-slate-200 disabled:text-slate-400 dark:disabled:bg-slate-800"
                                            :disabled="!canStart(quiz)"
                                            @click="startQuiz(quiz)"
                                        >
                                            <AppIcon :name="quiz.my_attempts_count ? 'repeat' : 'arrowRight'" :size="12" />
                                            <span>{{ canStart(quiz) ? (quiz.my_attempts_count ? 'Ulangi' : 'Mulai') : 'Selesai' }}</span>
                                        </button>
                                    </div>
                                </template>
                            </QuizCard>
                        </div>
                        <p
                            v-else
                            class="mt-6 rounded-xl bg-slate-50 px-4 py-6 text-center text-xs font-medium text-slate-500 dark:bg-slate-800"
                        >
                            Tidak ada kuis di kategori ini.
                        </p>
                    </section>

                    <!-- Badge Collection -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <AppIcon name="badge" :size="18" class="text-brand-primary dark:text-brand-secondary" />
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                    Badge Saya
                                </h2>
                            </div>
                            <Link
                                href="/participant/badges"
                                class="text-xs font-bold text-brand-primary hover:underline dark:text-brand-secondary"
                            >
                                Panduan Lengkap &rarr;
                            </Link>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Pencapaian belajar yang telah dibuka.
                        </p>
                        <BadgeGrid
                            class="mt-4"
                            :badges="badges"
                            empty-text="Selesaikan kuis pertama untuk membuka badge."
                        />
                    </section>
                </div>

                <!-- Misi Belajar (RPG Guild Quest Board) -->
                <section
                    data-motion-section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <AppIcon name="target" :size="18" class="text-brand-primary dark:text-brand-secondary" />
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                    Papan Misi Petualang (Quest Board)
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Selesaikan bounty harian, raid mingguan, dan jalur legenda untuk panen XP dan lencana.
                                </p>
                            </div>
                        </div>

                        <!-- Quest Tabs -->
                        <div class="flex items-center gap-1.5 rounded-xl bg-slate-100 p-1 text-xs font-bold dark:bg-slate-800">
                            <button
                                type="button"
                                @click="activeMissionTab = 'all'"
                                class="rounded-lg px-2.5 py-1 transition"
                                :class="activeMissionTab === 'all' ? 'bg-white text-brand-primary shadow-xs dark:bg-slate-900 dark:text-brand-secondary' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                            >
                                Semua ({{ missions.length }})
                            </button>
                            <button
                                type="button"
                                @click="activeMissionTab = 'daily'"
                                class="rounded-lg px-2.5 py-1 transition"
                                :class="activeMissionTab === 'daily' ? 'bg-white text-brand-primary shadow-xs dark:bg-slate-900 dark:text-brand-secondary' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                            >
                                Bounty Harian
                            </button>
                            <button
                                type="button"
                                @click="activeMissionTab = 'weekly'"
                                class="rounded-lg px-2.5 py-1 transition"
                                :class="activeMissionTab === 'weekly' ? 'bg-white text-brand-primary shadow-xs dark:bg-slate-900 dark:text-brand-secondary' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                            >
                                Raid Mingguan
                            </button>
                            <button
                                type="button"
                                @click="activeMissionTab = 'campaign'"
                                class="rounded-lg px-2.5 py-1 transition"
                                :class="activeMissionTab === 'campaign' ? 'bg-white text-brand-primary shadow-xs dark:bg-slate-900 dark:text-brand-secondary' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                            >
                                Jalur Legenda
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="filteredMissions.length"
                        class="mt-4 grid gap-3 md:grid-cols-3"
                    >
                        <article
                            v-for="mission in filteredMissions"
                            :key="mission.key"
                            class="flex flex-col justify-between rounded-xl border p-4 transition duration-200"
                            :class="[
                                mission.completed_at || mission.progress >= mission.goal
                                    ? 'border-status-success/40 bg-slate-50/70 dark:border-status-success/30 dark:bg-slate-800/50'
                                    : 'border-slate-200/80 bg-white hover:border-brand-primary/40 dark:border-slate-800 dark:bg-slate-900',
                            ]"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-bold"
                                        :class="[
                                            mission.kind === 'daily'
                                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                                                : mission.kind === 'weekly'
                                                ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300'
                                                : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                        ]"
                                    >
                                        <AppIcon :name="mission.kind === 'daily' ? 'target' : mission.kind === 'weekly' ? 'calendar' : 'trophy'" :size="12" />
                                        <span>{{ mission.kind === 'daily' ? 'Bounty Harian' : mission.kind === 'weekly' ? 'Raid Mingguan' : 'Jalur Legenda' }}</span>
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-primary/10 px-2 py-0.5 text-xs font-bold text-brand-primary dark:text-brand-secondary">
                                        <AppIcon name="xp" :size="12" />
                                        <span>+{{ mission.reward_xp }} XP</span>
                                    </span>
                                </div>

                                <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">
                                    {{ mission.title }}
                                </h3>
                                <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                                    {{ mission.description }}
                                </p>

                                <div v-if="missionBadgeLink[mission.key]" class="mt-2.5 inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200/50 dark:border-amber-800/40">
                                    <AppIcon name="badge" :size="11" />
                                    <span>Membuka Lencana: [{{ missionBadgeLink[mission.key] }}]</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <ProgressBar
                                    :current="mission.progress"
                                    :max="mission.goal"
                                    :label="`${mission.progress}/${mission.goal}`"
                                />
                                <div class="mt-2 flex items-center justify-between text-xs font-semibold">
                                    <span
                                        :class="mission.completed_at || mission.progress >= mission.goal ? 'text-status-success font-bold inline-flex items-center gap-1' : 'text-slate-500'"
                                    >
                                        <AppIcon v-if="mission.completed_at || mission.progress >= mission.goal" name="check" :size="12" />
                                        <span>{{ mission.completed_at || mission.progress >= mission.goal ? 'Quest Tuntas ✨' : `${mission.progress}/${mission.goal} selesai` }}</span>
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <p
                        v-else
                        class="mt-4 rounded-xl bg-slate-50 px-4 py-6 text-center text-xs font-medium text-slate-500 dark:bg-slate-800"
                    >
                        Belum ada quest di kategori ini.
                    </p>
                </section>

                <!-- Riwayat Attempt Terbaru -->
                <section
                    data-motion-section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <AppIcon name="results" :size="18" class="text-brand-primary dark:text-brand-secondary" />
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                Riwayat Pengerjaan
                            </h2>
                        </div>
                        <Link
                            href="/attempts"
                            class="text-xs font-semibold text-brand-primary hover:underline dark:text-brand-secondary"
                        >
                            Semua Riwayat &rarr;
                        </Link>
                    </div>
                    <ActivityFeed
                        class="mt-4"
                        :items="attemptItems"
                        empty-text="Belum ada attempt. Mulai dari Quiz of the Day."
                    />
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
