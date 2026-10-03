<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

type Organization = {
    id: number;
    name: string;
    slug: string;
    members_count: number;
};
type Category = { id: number; name: string; theme_key?: string | null };
const props = defineProps<{
    organizations: { data: Organization[] };
    categories: Category[];
    health: Record<string, number>;
}>();

const hasFailures = computed(
    () => (props.health.failed_jobs ?? 0) > 0 || (props.health.ai_failures ?? 0) > 0,
);
</script>

<template>
    <Head title="AI Monitoring" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent/20 py-6 sm:py-8">
            <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
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
                                    <span class="h-2 w-2 rounded-full bg-brand-secondary" />
                                    SUPER ADMIN • PLATFORM
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/90 backdrop-blur-sm"
                                >
                                    <AppIcon name="ai" :size="14" class="text-brand-secondary" />
                                    AI Operations
                                </span>
                            </div>
                            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl">
                                AI Monitoring
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">
                                Pantau antrean generator AI dan tinjau kegagalan layanan dari satu ruang kontrol.
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-brand-secondary/70 bg-brand-secondary p-5 text-brand-dark shadow-figma-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-brand-primary">
                                    <AppIcon name="platform" :size="16" />
                                    STATUS PLATFORM
                                </span>
                                <span
                                    class="rounded-full bg-brand-primary/10 px-2.5 py-0.5 text-[11px] font-black text-brand-primary"
                                >
                                    {{ hasFailures ? 'Perlu ditinjau' : 'Berjalan normal' }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary text-brand-secondary"
                                >
                                    <AppIcon :name="hasFailures ? 'moderation' : 'admin'" :size="24" />
                                </div>
                                <div>
                                    <p class="text-xl font-black text-brand-primary">
                                        {{ hasFailures ? 'Ada anomali' : 'Semua sistem siap' }}
                                    </p>
                                    <p class="text-xs font-semibold text-brand-dark/75">
                                        Monitoring data terbaru
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary">
                            <AppIcon name="clock" :size="20" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500">Queued Jobs</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ health.queued_jobs ?? 0 }}</p>
                        </div>
                    </article>
                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/30 text-brand-primary">
                            <AppIcon name="admin" :size="20" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500">Failed Jobs</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ health.failed_jobs ?? 0 }}</p>
                        </div>
                    </article>
                    <article class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                            <AppIcon name="ai" :size="20" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500">AI Failures</p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">{{ health.ai_failures ?? 0 }}</p>
                        </div>
                    </article>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-figma-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-primary text-white">
                            <AppIcon name="ai" :size="18" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Ringkasan Operasional</h2>
                            <p class="text-xs font-semibold text-slate-500">Sinyal utama untuk menjaga layanan AI tetap stabil</p>
                        </div>
                    </div>
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                :class="hasFailures ? 'bg-rose-100 text-rose-700' : 'bg-brand-secondary/25 text-brand-primary'"
                            >
                                <AppIcon :name="hasFailures ? 'moderation' : 'admin'" :size="20" />
                            </div>
                            <div>
                                <p class="font-black text-slate-900">
                                    {{ hasFailures ? 'Ada job yang perlu ditinjau' : 'Belum ada kegagalan yang perlu ditinjau' }}
                                </p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                    {{ hasFailures ? 'Periksa failed jobs dan AI failures sebelum proses berikutnya menumpuk.' : 'Antrean dan proses AI berada dalam kondisi normal berdasarkan data monitoring saat ini.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
