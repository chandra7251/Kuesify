<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

defineProps<{
    materials: Material[];
    generations: Generation[];
    quota?: Quota;
}>();
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
    router.post(
        route('ai-generations.store', generateForm.material_id),
        {
            question_count: generateForm.question_count,
            difficulty: generateForm.difficulty,
            types: generateForm.types,
        },
        { preserveScroll: true },
    );
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
        <template #header
            ><div>
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700"
                >
                    Creator workspace
                </p>
                <h1 class="mt-1 text-2xl font-extrabold">
                    Materi & AI Generator
                </h1>
            </div></template
        >
        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 lg:px-8">
            <section
                class="rounded-3xl bg-teal-800 p-6 text-white sm:grid sm:grid-cols-2 sm:gap-8"
            >
                <div>
                    <h2 class="font-extrabold">Upload materi</h2>
                    <p class="mt-1 text-sm text-teal-100">
                        PDF/PPT/PPTX maks. 25 MB. Teks akan diekstrak, lalu bisa
                        dibuat soal AI.
                    </p>
                    <form class="mt-5 space-y-3" @submit.prevent="upload">
                        <input
                            type="file"
                            accept=".pdf,.ppt,.pptx"
                            class="block w-full rounded-xl bg-white/10 px-3 py-2 text-sm text-white file:mr-3 file:rounded-lg file:border-0 file:bg-amber-400 file:px-3 file:py-2 file:text-xs file:font-extrabold file:text-amber-950"
                            @change="
                                (e: Event) => {
                                    uploadForm.file =
                                        (e.target as HTMLInputElement)
                                            .files?.[0] ?? null;
                                }
                            "
                        />
                        <p
                            v-if="uploadForm.errors.file"
                            class="text-sm text-red-200"
                        >
                            {{ uploadForm.errors.file }}
                        </p>
                        <button
                            class="min-h-11 rounded-xl bg-amber-400 px-5 font-extrabold text-amber-950"
                            :disabled="uploadForm.processing"
                        >
                            Upload
                        </button>
                    </form>
                </div>
                <div class="mt-6 sm:mt-0">
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="font-extrabold">Generate soal AI</h2>
                        <span
                            v-if="quota"
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                            :class="
                                quota.weekly_remaining > 0
                                    ? 'bg-white/20 text-teal-100'
                                    : 'bg-red-500/20 text-red-200 ring-1 ring-red-400/30'
                            "
                        >
                            Sisa kuota: {{ quota.weekly_remaining }}/{{
                                quota.weekly_limit
                            }}
                            minggu ini
                        </span>
                    </div>
                    <div class="mt-5 space-y-3">
                        <div
                            v-if="quota && quota.weekly_remaining <= 0"
                            class="rounded-xl border border-amber-300/30 bg-amber-400/15 p-3 text-xs leading-relaxed text-amber-100"
                        >
                            <span class="font-extrabold text-amber-300"
                                >Batas kuota tercapai:</span
                            >
                            Anda telah menggunakan seluruh kuota mingguan ({{
                                quota.weekly_limit
                            }}
                            kali). Kuota akan di-reset otomatis setiap hari
                            Senin.
                        </div>
                        <select
                            v-model="generateForm.material_id"
                            class="min-h-11 w-full rounded-xl border-0 text-slate-900"
                        >
                            <option value="">Pilih materi terekstrak</option>
                            <option
                                v-for="material in materials.filter(
                                    (m) => m.status === 'extracted',
                                )"
                                :key="material.id"
                                :value="material.id"
                            >
                                {{ material.original_name }}
                            </option>
                        </select>
                        <div class="flex gap-3">
                            <select
                                v-model.number="generateForm.question_count"
                                class="min-h-11 flex-1 rounded-xl border-0 text-slate-900"
                            >
                                <option :value="5">5 soal</option>
                                <option :value="10">10 soal</option>
                                <option :value="20">20 soal</option>
                            </select>
                            <select
                                v-model="generateForm.difficulty"
                                class="min-h-11 flex-1 rounded-xl border-0 text-slate-900"
                            >
                                <option value="easy">Mudah</option>
                                <option value="medium">Sedang</option>
                                <option value="hard">Sulit</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="type in [
                                    'multiple_choice',
                                    'true_false',
                                    'fill_blank',
                                    'essay',
                                ]"
                                :key="type"
                                class="flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-2 text-xs font-bold"
                            >
                                <input
                                    v-model="generateForm.types"
                                    :value="type"
                                    type="checkbox"
                                    class="rounded border-0 text-teal-700"
                                />{{ type.replace('_', ' ') }}
                            </label>
                        </div>
                        <button
                            class="min-h-11 rounded-xl bg-amber-400 px-5 font-extrabold text-amber-950 disabled:opacity-50"
                            :disabled="
                                !generateForm.material_id ||
                                generateForm.types.length === 0 ||
                                (quota ? quota.weekly_remaining <= 0 : false)
                            "
                            @click="generate"
                        >
                            Generate soal
                        </button>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="font-extrabold">Materi saya</h2>
                <p
                    v-if="materials.length === 0"
                    class="mt-4 text-sm text-slate-500"
                >
                    Belum ada materi. Upload dulu.
                </p>
                <table v-else class="mt-4 w-full text-sm">
                    <thead>
                        <tr
                            class="border-b text-left text-xs font-bold uppercase text-slate-400"
                        >
                            <th class="py-2">Nama</th>
                            <th>Ukuran</th>
                            <th>Status</th>
                            <th>Diupload oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="material in materials"
                            :key="material.id"
                            class="border-b border-slate-50 last:border-0"
                        >
                            <td class="py-3 font-bold">
                                {{ material.original_name }}
                            </td>
                            <td class="text-slate-500">
                                {{ sizeKb(material.size) }}
                            </td>
                            <td>
                                <span
                                    :class="
                                        material.status === 'extracted'
                                            ? 'bg-teal-100 text-teal-800'
                                            : 'bg-slate-100 text-slate-600'
                                    "
                                    class="rounded-full px-2 py-0.5 text-xs font-bold"
                                    >{{ material.status }}</span
                                >
                            </td>
                            <td class="text-slate-500">
                                {{ material.creator.name }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section
                v-for="gen in generations"
                :key="gen.id"
                class="rounded-2xl bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-extrabold">
                            {{ gen.material.original_name }} ·
                            {{ gen.question_count }} soal · {{ gen.difficulty }}
                        </h2>
                        <p class="text-sm text-slate-500">{{ gen.status }}</p>
                        <p
                            v-if="gen.status === 'failed' && gen.failure_reason"
                            class="mt-1 text-sm font-medium text-red-700"
                        >
                            {{ gen.failure_reason }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            v-if="gen.status === 'failed'"
                            class="min-h-10 rounded-xl bg-teal-700 px-4 text-sm font-extrabold text-white"
                            @click="retry(gen.id)"
                        >
                            Coba lagi
                        </button>
                        <button
                            class="text-sm font-bold text-teal-700"
                            @click="
                                expandedGen =
                                    expandedGen === gen.id ? null : gen.id
                            "
                        >
                            {{
                                expandedGen === gen.id ? 'Tutup' : 'Lihat draft'
                            }}
                        </button>
                    </div>
                </div>
                <div v-if="expandedGen === gen.id" class="mt-5 space-y-4">
                    <p
                        v-if="gen.drafts.length === 0"
                        class="text-sm text-slate-500"
                    >
                        Belum ada draft. Tunggu proses queue.
                    </p>
                    <article
                        v-for="draft in gen.drafts"
                        :key="draft.id"
                        class="rounded-xl border border-slate-100 p-4"
                    >
                        <div
                            v-if="
                                editDraft.editing && editDraft.id === draft.id
                            "
                            class="space-y-3"
                        >
                            <textarea
                                v-model="editDraft.prompt"
                                class="w-full rounded-xl border-slate-200 text-sm"
                                rows="3"
                            />
                            <input
                                v-model="editDraft.correct_answer"
                                class="w-full rounded-xl border-slate-200 text-sm"
                                placeholder="Jawaban benar"
                            />
                            <input
                                v-model.number="editDraft.points"
                                type="number"
                                class="w-32 rounded-xl border-slate-200 text-sm"
                            />
                            <div class="flex gap-2">
                                <button
                                    class="min-h-10 rounded-xl bg-teal-700 px-4 text-sm font-extrabold text-white"
                                    @click="saveEdit(draft.id)"
                                >
                                    Simpan</button
                                ><button
                                    class="min-h-10 rounded-xl border px-4 text-sm"
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
                                              ? 'text-teal-700'
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
                                    class="min-h-10 rounded-xl bg-teal-700 px-4 text-sm font-extrabold text-white"
                                    @click="approve(draft.id)"
                                >
                                    Approve
                                </button>
                                <button
                                    class="min-h-10 rounded-xl border border-slate-200 px-4 text-sm font-extrabold"
                                    @click="startEdit(draft)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="min-h-10 rounded-xl border border-red-200 px-4 text-sm font-extrabold text-red-700"
                                    @click="reject(draft.id)"
                                >
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
