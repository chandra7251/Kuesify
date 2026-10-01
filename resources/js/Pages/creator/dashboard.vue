<script setup lang="ts">
import ActivityFeed from '@/Components/ActivityFeed.vue';
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref } from 'vue';

type RecentQuiz = {
    id: number;
    title: string;
    status: string;
    questions_count: number;
    updated_at: string | null;
};

type Activity = {
    id: number;
    quiz_title: string;
    participant_name: string;
    status: string;
    score: number;
    updated_at: string | null;
};

type WeakTopic = {
    id: number;
    prompt: string;
    total: number;
    correct_rate: number | null;
};

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        quizzes: number;
        questions: number;
        liveSessions: number;
        attempts: number;
    };
    analytics: {
        studentRetention: number | null;
        weakTopics: WeakTopic[];
        avgTimePerQuestion: number | null;
    };
    recentQuizzes: RecentQuiz[];
    recentActivity: Activity[];
}>();

const statCards = computed(() => [
    {
        label: 'Kuis saya',
        value: props.stats.quizzes,
        accent: 'bg-brand-primary',
        icon: 'quiz',
    },
    {
        label: 'Soal aktif',
        value: props.stats.questions,
        accent: 'bg-support-1',
        icon: 'question',
    },
    {
        label: 'Live aktif',
        value: props.stats.liveSessions,
        accent: 'bg-support-3',
        icon: 'live',
    },
    {
        label: 'Total attempt',
        value: props.stats.attempts,
        accent: 'bg-brand-secondary',
        icon: 'results',
    },
]);

const retentionLabel = computed(() =>
    props.analytics.studentRetention === null
        ? 'Belum ada data'
        : `${props.analytics.studentRetention}%`,
);
const avgTimeLabel = computed(() =>
    props.analytics.avgTimePerQuestion === null
        ? 'Belum dilacak'
        : `${props.analytics.avgTimePerQuestion} detik`,
);

const activityItems = computed(() =>
    props.recentActivity.map((item) => ({
        id: item.id,
        title: `${item.participant_name} · ${item.quiz_title}`,
        subtitle: item.status,
        meta: `${item.score} poin`,
    })),
);

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
});

const motionRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    const mm = gsap.matchMedia();
    mm.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            gsap.from('[data-motion-item]', {
                opacity: 0,
                y: 22,
                duration: 0.4,
                stagger: 0.07,
                ease: 'power2.out',
                clearProps: 'all',
            });
        }, motionRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <Head title="Dashboard Creator" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main
                ref="motionRoot"
                data-motion="creator-dashboard"
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <section
                    data-motion-item
                    class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8"
                >
                    <p
                        class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-brand-secondary"
                            aria-hidden="true"
                        ></span>
                        Creator · {{ organization.name }}
                    </p>
                    <div
                        class="mt-4 grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-end"
                    >
                        <div>
                            <h1
                                class="max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl"
                            >
                                Pusat kontrol pembelajaran
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/80"
                            >
                                Pantau kuis, aktivitas siswa, dan topik lemah
                                dari satu dashboard creator.
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                            <Link
                                href="/quizzes"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-secondary px-5 text-sm font-bold text-white shadow-sm transition hover:brightness-105"
                                >Buat kuis</Link
                            >
                            <Link
                                href="/reports"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15"
                                >Lihat laporan</Link
                            >
                        </div>
                    </div>
                </section>

                <section
                    data-motion-item
                    aria-labelledby="creator-kpi"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-4"
                >
                    <h2 id="creator-kpi" class="sr-only">Ringkasan creator</h2>
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        :label="card.label"
                        :value="card.value"
                        :accent="card.accent"
                        :icon="card.icon"
                    />
                </section>

                <section
                    data-motion-item
                    aria-labelledby="creator-insight"
                    class="grid gap-4 lg:grid-cols-3"
                >
                    <h2 id="creator-insight" class="sr-only">
                        Insight kuis creator
                    </h2>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Student retention
                        </p>
                        <p class="mt-2 text-3xl font-black text-brand-primary">
                            {{ retentionLabel }}
                        </p>
                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Peserta yang menyelesaikan attempt dibanding peserta
                            yang mulai.
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Avg time/question
                        </p>
                        <p class="mt-2 text-3xl font-black text-support-3">
                            {{ avgTimeLabel }}
                        </p>
                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Rata-rata waktu dari attempt mulai sampai jawaban
                            tersimpan.
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Weak topics
                        </p>
                        <p class="mt-2 text-3xl font-black text-support-1">
                            {{ analytics.weakTopics.length }}
                        </p>
                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Diurutkan dari correct rate terendah.
                        </p>
                    </article>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    Kuis terbaru
                                </h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    Kuis milik creator aktif.
                                </p>
                            </div>
                            <Link
                                href="/quizzes"
                                class="rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-primary hover:bg-brand-accent"
                                >Kelola</Link
                            >
                        </div>
                        <div
                            v-if="recentQuizzes.length"
                            class="mt-4 divide-y divide-slate-100"
                        >
                            <div
                                v-for="quiz in recentQuizzes"
                                :key="quiz.id"
                                class="flex items-center justify-between gap-3 py-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ quiz.title }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ quiz.questions_count }} soal · Update
                                        {{
                                            quiz.updated_at
                                                ? dateFormatter.format(
                                                      new Date(quiz.updated_at),
                                                  )
                                                : '-'
                                        }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-[#527A12]"
                                    >{{ quiz.status }}</span
                                >
                            </div>
                        </div>
                        <p
                            v-else
                            class="mt-6 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600"
                        >
                            Belum ada kuis. Mulai dari tombol Buat kuis.
                        </p>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <h2 class="text-base font-bold text-slate-900">
                            Topik lemah
                        </h2>
                        <div
                            v-if="analytics.weakTopics.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="topic in analytics.weakTopics"
                                :key="topic.id"
                                class="rounded-xl bg-brand-accent p-3"
                            >
                                <p
                                    class="line-clamp-2 text-sm font-semibold text-slate-900"
                                >
                                    {{ topic.prompt }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Correct rate {{ topic.correct_rate ?? 0 }}%
                                    dari {{ topic.total }} jawaban
                                </p>
                            </div>
                        </div>
                        <p
                            v-else
                            class="mt-4 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600"
                        >
                            Belum ada jawaban siswa untuk dianalisis.
                        </p>
                    </section>
                </div>

                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                >
                    <h2 class="text-base font-bold text-slate-900">
                        Aktivitas siswa terbaru
                    </h2>
                    <ActivityFeed
                        class="mt-4"
                        :items="activityItems"
                        empty-text="Belum ada attempt dari siswa."
                    />
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
