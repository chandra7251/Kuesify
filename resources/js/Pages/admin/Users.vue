<script setup lang="ts">
import AvatarIcon from '@/Components/AvatarIcon.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    is_banned?: boolean;
    banned_reason?: string | null;
    banned_at?: string | null;
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

function isSuperAdminUser(user: UserItem): boolean {
    return user.organizations?.some((org) => org.pivot?.role === 'super_admin') ?? false;
}

function toggleBanUser(user: UserItem) {
    const actionLabel = user.is_banned ? 'mencabut blokir (unban)' : 'memblokir (ban)';
    const reasonPrompt = user.is_banned
        ? null
        : prompt('Masukkan alasan pemblokiran akun ini:', 'Pelanggaran ketentuan platform.') ?? null;

    if (!user.is_banned && reasonPrompt === null) {
        return; // Dibatalkan pengguna
    }

    if (confirm(`Yakin ingin ${actionLabel} akun ${user.name}?`)) {
        router.patch(
            `/superadmin/users/${user.id}/ban`,
            { reason: reasonPrompt },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (selectedUser.value && selectedUser.value.id === user.id) {
                        selectedUser.value.is_banned = !selectedUser.value.is_banned;
                    }
                },
            },
        );
    }
}

function deleteUserPermanently(user: UserItem) {
    if (confirm(`PERINGATAN: Yakin ingin menghapus permanen akun ${user.name} (${user.email})? Semua data terkait pengguna ini akan terhapus!`)) {
        router.delete(`/superadmin/users/${user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedUser.value && selectedUser.value.id === user.id) {
                    closeDetail();
                }
            },
        });
    }
}

function applyFilters() {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
            status:
                statusFilter.value !== 'all' ? statusFilter.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
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
            <main
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <!-- Header Banner (Clean Theme Matching Dashboard) -->
                <section
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
                                ></span>
                                Super Admin Platform · User Monitoring
                            </p>
                            <h1
                                class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl"
                            >
                                Monitoring Pengguna Global
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base"
                            >
                                Pantau seluruh akun terdaftar di platform
                                Kuesify, status verifikasi email, keanggotaan
                                instansi, dan aktivitas kuis.
                            </p>
                        </div>
                        <div class="grid shrink-0 gap-3 sm:flex">
                            <Link
                                href="/admin"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20"
                            >
                                <i
                                    class="fa-solid fa-arrow-left mr-2 text-sm"
                                ></i>
                                Kembali ke Platform Admin
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- SECTION SUMMARY -->
                <section aria-labelledby="users-summary">
                    <div class="mb-3">
                        <h2
                            id="users-summary"
                            class="text-lg font-bold text-slate-950"
                        >
                            Ringkasan Pengguna Platform
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Metrik status verifikasi dan registrasi seluruh
                            ekosistem SaaS.
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4"
                    >
                        <article
                            class="border-b border-r border-slate-100 p-5 sm:border-b-0 sm:p-6"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Total Pengguna
                            </p>
                            <p
                                class="mt-2 text-3xl font-extrabold text-[#3154D5]"
                            >
                                {{ stats.total_users }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Akun terdaftar global
                            </p>
                        </article>

                        <article
                            class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r sm:p-6"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Terverifikasi
                            </p>
                            <p
                                class="mt-2 text-3xl font-extrabold text-[#527A12]"
                            >
                                {{ stats.verified_users }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Email telah aktif
                            </p>
                        </article>

                        <article class="border-r border-slate-100 p-5 sm:p-6">
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Belum Verifikasi
                            </p>
                            <p
                                class="mt-2 text-3xl font-extrabold text-amber-600"
                            >
                                {{ stats.unverified_users }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Pending konfirmasi
                            </p>
                        </article>

                        <article class="p-5 sm:p-6">
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Total Instansi
                            </p>
                            <p
                                class="mt-2 text-3xl font-extrabold text-[#3154D5]"
                            >
                                {{ stats.total_organizations }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Sekolah & Organisasi
                            </p>
                        </article>
                    </div>
                </section>

                <!-- SEARCH & FILTER CONTROLS -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <form
                        class="grid grid-cols-1 gap-4 md:grid-cols-4"
                        @submit.prevent="applyFilters"
                    >
                        <!-- Search Input -->
                        <div class="md:col-span-2">
                            <label
                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                >Cari Pengguna</label
                            >
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Ketik nama atau alamat email..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-10 pr-4 text-sm text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                />
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-sm text-slate-400"
                                ></i>
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                >Filter Role</label
                            >
                            <select
                                v-model="roleFilter"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                @change="applyFilters"
                            >
                                <option value="all">Semua Role</option>
                                <option value="participant">
                                    Siswa / Peserta
                                </option>
                                <option value="creator">Guru / Creator</option>
                                <option value="organization_admin">
                                    Admin Sekolah
                                </option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>

                        <!-- Status Filter & Actions -->
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                >Status Email</label
                            >
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="statusFilter"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                    @change="applyFilters"
                                >
                                    <option value="all">Semua Status</option>
                                    <option value="verified">
                                        Terverifikasi
                                    </option>
                                    <option value="unverified">
                                        Belum Verifikasi
                                    </option>
                                </select>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-50"
                                    title="Reset Filter"
                                    @click="resetFilters"
                                >
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- USER TABLE -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                Daftar Akun Pengguna
                            </h3>
                            <p class="text-xs text-slate-500">
                                Total ditemukan: {{ users.total }} akun
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="border-y border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                <tr>
                                    <th class="px-4 py-3">Pengguna</th>
                                    <th class="px-4 py-3">Instansi & Role</th>
                                    <th class="px-4 py-3 text-center">
                                        Aktivitas
                                    </th>
                                    <th class="px-4 py-3">Bergabung</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="transition-colors hover:bg-slate-50/50"
                                >
                                    <!-- Info Pengguna -->
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl border border-blue-100 bg-blue-50 text-sm font-bold text-[#3154D5]"
                                            >
                                                <AvatarIcon
                                                    v-if="user.avatar"
                                                    :name="user.avatar"
                                                    class="h-5 w-5"
                                                />
                                                <span v-else>{{
                                                    user.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}</span>
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-slate-800"
                                                >
                                                    {{ user.name }}
                                                    <span
                                                        v-if="user.is_banned"
                                                        class="ml-2 inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-black text-rose-700"
                                                    >
                                                        BANNED
                                                    </span>
                                                </div>
                                                <div
                                                    class="text-xs text-slate-400"
                                                >
                                                    {{ user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Organisasi & Role -->
                                    <td class="px-4 py-3.5">
                                        <div
                                            class="flex max-w-xs flex-wrap gap-1.5"
                                        >
                                            <div
                                                v-for="org in user.organizations"
                                                :key="org.id"
                                                class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-0.5 text-xs font-medium"
                                                :class="
                                                    getRoleBadgeColor(
                                                        org.pivot?.role,
                                                    )
                                                "
                                            >
                                                <span class="font-bold"
                                                    >{{ org.name }}:</span
                                                >
                                                <span>{{
                                                    formatRoleName(
                                                        org.pivot?.role,
                                                    )
                                                }}</span>
                                            </div>
                                            <div
                                                v-if="
                                                    !user.organizations?.length
                                                "
                                                class="text-xs italic text-slate-400"
                                            >
                                                Belum ada organisasi
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Aktivitas -->
                                    <td class="px-4 py-3.5 text-center">
                                        <div
                                            class="inline-flex items-center gap-1.5 text-xs"
                                        >
                                            <span
                                                class="rounded-md border border-slate-100 bg-slate-50 px-2 py-0.5 font-semibold text-slate-600"
                                                title="Kuis Dibuat"
                                            >
                                                <i
                                                    class="fa-solid fa-pen-to-square mr-1 text-[10px] text-[#3154D5]"
                                                ></i>
                                                {{ user.created_quizzes_count }}
                                            </span>
                                            <span
                                                class="rounded-md border border-slate-100 bg-slate-50 px-2 py-0.5 font-semibold text-slate-600"
                                                title="Attempts Pengerjaan"
                                            >
                                                <i
                                                    class="fa-solid fa-bullseye mr-1 text-[10px] text-[#527A12]"
                                                ></i>
                                                {{ user.attempts_count }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Bergabung -->
                                    <td
                                        class="whitespace-nowrap px-4 py-3.5 text-xs text-slate-500"
                                    >
                                        {{ formatDate(user.created_at) }}
                                    </td>

                                    <!-- Status Verifikasi -->
                                    <td class="px-4 py-3.5">
                                        <span
                                            v-if="user.email_verified_at"
                                            class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-[#0AB883]"
                                        >
                                            <i
                                                class="fa-solid fa-circle-check text-xs text-[#0AB883]"
                                            ></i>
                                            Verified
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700"
                                        >
                                            <i
                                                class="fa-solid fa-circle-exclamation text-xs text-amber-500"
                                            ></i>
                                            Unverified
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                                @click="openDetail(user)"
                                            >
                                                Detail
                                            </button>
                                            <button
                                                v-if="!isSuperAdminUser(user)"
                                                type="button"
                                                class="rounded-xl border px-3 py-1.5 text-xs font-bold shadow-sm transition"
                                                :class="
                                                    user.is_banned
                                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                                        : 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'
                                                "
                                                @click="toggleBanUser(user)"
                                            >
                                                {{ user.is_banned ? 'Unban' : 'Ban' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!users.data?.length">
                                    <td
                                        colspan="6"
                                        class="py-8 text-center text-xs italic text-slate-400"
                                    >
                                        Tidak ada pengguna yang cocok dengan
                                        kriteria pencarian.
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
        <div
            v-if="selectedUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
        >
            <div
                class="animate-in fade-in zoom-in-95 w-full max-w-lg space-y-5 rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl duration-200"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <h3 class="text-base font-bold text-slate-900">
                        Detail Pengguna
                    </h3>
                    <button
                        class="p-1 font-bold text-slate-400 hover:text-slate-600"
                        @click="closeDetail"
                    >
                        <i class="fa-solid fa-xmark text-lg leading-none"></i>
                    </button>
                </div>

                <div
                    class="flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-blue-100 text-lg font-extrabold text-[#3154D5]"
                    >
                        <AvatarIcon
                            v-if="selectedUser.avatar"
                            :name="selectedUser.avatar"
                            class="h-7 w-7"
                        />
                        <span v-else>{{
                            selectedUser.name.charAt(0).toUpperCase()
                        }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900">
                                {{ selectedUser.name }}
                            </span>
                            <span
                                v-if="selectedUser.is_banned"
                                class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-black text-rose-700"
                            >
                                BANNED
                            </span>
                        </div>
                        <div class="font-mono text-xs text-slate-500">
                            {{ selectedUser.email }}
                        </div>
                        <div v-if="selectedUser.is_banned && selectedUser.banned_reason" class="mt-1 text-xs text-rose-600 font-semibold">
                            Alasan Ban: {{ selectedUser.banned_reason }}
                        </div>
                        <div class="mt-0.5 text-xs text-slate-400">
                            Terdaftar sejak
                            {{ formatDate(selectedUser.created_at) }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <div
                        class="text-xs font-bold uppercase tracking-wider text-slate-500"
                    >
                        Keanggotaan Organisasi & Role
                    </div>
                    <div class="space-y-1.5">
                        <div
                            v-for="org in selectedUser.organizations"
                            :key="org.id"
                            class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-2.5 text-xs"
                        >
                            <span class="font-bold text-slate-800">{{
                                org.name
                            }}</span>
                            <span
                                class="rounded-md border px-2.5 py-0.5 font-bold"
                                :class="getRoleBadgeColor(org.pivot?.role)"
                            >
                                {{ formatRoleName(org.pivot?.role) }}
                            </span>
                        </div>
                        <div
                            v-if="!selectedUser.organizations?.length"
                            class="rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs italic text-slate-400"
                        >
                            Pengguna ini belum terhubung ke instansi /
                            organisasi manapun.
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div
                        class="rounded-xl border border-blue-100 bg-blue-50/50 p-3 text-center"
                    >
                        <div class="text-xs font-medium text-[#3154D5]">
                            Kuis Dibuat
                        </div>
                        <div
                            class="mt-0.5 text-xl font-extrabold text-[#3154D5]"
                        >
                            {{ selectedUser.created_quizzes_count }}
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3 text-center"
                    >
                        <div class="text-xs font-medium text-[#527A12]">
                            Attempts Selesai
                        </div>
                        <div
                            class="mt-0.5 text-xl font-extrabold text-[#527A12]"
                        >
                            {{ selectedUser.attempts_count }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div v-if="!isSuperAdminUser(selectedUser)" class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl border px-3 py-2 text-xs font-bold transition"
                            :class="
                                selectedUser.is_banned
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    : 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'
                            "
                            @click="toggleBanUser(selectedUser)"
                        >
                            {{ selectedUser.is_banned ? 'Buka Blokir (Unban)' : 'Blokir Pengguna (Ban)' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100"
                            @click="deleteUserPermanently(selectedUser)"
                        >
                            Hapus Akun
                        </button>
                    </div>
                    <div v-else></div>
                    <button
                        type="button"
                        class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-bold text-white transition hover:bg-slate-800"
                        @click="closeDetail"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
