<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

interface AdminAnalytics {
    kpi: {
        total_organizations: number;
        total_users: number;
        total_quizzes: number;
        total_attempts: number;
        avg_platform_score: number;
        active_live_sessions: number;
        ai_generations_total: number;
        ai_generations_month: number;
        queued_jobs: number;
        failed_jobs: number;
        reverb_running: boolean;
        reverb_latency_ms: number;
    };
    charts: {
        attempts_trend: {
            labels: string[];
            attempts: number[];
            scores: number[];
        };
        role_distribution: {
            labels: string[];
            data: number[];
        };
        top_organizations: {
            labels: string[];
            attempts: number[];
            members: number[];
            items: Array<{
                id: number;
                name: string;
                code: string;
                users_count: number;
                attempts_count: number;
            }>;
        };
    };
    recent_attempts: Array<{
        id: number;
        quiz_title: string;
        participant_name: string;
        organization_name: string;
        score: number;
        status: string;
        created_at: string;
    }>;
}

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    isSuperAdmin?: boolean;
    adminAnalytics?: AdminAnalytics | null;
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

const trendCanvas = ref<HTMLCanvasElement | null>(null);
const rolesCanvas = ref<HTMLCanvasElement | null>(null);
const topOrgsCanvas = ref<HTMLCanvasElement | null>(null);

let trendChart: Chart | null = null;
let rolesChart: Chart | null = null;
let topOrgsChart: Chart | null = null;

onMounted(async () => {
    if (!props.isSuperAdmin || !props.adminAnalytics) return;

    await nextTick();

    // 1. Line Chart: Tren Pengerjaan & Skor 7 Hari Terakhir
    if (trendCanvas.value) {
        trendChart = new Chart(trendCanvas.value, {
            type: 'line',
            data: {
                labels: props.adminAnalytics.charts.attempts_trend.labels,
                datasets: [
                    {
                        label: 'Total Attempts Pengerjaan',
                        data: props.adminAnalytics.charts.attempts_trend.attempts,
                        borderColor: '#3154D5',
                        backgroundColor: 'rgba(49, 84, 213, 0.08)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y',
                        pointBackgroundColor: '#3154D5',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Rata-Rata Skor',
                        data: props.adminAnalytics.charts.attempts_trend.scores,
                        borderColor: '#0AB883',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        borderDash: [4, 4],
                        tension: 0.35,
                        yAxisID: 'y1',
                        pointBackgroundColor: '#0AB883',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Figtree, sans-serif', size: 12, weight: 'bold' },
                            usePointStyle: true,
                            boxWidth: 8,
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: 'bold' }, color: '#64748b' },
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, color: '#64748b', precision: 0 },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 100,
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 11 }, color: '#0AB883' },
                    },
                },
            },
        });
    }

    // 2. Doughnut Chart: Distribusi Role Pengguna
    if (rolesCanvas.value) {
        rolesChart = new Chart(rolesCanvas.value, {
            type: 'doughnut',
            data: {
                labels: props.adminAnalytics.charts.role_distribution.labels,
                datasets: [
                    {
                        data: props.adminAnalytics.charts.role_distribution.data,
                        backgroundColor: [
                            '#3154D5', // Siswa
                            '#0AB883', // Guru
                            '#F59E0B', // Admin Sekolah
                            '#8B5CF6', // Super Admin
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Figtree, sans-serif', size: 11, weight: 'bold' },
                            usePointStyle: true,
                            padding: 14,
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
            },
        });
    }

    // 3. Bar Chart: Top 5 Organisasi Teraktif
    if (topOrgsCanvas.value) {
        topOrgsChart = new Chart(topOrgsCanvas.value, {
            type: 'bar',
            data: {
                labels: props.adminAnalytics.charts.top_organizations.labels,
                datasets: [
                    {
                        label: 'Total Attempts Pengerjaan',
                        data: props.adminAnalytics.charts.top_organizations.attempts,
                        backgroundColor: '#3154D5',
                        borderRadius: 6,
                        barThickness: 16,
                    },
                    {
                        label: 'Member Terdaftar',
                        data: props.adminAnalytics.charts.top_organizations.members,
                        backgroundColor: '#90CB31',
                        borderRadius: 6,
                        barThickness: 16,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Figtree, sans-serif', size: 11, weight: 'bold' },
                            usePointStyle: true,
                            boxWidth: 8,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: 'bold' }, color: '#64748b' },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, color: '#64748b', precision: 0 },
                    },
                },
            },
        });
    }
});

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
    <Head :title="isSuperAdmin ? 'Executive Dashboard' : 'Dashboard'" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-[#E6F1F5]">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                <!-- ═════════════════════════════════════════════════════════════════ -->
                <!-- VIEW 1: EXECUTIVE SAAS DASHBOARD KHUSUS SUPER ADMIN            -->
                <!-- ═════════════════════════════════════════════════════════════════ -->
                <template v-if="isSuperAdmin && adminAnalytics">
                    <!-- HERO BANNER SUPER ADMIN -->
                    <section class="overflow-hidden rounded-2xl bg-[#3b5fe1] px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                    <span class="h-2 w-2 rounded-full bg-[#90CB31]"></span>
                                    SaaS Platform Owner · Executive Dashboard
                                </p>
                                <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                                    Selamat Datang, {{ $page.props.auth.user.name }}
                                </h1>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base">
                                    Pantau metrik analitik ekosistem multi-tenant, tren pengerjaan kuis, utilisasi AI, dan kesehatan infrastruktur server secara real-time.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- EXECUTIVE KPI METRIC CARDS -->
                    <section aria-labelledby="executive-summary">
                        <div class="mb-3">
                            <h2 id="executive-summary" class="text-lg font-bold text-slate-950">Ringkasan Ekosistem Multi-Tenant</h2>
                            <p class="mt-1 text-sm text-slate-600">Statistik performa dan utilisasi seluruh platform Kuesify.</p>
                        </div>
                        <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4">
                            <article class="border-b border-r border-slate-100 p-5 sm:border-b-0 sm:p-6">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Instansi</p>
                                <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ adminAnalytics.kpi.total_organizations }}</p>
                                <p class="mt-1 text-xs text-slate-500">Sekolah & Tenant Aktif</p>
                            </article>

                            <article class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r sm:p-6">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengguna</p>
                                <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ adminAnalytics.kpi.total_users }}</p>
                                <p class="mt-1 text-xs text-slate-500">Siswa, Guru & Admin</p>
                            </article>

                            <article class="border-r border-slate-100 p-5 sm:p-6">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Attempts</p>
                                <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ adminAnalytics.kpi.total_attempts }}</p>
                                <p class="mt-1 text-xs text-slate-500">Pengerjaan Kuis Selesai</p>
                            </article>

                            <article class="p-5 sm:p-6">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-Rata Skor</p>
                                <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ adminAnalytics.kpi.avg_platform_score }}</p>
                                <p class="mt-1 text-xs text-slate-500">Skor Agregat Nasional</p>
                            </article>
                        </div>
                    </section>

                    <!-- GRAPHS SECTION (CHART.JS) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- CHART 1: TREN ATTEMPTS & SKOR (2 SPAN) -->
                        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">Tren Pengerjaan & Skor Kuis (7 Hari Terakhir)</h3>
                                    <p class="text-xs text-slate-500">Volume attempts pengerjaan harian vs rata-rata skor peserta</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#3154D5] border border-blue-100">
                                    <i class="fa-solid fa-chart-line mr-1"></i> Live Metric
                                </span>
                            </div>

                            <div class="h-64 w-full relative">
                                <canvas ref="trendCanvas"></canvas>
                            </div>
                        </div>

                        <!-- CHART 2: DISTRIBUSI ROLE PENGGUNA (1 SPAN) -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">Distribusi Pengguna</h3>
                                    <p class="text-xs text-slate-500">Proporsi role pengguna se-platform</p>
                                </div>
                                <i class="fa-solid fa-chart-pie text-[#3154D5] text-base"></i>
                            </div>

                            <div class="h-64 w-full relative flex items-center justify-center">
                                <canvas ref="rolesCanvas"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- SECOND ROW: TOP ORGS & INFRASTRUCTURE / AI STATUS -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- CHART 3: TOP 5 ORGANISASI -->
                        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-base">Top 5 Instansi / Sekolah Teraktif</h3>
                                    <p class="text-xs text-slate-500">Perbandingan jumlah pengerjaan kuis dan member terdaftar</p>
                                </div>
                                <i class="fa-solid fa-ranking-star text-[#0AB883] text-base"></i>
                            </div>

                            <div class="h-60 w-full relative">
                                <canvas ref="topOrgsCanvas"></canvas>
                            </div>
                        </div>

                        <!-- STATUS INFRASTRUKTUR & UTILISASI AI -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Status Sistem & AI Generator</h3>
                                <p class="text-xs text-slate-500">Kesehatan queue, WebSocket & kuota AI</p>
                            </div>

                            <div class="space-y-3">
                                <!-- Reverb Status -->
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#3154D5] flex items-center justify-center">
                                            <i class="fa-solid fa-network-wired text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">WebSocket Reverb</div>
                                            <div class="text-[11px] text-slate-500">Latensi: {{ adminAnalytics.kpi.reverb_latency_ms }}ms</div>
                                        </div>
                                    </div>
                                    <span
                                        :class="adminAnalytics.kpi.reverb_running ? 'bg-emerald-50 text-[#0AB883] border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                        class="px-2 py-0.5 rounded-full text-xs font-bold border"
                                    >
                                        {{ adminAnalytics.kpi.reverb_running ? 'Online' : 'Offline' }}
                                    </span>
                                </div>

                                <!-- AI Monthly Gen -->
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">AI Gemini Generate</div>
                                            <div class="text-[11px] text-slate-500">Bulan Ini: {{ adminAnalytics.kpi.ai_generations_month }} kuis</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-extrabold text-indigo-700">
                                        {{ adminAnalytics.kpi.ai_generations_total }} Total
                                    </span>
                                </div>

                                <!-- Background Queue -->
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                            <i class="fa-solid fa-layer-group text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Antrean Background</div>
                                            <div class="text-[11px] text-slate-500">Failed: {{ adminAnalytics.kpi.failed_jobs }} job</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">
                                        {{ adminAnalytics.kpi.queued_jobs }} Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL AKTIVITAS PENGERJAAN KUIS TERBARU GLOBAL -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Aktivitas Pengerjaan Kuis Terbaru se-Platform</h3>
                                <p class="text-xs text-slate-500">Pengerjaan kuis real-time dari seluruh instansi dan siswa</p>
                            </div>
                            <Link href="/reports" class="text-xs font-bold text-[#3154D5] hover:underline flex items-center gap-1">
                                <span>Lihat Laporan Lengkap</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                    <tr>
                                        <th class="py-3 px-4">Judul Kuis</th>
                                        <th class="py-3 px-4">Peserta</th>
                                        <th class="py-3 px-4">Instansi</th>
                                        <th class="py-3 px-4 text-center">Skor</th>
                                        <th class="py-3 px-4 text-right">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="att in adminAnalytics.recent_attempts" :key="att.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ att.quiz_title }}</td>
                                        <td class="py-3.5 px-4 text-xs text-slate-600 font-medium">{{ att.participant_name }}</td>
                                        <td class="py-3.5 px-4 text-xs text-slate-500">{{ att.organization_name }}</td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="att.score >= 70 ? 'bg-emerald-50 text-[#0AB883] border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                                                {{ att.score }} / 100
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-slate-400 text-right">{{ att.created_at }}</td>
                                    </tr>
                                    <tr v-if="!adminAnalytics.recent_attempts?.length">
                                        <td colspan="5" class="py-6 text-center text-xs text-slate-400 italic">
                                            Belum ada aktivitas pengerjaan kuis terbaru.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- ═════════════════════════════════════════════════════════════════ -->
                <!-- VIEW 2: STANDARD DASHBOARD GURU / CREATOR / PESERTA              -->
                <!-- ═════════════════════════════════════════════════════════════════ -->
                <template v-else>
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
                                        class="h-2 w-2 rounded-full bg-[#90CB31]"
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
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#3154D5] shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                                >
                                    Buat kuis baru
                                </Link>
                                <Link
                                    href="/live-sessions"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20"
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
                                class="p-5 sm:p-6"
                                :class="[
                                    index < 2
                                        ? 'border-b border-slate-100 sm:border-b-0'
                                        : '',
                                    index % 2 === 0 ? 'border-r border-slate-100' : '',
                                    index === 1 ? 'sm:border-r' : '',
                                ]"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    {{ item.label }}
                                </p>
                                <p
                                    class="mt-2 text-3xl font-extrabold"
                                    :class="item.color"
                                >
                                    {{ item.value }}
                                </p>
                            </article>
                        </div>
                    </section>

                    <section aria-labelledby="quick-actions">
                        <div class="mb-3">
                            <h2 id="quick-actions" class="text-lg font-bold text-slate-950">
                                Akses cepat
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Navigasi langsung ke fitur utama kuis.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <Link
                                v-for="shortcut in shortcuts"
                                :key="shortcut.title"
                                :href="shortcut.href"
                                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[#3154D5]/40 hover:shadow-md"
                            >
                                <h3 class="font-bold text-slate-900">
                                    {{ shortcut.title }}
                                </h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ shortcut.description }}
                                </p>
                            </Link>
                        </div>
                    </section>

                    <section aria-labelledby="recent-quizzes">
                        <div class="mb-3">
                            <h2 id="recent-quizzes" class="text-lg font-bold text-slate-950">
                                Kuis terbaru
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Kuis yang baru saja diperbarui dalam organisasi.
                            </p>
                        </div>
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                        >
                            <div
                                v-if="recentQuizzes.length === 0"
                                class="p-8 text-center text-sm text-slate-500"
                            >
                                Belum ada kuis yang dibuat.
                            </div>
                            <div
                                v-else
                                class="divide-y divide-slate-100"
                            >
                                <div
                                    v-for="quiz in recentQuizzes"
                                    :key="quiz.id"
                                    class="flex items-center justify-between p-4 hover:bg-slate-50/50"
                                >
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">
                                            {{ quiz.title }}
                                        </h4>
                                        <p class="text-xs text-slate-400">
                                            Diperbarui {{ formatDate(quiz.updated_at) }}
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                        :class="
                                            quiz.status === 'published'
                                                ? 'bg-emerald-50 text-[#0AB883] border border-emerald-200'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ quiz.status === 'published' ? 'Published' : 'Draft' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
