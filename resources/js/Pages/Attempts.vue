<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

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

const props = defineProps<{
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
const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
});

const participantStats = computed(() => {
    const attempts = props.attempts.data;
    const completed = attempts.filter(
        (attempt) => attempt.status === 'completed',
    );
    const pending = attempts.filter(
        (attempt) => attempt.status === 'pending_review',
    );
    const average = completed.length
        ? Math.round(
              completed.reduce((sum, attempt) => sum + attempt.score, 0) /
                  completed.length,
          )
        : 0;

    return {
        total: attempts.length,
        completed: completed.length,
        average,
        pending: pending.length,
    };
});

function start(quizId: number): void {
    useForm({}).post(route('attempts.store', quizId));
}

function exportHref(format: 'csv' | 'xlsx'): string {
    return route('attempts.export', { format });
}

function grade(attemptId: number, answerId: number): void {
    const value = grades[answerId];
    if (!value) return;
    router.post(route('attempts.answers.grade', [attemptId, answerId]), value, {
        preserveScroll: true,
    });
}

function statusLabel(status: string): string {
    return (
        {
            completed: 'Selesai',
            pending_review: 'Menunggu penilaian',
            in_progress: 'Belum selesai',
        }[status] ?? status
    );
}

function statusClass(status: string): string {
    return (
        {
            completed: 'bg-brand-secondary/15 text-[#527A12]',
            pending_review: 'bg-amber-100 text-amber-800',
            in_progress: 'bg-slate-100 text-slate-600',
        }[status] ?? 'bg-slate-100 text-slate-600'
    );
}

function formatDate(value: string): string {
    return dateFormatter.format(new Date(value));
}
</script>

<template>
    <Head title="Hasil Belajar" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-[0.2em] text-brand-primary"
                    >
                        Perjalanan belajar
                    </p>
                    <h1
                        class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl"
                    >
                        Hasil Belajar
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Lihat progresmu, lanjutkan kuis, dan kenali capaian
                        belajar dari waktu ke waktu.
                    </p>
                </div>
                <Link
                    :href="route('dashboard')"
                    class="hidden min-h-11 shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-brand-primary shadow-sm transition hover:border-brand-primary hover:bg-brand-primary/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary sm:inline-flex"
                >
                    <span aria-hidden="true">←</span>
                    Dashboard
                </Link>
            </div>

            <Link
                :href="route('dashboard')"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-brand-primary shadow-sm transition hover:border-brand-primary hover:bg-brand-primary/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary sm:hidden"
            >
                <span aria-hidden="true">←</span>
                Kembali ke Dashboard
            </Link>

            <template v-if="!gradebook">
                <section
                    class="rounded-2xl bg-brand-primary p-5 text-white shadow-figma sm:p-7"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-bold text-brand-secondary">
                                Ringkasan progres
                            </p>
                            <h2
                                class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                            >
                                Konsisten sedikit demi sedikit.
                            </h2>
                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-white/75"
                            >
                                Setiap percobaan membantumu melihat bagian yang
                                sudah kuat dan yang masih perlu dilatih.
                            </p>
                        </div>
                        <Link
                            :href="route('participant.quizzes.index')"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-secondary px-5 text-sm font-black text-brand-primary transition hover:bg-brand-lime focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            Jelajahi kuis
                        </Link>
                    </div>
                </section>

                <section
                    aria-label="Ringkasan hasil"
                    class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Total percobaan
                        </p>
                        <p class="mt-3 text-3xl font-black text-slate-950">
                            {{ participantStats.total }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Semua kuis yang pernah dikerjakan
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Kuis selesai
                        </p>
                        <p class="mt-3 text-3xl font-black text-brand-primary">
                            {{ participantStats.completed }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Capaian yang sudah tersimpan
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Rata-rata nilai
                        </p>
                        <p
                            class="mt-3 text-3xl font-black text-brand-secondary"
                        >
                            {{ participantStats.average }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Dari kuis yang selesai
                        </p>
                    </article>
                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            Perlu ditinjau
                        </p>
                        <p class="mt-3 text-3xl font-black text-support-1">
                            {{ participantStats.pending }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Menunggu penilaian essay
                        </p>
                    </article>
                </section>

                <section>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                            >
                                Langkah berikutnya
                            </p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Lanjutkan latihan
                            </h2>
                        </div>
                        <Link
                            :href="route('participant.quizzes.index')"
                            class="text-sm font-extrabold text-brand-primary hover:underline"
                        >
                            Lihat semua
                        </Link>
                    </div>
                    <div
                        v-if="publishedQuizzes.length"
                        class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <article
                            v-for="quiz in publishedQuizzes.slice(0, 3)"
                            :key="quiz.id"
                            class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm transition hover:-translate-y-0.5 hover:shadow-figma"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="rounded-full bg-brand-accent px-3 py-1 text-xs font-black text-brand-primary"
                                >
                                    {{ quiz.questions_count }} soal
                                </span>
                                <span
                                    v-if="quiz.max_attempts"
                                    class="text-xs font-semibold text-slate-500"
                                >
                                    {{ quiz.max_attempts }}x kesempatan
                                </span>
                            </div>
                            <h3 class="mt-4 text-lg font-black text-slate-950">
                                {{ quiz.title }}
                            </h3>
                            <p
                                class="mt-2 min-h-12 text-sm leading-6 text-slate-600"
                            >
                                {{
                                    quiz.description ||
                                    'Latihan singkat untuk menguatkan pemahamanmu.'
                                }}
                            </p>
                            <p
                                v-if="quiz.deadline_at"
                                class="mt-3 text-xs font-semibold text-amber-700"
                            >
                                Deadline
                                {{
                                    new Date(
                                        quiz.deadline_at,
                                    ).toLocaleDateString('id-ID')
                                }}
                            </p>
                            <button
                                class="mt-5 min-h-11 w-full rounded-xl bg-brand-primary px-4 text-sm font-black text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                                @click="start(quiz.id)"
                            >
                                Mulai quiz
                            </button>
                        </article>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center"
                    >
                        <p class="font-bold text-slate-900">
                            Belum ada kuis tersedia
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Kuis baru akan muncul di sini saat pengajar
                            mempublikasikannya.
                        </p>
                    </div>
                </section>

                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm sm:p-6"
                >
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                            >
                                Jejak belajarmu
                            </p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Riwayat pengerjaan
                            </h2>
                        </div>
                        <span class="text-xs font-semibold text-slate-500"
                            >{{ participantStats.total }} percobaan</span
                        >
                    </div>
                    <div
                        v-if="attempts.data.length === 0"
                        class="mt-5 rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500"
                    >
                        Belum ada hasil. Mulai kuis pertamamu dari bagian
                        latihan di atas.
                    </div>
                    <div v-else class="mt-5 divide-y divide-slate-100">
                        <article
                            v-for="attempt in attempts.data"
                            :key="attempt.id"
                            class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="truncate font-black text-slate-950"
                                    >
                                        {{ attempt.quiz.title }}
                                    </h3>
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-[11px] font-black',
                                            statusClass(attempt.status),
                                        ]"
                                    >
                                        {{ statusLabel(attempt.status) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">
                                    Dikerjakan
                                    {{ formatDate(attempt.updated_at) }}
                                </p>
                            </div>
                            <div
                                class="flex items-center justify-between gap-4 sm:justify-end"
                            >
                                <div class="text-left sm:text-right">
                                    <p
                                        class="text-lg font-black text-slate-950"
                                    >
                                        {{ attempt.score }}
                                        <span
                                            class="text-xs font-bold text-slate-500"
                                            >poin</span
                                        >
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Hasil percobaan
                                    </p>
                                </div>
                                <Link
                                    :href="route('attempts.play', attempt.id)"
                                    class="inline-flex min-h-10 items-center rounded-xl bg-brand-primary px-4 text-sm font-black text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                                >
                                    {{
                                        attempt.status === 'in_progress'
                                            ? 'Lanjutkan'
                                            : 'Lihat hasil'
                                    }}
                                </Link>
                            </div>
                        </article>
                    </div>
                </section>
            </template>

            <template v-else>
                <section
                    aria-labelledby="results-summary-title"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-5"
                    >
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.18em] text-brand-primary"
                            >
                                Laporan Hasil
                            </p>
                            <h2
                                id="results-summary-title"
                                class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                Laporan Hasil Quiz
                            </h2>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                            >
                                Tinjau seluruh hasil pengerjaan peserta, pantau
                                nilai, dan berikan penilaian pada jawaban essay.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-for="format in ['csv', 'xlsx'] as const"
                                :key="format"
                                :href="exportHref(format)"
                                class="inline-flex min-h-11 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold uppercase text-white transition hover:bg-brand-hover"
                                >Export {{ format }}</a
                            >
                        </div>
                    </div>
                </section>
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-extrabold">Semua Hasil Peserta</h2>
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
                                    {{ statusLabel(attempt.status) }} ·
                                    {{ attempt.score }} poin
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
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
                                    />
                                    <input
                                        v-model="
                                            (grades[answer.id] ??= {
                                                points: answer.points_awarded,
                                                feedback: answer.feedback ?? '',
                                            }).feedback
                                        "
                                        class="min-h-11 rounded-xl border-slate-200"
                                        placeholder="Feedback"
                                    />
                                    <button
                                        class="min-h-11 rounded-xl bg-brand-primary px-4 font-extrabold text-white"
                                    >
                                        Nilai
                                    </button>
                                </div>
                            </form>
                        </div>
                    </article>
                </section>
            </template>
        </main>
    </AuthenticatedLayout>
</template>
