<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Draft = {
    id: number;
    type: string;
    prompt: string;
    options: string[] | null;
    correct_answer: string | null;
    points: number;
    status: string;
};
type Generation = {
    id: number;
    status: string;
    difficulty: string;
    question_count: number;
    failure_reason: string | null;
    material: { original_name: string };
    drafts: Draft[];
};
type Material = {
    id: number;
    original_name: string;
    status: string;
    size: number;
    creator: { name: string };
    created_at?: string;
};
type Quota = {
    weekly_limit: number;
    weekly_used: number;
    weekly_remaining: number;
};

const props = defineProps<{
    materials: Material[];
    generations: Generation[];
    quota?: Quota;
}>();
const questionTypes = [
    { value: 'multiple_choice', label: 'Pilihan ganda' },
    { value: 'true_false', label: 'Benar / salah' },
    { value: 'fill_blank', label: 'Isian singkat' },
    { value: 'essay', label: 'Esai' },
];
const extractedMaterials = computed(() =>
    props.materials.filter((material) => material.status === 'extracted'),
);
const uploadForm = useForm({ file: null as File | null });
const generateForm = useForm({
    material_id: '',
    question_count: 10,
    difficulty: 'medium',
    types: ['multiple_choice', 'true_false'],
});
const expandedGen = ref<number | null>(null);
const editDraft = ref<Partial<Draft> & { editing: boolean }>({
    editing: false,
});

function upload(): void {
    uploadForm.post(route('materials.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => uploadForm.reset(),
    });
}
function generate(): void {
    generateForm.post(route('ai-generations.store', generateForm.material_id), {
        preserveScroll: true,
    });
}
function retry(generationId: number): void {
    router.post(
        route('ai-generations.retry', generationId),
        {},
        { preserveScroll: true },
    );
}
function approve(draftId: number): void {
    router.post(
        route('ai-drafts.approve', draftId),
        {},
        { preserveScroll: true },
    );
}
function reject(draftId: number): void {
    router.post(
        route('ai-drafts.reject', draftId),
        {},
        { preserveScroll: true },
    );
}
function saveEdit(draftId: number): void {
    router.patch(route('ai-drafts.update', draftId), editDraft.value, {
        preserveScroll: true,
        onSuccess: () => {
            editDraft.value = { editing: false };
        },
    });
}
function startEdit(draft: Draft): void {
    editDraft.value = { ...draft, editing: true };
}
function sizeKb(bytes: number): string {
    return (bytes / 1024).toFixed(0) + ' KB';
}
</script>

<template>
    <Head title="Materi AI" />
    <AuthenticatedLayout>
        <main class="min-h-full bg-brand-accent/30 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <section
                    class="relative overflow-hidden rounded-3xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
                >
                    <div
                        class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full bg-brand-secondary/20"
                    />
                    <div
                        class="relative flex flex-wrap items-end justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-secondary"
                            >
                                Ruang belajar berbasis AI
                            </p>
                            <h1
                                class="mt-2 text-3xl font-black tracking-tight sm:text-4xl"
                            >
                                Materi AI
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-white/75"
                            >
                                Ubah dokumen pembelajaran menjadi draft soal
                                yang siap ditinjau dan disusun ulang.
                            </p>
                        </div>
                        <div
                            v-if="quota"
                            class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-right"
                        >
                            <p class="text-xs font-bold text-white/70">
                                Kuota mingguan
                            </p>
                            <p
                                class="mt-1 text-xl font-black text-brand-secondary"
                            >
                                {{ quota.weekly_remaining }}
                                <span class="text-sm text-white/70"
                                    >/ {{ quota.weekly_limit }}</span
                                >
                            </p>
                        </div>
                    </div>
                </section>

                <section class="grid gap-5 lg:grid-cols-2">
                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-4 sm:px-6"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.16em] text-[#527A12]"
                            >
                                Langkah 1
                            </p>
                            <h1
                                class="mt-1 text-xl font-extrabold text-slate-950"
                            >
                                Upload materi
                            </h1>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Unggah PDF, PPT, atau PPTX maksimal 25 MB untuk
                                diekstrak menjadi sumber soal.
                            </p>
                        </div>
                        <form
                            class="space-y-4 p-5 sm:p-6"
                            @submit.prevent="upload"
                        >
                            <label
                                for="material-file"
                                class="block text-sm font-bold text-slate-800"
                            >
                                File materi
                            </label>
                            <input
                                id="material-file"
                                type="file"
                                accept=".pdf,.ppt,.pptx"
                                class="block min-h-11 w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:min-h-11 file:border-0 file:border-r file:border-slate-200 file:bg-brand-secondary/15 file:px-4 file:text-sm file:font-bold file:text-[#527A12] hover:file:bg-brand-secondary/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                                @change="
                                    (event: Event) => {
                                        uploadForm.file =
                                            (event.target as HTMLInputElement)
                                                .files?.[0] ?? null;
                                    }
                                "
                            />
                            <p
                                v-if="uploadForm.errors.file"
                                class="text-sm font-medium text-red-700"
                            >
                                {{ uploadForm.errors.file }}
                            </p>
                            <button
                                type="submit"
                                class="min-h-11 rounded-lg bg-brand-primary px-5 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    !uploadForm.file || uploadForm.processing
                                "
                            >
                                {{
                                    uploadForm.processing
                                        ? 'Mengunggah...'
                                        : 'Upload materi'
                                }}
                            </button>
                        </form>
                    </article>

                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                    >
                        <div
                            class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.16em] text-[#527A12]"
                                >
                                    Langkah 2
                                </p>
                                <h2
                                    class="mt-1 text-xl font-extrabold text-slate-950"
                                >
                                    Generate soal AI
                                </h2>
                                <p
                                    class="mt-1 text-sm leading-6 text-slate-600"
                                >
                                    Atur jumlah, tingkat kesulitan, dan tipe
                                    soal.
                                </p>
                            </div>
                            <span
                                v-if="quota"
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                :class="
                                    quota.weekly_remaining > 0
                                        ? 'bg-brand-secondary/15 text-[#527A12]'
                                        : 'bg-red-50 text-red-700'
                                "
                            >
                                {{ quota.weekly_remaining }}/{{
                                    quota.weekly_limit
                                }}
                                tersisa
                            </span>
                        </div>

                        <form
                            class="space-y-4 p-5 sm:p-6"
                            @submit.prevent="generate"
                        >
                            <div
                                v-if="quota && quota.weekly_remaining <= 0"
                                class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900"
                            >
                                <strong>Batas kuota tercapai.</strong> Kuota
                                mingguan akan direset setiap hari Senin.
                            </div>

                            <label
                                class="block text-sm font-bold text-slate-800"
                            >
                                Materi sumber
                                <select
                                    v-model="generateForm.material_id"
                                    class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm text-slate-900 focus:border-brand-secondary focus:ring-brand-secondary"
                                >
                                    <option value="">
                                        Pilih materi terekstrak
                                    </option>
                                    <option
                                        v-for="material in extractedMaterials"
                                        :key="material.id"
                                        :value="material.id"
                                    >
                                        {{ material.original_name }}
                                    </option>
                                </select>
                            </label>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="text-sm font-bold text-slate-800">
                                    Jumlah soal
                                    <select
                                        v-model.number="
                                            generateForm.question_count
                                        "
                                        class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm text-slate-900 focus:border-brand-secondary focus:ring-brand-secondary"
                                    >
                                        <option :value="5">5 soal</option>
                                        <option :value="10">10 soal</option>
                                        <option :value="20">20 soal</option>
                                    </select>
                                </label>
                                <label class="text-sm font-bold text-slate-800">
                                    Kesulitan
                                    <select
                                        v-model="generateForm.difficulty"
                                        class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm text-slate-900 focus:border-brand-secondary focus:ring-brand-secondary"
                                    >
                                        <option value="easy">Mudah</option>
                                        <option value="medium">Sedang</option>
                                        <option value="hard">Sulit</option>
                                    </select>
                                </label>
                            </div>

                            <fieldset>
                                <legend
                                    class="text-sm font-bold text-slate-800"
                                >
                                    Tipe soal
                                </legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <label
                                        v-for="type in questionTypes"
                                        :key="type.value"
                                        class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-600 transition hover:border-brand-secondary hover:bg-brand-secondary/10 has-[:checked]:border-brand-secondary has-[:checked]:bg-brand-secondary/15 has-[:checked]:text-[#3F5F0D]"
                                    >
                                        <input
                                            v-model="generateForm.types"
                                            :value="type.value"
                                            type="checkbox"
                                            class="rounded border-slate-300 text-brand-secondary focus:ring-brand-secondary"
                                        />
                                        {{ type.label }}
                                    </label>
                                </div>
                            </fieldset>

                            <button
                                type="submit"
                                class="min-h-11 rounded-lg bg-brand-primary px-5 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    generateForm.processing ||
                                    !generateForm.material_id ||
                                    generateForm.types.length === 0 ||
                                    (quota
                                        ? quota.weekly_remaining <= 0
                                        : false)
                                "
                            >
                                {{
                                    generateForm.processing
                                        ? 'Membuat soal...'
                                        : 'Generate soal'
                                }}
                            </button>
                        </form>
                    </article>
                </section>

                <section
                    aria-labelledby="materials-title"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6"
                    >
                        <div>
                            <h2
                                id="materials-title"
                                class="font-extrabold text-slate-950"
                            >
                                Materi saya
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Dokumen yang tersedia untuk pembuatan soal AI.
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-brand-primary px-2.5 py-1 text-xs font-bold tabular-nums text-white"
                        >
                            {{ materials.length }}
                        </span>
                    </div>

                    <p
                        v-if="materials.length === 0"
                        class="flex min-h-48 flex-col items-center justify-center px-5 py-10 text-center text-sm text-slate-500"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-primary text-brand-secondary"
                        >
                            <AppIcon
                                name="material"
                                class="h-6 w-6"
                                :stroke-width="2"
                            />
                        </span>
                        <span class="mt-4 font-bold text-slate-900"
                            >Belum ada materi</span
                        >
                        <span class="mt-1"
                            >Unggah dokumen pertama untuk memulai.</span
                        >
                    </p>
                    <div v-else class="grid gap-3 p-4 sm:p-5">
                        <article
                            v-for="material in materials"
                            :key="material.id"
                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-brand-accent/25 p-4 transition hover:-translate-y-0.5 hover:border-brand-primary/30 hover:bg-white hover:shadow-sm"
                        >
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-primary text-brand-secondary"
                            >
                                <AppIcon
                                    name="material"
                                    class="h-5 w-5"
                                    :stroke-width="2"
                                />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-black text-slate-900">
                                    {{ material.original_name }}
                                </p>
                                <div
                                    class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500"
                                >
                                    <span>{{ sizeKb(material.size) }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span
                                        >Oleh {{ material.creator.name }}</span
                                    >
                                </div>
                            </div>
                            <span
                                :class="
                                    material.status === 'extracted'
                                        ? 'bg-brand-secondary/20 text-brand-dark'
                                        : 'bg-slate-100 text-slate-600'
                                "
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                            >
                                {{ material.status }}
                            </span>
                        </article>
                    </div>
                </section>

                <section
                    v-for="gen in generations"
                    :key="gen.id"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0">
                            <h2 class="font-extrabold text-slate-950">
                                {{ gen.material.original_name }} ·
                                {{ gen.question_count }} soal ·
                                {{ gen.difficulty }}
                            </h2>
                            <p
                                class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"
                            >
                                {{ gen.status }}
                            </p>
                            <p
                                v-if="
                                    gen.status === 'failed' &&
                                    gen.failure_reason
                                "
                                class="mt-1 text-sm font-medium text-red-700"
                            >
                                {{ gen.failure_reason }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <button
                                v-if="gen.status === 'failed'"
                                type="button"
                                class="min-h-10 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
                                @click="retry(gen.id)"
                            >
                                Coba lagi
                            </button>
                            <button
                                type="button"
                                class="min-h-10 rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                                :aria-expanded="expandedGen === gen.id"
                                @click="
                                    expandedGen =
                                        expandedGen === gen.id ? null : gen.id
                                "
                            >
                                {{
                                    expandedGen === gen.id
                                        ? 'Tutup'
                                        : 'Lihat draft'
                                }}
                            </button>
                        </div>
                    </div>
                    <div
                        v-if="expandedGen === gen.id"
                        class="mt-5 space-y-4 border-t border-slate-200 pt-5"
                    >
                        <p
                            v-if="gen.drafts.length === 0"
                            class="text-sm text-slate-500"
                        >
                            Belum ada draft. Tunggu proses queue.
                        </p>
                        <article
                            v-for="draft in gen.drafts"
                            :key="draft.id"
                            class="rounded-lg border border-slate-200 p-4"
                        >
                            <div
                                v-if="
                                    editDraft.editing &&
                                    editDraft.id === draft.id
                                "
                                class="space-y-3"
                            >
                                <textarea
                                    v-model="editDraft.prompt"
                                    class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-secondary focus:ring-brand-secondary"
                                    rows="3"
                                />
                                <input
                                    v-model="editDraft.correct_answer"
                                    class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-secondary focus:ring-brand-secondary"
                                    placeholder="Jawaban benar"
                                />
                                <input
                                    v-model.number="editDraft.points"
                                    type="number"
                                    class="w-32 rounded-lg border-slate-300 text-sm focus:border-brand-secondary focus:ring-brand-secondary"
                                />
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="min-h-10 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-hover"
                                        @click="saveEdit(draft.id)"
                                    >
                                        Simpan</button
                                    ><button
                                        type="button"
                                        class="min-h-10 rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                        @click="editDraft.editing = false"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                            <div v-else>
                                <p class="font-bold">{{ draft.prompt }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ draft.type }} · {{ draft.points }} poin ·
                                    <span
                                        :class="
                                            draft.status === 'pending'
                                                ? 'text-amber-600'
                                                : draft.status === 'approved'
                                                  ? 'text-[#527A12]'
                                                  : 'text-red-600'
                                        "
                                        class="font-bold"
                                        >{{ draft.status }}</span
                                    >
                                </p>
                                <div
                                    v-if="draft.status === 'pending'"
                                    class="mt-3 flex gap-2"
                                >
                                    <button
                                        type="button"
                                        class="min-h-10 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-hover"
                                        @click="approve(draft.id)"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        type="button"
                                        class="min-h-10 rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                        @click="startEdit(draft)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="min-h-10 rounded-lg border border-red-200 px-4 text-sm font-bold text-red-700 hover:bg-red-50"
                                        @click="reject(draft.id)"
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
