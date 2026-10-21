<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

type Material = { id: number; original_name: string; visibility: 'organization' | 'public'; version: number; page_count: number | null; size: number; extracted_text: string | null; updated_at: string; creator: { id: number; name: string } | null };
type Check = { answer: string; confidence: number } | null;
const props = defineProps<{ material: Material; note: string | null; check: Check; isRead: boolean }>();
const note = ref(props.note ?? '');
const check = reactive({ answer: props.check?.answer ?? '', confidence: props.check?.confidence ?? 3 });
const feedback = ref('');
const processing = ref(false);
const showTools = ref(false);
function formatSize(bytes: number): string { if (bytes < 1024) return `${bytes} B`; if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`; return `${(bytes / (1024 * 1024)).toFixed(1)} MB`; }
function formatDate(value: string): string { return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value)); }
function save(url: string, method: 'post' | 'put', data: Record<string, string | number> = {}): void { processing.value = true; const options = { preserveScroll: true, onSuccess: () => { feedback.value = 'Perubahan tersimpan.'; }, onError: () => { feedback.value = 'Perubahan gagal disimpan.'; }, onFinish: () => { processing.value = false; } }; if (method === 'post') router.post(url, data, options); else router.put(url, data, options); }
function markRead(): void { if (!props.isRead) save(route('participant.materials.read', props.material.id), 'post'); }
function saveNote(): void { if (note.value.trim()) save(route('participant.materials.note.save', props.material.id), 'put', { body: note.value }); }
function saveCheck(): void { if (check.answer.trim()) save(route('participant.materials.check.save', props.material.id), 'put', { answer: check.answer, confidence: check.confidence }); }
</script>

<template>
    <Head :title="material.original_name" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link
                    href="/participant/materials"
                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:border-brand-primary/30 hover:text-brand-primary"
                >
                    <span aria-hidden="true">←</span>
                    Kembali ke materi
                </Link>
                <span class="rounded-full bg-brand-accent px-3 py-1.5 text-xs font-bold text-brand-primary">
                    {{ material.visibility === 'public' ? 'Publik' : 'Organisasi' }}
                </span>
            </div>

            <section class="overflow-hidden rounded-3xl bg-brand-primary text-white shadow-figma">
                <div class="p-6 sm:p-8">
                    <div class="max-w-4xl">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-secondary">
                            Ruang baca materi
                        </p>
                        <h1 class="mt-3 break-words text-2xl font-black leading-tight tracking-tight sm:text-4xl">
                            {{ material.original_name }}
                        </h1>
                        <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold text-white/75">
                            <span>{{ formatSize(material.size) }}</span>
                            <span>Versi {{ material.version }}</span>
                            <span v-if="material.page_count">{{ material.page_count }} halaman</span>
                            <span>Diperbarui {{ formatDate(material.updated_at) }}</span>
                        </div>
                    </div>
                    <div class="mt-7 flex flex-wrap gap-3 border-t border-white/15 pt-5">
                        <button
                            type="button"
                            :disabled="isRead || processing"
                            class="inline-flex min-h-11 items-center rounded-xl bg-brand-secondary px-4 text-sm font-black text-brand-primary transition hover:bg-brand-secondary/85 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="markRead"
                        >
                            {{ isRead ? 'Sudah selesai' : 'Tandai selesai' }}
                        </button>
                        <a
                            :href="route('materials.download', material.id)"
                            class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-bold text-brand-primary transition hover:bg-slate-100"
                        >
                            Unduh materi
                        </a>
                        <Link
                            :href="route('participant.materials.print', material.id)"
                            class="inline-flex min-h-11 items-center rounded-xl border border-white/30 px-4 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Cetak
                        </Link>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                <article class="min-w-0 rounded-3xl border border-slate-200 bg-white p-6 shadow-figma sm:p-8">
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-5">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary">Materi lengkap</p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">{{ material.original_name }}</h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">Baca dengan ritme kamu</span>
                    </div>
                    <div class="mt-6 rounded-2xl bg-slate-50 p-5 sm:p-7">
                        <div class="max-w-3xl whitespace-pre-wrap break-words text-[15px] leading-8 text-slate-700">
                            {{ material.extracted_text || 'Belum ada teks hasil ekstraksi.' }}
                        </div>
                    </div>
                </article>

                <aside class="flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-5 shadow-figma lg:sticky lg:top-6">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary">Ringkasan materi</p>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Ukuran</dt><dd class="text-right font-bold text-slate-900">{{ formatSize(material.size) }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Halaman</dt><dd class="text-right font-bold text-slate-900">{{ material.page_count ?? '-' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Pengunggah</dt><dd class="max-w-[150px] truncate text-right font-bold text-slate-900">{{ material.creator?.name ?? '-' }}</dd></div>
                    </dl>
                    <button
                        type="button"
                        class="mt-auto inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:border-brand-primary/30 hover:text-brand-primary"
                        :aria-expanded="showTools"
                        @click="showTools = !showTools"
                    >
                        {{ showTools ? 'Tutup alat belajar' : 'Buka alat belajar' }}
                    </button>
                </aside>
            </section>

            <section v-if="showTools" class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-figma sm:p-6">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-brand-primary">Catatan pribadi</p>
                    <textarea v-model="note" rows="5" class="mt-4 w-full rounded-xl border-slate-300 text-sm leading-6 focus:border-brand-primary focus:ring-brand-primary" placeholder="Tulis ringkasan atau hal penting…" />
                    <button type="button" :disabled="processing || !note.trim()" class="mt-3 min-h-10 rounded-xl bg-brand-primary px-4 text-sm font-bold text-white disabled:opacity-50" @click="saveNote">Simpan catatan</button>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-figma sm:p-6">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-brand-primary">Cek pemahaman</p>
                    <p class="mt-2 text-sm text-slate-500">Tulis satu hal utama yang kamu pahami.</p>
                    <textarea v-model="check.answer" rows="4" class="mt-4 w-full rounded-xl border-slate-300 text-sm leading-6 focus:border-brand-primary focus:ring-brand-primary" placeholder="Contoh: inti materi ini adalah…" />
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <label for="confidence" class="text-sm font-semibold text-slate-600">Tingkat yakin</label>
                        <select id="confidence" v-model.number="check.confidence" class="rounded-xl border-slate-300 text-sm"><option v-for="level in 5" :key="level" :value="level">{{ level }}/5</option></select>
                        <button type="button" :disabled="processing || !check.answer.trim()" class="min-h-10 rounded-xl bg-brand-primary px-4 text-sm font-bold text-white disabled:opacity-50" @click="saveCheck">Simpan</button>
                    </div>
                </div>
            </section>
            <p v-if="feedback" class="rounded-xl bg-brand-accent px-4 py-3 text-sm font-bold text-brand-primary" role="status">{{ feedback }}</p>
        </main>
    </AuthenticatedLayout>
</template>