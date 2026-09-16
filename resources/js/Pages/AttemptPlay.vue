<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Question = {
    id: number;
    type: string;
    prompt: string;
    options: string[] | null;
    points: number;
    explanation?: string | null;
};
type Answer = {
    id: number;
    question_id: number;
    answer: string;
    is_correct: boolean | null;
    points_awarded: number;
    feedback?: string | null;
};
const props = defineProps<{
    attempt: {
        id: number;
        status: string;
        score: number;
        quiz: {
            id: number;
            title: string;
            show_explanations: boolean;
            questions: Question[];
        };
        answers: Answer[];
    };
}>();
const index = ref(0);
const answer = useForm({ answer: props.attempt.answers[0]?.answer ?? '' });
const question = computed(() => props.attempt.quiz.questions[index.value]);
const existing = computed(() =>
    props.attempt.answers.find(
        (item) => item.question_id === question.value.id,
    ),
);
const finished = computed(() => props.attempt.status !== 'in_progress');

function choose(value: string): void {
    answer.answer = value;
    save();
}
function save(): void {
    answer.put(
        route('attempts.answers.upsert', [props.attempt.id, question.value.id]),
        { preserveScroll: true },
    );
}
function next(): void {
    if (index.value < props.attempt.quiz.questions.length - 1) {
        index.value += 1;
        answer.answer =
            props.attempt.answers.find(
                (item) => item.question_id === question.value.id,
            )?.answer ?? '';
    }
}
function previous(): void {
    if (index.value > 0) {
        index.value -= 1;
        answer.answer =
            props.attempt.answers.find(
                (item) => item.question_id === question.value.id,
            )?.answer ?? '';
    }
}
function submit(): void {
    useForm({}).post(route('attempts.submit', props.attempt.id));
}
</script>

<template>
    <Head :title="attempt.quiz.title" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <Link
                    :href="route('attempts.index')"
                    class="text-sm font-bold text-teal-700"
                    >← Kembali</Link
                ><span
                    class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800"
                    >{{ attempt.status }}</span
                >
            </div>
            <section
                v-if="finished"
                class="mt-5 rounded-3xl bg-teal-800 p-7 text-white"
            >
                <p
                    class="text-sm font-bold uppercase tracking-wide text-teal-100"
                >
                    Hasil kuis
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">
                    {{ attempt.quiz.title }}
                </h1>
                <p class="mt-5 text-5xl font-extrabold">
                    {{ attempt.score }} <span class="text-lg">poin</span>
                </p>
                <p
                    v-if="attempt.status === 'pending_review'"
                    class="mt-4 text-teal-100"
                >
                    Jawaban essay menunggu penilaian creator.
                </p>
            </section>
            <template v-else>
                <div class="mt-5 h-2 overflow-hidden rounded-full bg-teal-100">
                    <div
                        class="h-full bg-teal-700 transition-all"
                        :style="{
                            width: `${((index + 1) / attempt.quiz.questions.length) * 100}%`,
                        }"
                    />
                </div>
                <section class="mt-5 rounded-3xl bg-white p-6 shadow-sm">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700"
                    >
                        Soal {{ index + 1 }} dari
                        {{ attempt.quiz.questions.length }} ·
                        {{ question.points }} poin
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold text-slate-900">
                        {{ question.prompt }}
                    </h1>
                    <div v-if="question.options" class="mt-6 grid gap-3">
                        <button
                            v-for="option in question.options"
                            :key="option"
                            class="min-h-14 rounded-2xl border px-4 text-left font-bold"
                            :class="
                                answer.answer === option
                                    ? 'border-teal-700 bg-teal-50'
                                    : 'border-slate-200 hover:border-teal-400'
                            "
                            :disabled="answer.processing"
                            @click="choose(option)"
                        >
                            {{ option }}
                        </button>
                    </div>
                    <form v-else class="mt-6" @submit.prevent="save">
                        <textarea
                            v-model="answer.answer"
                            class="min-h-32 w-full rounded-2xl border-slate-200"
                            :placeholder="
                                question.type === 'essay'
                                    ? 'Tulis jawaban lengkap.'
                                    : 'Ketik jawaban.'
                            "
                            required
                        /><button
                            class="mt-3 min-h-11 rounded-xl bg-teal-700 px-5 font-extrabold text-white"
                            :disabled="answer.processing"
                        >
                            Simpan jawaban
                        </button>
                    </form>
                    <p
                        v-if="existing"
                        class="mt-4 text-sm font-bold text-teal-800"
                    >
                        Jawaban tersimpan.
                    </p>
                </section>
                <div class="mt-5 flex justify-between gap-3">
                    <button
                        class="min-h-11 rounded-xl border border-slate-200 px-5 font-bold disabled:opacity-50"
                        :disabled="index === 0"
                        @click="previous"
                    >
                        Sebelumnya</button
                    ><button
                        v-if="index < attempt.quiz.questions.length - 1"
                        class="min-h-11 rounded-xl bg-slate-900 px-5 font-extrabold text-white"
                        @click="next"
                    >
                        Berikutnya</button
                    ><button
                        v-else
                        class="min-h-11 rounded-xl bg-teal-700 px-5 font-extrabold text-white"
                        @click="submit"
                    >
                        Kumpulkan
                    </button>
                </div>
            </template>
            <section
                v-if="finished && attempt.quiz.show_explanations"
                class="mt-5 space-y-3"
            >
                <article
                    v-for="item in attempt.quiz.questions"
                    :key="item.id"
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p class="font-extrabold">{{ item.prompt }}</p>
                    <p class="mt-2 text-sm">
                        Jawaban:
                        {{
                            attempt.answers.find(
                                (answer) => answer.question_id === item.id,
                            )?.answer || 'Belum dijawab'
                        }}
                    </p>
                    <p
                        v-if="item.explanation"
                        class="mt-2 text-sm text-slate-600"
                    >
                        {{ item.explanation }}
                    </p>
                </article>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
