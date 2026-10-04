<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Organization = {
    id: number;
    name: string;
    slug: string;
    members_count: number;
};

type Category = {
    id: number;
    name: string;
    theme_key?: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    organizations: {
        data: Organization[];
        links?: PaginationLink[];
        total?: number;
        current_page?: number;
        last_page?: number;
    };
    categories: Category[];
    health: Record<string, number>;
}>();

const searchQuery = ref('');

const filteredOrganizations = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return props.organizations.data;
    return props.organizations.data.filter(
        (org) =>
            org.name.toLowerCase().includes(query) ||
            org.slug.toLowerCase().includes(query),
    );
});

const totalMembers = computed(() =>
    props.organizations.data.reduce(
        (sum, item) => sum + (item.members_count || 0),
        0,
    ),
);

const totalTenants = computed(
    () => props.organizations.total ?? props.organizations.data.length,
);
</script>

<template>
    <Head title="Tenant Organisasi" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent/20 py-6 sm:py-8">
            <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Hero Banner ala Siswa -->
                <section
                    class="relative overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma-sm sm:rounded-3xl sm:p-8"
                >
                    <div
                        class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5 blur-2xl"
                    />
                    <div
                        class="absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-brand-secondary/15 blur-2xl"
                    />

                    <div
                        class="relative z-10 grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/30 bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-brand-secondary"
                                    />
                                    SUPER ADMIN • PLATFORM
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/90 backdrop-blur-sm"
                                >
                                    <AppIcon
                                        name="tenants"
                                        :size="14"
                                        class="text-brand-secondary"
                                    />
                                    Tenant Control
                                </span>
                            </div>

                            <h1
                                class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl"
                            >
                                Tenant Organisasi
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base"
                            >
                                Monitoring entitas organisasi, kapasitas basis
                                anggota aktif, dan persebaran ekosistem Kuesify
                                secara terpusat.
                            </p>
                        </div>

                        <!-- Mini Card Highlight -->
                        <div
                            class="rounded-2xl border border-brand-secondary/70 bg-brand-secondary p-5 text-brand-dark shadow-figma-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-black text-brand-primary"
                                >
                                    <AppIcon name="organization" :size="16" />
                                    EKOSISTEM TENANT
                                </span>
                                <span
                                    class="rounded-full bg-brand-primary/10 px-2.5 py-0.5 text-[11px] font-black text-brand-primary"
                                >
                                    Live Data
                                </span>
                            </div>
                            <div
                                class="mt-3 flex items-baseline justify-between"
                            >
                                <p
                                    class="text-3xl font-black text-brand-primary"
                                >
                                    {{ totalTenants }}
                                    <span
                                        class="text-xs font-bold text-brand-dark"
                                    >
                                        organisasi
                                    </span>
                                </p>
                            </div>
                            <p
                                class="mt-2 text-xs font-semibold text-brand-dark/80"
                            >
                                Total anggota terdata di halaman ini:
                                <strong class="font-black text-brand-primary">
                                    {{ totalMembers }} pengguna
                                </strong>
                            </p>
                        </div>
                    </div>
                </section>

                <!-- KPI / Stats Row ala Siswa -->
                <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary"
                        >
                            <AppIcon name="organization" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Total Tenant
                            </p>
                            <p
                                class="text-lg font-black text-slate-900 sm:text-xl"
                            >
                                {{ totalTenants }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/30 text-brand-primary"
                        >
                            <AppIcon name="members" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Total Anggota
                            </p>
                            <p
                                class="text-lg font-black text-slate-900 sm:text-xl"
                            >
                                {{ totalMembers }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700"
                        >
                            <AppIcon name="categories" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Kategori Global
                            </p>
                            <p
                                class="text-lg font-black text-slate-900 sm:text-xl"
                            >
                                {{ categories.length }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
                        >
                            <AppIcon name="platform" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Antrean Job
                            </p>
                            <p
                                class="text-lg font-black text-slate-900 sm:text-xl"
                            >
                                {{ health.queued_jobs ?? 0 }}
                            </p>
                        </div>
                    </article>
                </section>

                <!-- Main Card: Papan Tenant -->
                <section
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-figma-sm sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-primary text-white"
                            >
                                <AppIcon name="tenants" :size="18" />
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Daftar Tenant Organisasi
                                </h2>
                                <p class="text-xs font-semibold text-slate-500">
                                    Semua entitas organisasi yang terdaftar di
                                    platform
                                </p>
                            </div>
                        </div>

                        <!-- Search Filter -->
                        <div class="w-full sm:w-72">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama atau slug tenant..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 placeholder-slate-400 transition focus:border-brand-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
                            />
                        </div>
                    </div>

                    <!-- Grid Cards Tenant ala Siswa -->
                    <div
                        v-if="filteredOrganizations.length > 0"
                        class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="item in filteredOrganizations"
                            :key="item.id"
                            class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/50 hover:shadow-figma-sm"
                        >
                            <div class="space-y-3">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white"
                                        >
                                            <AppIcon
                                                name="organization"
                                                :size="20"
                                            />
                                        </div>
                                        <div class="min-w-0">
                                            <h3
                                                class="truncate text-base font-black text-slate-900 group-hover:text-brand-primary"
                                            >
                                                {{ item.name }}
                                            </h3>
                                            <p
                                                class="truncate text-xs font-bold text-slate-400"
                                            >
                                                /{{ item.slug }}
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500"
                                    >
                                        #{{ item.id }}
                                    </span>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/80 p-3"
                                >
                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
                                        <span class="font-bold text-slate-500">
                                            Kapasitas Anggota
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border border-brand-secondary/60 bg-brand-secondary/30 px-2.5 py-0.5 text-xs font-black text-brand-dark"
                                        >
                                            <AppIcon
                                                name="members"
                                                :size="13"
                                            />
                                            {{ item.members_count }} anggota
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 font-bold text-emerald-600"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-emerald-500"
                                    />
                                    Aktif
                                </span>
                                <span class="font-bold text-slate-400">
                                    Organisasi Terdaftar
                                </span>
                            </div>
                        </article>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="my-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-secondary/20 text-brand-primary"
                        >
                            <AppIcon name="tenants" :size="28" />
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-800">
                            {{
                                searchQuery
                                    ? 'Tenant Tidak Ditemukan'
                                    : 'Belum Ada Tenant'
                            }}
                        </h3>
                        <p class="mt-1 max-w-sm text-xs text-slate-500">
                            {{
                                searchQuery
                                    ? `Tidak ada organisasi dengan nama atau slug "${searchQuery}". Coba kata kunci lain.`
                                    : 'Belum ada organisasi tenant yang terdaftar dalam sistem.'
                            }}
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            organizations.links &&
                            organizations.links.length > 3
                        "
                        class="mt-6 flex justify-center border-t border-slate-100 pt-4"
                    >
                        <Pagination :links="organizations.links" />
                    </div>
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
