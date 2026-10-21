<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { gsap } from 'gsap';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue';

type Material = {
    id: number;
    original_name: string;
    size: number;
    page_count: number | null;
    read_count: number;
    visibility: 'organization' | 'public';
    version: number;
    extracted_text: string | null;
    updated_at: string;
    creator: { id: number; name: string } | null;
    notes: { id: number; body: string }[];
    checks: { id: number; answer: string; confidence: number }[];
};
type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    materials: Paginator<Material>;
    filters: { search?: string | null; scope?: string | null };
}>();

const filters = useForm({
    search: props.filters.search ?? '',
    scope: props.filters.scope ?? 'all',
});
const noteDrafts = reactive<Record<number, string>>({});
const checkDrafts = reactive<
    Record<number, { answer: string; confidence: number }>
>({});
const expandedMaterials = reactive<Record<number, boolean>>({});
const actionFeedback = reactive<Record<number, string>>({});
const actionProcessing = reactive<Record<number, boolean>>({});
const motionRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;
let motionMedia: gsap.MatchMedia | null = null;

onMounted(() => {
    motionMedia = gsap.matchMedia();
    motionMedia.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            const timeline = gsap.timeline({
                defaults: { ease: 'power2.out' },
            });
            const hero = motionRoot.value?.querySelector('[data-motion-hero]');
            const search = motionRoot.value?.querySelector('[data-motion-search]');
            const results = motionRoot.value?.querySelector('[data-motion-results]');
            const cards = motionRoot.value?.querySelectorAll('[data-motion-card]');

            if (hero)
                timeline.from(hero, {
                    opacity: 0,
                    y: 20,
                    duration: 0.4,
                    clearProps: 'all',
                });
            if (search)
                timeline.from(
                    search,
                    {
                        opacity: 0,
                        y: 20,
                        duration: 0.4,
                        clearProps: 'all',
                    },
                    '-=0.2',
                );
            if (results)
                timeline.from(
                    results,
                    {
                        opacity: 0,
                        y: 20,
                        duration: 0.4,
                        clearProps: 'all',
                    },
                    '-=0.15',
                );
            if (cards?.length)
                timeline.from(
                    cards,
                    {
                        opacity: 0,
                        y: 20,
                        duration: 0.4,
                        stagger: 0.07,
                        clearProps: 'all',
                    },
                    '-=0.2',
                );

            return () => timeline.kill();
        }, motionRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    motionMedia?.revert();
    gsapCtx?.revert();
});

function paginationLabel(label: string): string {
    if (label.includes('Previous') || label.includes('&laquo;')) return '‹';
    if (label.includes('Next') || label.includes('&raquo;')) return '›';
    if (label.includes('...')) return '…';

    return label.replace(/<[^>]*>/g, '');
}

function syncNoteDrafts(): void {
    for (const material of props.materials.data) {
        if (noteDrafts[material.id] === undefined) {
            noteDrafts[material.id] = material.notes[0]?.body ?? '';
        }

        if (checkDrafts[material.id] === undefined) {
            checkDrafts[material.id] = {
                answer: material.checks[0]?.answer ?? '',
                confidence: material.checks[0]?.confidence ?? 3,
            };
        }
    }
}

syncNoteDrafts();
watch(() => props.materials.data, syncNoteDrafts);

function applyFilters(): void {
    router.get(
        route('participant.materials.index'),
        {
            search: filters.search || undefined,
            scope: filters.scope === 'all' ? undefined : filters.scope,
        },
        { preserveState: true, replace: true },
    );
}

function clearFilters(): void {
    filters.search = '';
    filters.scope = 'all';
    applyFilters();
}

function formatSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const kilobytes = bytes / 1024;

    if (kilobytes < 1024) {
        return `${kilobytes.toFixed(1)} KB`;
    }

    return `${(kilobytes / 1024).toFixed(1)} MB`;
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function excerpt(material: Material): string {
    const text = material.extracted_text?.replace(/\s+/g, ' ').trim();

    if (!text) {
        return 'Materi siap diunduh untuk belajar mandiri.';
    }

    return text.length > 150 ? `${text.slice(0, 150)}…` : text;
}

function markRead(material: Material): void {
    if (material.read_count > 0) {
        return;
    }

    actionProcessing[material.id] = true;
    router.post(
        route('participant.materials.read', material.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                actionFeedback[material.id] = 'Materi ditandai selesai.';
            },
            onError: () => {
                actionFeedback[material.id] =
                    'Gagal menandai materi. Coba lagi.';
            },
            onFinish: () => {
                actionProcessing[material.id] = false;
            },
        },
    );
}

function toggleLearningTools(material: Material): void {
    expandedMaterials[material.id] = !expandedMaterials[material.id];
}

function saveNote(material: Material): void {
    const body = noteDrafts[material.id]?.trim() ?? '';

    if (!body) {
        return;
    }

    actionProcessing[material.id] = true;
    router.put(
        route('participant.materials.note.save', material.id),
        { body },
        {
            preserveScroll: true,
            onSuccess: () => {
                actionFeedback[material.id] = 'Catatan tersimpan.';
            },
            onError: () => {
                actionFeedback[material.id] = 'Catatan gagal disimpan.';
            },
            onFinish: () => {
                actionProcessing[material.id] = false;
            },
        },
    );
}

const materialToDelete = ref<Material | null>(null);
const showDeleteModal = ref(false);

function promptDeleteNote(material: Material): void {
    materialToDelete.value = material;
    showDeleteModal.value = true;
}

function confirmDeleteNote(): void {
    if (!materialToDelete.value) return;
    const material = materialToDelete.value;
    showDeleteModal.value = false;
    if (!window.confirm('Hapus catatan pribadi untuk materi ini?')) {
        return;
    }

    actionProcessing[material.id] = true;
    router.delete(route('participant.materials.note.delete', material.id), {
        preserveScroll: true,
        onSuccess: () => {
            noteDrafts[material.id] = '';
            actionFeedback[material.id] = 'Catatan dihapus.';
        },
        onError: () => {
            actionFeedback[material.id] = 'Catatan gagal dihapus.';
        },
        onFinish: () => {
            actionProcessing[material.id] = false;
        },
    });
}

function saveCheck(material: Material): void {
    const draft = checkDrafts[material.id];
    const answer = draft?.answer.trim() ?? '';

    if (!draft || !answer) {
        return;
    }

    actionProcessing[material.id] = true;
    router.put(
        route('participant.materials.check.save', material.id),
        { answer, confidence: draft.confidence },
        {
            preserveScroll: true,
            onSuccess: () => {
                actionFeedback[material.id] =
                    'Cek pemahaman tersimpan. Kamu bisa lanjut tandai selesai.';
            },
            onError: () => {
                actionFeedback[material.id] = 'Cek pemahaman gagal disimpan.';
            },
            onFinish: () => {
                actionProcessing[material.id] = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Materi Belajar" />

    <AuthenticatedLayout>
        <main
            ref="motionRoot"
            data-motion="participant-materials"
            class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
        >
            <section
                data-motion-hero
                class="rounded-2xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-brand-secondary"
                >
                    Perpustakaan siswa
                </p>
                <div
                    class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-black tracking-tight">
                            Materi belajar dari organisasi dan publik
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-white/80"
                        >
                            Baca ringkasan materi, cari topik penting, lalu
                            unduh file sumber tanpa membuka workspace guru.
                        </p>
                    </div>
                    <Link
                        href="/dashboard"
                        class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-bold text-brand-primary shadow-sm"
                    >
                        Kembali dashboard
                    </Link>
                </div>
            </section>

            <section
                data-motion-search
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
            >
                <form
                    class="grid gap-3 md:grid-cols-[1fr_16rem_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <label class="sr-only" for="material-search"
                        >Cari materi</label
                    >
                    <input
                        id="material-search"
                        v-model="filters.search"
                        type="search"
                        class="min-h-11 rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="Cari nama file atau isi materi…"
                    />
                    <label class="sr-only" for="material-scope">Cakupan</label>
                    <select
                        id="material-scope"
                        v-model="filters.scope"
                        class="min-h-11 rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                    >
                        <option value="all">Semua materi</option>
                        <option value="organization">Organisasi saya</option>
                        <option value="public">Publik</option>
                    </select>
                    <button
                        type="submit"
                        class="min-h-11 rounded-xl bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-hover"
                    >
                        Cari
                    </button>
                    <button
                        type="button"
                        class="min-h-11 rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 hover:bg-slate-50"
                        @click="clearFilters"
                    >
                        Reset
                    </button>
                </form>
            </section>

            <section data-motion-results class="space-y-4">
                <p class="text-sm font-semibold text-slate-600">
                    Menampilkan {{ materials.from ?? 0 }}–{{
                        materials.to ?? 0
                    }}
                    dari {{ materials.total }} materi
                </p>

                <div
                    v-if="materials.data.length"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="material in materials.data"
                        :key="material.id"
                        data-motion-card
                        class="group flex min-h-[300px] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-figma transition duration-200 hover:-translate-y-0.5 hover:border-brand-primary/30 hover:shadow-lg"
                    >
                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-brand-primary">
                                        {{ material.visibility === 'public' ? 'Publik' : 'Organisasi' }}
                                    </p>
                                    <h2 class="mt-2 line-clamp-2 min-h-[3rem] overflow-hidden text-ellipsis text-lg font-black leading-7 text-slate-950" :title="material.original_name">
                                        {{ material.original_name }}
                                    </h2>
                                </div>
                                <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold" :class="material.read_count > 0 ? 'bg-brand-secondary/20 text-brand-primary' : 'bg-brand-accent text-brand-primary'">
                                    {{ material.read_count > 0 ? 'Selesai' : 'Materi' }}
                                </span>
                            </div>
                            <p class="mt-2 line-clamp-2 min-h-[3rem] overflow-hidden text-ellipsis text-sm leading-6 text-slate-500">{{ excerpt(material) }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-slate-500">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1.5">{{ formatSize(material.size) }}</span>
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1.5">Versi {{ material.version }}</span>
                                <span v-if="material.page_count" class="rounded-lg bg-slate-100 px-2.5 py-1.5">{{ material.page_count }} halaman</span>
                            </div>
                            <div class="mt-4 flex min-h-10 items-center gap-2 text-xs text-slate-500">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-brand-secondary"></span>
                                <span class="truncate">Diperbarui {{ formatDate(material.updated_at) }}</span>
                                <span v-if="material.creator" class="truncate">· {{ material.creator.name }}</span>
                            </div>
                            <p v-if="actionFeedback[material.id]" class="mt-4 rounded-xl bg-brand-accent px-3 py-2 text-sm font-semibold text-brand-primary" role="status">{{ actionFeedback[material.id] }}</p>
                            <Link :href="route('participant.materials.show', material.id)" class="mt-auto inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand-primary px-4 py-3.5 text-sm font-black text-brand-secondary transition hover:bg-brand-hover">
                                Lihat Detail Materi
                                <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </Link>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-figma"
                >
                    <p class="text-base font-bold text-slate-900">
                        Materi tidak ditemukan
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Coba kata kunci lain atau reset filter cakupan.
                    </p>
                    <button
                        type="button"
                        class="mt-5 rounded-xl bg-brand-primary px-5 py-2.5 text-sm font-bold text-white"
                        @click="clearFilters"
                    >
                        Reset filter
                    </button>
                </div>

                <nav
                    v-if="materials.total > 0"
                    class="flex flex-wrap justify-start gap-2"
                    aria-label="Navigasi halaman materi belajar"
                >
                    <Link
                        v-for="link in materials.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="inline-flex h-12 w-12 items-center justify-center rounded-lg border p-0 text-sm font-semibold leading-normal"
                        :class="[
                            link.active
                                ? 'border-brand-primary bg-brand-primary text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                    >
                        <span>{{ paginationLabel(link.label) }}</span>
                    </Link>
                </nav>
            </section>
        </main>

        <!-- Delete Note Confirmation Modal -->
        <Modal
            :show="showDeleteModal"
            title="Hapus Catatan Pribadi"
            @close="showDeleteModal = false"
        >
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Hapus Catatan Materi?
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Catatan pribadi kamu pada materi ini akan dihapus
                            permanen.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        @click="showDeleteModal = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-4 py-2 text-xs font-black text-white hover:bg-red-700"
                        @click="confirmDeleteNote"
                    >
                        Hapus Catatan
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
