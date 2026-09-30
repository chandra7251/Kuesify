<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    section: string;
    items: { data: { id: number; name: string; slug: string; members_count: number; created_at: string }[]; links?: unknown[]; meta?: unknown };
    filters: Record<string, unknown>;
    summary: {
        categories: { id: number; name: string }[];
        health: Record<string, unknown>;
    };
}>();

const orgs = computed(() => (Array.isArray((props.items as any)?.data) ? (props.items as any).data : Array.isArray(props.items) ? props.items : []) as { id: number; name: string; slug: string; members_count: number; created_at: string }[]);
const categories = computed(() => (props.summary.categories ?? []) as { id: number; name: string }[]);
const health = computed(() => (props.summary.health ?? {}) as Record<string, unknown>);

const fmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Platform Admin — Tenants & Health" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Hero — brand tokens ONLY -->
                <section class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8">
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                        <span class="h-2 w-2 rounded-full bg-brand-secondary" aria-hidden="true"></span>
                        Platform · Super Admin
                    </p>
                    <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Platform Admin</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                        Kelola tenant organisasi, kategori global, dan pantau kesehatan layanan — terpisah dari dashboard eksekutif.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <Link href="/dashboard" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-secondary px-5 text-sm font-bold text-white shadow-sm transition hover:brightness-105">Ke Dashboard Eksekutif</Link>
                        <span class="inline-flex min-h-11 items-center rounded-xl border border-white/20 bg-white/10 px-4 text-xs font-semibold text-white"> {{ orgs.length }} tenant · {{ categories.length }} kategori </span>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Tenants -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Tenants — Organisasi</h2>
                                <p class="mt-1 text-xs text-slate-500">Data global tanpa tenant scope. Kelola dari sini, bukan dari dashboard.</p>
                            </div>
                            <span class="rounded-full bg-brand-secondary/15 px-3 py-1 text-xs font-bold text-[#527A12]">{{ orgs.length }} total</span>
                        </div>
                        <div v-if="orgs.length" class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200">
                            <div v-for="o in orgs" :key="o.id" class="flex items-center justify-between gap-3 bg-white px-4 py-3.5 hover:bg-brand-accent/60">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ o.name }}</p>
                                    <p class="truncate text-xs text-slate-500">/{{ o.slug }} · {{ o.members_count }} anggota · {{ fmt.format(new Date(o.created_at)) }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-brand-primary/10 px-2.5 py-1 text-xs font-bold text-brand-primary">#{{ o.id }}</span>
                            </div>
                        </div>
                        <p v-else class="mt-4 rounded-xl bg-brand-accent px-4 py-8 text-center text-sm text-slate-600">Belum ada organisasi.</p>
                    </section>

                    <!-- Health + Categories -->
                    <div class="grid gap-6">
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                            <h2 class="text-base font-bold text-slate-900">Kesehatan Sistem</h2>
                            <p class="mt-1 text-xs text-slate-500">Reverb, queue, dan AI — sumber yang sama dengan dashboard.</p>
                            <dl class="mt-4 divide-y divide-slate-100 text-sm">
                                <div v-for="(v, k) in health" :key="String(k)" class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                    <dt class="capitalize text-slate-500">{{ String(k).replaceAll('_', ' ') }}</dt>
                                    <dd v-if="String(k) === 'reverb_status'" class="rounded-full px-2.5 py-1 text-xs font-bold" :class="v === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">{{ v }}</dd>
                                    <dd v-else class="font-bold text-slate-900">{{ (v as string) ?? '—' }}</dd>
                                </div>
                            </dl>
                        </section>
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma">
                            <h2 class="text-base font-bold text-slate-900">Kategori Global</h2>
                            <p class="mt-1 text-xs text-slate-500">Dipakai lintas tenant untuk bank soal.</p>
                            <div v-if="categories.length" class="mt-3 flex flex-wrap gap-2">
                                <span v-for="c in categories" :key="c.id" class="rounded-full bg-brand-accent px-3 py-1.5 text-xs font-semibold text-brand-primary">{{ c.name }}</span>
                            </div>
                            <p v-else class="mt-3 text-sm text-slate-500">Belum ada kategori.</p>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
