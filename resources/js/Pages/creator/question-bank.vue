<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type Question = {
    id: number;
    type: string;
    prompt: string;
    hint?: string | null;
    points: number;
};
const props = defineProps<{ questions: Question[] }>();
const form = useForm({
    type: 'multiple_choice',
    prompt: '',
    options: ['', ''],
    correct_answer: '',
    hint: '',
    points: 1000,
    tags: [] as string[],
});
const tagQuery = ref('');
const tagSuggestions = ref<{ id: number; name: string }[]>([]);
let tagTimer: number | undefined;
watch(tagQuery, (value) => {
    if (tagTimer) window.clearTimeout(tagTimer);
    if (!value.trim()) {
        tagSuggestions.value = [];
        return;
    }
    tagTimer = window.setTimeout(async () => {
        const response = await fetch(
            `${route('questions.tags')}?q=${encodeURIComponent(value)}`,
            { headers: { Accept: 'application/json' } },
        );
        tagSuggestions.value = response.ok ? (await response.json()).data : [];
    }, 200);
});
function addTag(name: string): void {
    if (!form.tags.includes(name)) form.tags.push(name);
    tagQuery.value = '';
    tagSuggestions.value = [];
}
function removeTag(name: string): void {
    form.tags = form.tags.filter((tag) => tag !== name);
}
function save(): void {
    form.post(route('questions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.type = 'multiple_choice';
            form.options = ['', ''];
            form.points = 1000;
            form.tags = [];
        },
    });
}
function remove(id: number): void {
    router.delete(route('questions.destroy', id), { preserveScroll: true });
}
function questionTypeLabel(type: string): string {
    return (
        {
            multiple_choice: 'Pilihan ganda',
            true_false: 'Benar / salah',
            fill_blank: 'Isian',
            essay: 'Essay',
        }[type] ?? type
    );
}
</script>

<template>
    <Head title="Question Bank Creator" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <section
                class="overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
            >
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.2em] text-brand-secondary"
                        >
                            Creator workspace
                        </p>
                        <h1
                            class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                        >
                            Bangun bank soalmu
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-white/75"
                        >
                            Buat soal reusable, rapikan berdasarkan tag, dan
                            siapkan materi untuk kuis yang lebih hidup.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:w-64">
                        <div
                            class="rounded-xl border border-white/15 bg-white/10 p-3 backdrop-blur-sm"
                        >
                            <p class="text-xs font-bold text-white/70">
                                Total soal
                            </p>
                            <p
                                class="mt-1 text-2xl font-black text-brand-secondary"
                            >
                                {{ questions.length }}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-white/15 bg-white/10 p-3 backdrop-blur-sm"
                        >
                            <p class="text-xs font-bold text-white/70">
                                Status
                            </p>
                            <p class="mt-1 text-sm font-black text-white">
                                Siap dipakai
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div
                class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]"
            >
                <form
                    class="rounded-2xl border border-l-4 border-slate-200 border-l-brand-secondary bg-white p-6 shadow-figma-sm sm:p-7"
                    @submit.prevent="save"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="grid h-10 w-10 place-items-center rounded-xl bg-brand-secondary/15 text-lg text-brand-primary"
                            >＋</span
                        >
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                            >
                                Koleksi soal
                            </p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Buat soal baru
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Simpan satu soal untuk dipakai ulang di kuis.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <label class="block">
                            <span
                                class="text-xs font-black uppercase tracking-wide text-slate-600"
                                >Tipe soal</span
                            >
                            <select
                                v-model="form.type"
                                class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                @change="form.correct_answer = ''; form.options = ['', '']"
                            >
                                <option value="multiple_choice">
                                    Pilihan ganda
                                </option>
                                <option value="true_false">
                                    Benar / salah
                                </option>
                                <option value="fill_blank">Isian</option>
                                <option value="essay">Essay</option>
                            </select>
                        </label>
                        <label class="block">
                            <span
                                class="text-xs font-black uppercase tracking-wide text-slate-600"
                                >Pertanyaan</span
                            >
                            <textarea
                                v-model="form.prompt"
                                required
                                rows="4"
                                class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Tulis pertanyaan yang ingin disimpan..."
                            />
                        </label>

                        <!-- Opsi pilihan ganda -->
                        <div v-if="form.type === 'multiple_choice'" class="space-y-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-600">Pilihan jawaban</span>
                            <div
                                v-for="(opt, idx) in form.options"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <span class="w-6 shrink-0 text-center text-xs font-black text-slate-400">
                                    {{ ['A', 'B', 'C', 'D', 'E'][idx] }}
                                </span>
                                <input
                                    v-model="form.options[idx]"
                                    :placeholder="`Opsi ${['A', 'B', 'C', 'D', 'E'][idx]}`"
                                    class="min-h-10 flex-1 rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                />
                                <button
                                    v-if="form.options.length > 2"
                                    type="button"
                                    class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"
                                    @click="form.options.splice(idx, 1); if (form.correct_answer === opt) form.correct_answer = ''"
                                >✕</button>
                            </div>
                            <button
                                v-if="form.options.length < 5"
                                type="button"
                                class="w-full rounded-xl border border-dashed border-slate-300 py-2 text-xs font-bold text-slate-500 hover:border-brand-primary hover:text-brand-primary"
                                @click="form.options.push('')"
                            >
                                + Tambah opsi
                            </button>
                        </div>

                        <!-- Jawaban benar: pilihan ganda → dropdown opsi -->
                        <label v-if="form.type === 'multiple_choice'" class="block">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-600">Jawaban benar</span>
                            <select
                                v-model="form.correct_answer"
                                required
                                class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                            >
                                <option value="" disabled>— Pilih jawaban yang benar —</option>
                                <option
                                    v-for="(opt, idx) in form.options.filter(o => o.trim())"
                                    :key="idx"
                                    :value="opt"
                                >
                                    {{ ['A', 'B', 'C', 'D', 'E'][form.options.indexOf(opt)] }}. {{ opt }}
                                </option>
                            </select>
                        </label>

                        <!-- Jawaban benar: true/false → radio button -->
                        <div v-else-if="form.type === 'true_false'" class="space-y-2">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-600">Jawaban benar</span>
                            <div class="flex gap-3">
                                <label
                                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border py-3 text-sm font-bold transition"
                                    :class="form.correct_answer === 'True'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-900'
                                        : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                >
                                    <input v-model="form.correct_answer" type="radio" value="True" class="sr-only" />
                                    ✓ Benar (True)
                                </label>
                                <label
                                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border py-3 text-sm font-bold transition"
                                    :class="form.correct_answer === 'False'
                                        ? 'border-red-500 bg-red-50 text-red-900'
                                        : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                >
                                    <input v-model="form.correct_answer" type="radio" value="False" class="sr-only" />
                                    ✕ Salah (False)
                                </label>
                            </div>
                        </div>

                        <!-- Jawaban benar: isian/essay → text input biasa -->
                        <label v-else-if="form.type === 'fill_blank'" class="block">
                            <span class="text-xs font-black uppercase tracking-wide text-slate-600">Jawaban benar</span>
                            <input
                                v-model="form.correct_answer"
                                required
                                class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Jawaban yang tepat untuk soal isian"
                            />
                        </label>

                        <!-- Essay: tidak perlu jawaban benar -->
                        <div v-else-if="form.type === 'essay'" class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3">
                            <p class="text-xs font-bold text-amber-800">ℹ️ Soal essay dinilai manual oleh creator — tidak perlu jawaban benar otomatis.</p>
                        </div>

                        <label class="block">
                            <span
                                class="text-xs font-black uppercase tracking-wide text-slate-600"
                                >Hint
                                <span class="font-normal text-slate-400"
                                    >(opsional)</span
                                ></span
                            >
                            <input
                                v-model="form.hint"
                                class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Beri petunjuk singkat"
                            />
                        </label>
                        <div class="relative">
                            <label
                                class="block text-xs font-black uppercase tracking-wide text-slate-600"
                                for="creator-tag"
                                >Tag organisasi</label
                            >
                            <input
                                id="creator-tag"
                                v-model="tagQuery"
                                class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Cari atau ketik tag"
                                @keydown.enter.prevent="
                                    tagQuery && addTag(tagQuery)
                                "
                            />
                            <div
                                v-if="tagSuggestions.length"
                                class="absolute z-10 mt-1 w-full rounded-xl border border-slate-200 bg-white p-1 shadow-lg"
                            >
                                <button
                                    v-for="tag in tagSuggestions"
                                    :key="tag.id"
                                    type="button"
                                    class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-brand-accent"
                                    @click="addTag(tag.name)"
                                >
                                    {{ tag.name }}
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="form.tags.length"
                            class="flex flex-wrap gap-2"
                        >
                            <button
                                v-for="tag in form.tags"
                                :key="tag"
                                type="button"
                                class="rounded-full bg-brand-secondary/20 px-3 py-1 text-xs font-black text-brand-dark"
                                @click="removeTag(tag)"
                            >
                                {{ tag }} ×
                            </button>
                        </div>
                        <button
                            type="submit"
                            class="min-h-12 w-full rounded-xl bg-brand-primary px-4 text-sm font-black text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing ? 'Menyimpan...' : 'Simpan soal'
                            }}
                        </button>
                    </div>
                </form>

                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-figma-sm sm:p-7"
                >
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                            >
                                Perpustakaan soal
                            </p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Soal tersimpan
                            </h2>
                        </div>
                        <span
                            class="rounded-full bg-brand-accent px-3 py-1 text-xs font-black text-brand-primary"
                            >{{ questions.length }} soal</span
                        >
                    </div>
                    <p
                        v-if="questions.length === 0"
                        class="mt-5 rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500"
                    >
                        Belum ada soal. Buat soal pertama dari panel di sebelah
                        kiri.
                    </p>
                    <div v-else class="mt-5 divide-y divide-slate-100">
                        <article
                            v-for="question in questions"
                            :key="question.id"
                            class="group flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-brand-secondary/15 px-2.5 py-1 text-[11px] font-black text-brand-dark"
                                        >{{
                                            questionTypeLabel(question.type)
                                        }}</span
                                    >
                                    <span
                                        class="text-xs font-semibold text-slate-500"
                                        >{{ question.points }} poin</span
                                    >
                                </div>
                                <p
                                    class="mt-2 font-black leading-6 text-slate-950"
                                >
                                    {{ question.prompt }}
                                </p>
                                <p
                                    v-if="question.hint"
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Hint tersedia
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg px-2 py-1 text-xs font-black text-red-600 opacity-70 transition hover:bg-red-50 hover:opacity-100"
                                @click="remove(question.id)"
                            >
                                Hapus
                            </button>
                        </article>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
