<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        members: number;
        groups: number;
        quizzes: number;
        questions: number;
        liveSessions: number;
        attempts: number;
    };
    recentQuizzes: { id: number; title: string; status: string; updated_at: string }[];
}>();

const statCards = computed(() => [
    { label: 'Anggota', value: props.stats.members, accent: 'bg-brand-primary' },
    { label: 'Grup', value: props.stats.groups, accent: 'bg-brand-secondary' },
    { label: 'Kuis', value: props.stats.quizzes, accent: 'bg-brand-primary' },
    { label: 'Soal', value: props.stats.questions, accent: 'bg-brand-secondary' },
    { label: 'Live Aktif', value: props.stats.liveSessions, accent: 'bg-brand-primary' },
    { label: 'Attempts', value: props.stats.attempts, accent: 'bg-brand-secondary' },
]);

const fmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Admin — Dashboard Organisasi" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <section class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                        <span class="h-2 w-2 rounded-full bg-brand-secondary" aria-hidden="true"></span>
                        {{ roleLabel(organization.role) }} · {{ organization.name }}
                    </p>
                    <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                        Dashboard Organisasi
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                        Kelola anggota, grup, kuis, dan pantau aktivitas belajar di tenant kamu — terpisah dari Platform Admin global.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <Link href="/organization" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-secondary px-5 text-sm font-bold text-white shadow-sm transition hover:brightness-105">Kelola anggota</Link>
                        <Link href="/reports" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15">Lihat laporan</Link>
                    </div>
                </section>

                <section aria-labelledby="org-kpi" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <h2 id="org-kpi" class="sr-only">Ringkasan tenant</h2>
                    <article v-for="c in statCards" :key="c.label" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
                        <div class="mb-3 h-1 w-8 rounded-full" :class="c.accent" aria-hidden="true"></div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ c.label }}</p>
                        <p class="mt-1 text-2xl font-black tabular-nums text-slate-900">{{ c.value }}</p>
                    </article>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Kuis terbaru di organisasi ini</h2>
                                <p class="mt-1 text-xs text-slate-500">Hanya data tenant kamu — bukan global.</p>
                            </div>
                            <Link href="/quizzes" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-primary hover:bg-brand-accent">Lihat semua</Link>
                        </div>
                        <div v-if="recentQuizzes.length" class="mt-4 divide-y divide-slate-100">
                            <div v-for="q in recentQuizzes" :key="q.id" class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ q.title }}</p>
                                    <p class="text-xs text-slate-500">Diperbarui {{ fmt.format(new Date(q.updated_at)) }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-[#527A12]">{{ q.status }}</span>
                            </div>
                        </div>
                        <p v-else class="mt-6 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600">Belum ada kuis di tenant ini.</p>
                    </section>
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                        <h2 class="text-base font-bold text-slate-900">Aksi cepat Org Admin</h2>
                        <div class="mt-4 grid gap-3">
                            <Link href="/questions" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-brand-secondary">Question Bank <span class="text-brand-primary">→</span></Link>
                            <Link href="/organization" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-brand-secondary">Anggota & Grup <span class="text-brand-primary">→</span></Link>
                            <Link href="/reports" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-brand-secondary">Laporan tenant <span class="text-brand-primary">→</span></Link>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
