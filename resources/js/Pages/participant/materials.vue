<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

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
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <section
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

            <section class="space-y-4">
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
                        class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-brand-primary"
                                >
                                    {{
                                        material.visibility === 'public'
                                            ? 'Publik'
                                            : 'Organisasi'
                                    }}
                                </p>
                                <h2
                                    class="mt-2 line-clamp-2 text-lg font-black text-slate-900"
                                >
                                    {{ material.original_name }}
                                </h2>
                            </div>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                :class="
                                    material.read_count > 0
                                        ? 'bg-brand-secondary text-brand-primary'
                                        : 'bg-brand-accent text-brand-primary'
                                "
                            >
                                {{
                                    material.read_count > 0
                                        ? 'Selesai'
                                        : 'Materi'
                                }}
                            </span>
                        </div>
                        <p
                            class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600"
                        >
                            {{ excerpt(material) }}
                        </p>
                        <div
                            class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-500"
                        >
                            <span>{{ formatSize(material.size) }}</span>
                            <span>Versi {{ material.version }}</span>
                            <span
                                >Diperbarui
                                {{ formatDate(material.updated_at) }}</span
                            >
                            <span v-if="material.page_count"
                                >{{ material.page_count }} halaman</span
                            >
                            <span v-if="material.creator"
                                >Oleh {{ material.creator.name }}</span
                            >
                        </div>
                        <p
                            v-if="actionFeedback[material.id]"
                            class="mt-4 rounded-xl bg-brand-accent px-3 py-2 text-sm font-semibold text-brand-primary"
                            role="status"
                        >
                            {{ actionFeedback[material.id] }}
                        </p>
                        <button
                            type="button"
                            class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
                            :aria-expanded="
                                Boolean(expandedMaterials[material.id])
                            "
                            :aria-controls="`material-tools-${material.id}`"
                            @click="toggleLearningTools(material)"
                        >
                            {{
                                expandedMaterials[material.id]
                                    ? 'Tutup alat belajar'
                                    : 'Buka alat belajar'
                            }}
                        </button>
                        <div
                            v-if="expandedMaterials[material.id]"
                            :id="`material-tools-${material.id}`"
                            class="mt-4 space-y-4"
                        >
                            <div class="rounded-xl bg-slate-50 p-3">
                                <label
                                    :for="`note-${material.id}`"
                                    class="text-xs font-bold uppercase tracking-[0.14em] text-slate-600"
                                >
                                    Catatan pribadi
                                </label>
                                <textarea
                                    :id="`note-${material.id}`"
                                    v-model="noteDrafts[material.id]"
                                    rows="3"
                                    maxlength="5000"
                                    class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="Tulis ringkasan atau hal penting…"
                                />
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="min-h-9 rounded-lg bg-white px-3 text-xs font-bold text-brand-primary ring-1 ring-slate-200 hover:bg-brand-accent disabled:opacity-50"
                                        :disabled="
                                            actionProcessing[material.id]
                                        "
                                        @click="saveNote(material)"
                                    >
                                        {{
                                            actionProcessing[material.id]
                                                ? 'Menyimpan…'
                                                : 'Simpan catatan'
                                        }}
                                    </button>
                                    <button
                                        v-if="material.notes.length"
                                        type="button"
                                        class="min-h-9 rounded-lg px-3 text-xs font-bold text-red-700 hover:bg-red-50 disabled:opacity-50"
                                        :disabled="
                                            actionProcessing[material.id]
                                        "
                                        @click="promptDeleteNote(material)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3">
                                <label
                                    :for="`check-${material.id}`"
                                    class="text-xs font-bold uppercase tracking-[0.14em] text-brand-primary"
                                >
                                    Cek pemahaman
                                </label>
                                <p class="mt-1 text-xs text-slate-500">
                                    Tulis satu hal utama yang kamu pahami dari
                                    materi ini.
                                </p>
                                <textarea
                                    :id="`check-${material.id}`"
                                    v-model="checkDrafts[material.id].answer"
                                    rows="2"
                                    maxlength="1000"
                                    class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="Contoh: inti materi ini adalah…"
                                />
                                <div
                                    class="mt-2 flex flex-wrap items-center gap-2"
                                >
                                    <label
                                        :for="`confidence-${material.id}`"
                                        class="text-xs font-semibold text-slate-600"
                                        >Yakin</label
                                    >
                                    <select
                                        :id="`confidence-${material.id}`"
                                        v-model.number="
                                            checkDrafts[material.id].confidence
                                        "
                                        class="min-h-9 rounded-lg border-slate-300 text-xs focus:border-brand-primary focus:ring-brand-primary"
                                    >
                                        <option
                                            v-for="level in [1, 2, 3, 4, 5]"
                                            :key="level"
                                            :value="level"
                                        >
                                            {{ level }}/5
                                        </option>
                                    </select>
                                    <button
                                        type="button"
                                        class="min-h-9 rounded-lg bg-brand-primary px-3 text-xs font-bold text-white hover:bg-brand-hover disabled:opacity-50"
                                        :disabled="
                                            actionProcessing[material.id]
                                        "
                                        @click="saveCheck(material)"
                                    >
                                        {{
                                            actionProcessing[material.id]
                                                ? 'Menyimpan…'
                                                : 'Simpan cek'
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-2 sm:grid-cols-3">
                            <button
                                type="button"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold"
                                :class="
                                    material.read_count > 0
                                        ? 'bg-slate-100 text-slate-500'
                                        : 'bg-brand-secondary text-brand-primary hover:bg-brand-secondary/80'
                                "
                                :disabled="
                                    material.read_count > 0 ||
                                    actionProcessing[material.id]
                                "
                                @click="markRead(material)"
                            >
                                {{
                                    material.read_count > 0
                                        ? 'Sudah selesai'
                                        : 'Tandai selesai'
                                }}
                            </button>
                            <a
                                :href="route('materials.download', material.id)"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-hover"
                            >
                                Unduh
                            </a>
                            <Link
                                :href="
                                    route(
                                        'participant.materials.print',
                                        material.id,
                                    )
                                "
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Cetak
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
                    v-if="materials.links.length > 3"
                    class="flex flex-wrap gap-2"
                    aria-label="Navigasi halaman materi belajar"
                >
                    <Link
                        v-for="link in materials.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded-lg border px-3 py-2 text-sm font-semibold"
                        :class="[
                            link.active
                                ? 'border-brand-primary bg-brand-primary text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                </nav>
            </section>
        </main>
    
    <!-- Delete Note Confirmation Modal -->
    <Modal :show="showDeleteModal" title="Hapus Catatan Pribadi" @close="showDeleteModal = false">
        <div class="p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Hapus Catatan Materi?</h3>
                    <p class="mt-1 text-xs text-slate-500">Catatan pribadi kamu pada materi ini akan dihapus permanen.</p>
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
