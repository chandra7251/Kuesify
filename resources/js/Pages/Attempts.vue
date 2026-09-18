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
    <Head title="Hasil" />
    <AuthenticatedLayout>
        <template #header
            ><div class="flex items-center justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700"
                    >
                        {{
                            gradebook ? 'Creator workspace' : 'Belajar mandiri'
                        }}
                    </p>
                    <h1 class="mt-1 text-2xl font-extrabold">
                        {{ gradebook ? 'Gradebook' : 'Kuis saya' }}
                    </h1>
                </div>
                <a
                    v-if="gradebook"
                    :href="route('attempts.export')"
                    class="min-h-11 rounded-xl border border-teal-700 px-4 py-3 text-sm font-extrabold text-teal-800"
                    >Export CSV</a
                >
            </div></template
        >
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
                        class="text-xs font-bold uppercase tracking-wide text-teal-700"
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
                        class="mt-5 min-h-11 w-full rounded-xl bg-teal-700 px-4 font-extrabold text-white"
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
                        gradebook ? 'Semua hasil peserta' : 'Riwayat percobaan'
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
                                    class="min-h-11 rounded-xl bg-teal-700 px-4 font-extrabold text-white"
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
