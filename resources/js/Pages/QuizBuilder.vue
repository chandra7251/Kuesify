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
        <template #header
            ><div>
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700"
                >
                    Creator workspace
                </p>
                <h1 class="mt-1 text-2xl font-extrabold">Quiz Builder</h1>
            </div></template
        >
        <main
            class="mx-auto grid max-w-7xl gap-5 px-4 py-6 lg:grid-cols-[18rem_1fr] lg:px-8"
        >
            <aside class="space-y-4">
                <form
                    class="rounded-2xl bg-teal-800 p-4 text-white"
                    @submit.prevent="createQuiz"
                >
                    <label for="new-quiz" class="text-sm font-extrabold"
                        >Quiz baru</label
                    >
                    <input
                        id="new-quiz"
                        v-model="createForm.title"
                        class="mt-3 min-h-11 w-full rounded-xl border-0 text-slate-900"
                        placeholder="Contoh: Kuis Ekosistem"
                        required
                    />
                    <p
                        v-if="createForm.errors.title"
                        class="mt-2 text-sm text-red-200"
                    >
                        {{ createForm.errors.title }}
                    </p>
                    <button
                        class="mt-3 min-h-11 w-full rounded-xl bg-white px-4 font-extrabold text-teal-900"
                        :disabled="createForm.processing"
                    >
                        Buat draft
                    </button>
                </form>
                <section class="rounded-2xl bg-white p-3 shadow-sm">
                    <p
                        class="px-2 pb-2 text-xs font-bold uppercase tracking-wide text-slate-500"
                    >
                        Daftar quiz
                    </p>
                    <button
                        v-for="quiz in quizzes"
                        :key="quiz.id"
                        class="mb-1 w-full rounded-xl px-3 py-3 text-left"
                        :class="
                            quiz.id === selectedQuizId
                                ? 'bg-teal-100 text-teal-950'
                                : 'hover:bg-slate-50'
                        "
                        @click="selectQuiz(quiz)"
                    >
                        <span class="block truncate font-bold">{{
                            quiz.title
                        }}</span
                        ><span class="text-xs"
                            >{{ quiz.status }} ·
                            {{ quiz.questions_count }} soal</span
                        >
                    </button>
                </section>
            </aside>

            <section v-if="selectedQuiz" class="space-y-5">
                <form
                    class="rounded-2xl bg-white p-5 shadow-sm"
                    @submit.prevent="saveMetadata"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3"
                    >
                        <h2 class="text-xl font-extrabold">Detail quiz</h2>
                        <span
                            class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800"
                            >{{ selectedQuiz.status }}</span
                        >
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <label class="text-sm font-bold"
                            >Judul<input
                                v-model="metadata.title"
                                class="mt-1 min-h-11 w-full rounded-xl border-slate-200"
                                required
                        /></label>
                        <label class="text-sm font-bold"
                            >Kategori<select
                                v-model="metadata.category_id"
                                class="mt-1 min-h-11 w-full rounded-xl border-slate-200"
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
                        <label class="text-sm font-bold md:col-span-2"
                            >Deskripsi<textarea
                                v-model="metadata.description"
                                class="mt-1 min-h-24 w-full rounded-xl border-slate-200"
                            />
                        </label>
                        <label class="text-sm font-bold"
                            >Visibilitas<select
                                v-model="metadata.visibility"
                                class="mt-1 min-h-11 w-full rounded-xl border-slate-200"
                            >
                                <option value="private">Private</option>
                                <option value="organization">Organisasi</option>
                                <option value="public">Public</option>
                            </select></label
                        >
                        <label class="text-sm font-bold"
                            >Maksimal percobaan<input
                                v-model="metadata.max_attempts"
                                type="number"
                                min="1"
                                class="mt-1 min-h-11 w-full rounded-xl border-slate-200"
                                placeholder="Tanpa batas"
                        /></label>
                        <label class="text-sm font-bold"
                            >Deadline<input
                                v-model="metadata.deadline_at"
                                type="datetime-local"
                                class="mt-1 min-h-11 w-full rounded-xl border-slate-200"
                        /></label>
                        <label
                            class="flex min-h-11 items-center gap-3 text-sm font-bold"
                            ><input
                                v-model="metadata.show_explanations"
                                type="checkbox"
                                class="rounded border-slate-300 text-teal-700"
                            />Tampilkan pembahasan</label
                        >
                    </div>
                    <button
                        class="mt-5 min-h-11 rounded-xl bg-teal-700 px-5 font-extrabold text-white"
                        :disabled="metadata.processing"
                    >
                        Simpan detail
                    </button>
                </form>

                <section class="grid gap-5 xl:grid-cols-2">
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="font-extrabold">Bank soal</h2>
                            <span class="text-sm text-slate-500"
                                >{{ selectedQuestionIds.length }} dipilih</span
                            >
                        </div>
                        <label
                            v-for="question in questions"
                            :key="question.id"
                            class="mt-3 flex cursor-pointer gap-3 rounded-xl border border-slate-100 p-3 hover:border-teal-300"
                        >
                            <input
                                v-model="selectedQuestionIds"
                                :value="question.id"
                                type="checkbox"
                                class="mt-1 rounded border-slate-300 text-teal-700"
                            />
                            <span
                                ><span class="block font-bold">{{
                                    question.prompt
                                }}</span
                                ><span class="text-xs text-slate-500"
                                    >{{ question.type }} ·
                                    {{ question.points }} poin</span
                                ></span
                            >
                        </label>
                        <button
                            class="mt-5 min-h-11 w-full rounded-xl bg-slate-900 px-4 font-extrabold text-white disabled:opacity-50"
                            :disabled="selectedQuestionIds.length === 0"
                            @click="syncQuestions"
                        >
                            Simpan susunan soal
                        </button>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <h2 class="font-extrabold">Urutan dan preview</h2>
                        <p
                            v-if="selectedQuestionIds.length === 0"
                            class="mt-4 text-sm text-slate-500"
                        >
                            Pilih minimal satu soal dari bank.
                        </p>
                        <article
                            v-for="(questionId, index) in selectedQuestionIds"
                            :key="questionId"
                            class="mt-3 rounded-xl bg-slate-50 p-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-bold">
                                    {{ index + 1 }}.
                                    {{
                                        questions.find(
                                            (question) =>
                                                question.id === questionId,
                                        )?.prompt
                                    }}
                                </p>
                                <div class="flex gap-1">
                                    <button
                                        class="min-h-9 rounded-lg px-3 text-sm font-bold hover:bg-white"
                                        :disabled="index === 0"
                                        @click="moveQuestion(index, -1)"
                                    >
                                        ↑</button
                                    ><button
                                        class="min-h-9 rounded-lg px-3 text-sm font-bold hover:bg-white"
                                        :disabled="
                                            index ===
                                            selectedQuestionIds.length - 1
                                        "
                                        @click="moveQuestion(index, 1)"
                                    >
                                        ↓
                                    </button>
                                </div>
                            </div>
                        </article>
                        <div class="mt-5 grid gap-2 sm:grid-cols-3">
                            <button
                                class="min-h-11 rounded-xl bg-teal-700 px-4 font-extrabold text-white"
                                :disabled="selectedQuestionIds.length === 0"
                                @click="publish"
                            >
                                Publish</button
                            ><button
                                class="min-h-11 rounded-xl border border-slate-200 px-4 font-extrabold"
                                @click="cloneQuiz"
                            >
                                Duplikat</button
                            ><button
                                class="min-h-11 rounded-xl border border-red-200 px-4 font-extrabold text-red-700"
                                @click="archiveQuiz"
                            >
                                Arsipkan
                            </button>
                        </div>
                    </div>
                </section>
            </section>
            <section
                v-else
                class="grid min-h-80 place-items-center rounded-2xl bg-white p-8 text-center shadow-sm"
            >
                <div>
                    <h2 class="text-xl font-extrabold">Belum ada quiz</h2>
                    <p class="mt-2 text-slate-500">
                        Buat draft untuk mulai menyusun soal.
                    </p>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
