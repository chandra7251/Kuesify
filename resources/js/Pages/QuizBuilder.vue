<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Question = {
    id: number;
    prompt: string;
    type: string;
    points: number;
    category?: { name: string } | null;
};
type Quiz = {
    id: number;
    title: string;
    description?: string | null;
    status: string;
    visibility: string;
    category_id?: number | null;
    max_attempts?: number | null;
    deadline_at?: string | null;
    show_explanations: boolean;
    questions: Question[];
    questions_count: number;
};

const props = defineProps<{
    quizzes: Quiz[];
    questions: Question[];
    categories: { id: number; name: string }[];
}>();
const createForm = useForm({ title: '' });
const selectedQuizId = ref<number | null>(props.quizzes[0]?.id ?? null);
const selectedQuestionIds = ref<number[]>([]);
const metadata = useForm({
    title: '',
    description: '',
    visibility: 'organization',
    category_id: '',
    max_attempts: '',
    deadline_at: '',
    show_explanations: false,
});

const selectedQuiz = computed(
    () =>
        props.quizzes.find((quiz) => quiz.id === selectedQuizId.value) ?? null,
);
const selectedQuestions = computed(() =>
    selectedQuestionIds.value
        .map((id) => props.questions.find((question) => question.id === id))
        .filter((question): question is Question => Boolean(question)),
);
const fieldClass =
    'mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm shadow-none focus:border-teal-600 focus:ring-teal-600';

function selectQuiz(quiz: Quiz): void {
    selectedQuizId.value = quiz.id;
    selectedQuestionIds.value = quiz.questions.map((question) => question.id);
    metadata.title = quiz.title;
    metadata.description = quiz.description ?? '';
    metadata.visibility = quiz.visibility;
    metadata.category_id = quiz.category_id ? String(quiz.category_id) : '';
    metadata.max_attempts = quiz.max_attempts ? String(quiz.max_attempts) : '';
    metadata.deadline_at = quiz.deadline_at?.slice(0, 16) ?? '';
    metadata.show_explanations = quiz.show_explanations;
}

if (selectedQuiz.value) selectQuiz(selectedQuiz.value);

function createQuiz(): void {
    createForm.post(route('quizzes.store'), {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}

function saveMetadata(): void {
    if (!selectedQuiz.value) return;
    metadata.patch(route('quizzes.update', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}

function syncQuestions(): void {
    if (!selectedQuiz.value || selectedQuestionIds.value.length === 0) return;
    useForm({ question_ids: selectedQuestionIds.value }).put(
        route('quizzes.questions.sync', selectedQuiz.value.id),
        { preserveScroll: true },
    );
}

function moveQuestion(index: number, amount: number): void {
    const nextIndex = index + amount;
    if (nextIndex < 0 || nextIndex >= selectedQuestionIds.value.length) return;
    const next = [...selectedQuestionIds.value];
    [next[index], next[nextIndex]] = [next[nextIndex], next[index]];
    selectedQuestionIds.value = next;
}

function publish(): void {
    if (!selectedQuiz.value) return;
    useForm({}).post(route('quizzes.publish', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}

function cloneQuiz(): void {
    if (!selectedQuiz.value) return;
    useForm({}).post(route('quizzes.clone', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}

function archiveQuiz(): void {
    if (!selectedQuiz.value) return;
    useForm({}).post(route('quizzes.archive', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Quiz Builder" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-[90rem] px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-[17rem_minmax(0,1fr)]">
                <aside class="space-y-4 lg:self-start">
                    <form
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                        @submit.prevent="createQuiz"
                    >
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.16em] text-teal-700"
                            >
                                Draft baru
                            </p>
                            <h2 class="mt-1 font-extrabold text-slate-950">
                                Buat quiz
                            </h2>
                        </div>
                        <label for="new-quiz" class="sr-only"
                            >Judul quiz baru</label
                        >
                        <input
                            id="new-quiz"
                            v-model="createForm.title"
                            class="mt-4 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"
                            placeholder="Contoh: Kuis Ekosistem"
                            required
                        />
                        <p
                            v-if="createForm.errors.title"
                            class="mt-2 text-sm font-medium text-red-700"
                        >
                            {{ createForm.errors.title }}
                        </p>
                        <button
                            class="mt-3 min-h-11 w-full rounded-md bg-[#3451b5] px-4 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5] disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="createForm.processing"
                        >
                            Buat draft
                        </button>
                    </form>

                    <section
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 px-4 py-3"
                        >
                            <h2
                                class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-500"
                            >
                                Daftar quiz
                            </h2>
                            <span
                                class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold tabular-nums text-slate-600"
                                >{{ quizzes.length }}</span
                            >
                        </div>
                        <div class="space-y-1 p-2">
                            <button
                                v-for="quiz in quizzes"
                                :key="quiz.id"
                                type="button"
                                class="group w-full rounded-lg px-3 py-3 text-left transition-colors"
                                :class="
                                    quiz.id === selectedQuizId
                                        ? 'bg-teal-50 text-teal-950 ring-1 ring-inset ring-teal-200'
                                        : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950'
                                "
                                @click="selectQuiz(quiz)"
                            >
                                <span class="block truncate font-bold">{{
                                    quiz.title
                                }}</span>
                                <span
                                    class="mt-1 flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="
                                            quiz.status === 'published'
                                                ? 'bg-teal-500'
                                                : 'bg-amber-400'
                                        "
                                    ></span>
                                    {{ quiz.status }} ·
                                    {{ quiz.questions_count }} soal
                                </span>
                            </button>
                            <p
                                v-if="quizzes.length === 0"
                                class="px-3 py-8 text-center text-sm text-slate-500"
                            >
                                Belum ada quiz.
                            </p>
                        </div>
                    </section>
                </aside>

                <section v-if="selectedQuiz" class="min-w-0 space-y-5">
                    <header
                        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="text-xs font-extrabold uppercase tracking-[0.16em] text-teal-700"
                                >
                                    Quiz aktif
                                </p>
                                <span
                                    class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-800"
                                    >{{ selectedQuiz.status }}</span
                                >
                            </div>
                            <h2
                                class="mt-2 truncate text-2xl font-extrabold tracking-tight text-slate-950"
                            >
                                {{ selectedQuiz.title }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ selectedQuestionIds.length }} soal ·
                                {{ metadata.visibility }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600"
                                @click="cloneQuiz"
                            >
                                Duplikat
                            </button>
                            <button
                                type="button"
                                class="min-h-11 rounded-lg px-3 text-sm font-bold text-red-700 transition-colors hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                @click="archiveQuiz"
                            >
                                Arsipkan
                            </button>
                        </div>
                    </header>

                    <form
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                        @submit.prevent="saveMetadata"
                    >
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h2 class="font-extrabold text-slate-950">
                                Pengaturan quiz
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Atur informasi, akses, dan batas pengerjaan.
                            </p>
                        </div>
                        <div class="grid gap-4 p-5 md:grid-cols-2">
                            <label class="text-sm font-bold text-slate-800"
                                >Judul<input
                                    v-model="metadata.title"
                                    :class="fieldClass"
                                    placeholder="Masukkan judul quiz"
                                    required
                            /></label>
                            <label class="text-sm font-bold text-slate-800"
                                >Kategori<select
                                    v-model="metadata.category_id"
                                    :class="fieldClass"
                                >
                                    <option value="">Tanpa kategori</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="String(category.id)"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select></label
                            >
                            <label
                                class="text-sm font-bold text-slate-800 md:col-span-2"
                                >Deskripsi<textarea
                                    v-model="metadata.description"
                                    :class="fieldClass"
                                    class="min-h-24 resize-y"
                                    placeholder="Masukkan deskripsi quiz"
                                />
                            </label>
                            <label class="text-sm font-bold text-slate-800"
                                >Visibilitas<select
                                    v-model="metadata.visibility"
                                    :class="fieldClass"
                                >
                                    <option value="private">Private</option>
                                    <option value="organization">
                                        Organisasi
                                    </option>
                                    <option value="public">Public</option>
                                </select></label
                            >
                            <label class="text-sm font-bold text-slate-800"
                                >Maksimal percobaan<input
                                    v-model="metadata.max_attempts"
                                    :class="fieldClass"
                                    type="number"
                                    min="1"
                                    placeholder="Tanpa batas"
                            /></label>
                            <label class="text-sm font-bold text-slate-800"
                                >Deadline<input
                                    v-model="metadata.deadline_at"
                                    :class="fieldClass"
                                    type="datetime-local"
                            /></label>
                            <label
                                class="flex min-h-11 cursor-pointer items-center gap-3 self-end rounded-lg border border-slate-200 px-4 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50"
                                ><input
                                    v-model="metadata.show_explanations"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                />Tampilkan pembahasan</label
                            >
                        </div>
                        <div
                            class="flex justify-end border-t border-slate-100 bg-slate-50 px-5 py-4"
                        >
                            <button
                                class="min-h-11 rounded-md bg-[#3451b5] px-5 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5] disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="metadata.processing"
                            >
                                Simpan detail
                            </button>
                        </div>
                    </form>

                    <section
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4"
                        >
                            <div>
                                <h2 class="font-extrabold text-slate-950">
                                    Susunan soal
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Pilih soal, atur urutan, lalu publikasikan.
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800"
                                >{{ selectedQuestionIds.length }} dipilih</span
                            >
                        </div>

                        <div
                            class="grid xl:grid-cols-[minmax(0,1fr)_minmax(22rem,0.82fr)]"
                        >
                            <div class="p-5 xl:border-r xl:border-slate-100">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <h3 class="font-bold text-slate-900">
                                        Bank soal
                                    </h3>
                                    <span class="text-xs text-slate-500"
                                        >{{ questions.length }} tersedia</span
                                    >
                                </div>
                                <div
                                    v-if="questions.length"
                                    class="mt-3 max-h-[32rem] space-y-2 overflow-y-auto pr-1"
                                >
                                    <label
                                        v-for="question in questions"
                                        :key="question.id"
                                        class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3 transition-colors hover:border-teal-300 hover:bg-teal-50/40 has-[:checked]:border-teal-300 has-[:checked]:bg-teal-50/70"
                                    >
                                        <input
                                            v-model="selectedQuestionIds"
                                            :value="question.id"
                                            type="checkbox"
                                            class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                        />
                                        <span class="min-w-0">
                                            <span
                                                class="block font-bold leading-6 text-slate-900"
                                                >{{ question.prompt }}</span
                                            >
                                            <span
                                                class="mt-1 block text-xs text-slate-500"
                                                >{{ question.type }} ·
                                                {{ question.points }} poin<span
                                                    v-if="question.category"
                                                >
                                                    ·
                                                    {{
                                                        question.category.name
                                                    }}</span
                                                ></span
                                            >
                                        </span>
                                    </label>
                                </div>
                                <p
                                    v-else
                                    class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500"
                                >
                                    Bank soal masih kosong.
                                </p>
                                <button
                                    type="button"
                                    class="mt-4 min-h-11 w-full rounded-md bg-[#3451b5] px-4 text-sm font-bold text-white transition hover:bg-[#29439d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#3451b5] disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="selectedQuestionIds.length === 0"
                                    @click="syncQuestions"
                                >
                                    Simpan susunan soal
                                </button>
                            </div>

                            <div
                                class="border-t border-slate-100 bg-slate-50/70 p-5 xl:border-t-0"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <h3 class="font-bold text-slate-900">
                                        Urutan dan preview
                                    </h3>
                                    <span class="text-xs text-slate-500"
                                        >Atur dengan tombol</span
                                    >
                                </div>
                                <div
                                    v-if="selectedQuestions.length"
                                    class="mt-3 space-y-2"
                                >
                                    <article
                                        v-for="(
                                            question, index
                                        ) in selectedQuestions"
                                        :key="question.id"
                                        class="flex items-start gap-3 rounded-lg border border-slate-200 bg-white p-3"
                                    >
                                        <span
                                            class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-teal-50 text-xs font-extrabold tabular-nums text-teal-800"
                                            >{{ index + 1 }}</span
                                        >
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="font-bold leading-6 text-slate-900"
                                            >
                                                {{ question.prompt }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{ question.type }} ·
                                                {{ question.points }} poin
                                            </p>
                                        </div>
                                        <div class="flex shrink-0 gap-1">
                                            <button
                                                type="button"
                                                class="grid h-9 w-9 place-items-center rounded-md text-slate-600 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30"
                                                :disabled="index === 0"
                                                :aria-label="`Naikkan soal ${index + 1}`"
                                                @click="moveQuestion(index, -1)"
                                            >
                                                <svg
                                                    aria-hidden="true"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m6 15 6-6 6 6"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                class="grid h-9 w-9 place-items-center rounded-md text-slate-600 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30"
                                                :disabled="
                                                    index ===
                                                    selectedQuestions.length - 1
                                                "
                                                :aria-label="`Turunkan soal ${index + 1}`"
                                                @click="moveQuestion(index, 1)"
                                            >
                                                <svg
                                                    aria-hidden="true"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m6 9 6 6 6-6"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </article>
                                </div>
                                <div
                                    v-else
                                    class="mt-3 rounded-lg border border-dashed border-slate-300 bg-white px-5 py-10 text-center"
                                >
                                    <p class="font-bold text-slate-700">
                                        Belum ada soal dipilih
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Pilih soal dari bank di sebelah kiri.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="mt-4 min-h-11 w-full rounded-md bg-teal-700 px-4 text-sm font-bold text-white transition hover:bg-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="selectedQuestionIds.length === 0"
                                    @click="publish"
                                >
                                    Publish
                                </button>
                            </div>
                        </div>
                    </section>
                </section>

                <section
                    v-else
                    class="grid min-h-96 place-items-center rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm"
                >
                    <div class="max-w-sm">
                        <h2 class="text-xl font-extrabold text-slate-950">
                            Belum ada quiz
                        </h2>
                        <p class="mt-2 text-slate-500">
                            Buat draft di panel kiri untuk mulai menyusun soal.
                        </p>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
