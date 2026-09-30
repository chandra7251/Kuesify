<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title: string;
    section?: 'questions' | 'admin' | 'categories' | 'reports';
    tab?: 'questions' | 'admin' | 'categories' | 'reports';
    items?: {
        data: any[];
        links: any[];
    };
    filters?: Record<string, any>;
    types?: string[];
    categories?: any[];
    tags?: any[];
    summary?: {
        total_questions?: number;
        total_quizzes?: number;
        active_sessions?: number;
        active_tab?: string;
        is_super_admin?: boolean;
        super_admin_metrics?: {
            total_users: number;
            total_organizations: number;
            total_attempts: number;
            avg_score: number;
            top_organizations: Array<{
                id: number;
                name: string;
                code: string;
                attempts_count: number;
            }>;
        };
        health?: {
            queued_jobs: number;
            failed_jobs: number;
            ai_failures: number;
            reverb_running: boolean;
            reverb_status: string;
            reverb_server?: string;
            reverb_latency_ms?: number;
        };
        maintenance?: {
            mode: string;
            scheduled_at: string | null;
            message: string;
        };
        ai_config?: {
            active_model: string;
            weekly_limit: number;
            total_generations_all_time: number;
            avg_draft_ratio: number;
        };
        audio_settings?: {
            master_volume?: number;
            contexts?: Record<string, {
                label: string;
                preset: string;
                custom_url?: string;
                volume: number;
                enabled: boolean;
            }>;
        };
        monthly_stats?: {
            active_participants_month?: number;
            ai_generations_month?: number;
            total_organizations?: number;
        };
    };
    tenants?: any[];
}

const sectionMeta: Record<
    string,
    {
        eyebrow: string;
        description: string;
        tone: string;
        action?: [string, string];
    }
> = {
    questions: {
        eyebrow: 'Bank materi',
        description:
            'Simpan, cari, impor, dan gunakan ulang pertanyaan terbaik.',
        tone: 'bg-white text-slate-900',
        action: ['Export', '/questions/export'],
    },
    bgm_gameplay: {
        label: 'BGM Gameplay / Soal Berjalan',
        preset: 'Ticking Pulse Electro',
        custom_url: '',
        volume: 65,
        enabled: true,
    },
    bgm_podium: {
        label: 'BGM Podium Juara & Kemenangan',
        preset: 'Grand Champion Fanfare',
        custom_url: '',
        volume: 80,
        enabled: true,
    },
    sfx_correct: {
        label: 'SFX Jawaban Benar',
        preset: 'Crystal Chime High',
        custom_url: '',
        volume: 90,
        enabled: true,
    },
    reports: {
        eyebrow: 'Laporan hasil',
        description:
            'Pantau progres pengerjaan kuis, analisis performa, dan evaluasi hasil belajar peserta.',
        tone: 'bg-white text-slate-900',
        action: ['Export', '/reports/export'],
    },
    sfx_tick: {
        label: 'SFX Detik Kritis (Countdown 5s)',
        preset: 'Clock Tick Fast',
        custom_url: '',
        volume: 80,
        enabled: true,
    },
    sfx_streak: {
        label: 'SFX Streak Combo Juara',
        preset: 'Powerup Spark Chord',
        custom_url: '',
        volume: 85,
        enabled: true,
    },
};

const meta = computed(
    () =>
        sectionMeta[props.section] ?? {
            eyebrow: 'Workspace',
            description: 'Kelola data Kuesify.',
            tone: 'bg-teal-800 text-white',
        },
);
const isQuestions = computed(() => props.section === 'questions');
const isReports = computed(() => props.section === 'reports');
const isOrganization = computed(() => props.section === 'organization');
const organizationGroups = computed(() => (Array.isArray(props.summary.groups) ? props.summary.groups : []) as { id: number; name: string }[]);
const groupForm = useForm({ name: '' });
const memberGroup = useForm({ user_id: 0 });
function createGroup(): void { groupForm.post(route('organization.groups.store'), { preserveScroll: true, onSuccess: () => groupForm.reset() }); }
function addMember(groupId: number, userId: number): void { memberGroup.user_id = userId; memberGroup.post(route('organization.groups.members.store', groupId), { preserveScroll: true }); }
const isAdmin = computed(() => props.section === 'admin');
const isStyledSection = computed(
    () => isQuestions.value || isReports.value || isAdmin.value,
);
const adminCategories = computed(
    () =>
        (Array.isArray(props.summary.categories)
            ? props.summary.categories
            : []) as { id: number; name: string }[],
);
function exportHref(path: string, format: 'csv' | 'xlsx'): string {
    return `${path}?format=${format}`;
}

const adminHealth = computed(
    () =>
        (typeof props.summary.health === 'object' &&
        props.summary.health !== null
            ? props.summary.health
            : {}) as Record<string, unknown>,
);
const rows = computed(() =>
    Array.isArray(props.items) ? props.items : props.items.data || [],
);
const summarySize = (value: unknown) =>
    typeof value === 'object' && value !== null ? Object.keys(value).length : 0;
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-[#E6F1F5]">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Header Banner (Clean Theme Matching Dashboard) -->
                <section class="overflow-hidden rounded-2xl bg-[#3b5fe1] px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                <span class="h-2 w-2 rounded-full bg-[#90CB31]"></span>
                                {{ isSuperAdmin ? 'SaaS Platform Owner' : 'Workspace' }} · Ecosystem Control
                            </p>
                            <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                                {{ title }}
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base">
                                {{ tab === 'admin' ? 'Pusat kendali arsitektur SaaS, audio live interaktif, kuota AI Gemini, dan status infrastruktur platform.' : 'Ringkasan analitik ekosistem dan laporan metrik platform Kuesify.' }}
                            </p>
                        </div>
                        <div class="grid shrink-0 gap-3 sm:flex">
                            <Link
                                v-if="tab === 'admin'"
                                href="/admin/users"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#3154D5] shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                            >
                                <i class="fa-solid fa-users mr-2 text-sm"></i>
                                Monitoring User
                            </Link>
                            <button
                                v-if="tab === 'admin'"
                                @click="probeHealth"
                                type="button"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20"
                            >
                                <i class="fa-solid fa-rotate mr-2 text-sm"></i>
                                Probe Server
                            </button>
                        </div>
                    </div>
                </section>

                <!-- TAB ADMIN: KONTROL PLATFORM SUPER ADMIN -->
                <div v-if="tab === 'admin'" class="space-y-6">
                    <!-- SECTION TITLE -->
                    <div>
                        <p
                            class="text-xs font-extrabold uppercase tracking-[0.18em]"
                            :class="
                                isStyledSection
                                    ? 'text-[#3154D5]'
                                    : 'opacity-75'
                            "
                        >
                            {{ meta.eyebrow }}
                        </p>
                        <h1
                            class="mt-2 font-extrabold tracking-tight"
                            :class="
                                isStyledSection
                                    ? 'text-2xl text-slate-900 sm:text-3xl'
                                    : 'text-3xl sm:text-4xl'
                            "
                        >
                            {{ isReports ? 'Laporan Hasil' : title }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6"
                            :class="
                                isStyledSection
                                    ? 'text-slate-600'
                                    : 'opacity-90'
                            "
                        >
                            {{ meta.description }}
                        </p>
                    </div>
                    <div v-if="meta.action" class="flex flex-wrap gap-2">
                        <a
                            v-for="format in ['csv', 'xlsx'] as const"
                            :key="format"
                            :href="exportHref(meta.action[1], format)"
                            class="inline-flex min-h-11 items-center justify-center px-4 text-sm font-bold uppercase transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                            :class="
                                isStyledSection
                                    ? 'rounded-md bg-[#3451b5] text-white hover:bg-[#29439d] focus-visible:outline-[#3451b5]'
                                    : 'rounded-xl bg-white text-slate-900 hover:bg-slate-100 focus-visible:outline-white'
                            "
                        >
                            {{ meta.action[0] }} {{ format }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- 3 Stat Cards untuk Reports (Persis Question Bank & Live Quiz) -->
            <section
                v-if="isReports"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Card 1: Total Penyelesaian -->
                <article
                    class="min-h-52 rounded-xl border-brand-primary bg-brand-primary p-5 text-white shadow-[0_4px_14px_rgba(47,69,171,0.22)]"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-white"
                        >
                            TOTAL SELESAI
                        </p>
                        <span
                            class="rounded-full bg-brand-secondary px-2.5 py-1 text-xs font-extrabold tabular-nums text-[#102449]"
                        >
                            {{ summary.completionCount ?? 0 }}
                        </span>
                    </div>

                    <div
                        v-if="summary.completionCount"
                        class="mt-3 space-y-2 text-xs text-brand-secondary"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Attempt terkumpul</span
                            >
                            <span class="font-bold text-brand-secondary">{{
                                summary.completionCount
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Status data</span
                            >
                            <span class="font-bold text-brand-secondary"
                                >Tersedia</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Mode evaluasi</span
                            >
                            <span class="font-bold text-brand-secondary"
                                >Otomatis</span
                            >
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-brand-secondary"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-brand-secondary/70"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Card 2: Rata-rata Skor -->
                <article
                    class="min-h-52 rounded-xl border-brand-primary bg-brand-primary p-5 text-white shadow-[0_4px_14px_rgba(47,69,171,0.22)] sm:col-span-2 lg:col-span-1"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-white"
                        >
                            RATA-RATA SKOR
                        </p>
                        <span
                            class="rounded-full bg-brand-secondary px-2.5 py-1 text-xs font-extrabold tabular-nums text-[#102449]"
                        >
                            {{ summary.averageScore ?? 0 }} pts
                        </span>
                    </div>

                    <div
                        v-if="summary.completionCount"
                        class="mt-3 space-y-2 text-xs text-brand-secondary"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Rata-rata kelas</span
                            >
                            <span class="font-bold text-brand-secondary"
                                >{{ summary.averageScore }} / 100</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Penguasaan materi</span
                            >
                            <span class="font-bold text-brand-secondary">
                                {{
                                    Number(summary.averageScore) >= 75
                                        ? 'Optimal'
                                        : Number(summary.averageScore) >= 50
                                          ? 'Cukup'
                                          : 'Perlu Evaluasi'
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span class="font-medium text-brand-secondary"
                                >Kuis diuji</span
                            >
                            <span class="font-bold text-brand-secondary"
                                >{{ rows.length }} kuis</span
                            >
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-brand-secondary"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-brand-secondary/70"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Card 3: Analisis Soal -->
                <article
                    class="min-h-52 rounded-xl border-brand-primary bg-brand-primary p-5 text-white shadow-[0_4px_14px_rgba(47,69,171,0.22)] sm:col-span-2 lg:col-span-1"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-white"
                        >
                            ANALISIS SOAL
                        </p>
                        <span
                            class="rounded-full bg-brand-secondary px-2.5 py-1 text-xs font-extrabold tabular-nums text-[#102449]"
                        >
                            {{
                                Array.isArray(summary.questions)
                                    ? summary.questions.length
                                    : 0
                            }}
                        </span>
                    </div>

                    <div
                        v-if="
                            Array.isArray(summary.questions) &&
                            summary.questions.length
                        "
                        class="mt-3 space-y-2 text-xs text-brand-secondary"
                    >
                        <div
                            v-for="q in (summary.questions as any[]).slice(
                                0,
                                3,
                            )"
                            :key="q.id"
                            class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2"
                        >
                            <span
                                class="truncate pr-2 font-medium text-brand-secondary"
                                >{{ q.prompt }}</span
                            >
                            <span
                                class="shrink-0 rounded-full bg-brand-secondary px-2 py-0.5 text-[10px] font-bold text-[#102449]"
                            >
                                {{
                                    q.correct_rate !== null
                                        ? `${q.correct_rate}% benar`
                                        : `${q.points} pts`
                                }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-brand-secondary"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-brand-secondary/70"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                </article>
            </section>

            <section v-else-if="isAdmin" class="grid gap-4 lg:grid-cols-2">
                <article
                    class="rounded-xl border-brand-primary bg-brand-primary p-5 text-white shadow-[0_4px_14px_rgba(47,69,171,0.22)] sm:p-6"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.16em] text-white"
                            >
                                Kategori
                            </p>
                            <h2 class="mt-2 text-xl font-extrabold text-white">
                                Kategori soal
                            </h2>
                        </div>
                        <span
                            class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-extrabold text-[#102449]"
                        >
                            {{ adminCategories.length }} kategori
                        </span>
                    </div>
                    <div
                        v-if="adminCategories.length"
                        class="mt-5 flex flex-wrap gap-2"
                    >
                        <span
                            v-for="category in adminCategories"
                            :key="category.id"
                            class="rounded-full bg-white/10 px-3 py-1.5 text-sm font-semibold text-brand-secondary"
                        >
                            {{ category.name }}
                        </span>
                    </div>
                    <p v-else class="mt-5 text-sm text-brand-secondary/80">
                        Belum ada kategori soal.
                    </p>
                </article>

                <article
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <div>
                        <p
                            class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-primary"
                        >
                            Kesehatan sistem
                        </p>
                        <h2 class="mt-2 text-xl font-extrabold text-slate-900">
                            Status layanan
                        </h2>
                    </div>
                    <dl class="mt-5 divide-y divide-slate-100 text-sm">
                        <div
                            v-for="(value, key) in adminHealth"
                            :key="String(key)"
                            class="flex items-center justify-between gap-4 py-2.5 first:pt-0 last:pb-0"
                        >
                            <dt class="text-slate-500">
                                {{ String(key).replaceAll('_', ' ') }}
                            </dt>
                            <dd
                                v-if="key === 'reverb_status'"
                                class="rounded-full px-2.5 py-1 text-xs font-extrabold"
                                :class="
                                    value === 'online'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-rose-100 text-rose-800'
                                "
                            >
                                {{ value }}
                            </dd>
                            <dd v-else class="font-bold text-slate-900">
                                {{ value ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </section>

            <!-- General Summary Section untuk section lain -->
            <section
                v-else-if="!isAdmin && Object.keys(summary).length"
                class="grid sm:grid-cols-2"
                :class="
                    isQuestions
                        ? 'gap-4 lg:grid-cols-3'
                        : 'gap-3 lg:grid-cols-4'
                "
            >
                <article
                    v-for="(value, key) in summary"
                    :key="String(key)"
                    class="border"
                    :class="
                        isQuestions
                            ? 'min-h-52 rounded-xl border-brand-primary bg-brand-primary p-5 text-white shadow-[0_4px_14px_rgba(47,69,171,0.22)]'
                            : 'rounded-2xl border-slate-100 bg-white p-4 shadow-sm'
                    "
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide"
                            :class="
                                isQuestions ? 'text-white' : 'text-slate-500'
                            "
                        >
                            {{ String(key).replaceAll('_', ' ') }}
                        </p>
                        <span
                            v-if="
                                isQuestions &&
                                typeof value === 'object' &&
                                value !== null
                            "
                            class="rounded-full bg-brand-secondary px-2.5 py-1 text-xs font-extrabold tabular-nums text-[#102449]"
                        >
                            {{ summarySize(value) }}
                        </span>
                    </div>
                    <div
                        v-if="
                            typeof value === 'object' &&
                            value !== null &&
                            (!isQuestions || summarySize(value))
                        "
                        class="mt-3 text-xs"
                        :class="
                            isQuestions
                                ? 'space-y-2 text-brand-secondary'
                                : 'space-y-1.5 text-slate-700'
                        "
                    >
                        <div
                            v-for="(subVal, subKey) in value as Record<
                                string,
                                unknown
                            >"
                            :key="String(subKey)"
                            class="flex items-center justify-between"
                            :class="
                                isQuestions
                                    ? 'rounded-lg bg-white/10 px-3 py-2'
                                    : 'border-b border-slate-50 py-0.5 last:border-none'
                            "
                        >
                            <span
                                class="font-medium"
                                :class="
                                    isQuestions
                                        ? 'text-brand-secondary'
                                        : 'text-slate-500'
                                "
                                >{{ String(subKey).replaceAll('_', ' ') }}</span
                            >
                            <span
                                v-if="subKey === 'reverb_status'"
                                :class="
                                    subVal === 'online'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-rose-100 text-rose-800'
                                "
                                class="rounded-full px-2 py-0.5 font-extrabold"
                            >
                                {{ subVal }}
                            </span>
                            <span
                                v-else
                                class="font-bold"
                                :class="
                                    isQuestions
                                        ? 'text-brand-secondary'
                                        : 'text-slate-900'
                                "
                                >{{ String(subVal ?? '-') }}</span
                            >
                        </div>
                    </div>
                    <div
                        v-else-if="
                            isQuestions &&
                            typeof value === 'object' &&
                            value !== null
                        "
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-brand-secondary/100"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-brand-secondary/70"
                            >
                                Belum ada data
                            </p>
                        </div>
                    </div>
                    <p
                        v-else
                        class="mt-3 break-words text-2xl font-extrabold"
                        :class="
                            isQuestions
                                ? 'text-brand-secondary'
                                : 'text-slate-900'
                        "
                    >
                        {{ value }}
                    </p>
                </article>
            </section>

            <section v-if="isOrganization" class="grid gap-5 lg:grid-cols-2">
                <form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="createGroup">
                    <h2 class="font-extrabold text-slate-900">Buat group / departemen</h2>
                    <div class="mt-3 flex gap-2"><input v-model="groupForm.name" required class="min-h-11 min-w-0 flex-1 rounded-lg border-slate-300 text-sm" placeholder="Nama group" /><button type="submit" class="min-h-11 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white">Tambah</button></div>
                </form>
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-extrabold text-slate-900">Group aktif</h2><div v-if="organizationGroups.length" class="mt-3 flex flex-wrap gap-2"><span v-for="group in organizationGroups" :key="group.id" class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-bold">{{ group.name }}</span></div><p v-else class="mt-3 text-sm text-slate-500">Belum ada group.</p></section>
            </section>

            <!-- Data Workspace List (Persis Question Bank) -->
            <section
                class="overflow-hidden border bg-white"
                :class="
                    isStyledSection
                        ? 'rounded-xl border-slate-200 shadow-[0_2px_7px_rgba(15,23,42,0.09)]'
                        : 'rounded-2xl border-slate-100 shadow-sm'
                "
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100"
                    :class="
                        isStyledSection ? 'bg-white px-6 py-5' : 'px-5 py-4'
                    "
                >
                    <div>
                        <h2 class="font-extrabold text-slate-900">
                            {{ isAdmin ? 'Organisasi' : 'Data workspace' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{
                                isAdmin
                                    ? `${rows.length} organisasi terdaftar.`
                                    : `${rows.length} item pada halaman ini.`
                            }}
                        </p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="
                            isStyledSection
                                ? 'bg-brand-primary/15 text-[#3154D5]'
                                : 'bg-brand-primary/15 text-brand-primary'
                        "
                        >{{ section }}</span
                    >
                </div>
                <div
                    v-if="rows.length"
                    :class="
                        isStyledSection
                            ? 'divide-y divide-slate-100 bg-white'
                            : 'divide-y divide-slate-100'
                    "
                >
                    <article
                        v-for="item in rows"
                        :key="String(item.id)"
                        class="group flex min-h-20 gap-3 transition-colors duration-150"
                        :class="
                            isStyledSection
                                ? 'question-row items-center border-l-4 border-l-transparent bg-white px-6 py-5 hover:bg-slate-50'
                                : 'flex-col justify-center border-l-2 border-l-transparent px-5 py-4 hover:bg-teal-50/40 sm:flex-row sm:items-center sm:justify-between'
                        "
                    >
                        <div
                            class="min-w-0 flex-1"
                            :class="
                                isStyledSection &&
                                'sm:flex sm:items-center sm:justify-between sm:gap-6'
                            "
                        >
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#3154D5] flex items-center justify-center">
                                            <i class="fa-solid fa-server text-base"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-base">Infrastruktur & WebSocket</h3>
                                            <p class="text-xs text-slate-500">Reverb WebSocket, Redis & Queue Workers</p>
                                        </div>
                                    </div>
                                    <span
                                        :class="summary.health?.reverb_running ? 'bg-emerald-50 text-[#0AB883] border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5"
                                    >
                                        <span :class="summary.health?.reverb_running ? 'bg-[#0AB883]' : 'bg-rose-500'" class="w-2 h-2 rounded-full"></span>
                                        {{ summary.health?.reverb_running ? 'ONLINE' : 'OFFLINE' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-3 my-4">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">Antrean Job</div>
                                        <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ summary.health?.queued_jobs ?? 0 }}</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">Job Gagal</div>
                                        <div class="text-xl font-extrabold text-rose-600 mt-0.5">{{ summary.health?.failed_jobs ?? 0 }}</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">AI Failures</div>
                                        <div class="text-xl font-extrabold text-amber-600 mt-0.5">{{ summary.health?.ai_failures ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>Latensi Server: <strong class="text-slate-800">{{ summary.health?.reverb_latency_ms ?? 12 }}ms</strong></span>
                                <button @click="probeHealth" class="text-[#3154D5] hover:underline font-bold">Cek Status Sekarang &rarr;</button>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-2"
                            :class="isStyledSection && 'ml-auto'"
                        >
                            <span
                                v-if="
                                    !isStyledSection &&
                                    (item.status || item.role)
                                "
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                                >{{
                                    item.status ||
                                    roleLabel(item.role as string | undefined)
                                }}</span
                            >
                            <span
                                v-if="
                                    !isReports &&
                                    (item.score ||
                                        item.questions_count ||
                                        item.members_count)
                                "
                                class="text-sm font-bold"
                                :class="
                                    isQuestions
                                        ? 'text-[#3154D5]'
                                        : 'text-teal-800'
                                "
                            >
                                {{
                                    item.score ??
                                    item.questions_count ??
                                    item.members_count ??
                                    ''
                                }}
                            </span>
                            <div v-if="isOrganization && organizationGroups.length" class="flex flex-wrap items-center gap-2">
                                <select class="min-h-9 rounded-md border-slate-300 text-xs" aria-label="Pilih group" @change="addMember(Number(($event.target as HTMLSelectElement).value), Number(item.id))">
                                    <option value="">Tambah ke group</option>
                                    <option v-for="group in organizationGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                </select>
                            </div>
                            <div v-if="isReports" class="flex flex-wrap gap-2">
                                <a
                                    v-for="format in ['csv', 'xlsx'] as const"
                                    :key="format"
                                    :href="exportHref('/reports/export', format)"
                                    class="inline-flex min-h-9 items-center justify-center rounded-md bg-[#3451b5] px-3.5 text-xs font-bold uppercase text-white transition hover:bg-[#29439d]"
                                >
                                    Unduh {{ format }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION MASTER KATEGORI SOAL -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Taksonomi Kategori Global</h3>
                                <p class="text-xs text-slate-500">Master kategori untuk pengelompokan bank soal se-platform</p>
                            </div>

                            <form @submit.prevent="submitCategory" class="flex items-center gap-2">
                                <input
                                    v-model="categoryForm.name"
                                    type="text"
                                    placeholder="Nama kategori baru..."
                                    required
                                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20 w-52"
                                />
                                <button
                                    type="submit"
                                    :disabled="categoryForm.processing"
                                    class="px-4 py-2 rounded-xl bg-[#3154D5] hover:bg-[#2744ab] text-white text-xs font-bold transition disabled:opacity-50"
                                >
                                    <i class="fa-solid fa-plus mr-1"></i>
                                    Tambah
                                </button>
                            </form>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <div
                                v-for="cat in categories"
                                :key="cat.id"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700"
                            >
                                <span>{{ cat.name }}</span>
                                <button
                                    @click="deleteCategory(cat.id)"
                                    type="button"
                                    class="text-slate-400 hover:text-rose-600 font-bold p-0.5 transition"
                                    title="Hapus Kategori"
                                >
                                    &times;
                                </button>
                            </div>
                            <div v-if="!categories?.length" class="text-xs text-slate-400 italic">
                                Belum ada kategori kuis dibuat.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB REPORTS: LAPORAN GLOBAL SAAS SUPER ADMIN -->
                <div v-else-if="tab === 'reports' && isSuperAdmin" class="space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Ringkasan Metrik Platform SaaS</h2>
                        <p class="mt-1 text-sm text-slate-600">Data agregat aktivitas multi-tenant dari seluruh sekolah & pengguna.</p>
                    </div>

                    <!-- METRIK KINERJA SAAS (CLEAN CARDS) -->
                    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4">
                        <article class="border-b border-r border-slate-100 p-5 sm:border-b-0 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Instansi</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ summary.super_admin_metrics?.total_organizations ?? tenants?.length ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Sekolah & Tenant Aktif</p>
                        </article>

                        <article class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengguna</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ summary.super_admin_metrics?.total_users ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Guru, Siswa & Admin</p>
                        </article>

                        <article class="border-r border-slate-100 p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Attempts</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ summary.super_admin_metrics?.total_attempts ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Pengerjaan Selesai</p>
                        </article>

                        <article class="p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-Rata Skor</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ summary.super_admin_metrics?.avg_score ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Skor Agregat Nasional</p>
                        </article>
                    </div>

                    <!-- TABEL AUDIT TENANT & ORGANISASI SE-PLATFORM -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Daftar Tenant Sekolah / Instansi Terdaftar</h3>
                                <p class="text-xs text-slate-500">Status penggunaan dan member masing-masing instansi</p>
                            </div>
                            <Link
                                href="/admin/users"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition"
                            >
                                <span>Lihat Rincian Pengguna Global &rarr;</span>
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                    <tr>
                                        <th class="py-3 px-4">Nama Instansi / Tenant</th>
                                        <th class="py-3 px-4">Kode Identifikasi</th>
                                        <th class="py-3 px-4">Zona Waktu</th>
                                        <th class="py-3 px-4">Total Member</th>
                                        <th class="py-3 px-4 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="org in tenants" :key="org.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ org.name }}</td>
                                        <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ org.code }}</td>
                                        <td class="py-3.5 px-4 text-xs text-slate-600">{{ org.timezone ?? 'Asia/Jakarta' }}</td>
                                        <td class="py-3.5 px-4 text-xs font-bold text-slate-700">{{ org.users_count ?? org.users?.length ?? '-' }} Akun</td>
                                        <td class="py-3.5 px-4 text-right">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0AB883] border border-emerald-200">
                                                Active Tenant
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!tenants?.length">
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs italic">
                                            Belum ada data tenant terdaftar.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB QUESTION BANK / TAB LAINNYA -->
                <div v-else class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-slate-900 text-base">Bank Soal & Materi</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Prompt Soal</th>
                                    <th class="py-3 px-4">Tipe</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in items.data" :key="item.id">
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ item.prompt }}</td>
                                    <td class="py-3 px-4 text-xs text-slate-500">{{ item.type }}</td>
                                    <td class="py-3 px-4 text-xs text-[#3154D5] font-semibold">{{ item.category?.name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-xs font-bold">{{ item.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="items.links" />
                    </div>
                </div>
            </main>
        </div>

        <!-- MODAL STUDIO AUDIO MULTI-KONTEKS INTERAKTIF -->
        <div v-if="showAudioModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-4xl max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="p-5 bg-[#3b5fe1] text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Studio Audio & Musik Multi-Konteks</span>
                        </h2>
                        <p class="text-xs text-white/80 mt-0.5">Kustomisasi preset atau input URL audio khusus untuk setiap suasana pengerjaan kuis</p>
                    </div>
                    <button
                        @click="showAudioModal = false; stopAudio()"
                        type="button"
                        class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition"
                    >
                        <i class="fa-solid fa-xmark text-lg leading-none"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-5">
                    <!-- Global Master Volume Bar -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Master Gain Volume</div>
                            <div class="text-xs text-slate-500">Volume utama yang mempengaruhi seluruh BGM dan efek suara game</div>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-64">
                            <input
                                v-model.number="audioForm.master_volume"
                                type="range"
                                min="0"
                                max="100"
                                class="w-full accent-[#3154D5]"
                            />
                            <span class="text-sm font-extrabold text-[#3154D5] w-12 text-right">{{ audioForm.master_volume }}%</span>
                        </div>
                    </div>

                    <!-- Contexts Audio List -->
                    <div class="space-y-4">
                        <div
                            v-for="(ctx, key) in audioForm.contexts"
                            :key="key"
                            class="p-4 rounded-xl border border-slate-200 bg-white hover:border-[#3154D5]/40 transition space-y-3"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <input
                                        v-model="ctx.enabled"
                                        type="checkbox"
                                        :id="'chk_' + key"
                                        class="rounded border-slate-300 text-[#3154D5] focus:ring-[#3154D5] w-4 h-4"
                                    />
                                    <label :for="'chk_' + key" class="font-bold text-slate-800 text-sm cursor-pointer">
                                        {{ ctx.label }}
                                    </label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        @click="playAudioPreview(String(key))"
                                        type="button"
                                        :disabled="!ctx.enabled"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-[#3154D5] hover:bg-blue-100 text-xs font-bold border border-blue-200 transition disabled:opacity-40 flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid" :class="activeAudioPlaying === key ? 'fa-volume-high' : 'fa-play'"></i>
                                        <span>{{ activeAudioPlaying === key ? 'Bunyi' : 'Tes Suara' }}</span>
                                    </button>
                                    <button
                                        v-if="activeAudioPlaying === key"
                                        @click="stopAudio"
                                        type="button"
                                        class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-bold border border-rose-200 flex items-center gap-1"
                                    >
                                        <i class="fa-solid fa-stop"></i>
                                        <span>Stop</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <!-- Preset Selector -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Preset Bawaan</label>
                                    <select
                                        v-model="ctx.preset"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                    >
                                        <option value="Arcade Retro 8-bit (Default)">Arcade Retro 8-bit</option>
                                        <option value="Ticking Pulse Electro">Ticking Pulse Electro</option>
                                        <option value="Grand Champion Fanfare">Grand Champion Fanfare</option>
                                        <option value="Crystal Chime High">Crystal Chime High</option>
                                        <option value="Muted Buzzer Low">Muted Buzzer Low</option>
                                        <option value="Clock Tick Fast">Clock Tick Fast</option>
                                        <option value="Powerup Spark Chord">Powerup Spark Chord</option>
                                        <option value="Custom URL">Input Custom Audio URL</option>
                                    </select>
                                </div>

                                <!-- Custom URL Input (if Custom URL selected) -->
                                <div class="sm:col-span-2" v-if="ctx.preset === 'Custom URL'">
                                    <label class="block text-xs font-semibold text-[#3154D5] mb-1">URL Audio Custom (MP3/WAV/CDN)</label>
                                    <input
                                        v-model="ctx.custom_url"
                                        type="url"
                                        placeholder="https://domain.com/audio/my-track.mp3"
                                        class="w-full rounded-xl border border-blue-300 bg-blue-50/20 px-3 py-2 text-xs font-mono text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                    />
                                </div>

                                <!-- Volume Slider -->
                                <div :class="ctx.preset === 'Custom URL' ? 'sm:col-span-3' : 'sm:col-span-2'">
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="text-xs font-semibold text-slate-500">Volume Konteks Ini</label>
                                        <span class="text-xs font-bold text-[#3154D5]">{{ ctx.volume }}%</span>
                                    </div>
                                    <input
                                        v-model.number="ctx.volume"
                                        type="range"
                                        min="0"
                                        max="100"
                                        class="w-full accent-[#3154D5]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button
                        @click="showAudioModal = false; stopAudio()"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition"
                    >
                        Tutup
                    </button>

                    <div class="flex items-center gap-3">
                        <button
                            @click="submitAudio"
                            type="button"
                            :disabled="audioForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-[#3154D5] hover:bg-[#2744ab] text-white text-xs font-bold transition disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ audioForm.processing ? 'Menyimpan...' : 'Simpan Konfigurasi Audio' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
