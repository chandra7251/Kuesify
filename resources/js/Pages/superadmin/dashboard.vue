<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import { onBeforeUnmount, onMounted, ref } from 'vue';

Chart.register(...registerables);

type DailyPoint = { date: string; count: number; avg_score: number | null };
type RoleCount = { role: string; count: number };
type TopOrg = { id: number; name: string; users_count: number; attempts_count: number };

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
    reverbHealth: { reverb_status: string; reverb_host: string; reverb_latency_ms: number | null };
    health: { queued_jobs: number; failed_jobs: number; ai_failures: number };
    recentAttempts: { id: number; quiz: string; participant: string; score: number | null; status: string; created_at: string }[];
}>();

const trendRef = ref<HTMLCanvasElement | null>(null);
const roleRef = ref<HTMLCanvasElement | null>(null);
const topRef = ref<HTMLCanvasElement | null>(null);
let chartTrend: Chart | null = null;
let chartRole: Chart | null = null;
let chartTop: Chart | null = null;

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
        return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
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
                        borderColor: '#3154D5',
                        backgroundColor: '#3154D5',
                        yAxisID: 'y1',
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        type: 'bar',
                        label: 'Attempts',
                        data: props.dailyAttempts.map((d) => d.count),
                        backgroundColor: 'rgba(144,203,49,0.85)',
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, max: 100 },
                },
            },
        });
    }

    if (roleRef.value) {
        chartRole = new Chart(roleRef.value, {
            type: 'doughnut',
            data: {
                labels: props.roleCounts.map((r) => roleLabelMap[r.role] ?? r.role),
                datasets: [
                    {
                        data: props.roleCounts.map((r) => r.count),
                        backgroundColor: ['#3154D5', '#90CB31', '#233EA8', '#D7A928', '#7C869C'],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    if (topRef.value) {
        chartTop = new Chart(topRef.value, {
            type: 'bar',
            data: {
                labels: props.topOrgs.map((o) => o.name),
                datasets: [
                    { label: 'Attempts', data: props.topOrgs.map((o) => o.attempts_count), backgroundColor: '#3154D5', borderRadius: 6 },
                    { label: 'Members', data: props.topOrgs.map((o) => o.users_count), backgroundColor: 'rgba(144,203,49,0.85)', borderRadius: 6 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { position: 'bottom' } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
});

onBeforeUnmount(destroyCharts);
</script>

<template>
    <Head title="Super Admin — Dashboard Eksekutif" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Hero -->
                <section class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                <span class="h-2 w-2 rounded-full bg-brand-secondary" aria-hidden="true"></span>
                                Super Admin · {{ organization.name }}
                            </p>
                            <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                                Dashboard Eksekutif — Platform Kuesify
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                                Pantau kesehatan platform, tren attempt 7 hari, distribusi role, dan top organisasi dari satu tempat.
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold text-white"
                                :class="reverbHealth.reverb_status === 'online' ? 'bg-brand-secondary/20 text-white' : 'bg-status-danger/20'"
                            >
                                <span class="h-2 w-2 rounded-full" :class="reverbHealth.reverb_status === 'online' ? 'bg-brand-secondary' : 'bg-status-danger'"></span>
                                Reverb {{ reverbHealth.reverb_status }} · {{ reverbHealth.reverb_latency_ms ?? '—' }} ms
                            </span>
                        </div>
                    </div>
                </section>

                <!-- KPI -->
                <section aria-labelledby="kpi-title" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <h2 id="kpi-title" class="sr-only">Ringkasan platform</h2>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Organisasi</p>
                        <p class="mt-1 text-2xl font-black text-brand-primary">{{ stats.organizations }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengguna</p>
                        <p class="mt-1 text-2xl font-black text-brand-primary">{{ stats.users }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kuis</p>
                        <p class="mt-1 text-2xl font-black text-brand-dark">{{ stats.quizzes }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Soal</p>
                        <p class="mt-1 text-2xl font-black text-brand-dark">{{ stats.questions }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Attempts</p>
                        <p class="mt-1 text-2xl font-black text-[#527A12]">{{ stats.attempts }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Live Aktif</p>
                        <p class="mt-1 text-2xl font-black text-[#527A12]">{{ stats.liveSessions }}</p>
                    </article>
                </section>

                <!-- Charts row 1 -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Tren 7 hari — Volume & Skor</h2>
                                <p class="mt-1 text-xs text-slate-500">Bar = attempts, garis = rata-rata skor harian.</p>
                            </div>
                        </div>
                        <div class="h-[280px]">
                            <canvas ref="trendRef" aria-label="Grafik tren harian"></canvas>
                        </div>
                    </section>
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                        <h2 class="text-base font-bold text-slate-900">Distribusi Role</h2>
                        <p class="mt-1 text-xs text-slate-500">Sebaran membership lintas tenant.</p>
                        <div class="mt-4 h-[280px]">
                            <canvas ref="roleRef" aria-label="Grafik distribusi role"></canvas>
                        </div>
                    </section>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <h2 class="text-base font-bold text-slate-900">Top 5 Organisasi — Aktivitas</h2>
                        <p class="mt-1 text-xs text-slate-500">Urut by attempts terbanyak (tanpa global scope).</p>
                        <div class="mt-4 h-[280px]">
                            <canvas ref="topRef" aria-label="Grafik top organisasi"></canvas>
                        </div>
                    </section>
                    <div class="grid gap-6">
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                            <h2 class="text-base font-bold text-slate-900">Kesehatan Sistem</h2>
                            <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                                <div class="rounded-xl bg-brand-accent px-2 py-3">
                                    <dt class="text-xs font-semibold text-slate-500">Queue</dt>
                                    <dd class="mt-1 text-lg font-black text-brand-primary">{{ health.queued_jobs }}</dd>
                                </div>
                                <div class="rounded-xl bg-brand-accent px-2 py-3">
                                    <dt class="text-xs font-semibold text-slate-500">Failed</dt>
                                    <dd class="mt-1 text-lg font-black text-status-danger">{{ health.failed_jobs }}</dd>
                                </div>
                                <div class="rounded-xl bg-brand-accent px-2 py-3">
                                    <dt class="text-xs font-semibold text-slate-500">AI Gagal</dt>
                                    <dd class="mt-1 text-lg font-black text-[#7C869C]">{{ health.ai_failures }}</dd>
                                </div>
                            </dl>
                            <p class="mt-3 text-xs text-slate-500">Host Reverb: {{ reverbHealth.reverb_host }}</p>
                        </section>
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                            <h2 class="text-base font-bold text-slate-900">Live Feed — Attempt Terbaru</h2>
                            <div v-if="recentAttempts.length" class="mt-3 divide-y divide-slate-100">
                                <div v-for="a in recentAttempts" :key="a.id" class="flex items-center justify-between gap-3 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ a.quiz }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ a.participant }} · {{ a.status }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-[#527A12]">{{ a.score ?? '—' }}</span>
                                </div>
                            </div>
                            <p v-else class="mt-3 text-sm text-slate-500">Belum ada attempt.</p>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
