<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

type Attempt = {
    id: number;
    status: string;
    score: number;
    updated_at: string;
    quiz: { id: number; title: string };
    participant?: { name: string; email: string };
    answers: {
        id: number;
        question: { prompt: string; type: string; points: number };
        answer: string;
        points_awarded: number;
        feedback?: string | null;
    }[];
};
defineProps<{
    attempts: { data: Attempt[] };
    publishedQuizzes: {
        id: number;
        title: string;
        description?: string | null;
        questions_count: number;
        deadline_at?: string | null;
        max_attempts?: number | null;
    }[];
    gradebook: boolean;
}>();
const grades = reactive<Record<number, { points: number; feedback: string }>>(
    {},
);
function start(quizId: number): void {
    useForm({}).post(route('attempts.store', quizId));
}
function grade(attemptId: number, answerId: number): void {
    const value = grades[answerId];
    if (!value) return;
    router.post(route('attempts.answers.grade', [attemptId, answerId]), value, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Hasil Belajar" />
    <AuthenticatedLayout>
        <div
            class="flex flex-wrap items-center justify-between gap-3 px-4 pt-6 lg:px-8"
        >
            <Link
                :href="route('dashboard')"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-brand-primary px-4 py-3 text-sm font-bold text-brand-primary transition hover:bg-brand-primary hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
            >
                <svg
                    aria-hidden="true"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Kembali ke Dashboard
            </Link>
            <a
                v-if="gradebook"
                :href="route('attempts.export')"
                class="inline-flex min-h-11 items-center rounded-xl bg-brand-primary px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
            >
                Export CSV
            </a>
        </div>
        <main class="mx-auto max-w-6xl space-y-5 px-4 py-6 lg:px-8">
            <section
                v-if="!gradebook"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="quiz in publishedQuizzes"
                    :key="quiz.id"
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-wide text-brand-secondary"
                    >
                        {{ quiz.questions_count }} soal
                    </p>
                    <h2 class="mt-2 text-xl font-extrabold">
                        {{ quiz.title }}
                    </h2>
                    <p class="mt-2 min-h-10 text-sm text-slate-600">
                        {{ quiz.description || 'Siap dikerjakan mandiri.' }}
                    </p>
                    <p
                        v-if="quiz.deadline_at"
                        class="mt-3 text-xs text-slate-500"
                    >
                        Deadline:
                        {{ new Date(quiz.deadline_at).toLocaleString('id-ID') }}
                    </p>
                    <button
                        class="mt-5 min-h-11 w-full rounded-xl bg-brand-primary px-4 font-extrabold text-white"
                        @click="start(quiz.id)"
                    >
                        Mulai quiz
                    </button>
                </article>
                <p
                    v-if="publishedQuizzes.length === 0"
                    class="rounded-2xl bg-white p-6 text-slate-500"
                >
                    Belum ada quiz yang dipublikasikan.
                </p>
            </section>
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-extrabold">
                    {{
                        gradebook ? 'Semua Hasil Peserta' : 'Riwayat percobaan'
                    }}
                </h2>
                <div
                    v-if="attempts.data.length === 0"
                    class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500"
                >
                    Belum ada hasil.
                </div>
                <article
                    v-for="attempt in attempts.data"
                    :key="attempt.id"
                    class="mt-4 rounded-2xl border border-slate-100 p-4"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <p class="font-extrabold text-slate-900">
                                {{ attempt.quiz.title }}
                            </p>
                            <p
                                v-if="attempt.participant"
                                class="text-sm text-slate-500"
                            >
                                {{ attempt.participant.name }} ·
                                {{ attempt.participant.email }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{ attempt.status }} · {{ attempt.score }} poin
                            </p>
                        </div>
                        <Link
                            v-if="!gradebook"
                            :href="route('attempts.play', attempt.id)"
                            class="min-h-11 rounded-xl bg-slate-900 px-4 py-3 text-sm font-extrabold text-white"
                            >{{
                                attempt.status === 'in_progress'
                                    ? 'Lanjutkan'
                                    : 'Lihat hasil'
                            }}</Link
                        >
                    </div>
                    <div v-if="gradebook" class="mt-4 space-y-3">
                        <form
                            v-for="answer in attempt.answers.filter(
                                (item) => item.question.type === 'essay',
                            )"
                            :key="answer.id"
                            class="rounded-xl bg-slate-50 p-4"
                            @submit.prevent="grade(attempt.id, answer.id)"
                        >
                            <p class="font-bold">
                                {{ answer.question.prompt }}
                            </p>
                            <p class="mt-1 text-sm text-slate-600">
                                Jawaban: {{ answer.answer }}
                            </p>
                            <div
                                class="mt-3 grid gap-3 sm:grid-cols-[8rem_1fr_auto]"
                            >
                                <input
                                    v-model.number="
                                        (grades[answer.id] ??= {
                                            points: answer.points_awarded,
                                            feedback: answer.feedback ?? '',
                                        }).points
                                    "
                                    type="number"
                                    min="0"
                                    :max="answer.question.points"
                                    class="min-h-11 rounded-xl border-slate-200"
                                /><input
                                    v-model="
                                        (grades[answer.id] ??= {
                                            points: answer.points_awarded,
                                            feedback: answer.feedback ?? '',
                                        }).feedback
                                    "
                                    class="min-h-11 rounded-xl border-slate-200"
                                    placeholder="Feedback"
                                /><button
                                    class="min-h-11 rounded-xl bg-brand-primary px-4 font-extrabold text-white"
                                >
                                    Nilai
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
