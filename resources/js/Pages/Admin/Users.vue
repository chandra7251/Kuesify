<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Organization {
    id: number;
    name: string;
    pivot?: {
        role: string;
        is_active: boolean;
    };
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    avatar?: string | null;
    created_at: string;
    organizations: Organization[];
    created_quizzes_count: number;
    attempts_count: number;
}

interface Props {
    users: {
        data: UserItem[];
        links: any[];
        total: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
    stats: {
        total_users: number;
        verified_users: number;
        unverified_users: number;
        total_organizations: number;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? 'all');
const statusFilter = ref(props.filters.status ?? 'all');

const selectedUser = ref<UserItem | null>(null);

function applyFilters() {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
            status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

function resetFilters() {
    search.value = '';
    roleFilter.value = 'all';
    statusFilter.value = 'all';
    applyFilters();
}

function openDetail(user: UserItem) {
    selectedUser.value = user;
}

function closeDetail() {
    selectedUser.value = null;
}

function formatDate(dateStr?: string | null) {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}

function getRoleBadgeColor(role?: string) {
    switch (role) {
        case 'super_admin':
            return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'organization_admin':
            return 'bg-blue-50 text-[#3154D5] border-blue-200';
        case 'creator':
            return 'bg-emerald-50 text-[#0AB883] border-emerald-200';
        case 'participant':
        default:
            return 'bg-slate-50 text-slate-600 border-slate-200';
    }
}

function formatRoleName(role?: string) {
    switch (role) {
        case 'super_admin':
            return 'Super Admin';
        case 'organization_admin':
            return 'Admin Sekolah';
        case 'creator':
            return 'Guru / Kreator';
        case 'participant':
            return 'Siswa / Peserta';
        default:
            return 'User Biasa';
    }
}
</script>

<template>
    <Head title="Monitoring Pengguna Global" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-[#E6F1F5]">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Header Banner (Clean Theme Matching Dashboard) -->
                <section class="overflow-hidden rounded-2xl bg-[#3b5fe1] px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                <span class="h-2 w-2 rounded-full bg-[#90CB31]"></span>
                                Super Admin Platform · User Monitoring
                            </p>
                            <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                                Monitoring Pengguna Global
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base">
                                Pantau seluruh akun terdaftar di platform Kuesify, status verifikasi email, keanggotaan instansi, dan aktivitas kuis.
                            </p>
                        </div>
                        <div class="grid shrink-0 gap-3 sm:flex">
                            <Link
                                href="/admin"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20"
                            >
                                <i class="fa-solid fa-arrow-left mr-2 text-sm"></i>
                                Kembali ke Platform Admin
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- SECTION SUMMARY -->
                <section aria-labelledby="users-summary">
                    <div class="mb-3">
                        <h2 id="users-summary" class="text-lg font-bold text-slate-950">Ringkasan Pengguna Platform</h2>
                        <p class="mt-1 text-sm text-slate-600">Metrik status verifikasi dan registrasi seluruh ekosistem SaaS.</p>
                    </div>

                    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4">
                        <article class="border-b border-r border-slate-100 p-5 sm:border-b-0 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengguna</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ stats.total_users }}</p>
                            <p class="mt-1 text-xs text-slate-500">Akun terdaftar global</p>
                        </article>

                        <article class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Terverifikasi</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ stats.verified_users }}</p>
                            <p class="mt-1 text-xs text-slate-500">Email telah aktif</p>
                        </article>

                        <article class="border-r border-slate-100 p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Belum Verifikasi</p>
                            <p class="mt-2 text-3xl font-extrabold text-amber-600">{{ stats.unverified_users }}</p>
                            <p class="mt-1 text-xs text-slate-500">Pending konfirmasi</p>
                        </article>

                        <article class="p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Instansi</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ stats.total_organizations }}</p>
                            <p class="mt-1 text-xs text-slate-500">Sekolah & Organisasi</p>
                        </article>
                    </div>
                </section>

                <!-- SEARCH & FILTER CONTROLS -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cari Pengguna</label>
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Ketik nama atau alamat email..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-4 py-2 text-sm text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                />
                                <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-3 text-sm"></i>
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Filter Role</label>
                            <select
                                v-model="roleFilter"
                                @change="applyFilters"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                            >
                                <option value="all">Semua Role</option>
                                <option value="participant">Siswa / Peserta</option>
                                <option value="creator">Guru / Creator</option>
                                <option value="organization_admin">Admin Sekolah</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>

                        <!-- Status Filter & Actions -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Email</label>
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="statusFilter"
                                    @change="applyFilters"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                >
                                    <option value="all">Semua Status</option>
                                    <option value="verified">Terverifikasi</option>
                                    <option value="unverified">Belum Verifikasi</option>
                                </select>
                                <button
                                    @click="resetFilters"
                                    type="button"
                                    class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 transition"
                                    title="Reset Filter"
                                >
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- USER TABLE -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Daftar Akun Pengguna</h3>
                            <p class="text-xs text-slate-500">Total ditemukan: {{ users.total }} akun</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Pengguna</th>
                                    <th class="py-3 px-4">Instansi & Role</th>
                                    <th class="py-3 px-4 text-center">Aktivitas</th>
                                    <th class="py-3 px-4">Bergabung</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                    <!-- Info Pengguna -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#3154D5] flex items-center justify-center font-bold text-sm overflow-hidden flex-shrink-0 border border-blue-100">
                                                <AvatarIcon v-if="user.avatar" :name="user.avatar" class="w-5 h-5" />
                                                <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ user.name }}</div>
                                                <div class="text-xs text-slate-400">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Organisasi & Role -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            <div
                                                v-for="org in user.organizations"
                                                :key="org.id"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-medium border"
                                                :class="getRoleBadgeColor(org.pivot?.role)"
                                            >
                                                <span class="font-bold">{{ org.name }}:</span>
                                                <span>{{ formatRoleName(org.pivot?.role) }}</span>
                                            </div>
                                            <div v-if="!user.organizations?.length" class="text-xs text-slate-400 italic">
                                                Belum ada organisasi
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Aktivitas -->
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 text-xs">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-50 border border-slate-100 text-slate-600 font-semibold" title="Kuis Dibuat">
                                                <i class="fa-solid fa-pen-to-square text-[10px] mr-1 text-[#3154D5]"></i>
                                                {{ user.created_quizzes_count }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-md bg-slate-50 border border-slate-100 text-slate-600 font-semibold" title="Attempts Pengerjaan">
                                                <i class="fa-solid fa-bullseye text-[10px] mr-1 text-[#527A12]"></i>
                                                {{ user.attempts_count }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Bergabung -->
                                    <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap">
                                        {{ formatDate(user.created_at) }}
                                    </td>

                                    <!-- Status Verifikasi -->
                                    <td class="py-3.5 px-4">
                                        <span
                                            v-if="user.email_verified_at"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-[#0AB883] border border-emerald-200"
                                        >
                                            <i class="fa-solid fa-circle-check text-xs text-[#0AB883]"></i>
                                            Verified
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                        >
                                            <i class="fa-solid fa-circle-exclamation text-xs text-amber-500"></i>
                                            Unverified
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-right">
                                        <button
                                            @click="openDetail(user)"
                                            type="button"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-sm"
                                        >
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!users.data?.length">
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs italic">
                                        Tidak ada pengguna yang cocok dengan kriteria pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-5 flex justify-end">
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </main>
        </div>

        <!-- MODAL DETAIL PENGGUNA -->
        <div v-if="selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Detail Pengguna</h3>
                    <button @click="closeDetail" class="text-slate-400 hover:text-slate-600 font-bold p-1">
                        <i class="fa-solid fa-xmark text-lg leading-none"></i>
                    </button>
                </div>

                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#3154D5] flex items-center justify-center font-extrabold text-lg overflow-hidden">
                        <AvatarIcon v-if="selectedUser.avatar" :name="selectedUser.avatar" class="w-7 h-7" />
                        <span v-else>{{ selectedUser.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                        <div class="text-base font-bold text-slate-900">{{ selectedUser.name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ selectedUser.email }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">Terdaftar sejak {{ formatDate(selectedUser.created_at) }}</div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Keanggotaan Organisasi & Role</div>
                    <div class="space-y-1.5">
                        <div
                            v-for="org in selectedUser.organizations"
                            :key="org.id"
                            class="p-2.5 rounded-lg border border-slate-200 bg-white flex items-center justify-between text-xs"
                        >
                            <span class="font-bold text-slate-800">{{ org.name }}</span>
                            <span class="px-2.5 py-0.5 rounded-md font-bold border" :class="getRoleBadgeColor(org.pivot?.role)">
                                {{ formatRoleName(org.pivot?.role) }}
                            </span>
                        </div>
                        <div v-if="!selectedUser.organizations?.length" class="text-xs text-slate-400 italic p-3 rounded-lg bg-slate-50 border border-slate-100">
                            Pengguna ini belum terhubung ke instansi / organisasi manapun.
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div class="p-3 rounded-xl bg-blue-50/50 border border-blue-100 text-center">
                        <div class="text-xs text-[#3154D5] font-medium">Kuis Dibuat</div>
                        <div class="text-xl font-extrabold text-[#3154D5] mt-0.5">{{ selectedUser.created_quizzes_count }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 text-center">
                        <div class="text-xs text-[#527A12] font-medium">Attempts Selesai</div>
                        <div class="text-xl font-extrabold text-[#527A12] mt-0.5">{{ selectedUser.attempts_count }}</div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        @click="closeDetail"
                        type="button"
                        class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
