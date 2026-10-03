<script setup lang="ts">
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import { gsap } from 'gsap';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

Chart.register(...registerables);

type DailyPoint = { date: string; count: number; avg_score: number | null };
type RoleCount = { role: string; count: number };
type TopOrg = {
    id: number;
    name: string;
    users_count: number;
    attempts_count: number;
};

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        organizations: number;
        users: number;
        quizzes: number;
        questions: number;
        attempts: number;
        liveSessions: number;
    };
    dailyAttempts: DailyPoint[];
    roleCounts: RoleCount[];
    topOrgs: TopOrg[];
    reverbHealth: {
        reverb_status: string;
        reverb_host: string;
        reverb_latency_ms: number | null;
    };
    health: { queued_jobs: number; failed_jobs: number; ai_failures: number };
    recentAttempts: {
        id: number;
        quiz: string;
        participant: string;
        score: number | null;
        status: string;
        created_at: string;
    }[];
}>();

const statCards = computed(() => [
    {
        label: 'Organisasi',
        value: props.stats.organizations,
        accent: 'bg-brand-primary',
        icon: 'organization',
    },
    {
        label: 'Pengguna',
        value: props.stats.users,
        accent: 'bg-brand-primary',
        icon: 'users',
    },
    {
        label: 'Kuis',
        value: props.stats.quizzes,
        accent: 'bg-brand-dark',
        icon: 'quiz',
    },
    {
        label: 'Soal',
        value: props.stats.questions,
        accent: 'bg-brand-dark',
        icon: 'question',
    },
    {
        label: 'Attempts',
        value: props.stats.attempts,
        accent: 'bg-brand-secondary',
        icon: 'results',
    },
    {
        label: 'Live Aktif',
        value: props.stats.liveSessions,
        accent: 'bg-brand-secondary',
        icon: 'live',
    },
]);

const trendRef = ref<HTMLCanvasElement | null>(null);
const roleRef = ref<HTMLCanvasElement | null>(null);
const topRef = ref<HTMLCanvasElement | null>(null);
const motionRoot = ref<HTMLElement | null>(null);
let chartTrend: Chart | null = null;
let chartRole: Chart | null = null;
let chartTop: Chart | null = null;
let motionContext: gsap.Context | null = null;
let motionMedia: gsap.MatchMedia | null = null;

const roleLabelMap: Record<string, string> = {
    super_admin: 'Super Admin',
    organization_admin: 'Org Admin',
    creator: 'Creator',
    participant: 'Participant',
};

function destroyCharts() {
    chartTrend?.destroy();
    chartRole?.destroy();
    chartTop?.destroy();
}

onMounted(() => {
    const labels = props.dailyAttempts.map((d) => {
        const dt = new Date(d.date);
        return dt.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
        });
    });

    if (trendRef.value) {
        chartTrend = new Chart(trendRef.value, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Avg Skor',
                        data: props.dailyAttempts.map((d) => d.avg_score ?? 0),
                        borderColor: '#90CB31',
                        backgroundColor: '#90CB31',
                        yAxisID: 'y1',
                        tension: 0.35,
                        pointRadius: 4,
                    },
                    {
                        type: 'bar',
                        label: 'Attempts',
                        data: props.dailyAttempts.map((d) => d.count),
                        backgroundColor: 'rgba(255, 255, 255, 0.85)',
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'rgba(255, 255, 255, 0.9)' },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: 'rgba(255, 255, 255, 0.8)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: 'rgba(255, 255, 255, 0.8)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: 'rgba(255, 255, 255, 0.8)' },
                        max: 100,
                    },
                },
            },
        });
    }

    if (roleRef.value) {
        chartRole = new Chart(roleRef.value, {
            type: 'doughnut',
            data: {
                labels: props.roleCounts.map(
                    (r) => roleLabelMap[r.role] ?? r.role,
                ),
                datasets: [
                    {
                        data: props.roleCounts.map((r) => r.count),
                        backgroundColor: [
                            '#90CB31',
                            '#60A5FA',
                            '#F59E0B',
                            '#EC4899',
                            '#A7F3D0',
                        ],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'rgba(255, 255, 255, 0.9)' },
                    },
                },
            },
        });
    }

    if (topRef.value) {
        chartTop = new Chart(topRef.value, {
            type: 'bar',
            data: {
                labels: props.topOrgs.map((o) => o.name),
                datasets: [
                    {
                        label: 'Attempts',
                        data: props.topOrgs.map((o) => o.attempts_count),
                        backgroundColor: '#90CB31',
                        borderRadius: 6,
                    },
                    {
                        label: 'Members',
                        data: props.topOrgs.map((o) => o.users_count),
                        backgroundColor: 'rgba(255, 255, 255, 0.85)',
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'rgba(255, 255, 255, 0.9)' },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: 'rgba(255, 255, 255, 0.8)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    },
                    y: {
                        ticks: { color: 'rgba(255, 255, 255, 0.8)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    },
                },
            },
        });
    }

    motionMedia = gsap.matchMedia();
    motionMedia.add('(prefers-reduced-motion: no-preference)', () => {
        motionContext = gsap.context(() => {
            gsap.from('[data-motion-item]', {
                autoAlpha: 0,
                y: 20,
                duration: 0.45,
                stagger: 0.08,
                ease: 'power3.out',
                clearProps: 'all',
            });
        }, motionRoot.value ?? undefined);
    });
});

onBeforeUnmount(() => {
    destroyCharts();
    motionMedia?.revert();
    motionContext?.revert();
});
</script>

<template>
    <Head title="Super Admin — Dashboard Eksekutif" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main
                ref="motionRoot"
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <!-- Hero -->
                <section
                    data-motion-item
                    class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8 dark:bg-brand-primary"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-brand-secondary"
                                    aria-hidden="true"
                                ></span>
                                Super Admin · {{ organization.name }}
                            </p>
                            <h1
                                class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl"
                            >
                                Dashboard Eksekutif — Platform Kuesify
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/80"
                            >
                                Pantau kesehatan platform, tren attempt 7 hari,
                                distribusi role, dan top organisasi dari satu
                                tempat.
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold text-white"
                                :class="
                                    reverbHealth.reverb_status === 'online'
                                        ? 'bg-brand-secondary/20 text-white'
                                        : 'bg-status-danger/20'
                                "
                            >
                                <span
                                    class="h-2 w-2 rounded-full"
                                    :class="
                                        reverbHealth.reverb_status === 'online'
                                            ? 'bg-brand-secondary'
                                            : 'bg-status-danger'
                                    "
                                ></span>
                                Reverb {{ reverbHealth.reverb_status }} ·
                                {{ reverbHealth.reverb_latency_ms ?? '—' }} ms
                            </span>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section
                    data-motion-item
                    aria-labelledby="kpi-title"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                >
                    <h2 id="kpi-title" class="sr-only">Ringkasan platform</h2>
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        :label="card.label"
                        :value="card.value"
                        :accent="card.accent"
                        :icon="card.icon"
                    />
                </section>

                <!-- Charts row 1 -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        data-motion-item
                        class="ui-card-hover rounded-2xl border border-brand-dark bg-brand-primary p-5 text-white shadow-figma lg:col-span-2 dark:border-brand-dark dark:bg-brand-primary"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-base font-bold text-white">
                                    Tren 7 hari — Volume & Skor
                                </h2>
                                <p class="mt-1 text-xs text-white/80">
                                    Bar = attempts, garis = rata-rata skor
                                    harian.
                                </p>
                            </div>
                        </div>
                        <div class="h-[280px]">
                            <canvas
                                ref="trendRef"
                                aria-label="Grafik tren harian"
                            ></canvas>
                        </div>
                    </section>
                    <section
                        data-motion-item
                        class="ui-card-hover rounded-2xl border border-brand-dark bg-brand-primary p-5 text-white shadow-figma dark:border-brand-dark dark:bg-brand-primary"
                    >
                        <h2 class="text-base font-bold text-white">
                            Distribusi Role
                        </h2>
                        <p class="mt-1 text-xs text-white/80">
                            Sebaran membership lintas tenant.
                        </p>
                        <div class="mt-4 h-[280px]">
                            <canvas
                                ref="roleRef"
                                aria-label="Grafik distribusi role"
                            ></canvas>
                        </div>
                    </section>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        data-motion-item
                        class="ui-card-hover rounded-2xl border border-brand-dark bg-brand-primary p-5 text-white shadow-figma lg:col-span-2 dark:border-brand-dark dark:bg-brand-primary"
                    >
                        <h2 class="text-base font-bold text-white">
                            Top 5 Organisasi — Aktivitas
                        </h2>
                        <p class="mt-1 text-xs text-white/80">
                            Urut by attempts terbanyak (tanpa global scope).
                        </p>
                        <div class="mt-4 h-[280px]">
                            <canvas
                                ref="topRef"
                                aria-label="Grafik top organisasi"
                            ></canvas>
                        </div>
                    </section>
                    <div class="grid gap-6">
                        <section
                            data-motion-item
                            class="ui-card-hover rounded-2xl border border-brand-dark bg-brand-primary p-5 text-white shadow-figma dark:border-brand-dark dark:bg-brand-primary"
                        >
                            <h2 class="text-base font-bold text-white">
                                Kesehatan Sistem
                            </h2>
                            <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                                <div
                                    class="rounded-xl bg-white/10 px-2 py-3 backdrop-blur-sm"
                                >
                                    <dt
                                        class="text-xs font-semibold text-white/80"
                                    >
                                        Queue
                                    </dt>
                                    <dd
                                        class="mt-1 text-lg font-black text-brand-secondary"
                                    >
                                        {{ health.queued_jobs }}
                                    </dd>
                                </div>
                                <div
                                    class="rounded-xl bg-white/10 px-2 py-3 backdrop-blur-sm"
                                >
                                    <dt
                                        class="text-xs font-semibold text-white/80"
                                    >
                                        Failed
                                    </dt>
                                    <dd
                                        class="mt-1 text-lg font-black text-rose-300"
                                    >
                                        {{ health.failed_jobs }}
                                    </dd>
                                </div>
                                <div
                                    class="rounded-xl bg-white/10 px-2 py-3 backdrop-blur-sm"
                                >
                                    <dt
                                        class="text-xs font-semibold text-white/80"
                                    >
                                        AI Gagal
                                    </dt>
                                    <dd
                                        class="mt-1 text-lg font-black text-slate-200"
                                    >
                                        {{ health.ai_failures }}
                                    </dd>
                                </div>
                            </dl>
                            <p class="mt-3 text-xs text-white/80">
                                Host Reverb: {{ reverbHealth.reverb_host }}
                            </p>
                        </section>
                        <section
                            data-motion-item
                            class="ui-card-hover rounded-2xl border border-brand-dark bg-brand-primary p-5 text-white shadow-figma dark:border-brand-dark dark:bg-brand-primary"
                        >
                            <h2 class="text-base font-bold text-white">
                                Live Feed — Attempt Terbaru
                            </h2>
                            <div
                                v-if="recentAttempts.length"
                                class="mt-3 divide-y divide-white/10"
                            >
                                <div
                                    v-for="a in recentAttempts"
                                    :key="a.id"
                                    class="flex items-center justify-between gap-3 py-2.5"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-white"
                                        >
                                            {{ a.quiz }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-white/80"
                                        >
                                            {{ a.participant }} · {{ a.status }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full bg-brand-secondary/20 px-2.5 py-1 text-xs font-bold text-brand-secondary"
                                        >{{ a.score ?? '—' }}</span
                                    >
                                </div>
                            </div>
                            <p v-else class="mt-3 text-sm text-white/80">
                                Belum ada attempt.
                            </p>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
