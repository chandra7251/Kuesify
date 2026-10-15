<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import SuperAdminBreadcrumb from '@/Components/SuperAdminBreadcrumb.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type UserSummary = {
    id: number;
    name: string;
    email: string;
};

type ActivityLog = {
    id: number;
    user_id: number | null;
    user?: UserSummary | null;
    request_id: string;
    method: string;
    url: string;
    status_code: number;
    duration_ms: number;
    ip: string;
    user_agent: string | null;
    request_body: Record<string, any> | null;
    created_at: string;
};

type CrashLog = {
    id: number;
    user_id: number | null;
    user?: UserSummary | null;
    request_id: string;
    method: string;
    url: string;
    status_code: number;
    message: string;
    trimmed_trace?: string;
    ip: string;
    user_agent: string | null;
    created_at: string;
};

const props = defineProps<{
    tab: 'activity' | 'crashes';
    logs: {
        data: (ActivityLog | CrashLog)[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
    stats: {
        total: number;
        today: number;
        errors?: number;
        server?: number;
        avg_ms?: number;
    };
    logging_enabled: boolean;
}>();

const search = ref(props.filters.search ?? '');
const selectedCrash = ref<CrashLog | null>(null);

function applySearch() {
    const routeName = props.tab === 'activity' ? 'superadmin.logs.activity' : 'superadmin.logs.crashes';
    router.get(
        route(routeName),
        {
            search: search.value || undefined,
            status: props.filters.status !== 'all' ? props.filters.status : undefined,
        },
        { preserveState: true, replace: true }
    );
}

function filterStatus(status: string) {
    router.get(
        route('superadmin.logs.activity'),
        {
            search: search.value || undefined,
            status: status !== 'all' ? status : undefined,
        },
        { preserveState: true, replace: true }
    );
}

function toggleLogging() {
    if (confirm(`Apakah kamu yakin ingin ${props.logging_enabled ? 'menonaktifkan' : 'mengaktifkan'} Platform API Logging?`)) {
        router.post(route('superadmin.logs.toggle'));
    }
}

function statusBadgeClass(code: number) {
    if (code >= 200 && code < 300) return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    if (code >= 300 && code < 400) return 'bg-sky-100 text-sky-800 border-sky-200';
    if (code >= 400 && code < 500) return 'bg-amber-100 text-amber-800 border-amber-200';
    return 'bg-rose-100 text-rose-800 border-rose-200';
}

function methodBadgeClass(method: string) {
    switch (method.toUpperCase()) {
        case 'GET': return 'bg-slate-100 text-slate-700 border-slate-200';
        case 'POST': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'PUT':
        case 'PATCH': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'DELETE': return 'bg-rose-100 text-rose-800 border-rose-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}
</script>

<template>
    <Head title="Platform API Logs" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent/20 py-6 sm:py-8">
            <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <SuperAdminBreadcrumb
                    :items="[
                        { label: 'System Logs', active: true },
                    ]"
                />

                <!-- Header Card -->
                <section class="relative overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma-sm sm:rounded-3xl sm:p-8">
                    <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5 blur-2xl" />
                    <div class="absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-brand-secondary/15 blur-2xl" />

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/30 bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm">
                                    <span class="h-2 w-2 rounded-full bg-brand-secondary" />
                                    SUPER ADMIN • OBSERVABILITY
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold backdrop-blur-sm',
                                        logging_enabled ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30'
                                    ]"
                                >
                                    <span :class="['h-2 w-2 rounded-full', logging_enabled ? 'bg-emerald-400' : 'bg-rose-400']" />
                                    {{ logging_enabled ? 'Logging Aktif' : 'Logging Nonaktif (Hemat RAM)' }}
                                </span>
                            </div>

                            <h1 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                                Platform API & Crash Logs
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">
                                Monitoring request HTTP real-time, pelacakan crash exception server (stack trace max 10 baris untuk efisiensi RAM), dan kontrol maintenance mode.
                            </p>
                        </div>

                        <!-- Toggle Button -->
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="toggleLogging"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition active:scale-95 shadow-md',
                                    logging_enabled
                                        ? 'bg-rose-500 text-white hover:bg-rose-600'
                                        : 'bg-brand-secondary text-slate-900 hover:bg-brand-secondary/90'
                                ]"
                            >
                                <AppIcon name="power" :size="18" />
                                {{ logging_enabled ? 'Nonaktifkan Logging' : 'Aktifkan Logging' }}
                            </button>
                        </div>
                    </div>
                </section>

                <!-- KPI Stats -->
                <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary">
                            <AppIcon name="activity" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">Total Log Tersimpan</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ stats.total }}</p>
                        </div>
                    </article>

                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/30 text-brand-dark">
                            <AppIcon name="clock" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">Aktivitas Hari Ini</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ stats.today }}</p>
                        </div>
                    </article>

                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                            <AppIcon name="warning" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">{{ tab === 'activity' ? 'Total Error (4xx/5xx)' : 'Server Crashes (5xx)' }}</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ tab === 'activity' ? stats.errors ?? 0 : stats.server ?? 0 }}</p>
                        </div>
                    </article>

                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                            <AppIcon name="gauge" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">{{ tab === 'activity' ? 'Rata-rata Respon' : 'Trim Limit RAM' }}</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ tab === 'activity' ? `${stats.avg_ms ?? 0} ms` : 'Max 10 baris' }}</p>
                        </div>
                    </article>
                </section>

                <!-- Navigation Tabs + Search -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-figma-sm sm:p-6">
                    <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Tab Links -->
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('superadmin.logs.activity')"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition',
                                    tab === 'activity'
                                        ? 'bg-brand-primary text-white shadow-sm'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                ]"
                            >
                                <AppIcon name="activity" :size="16" />
                                Activity Logs
                            </Link>

                            <Link
                                :href="route('superadmin.logs.crashes')"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition',
                                    tab === 'crashes'
                                        ? 'bg-rose-600 text-white shadow-sm'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                ]"
                            >
                                <AppIcon name="warning" :size="16" />
                                Crash Logs (10-Line Trim)
                            </Link>
                        </div>

                        <!-- Search Bar -->
                        <div class="flex items-center gap-2">
                            <div class="relative w-full sm:w-72">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari URL, method, IP, user..."
                                    @keyup.enter="applySearch"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 placeholder-slate-400 transition focus:border-brand-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
                                />
                            </div>
                            <button
                                type="button"
                                @click="applySearch"
                                class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-black text-slate-700 hover:bg-slate-200"
                            >
                                Cari
                            </button>
                        </div>
                    </div>

                    <!-- Filter pills for Activity Tab -->
                    <div v-if="tab === 'activity'" class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 mr-1">Status:</span>
                        <button
                            v-for="st in ['all', '2xx', '3xx', '4xx', '5xx']"
                            :key="st"
                            type="button"
                            @click="filterStatus(st)"
                            :class="[
                                'rounded-lg px-2.5 py-1 text-[11px] font-black uppercase transition',
                                (filters.status ?? 'all') === st
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            ]"
                        >
                            {{ st === 'all' ? 'Semua' : st }}
                        </button>
                    </div>

                    <!-- Table Data -->
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                <tr v-if="tab === 'activity'">
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Method</th>
                                    <th class="px-4 py-3">URL & Request</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Latency</th>
                                    <th class="px-4 py-3">IP Address</th>
                                    <th class="px-4 py-3">Waktu</th>
                                </tr>
                                <tr v-else>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Method & URL</th>
                                    <th class="px-4 py-3">Pesan Error</th>
                                    <th class="px-4 py-3">Stack Trace (10 Baris)</th>
                                    <th class="px-4 py-3">User & IP</th>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                                <!-- Activity Rows -->
                                <template v-if="tab === 'activity'">
                                    <tr v-for="item in (logs.data as ActivityLog[])" :key="item.id" class="hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span :class="['inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-black', statusBadgeClass(item.status_code)]">
                                                {{ item.status_code }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span :class="['inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-black', methodBadgeClass(item.method)]">
                                                {{ item.method }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 max-w-md truncate" :title="item.url">
                                            <p class="font-bold text-slate-900 truncate">{{ item.url }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">Req ID: {{ item.request_id }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div v-if="item.user">
                                                <p class="font-bold text-slate-900">{{ item.user.name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ item.user.email }}</p>
                                            </div>
                                            <span v-else class="text-[11px] text-slate-400 italic">Guest</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px]">
                                            <span :class="item.duration_ms > 1000 ? 'text-rose-600 font-black' : item.duration_ms > 400 ? 'text-amber-600 font-black' : 'text-slate-600'">
                                                {{ item.duration_ms }} ms
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                            {{ item.ip }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-400 text-[11px]">
                                            {{ new Date(item.created_at).toLocaleString('id-ID') }}
                                        </td>
                                    </tr>
                                </template>

                                <!-- Crash Rows -->
                                <template v-else>
                                    <tr v-for="item in (logs.data as CrashLog[])" :key="item.id" class="hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span :class="['inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-black', statusBadgeClass(item.status_code)]">
                                                {{ item.status_code }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 max-w-xs truncate">
                                            <div class="flex items-center gap-1.5">
                                                <span :class="['inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-black', methodBadgeClass(item.method)]">
                                                    {{ item.method }}
                                                </span>
                                                <span class="font-bold text-slate-900 truncate" :title="item.url">{{ item.url }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 max-w-md">
                                            <p class="font-bold text-rose-700 line-clamp-2" :title="item.message">
                                                {{ item.message }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 max-w-xs">
                                            <pre class="bg-slate-900 text-slate-100 p-2 rounded-lg text-[10px] font-mono overflow-hidden line-clamp-3 select-all">{{ item.trimmed_trace || 'Tidak ada trace' }}</pre>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <p class="font-bold text-slate-800">{{ item.user?.name ?? 'Guest / Anon' }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">{{ item.ip }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-400 text-[11px]">
                                            {{ new Date(item.created_at).toLocaleString('id-ID') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <button
                                                type="button"
                                                @click="selectedCrash = item"
                                                class="rounded-lg bg-brand-primary/10 px-3 py-1.5 text-xs font-black text-brand-primary hover:bg-brand-primary hover:text-white transition"
                                            >
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="logs.data.length === 0" class="my-10 flex flex-col items-center justify-center p-8 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <AppIcon name="scrollText" :size="28" />
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">Belum Ada Data Log</h3>
                            <p class="mt-1 max-w-sm text-xs text-slate-500">
                                {{ search ? 'Tidak ada catatan log yang cocok dengan kata kunci pencarian.' : 'Belum ada log yang tercatat atau logging sedang dinonaktifkan.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.links && logs.links.length > 3" class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                        <p class="text-xs font-bold text-slate-500">
                            Menampilkan hal {{ logs.current_page }} dari {{ logs.last_page }} (Total {{ logs.total }} entri)
                        </p>
                        <div class="flex items-center gap-1">
                            <Link
                                v-for="(link, i) in logs.links"
                                :key="i"
                                :href="link.url ?? '#'"
                                :class="[
                                    'rounded-lg px-3 py-1.5 text-xs font-black transition',
                                    link.active
                                        ? 'bg-brand-primary text-white'
                                        : link.url
                                            ? 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                            : 'bg-slate-50 text-slate-300 pointer-events-none'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Detail Modal Crash Log -->
        <div v-if="selectedCrash" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span :class="['rounded-md border px-2 py-0.5 text-xs font-black', statusBadgeClass(selectedCrash.status_code)]">
                            {{ selectedCrash.status_code }}
                        </span>
                        <h3 class="text-base font-black text-slate-900">Detail Crash Exception</h3>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        @click="selectedCrash = null"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[10px]">URL Request</p>
                        <p class="font-bold text-slate-900 break-all">{{ selectedCrash.method }} {{ selectedCrash.url }}</p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[10px]">Pesan Exception</p>
                        <p class="font-bold text-rose-700 bg-rose-50 border border-rose-200 p-2.5 rounded-xl">{{ selectedCrash.message }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="font-bold text-slate-400 uppercase text-[10px]">Stack Trace (Dipangkas Max 10 Baris)</p>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Hemat RAM Mode</span>
                        </div>
                        <pre class="bg-slate-900 text-emerald-300 p-3 rounded-xl font-mono text-[11px] overflow-x-auto whitespace-pre-wrap leading-relaxed">{{ selectedCrash.trimmed_trace }}</pre>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <p class="font-bold text-slate-400 uppercase text-[10px]">User & IP</p>
                            <p class="font-bold text-slate-800">{{ selectedCrash.user?.name ?? 'Guest' }} ({{ selectedCrash.user?.email ?? 'N/A' }})</p>
                            <p class="text-slate-500 font-mono text-[11px]">IP: {{ selectedCrash.ip }}</p>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <p class="font-bold text-slate-400 uppercase text-[10px]">Waktu Kejadian</p>
                            <p class="font-bold text-slate-800">{{ new Date(selectedCrash.created_at).toLocaleString('id-ID') }}</p>
                            <p class="text-slate-500 font-mono text-[10px]">ID: {{ selectedCrash.request_id }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white hover:bg-slate-800"
                        @click="selectedCrash = null"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
