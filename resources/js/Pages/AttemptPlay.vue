<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref } from 'vue';

type Question = {
    id: number;
    type: string;
    prompt: string;
    options: string[] | null;
    points: number;
    explanation?: string | null;
    hint?: string | null;
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
        attempts_used: number;
        attempts_remaining: number | null;
        retry_allowed: boolean;
        latest_score: number | null;
        best_score: number | null;
        result: {
            total_points: number;
            percentage: number | null;
            correct_answers: number | null;
            incorrect_answers: number | null;
            unanswered_answers: number | null;
            answered_answers: number | null;
            xp_earned: number;
            level_before: number | null;
            level_after: number | null;
            level_up: boolean;
            earned_badges: { key: string; name: string }[];
        };
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

const isOffline = ref(typeof navigator !== "undefined" ? !navigator.onLine : false);

function handleOnlineStatus(): void {
    isOffline.value = false;
}
function handleOfflineStatus(): void {
    isOffline.value = true;
}

const resultRoot = ref<HTMLElement | null>(null);
const submitButton = ref<HTMLButtonElement | null>(null);
let resultContext: gsap.Context | undefined;
let resultMedia: gsap.MatchMedia | undefined;
const showHint = ref(false);
const isSubmitting = ref(false);
const submission = useForm({});
const answer = useForm({ answer: "" });

const showSubmitConfirm = ref(false);
const confirmationMessage = ref("");
function confirmSubmit(): void {
    showSubmitConfirm.value = false;
    isSubmitProcessing.value = true;
    submission.post(route("attempts.submit", props.attempt.id), {
        onFinish: () => { isSubmitProcessing.value = false; },
    });
}
const isSubmitProcessing = ref(false);
const question = computed(() => props.attempt.quiz.questions[index.value]);
const existing = computed(() =>
    props.attempt.answers.find(
        (item) => item.question_id === question.value.id,
    ),
);
const finished = computed(() => props.attempt.status !== 'in_progress');
const currentOptionIndex = computed(
    () =>
        question.value.options?.findIndex(
            (option) => option === answer.answer,
        ) ?? -1,
);

function animateResult(): void {
    if (!resultRoot.value || !finished.value) return;

    resultContext = gsap.context(() => {
        resultMedia = gsap.matchMedia();
        resultMedia.add('(prefers-reduced-motion: no-preference)', () => {
            const timeline = gsap.timeline({
                defaults: { ease: 'power3.out' },
            });
            const header = resultRoot.value?.querySelector(
                '.gsap-result-header',
            );
            const stats = resultRoot.value?.querySelectorAll('.gsap-stat');
            const reward = resultRoot.value?.querySelector('.gsap-reward');
            const badges =
                resultRoot.value?.querySelectorAll('.gsap-badge-unlock');
            const mascot = resultRoot.value?.querySelector('.gsap-mascot');
            const levelUp = resultRoot.value?.querySelector('.gsap-level-up');

            if (header)
                timeline.from(header, {
                    autoAlpha: 0,
                    y: 18,
                    scale: 0.98,
                    duration: 0.5,
                });
            if (stats?.length)
                timeline.from(
                    stats,
                    { autoAlpha: 0, y: 12, stagger: 0.08, duration: 0.32 },
                    '-=0.22',
                );
            if (reward)
                timeline.from(
                    reward,
                    { autoAlpha: 0, y: 12, scale: 0.98, duration: 0.42 },
                    '-=0.12',
                );
            if (badges?.length)
                timeline.from(
                    badges,
                    {
                        autoAlpha: 0,
                        y: 8,
                        scale: 0.86,
                        rotation: -4,
                        stagger: 0.1,
                        duration: 0.3,
                    },
                    '-=0.18',
                );
            if (mascot)
                timeline.from(
                    mascot,
                    { autoAlpha: 0, x: 16, rotation: 6, duration: 0.42 },
                    '<0.05',
                );
            if (levelUp)
                timeline.from(
                    levelUp,
                    { autoAlpha: 0, y: 10, scale: 0.98, duration: 0.36 },
                    '-=0.22',
                );

            return () => timeline.kill();
        });
    }, resultRoot.value);
}

function answerFor(questionId: number): Answer | undefined {
    return props.attempt.answers.find(
        (item) => item.question_id === questionId,
    );
}

function answerState(questionId: number): string {
    const item = answerFor(questionId);
    if (!item || item.is_correct === null) return 'border-slate-200';
    return item.is_correct
        ? 'border-status-success bg-green-50'
        : 'border-status-danger bg-red-50';
}

function moveOption(delta: number): void {
    if (!question.value.options?.length) return;
    const nextIndex =
        (currentOptionIndex.value + delta + question.value.options.length) %
        question.value.options.length;
    answer.answer = question.value.options[nextIndex];
}

function handleShortcut(event: KeyboardEvent): void {
    const target = event.target as HTMLElement | null;
    if (target?.tagName === 'TEXTAREA' || target?.tagName === 'INPUT') return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        moveOption(1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        moveOption(-1);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (finished.value) return;
        question.value.options ? save() : submit();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleShortcut);
    window.addEventListener('online', handleOnlineStatus);
    window.addEventListener('offline', handleOfflineStatus);
});
onMounted(animateResult);
onUnmounted(() => {
    window.removeEventListener('keydown', handleShortcut);
    window.removeEventListener('online', handleOnlineStatus);
    window.removeEventListener('offline', handleOfflineStatus);
    resultMedia?.revert();
    resultContext?.revert();
});

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
        showHint.value = false;
        answer.answer =
            props.attempt.answers.find(
                (item) => item.question_id === question.value.id,
            )?.answer ?? '';
    }
}
function previous(): void {
    if (index.value > 0) {
        index.value -= 1;
        showHint.value = false;
        answer.answer =
            props.attempt.answers.find(
                (item) => item.question_id === question.value.id,
            )?.answer ?? '';
    }
}
function retry(): void {
    router.post(route('attempts.store', props.attempt.quiz.id));
}
function submit(): void {
    if (isSubmitting.value) return;
    const unanswered =
        props.attempt.quiz.questions.length - props.attempt.answers.length;
    const message =
        unanswered > 0
            ? `Masih ada ${unanswered} soal belum dijawab. Tetap kumpulkan?`
            : 'Kumpulkan kuis sekarang?';
    if (finished.value) { showSubmitConfirm.value = true; return; }
    isSubmitting.value = true;
    if (submitButton.value) {
        gsap.timeline({ defaults: { duration: 0.18, ease: 'power2.out' } })
            .to(submitButton.value, { scale: 0.96 })
            .to(submitButton.value, {
                scale: 1,
                boxShadow: '0 0 0 5px rgb(144 203 49 / 0.2)',
            });
    }
    submission.post(route('attempts.submit', props.attempt.id), {
        onFinish: () => {
            isSubmitting.value = false;
            if (submitButton.value)
                gsap.to(submitButton.value, {
                    boxShadow: '0 0 0 0 rgb(144 203 49 / 0)',
                    duration: 0.25,
                });
        },
    });
}
</script>

<template>
    <Head :title="attempt.quiz.title" />
    <AuthenticatedLayout>
        <main ref="resultRoot" class="mx-auto max-w-3xl px-4 py-6 sm:px-6">

            <!-- Offline Connection Indicator Banner -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-4 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="-translate-y-4 opacity-0"
            >
                <div
                    v-if="isOffline"
                    class="mb-4 flex items-center justify-between gap-3 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-md"
                    role="alert"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-amber-900 font-bold">
                            ⚠️
                        </span>
                        <div>
                            <p class="text-xs font-black sm:text-sm">Koneksi Internet Terputus</p>
                            <p class="text-[11px] text-amber-800">
                                Jawaban kamu tetap aman di layar ini. Sistem akan mencoba mengirim ulang saat koneksi pulih.
                            </p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full bg-amber-200/80 px-2.5 py-1 text-[10px] font-black uppercase text-amber-950">
                        Offline
                    </span>
                </div>
            </transition>

            <div class="flex items-center justify-between gap-3">
                <Link
                    :href="route('attempts.index')"
                    class="text-sm font-bold text-brand-primary"
                    >← Kembali</Link
                ><span
                    class="rounded-full bg-brand-accent px-3 py-1 text-xs font-bold text-brand-primary"
                    >{{ attempt.status }}</span
                >
            </div>
            <section
                v-if="finished"
                class="gsap-result-header mt-5 overflow-hidden rounded-3xl bg-brand-primary p-7 text-white shadow-figma"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-sm font-bold uppercase tracking-wide text-white/80"
                        >
                            Hasil kuis
                        </p>
                        <h1 class="mt-2 text-3xl font-extrabold">
                            {{ attempt.quiz.title }}
                        </h1>
                    </div>
                    <span
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white/15"
                        aria-hidden="true"
                    >
                        <AppIcon
                            name="trophy"
                            :size="26"
                            :interactive="false"
                        />
                    </span>
                </div>
                <p class="mt-5 text-5xl font-extrabold">
                    {{ attempt.score }} <span class="text-lg">poin</span>
                </p>
                <p
                    v-if="attempt.status === 'pending_review'"
                    class="mt-4 text-white/80"
                >
                    Jawaban essay menunggu penilaian creator.
                </p>
            </section>
            <section v-if="finished" class="mt-4 grid gap-3 sm:grid-cols-3">
                <div
                    class="gsap-stat rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold text-slate-500"
                    >
                        Persentase
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-slate-900"
                    >
                        {{ attempt.result.percentage ?? 0 }}%
                    </p>
                </div>
                <div
                    class="gsap-stat rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold text-slate-500"
                    >
                        Benar / salah
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-slate-900"
                    >
                        {{ attempt.result.correct_answers ?? 0 }} /
                        {{ attempt.result.incorrect_answers ?? 0 }}
                    </p>
                </div>
                <div
                    class="gsap-stat rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold text-slate-500"
                    >
                        Attempt
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-slate-900"
                    >
                        {{ attempt.attempts_used
                        }}<span
                            v-if="attempt.attempts_remaining !== null"
                            class="text-sm font-bold text-slate-500"
                        >
                            /
                            {{
                                attempt.attempts_used +
                                attempt.attempts_remaining
                            }}</span
                        >
                    </p>
                </div>
            </section>
            <section
                v-if="
                    finished &&
                    (attempt.result.xp_earned > 0 ||
                        attempt.result.earned_badges.length)
                "
                class="gsap-reward mt-4 rounded-2xl border border-brand-secondary/40 bg-brand-accent p-4"
                aria-live="polite"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-secondary text-white"
                        aria-hidden="true"
                    >
                        <AppIcon name="xp" :size="20" :interactive="false" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-black text-slate-900"
                        >
                            Reward belajar
                        </p>
                        <p
                            v-if="attempt.result.xp_earned > 0"
                            class="mt-1 text-sm font-bold text-brand-primary"
                        >
                            +{{ attempt.result.xp_earned }} XP dari kuis ini
                        </p>
                        <div
                            v-if="attempt.result.earned_badges.length"
                            class="mt-2 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="badge in attempt.result.earned_badges"
                                :key="badge.key"
                                class="gsap-badge-unlock inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-sm"
                            >
                                <AppIcon
                                    name="badge"
                                    :size="13"
                                    :interactive="false"
                                />{{ badge.name }} terbuka
                            </span>
                        </div>
                    </div>
                    <img
                        v-if="attempt.result.earned_badges.length"
                        class="gsap-mascot h-16 w-16 shrink-0 object-contain"
                        src="/assets/kuesify/illustrations/shared/mascot-support.png"
                        alt="Maskot Kuesify merayakan badge baru"
                    />
                </div>
            </section>
            <section
                v-if="finished && attempt.result.level_up"
                class="gsap-level-up mt-3 rounded-2xl border border-support-1/50 bg-support-1/10 p-4"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-support-1 text-white"
                        aria-hidden="true"
                        ><AppIcon name="level" :size="20" :interactive="false"
                    /></span>
                    <div>
                        <p
                            class="text-sm font-black text-slate-900"
                        >
                            Level naik!
                        </p>
                        <p
                            class="mt-1 text-sm font-bold text-slate-700"
                        >
                            Level {{ attempt.result.level_after }} terbuka.
                            Teruskan ritmemu.
                        </p>
                    </div>
                </div>
            </section>
            <div v-if="finished" class="mt-4 flex flex-wrap gap-2">
                <button
                    v-if="attempt.retry_allowed"
                    type="button"
                    class="min-h-11 rounded-xl bg-brand-secondary px-5 font-extrabold text-white"
                    @click="retry"
                >
                    Ulangi kuis
                </button>
                <Link
                    href="/participant/quizzes"
                    class="inline-flex min-h-11 items-center rounded-xl border border-slate-200 px-5 font-bold text-slate-700"
                    >Kembali ke katalog</Link
                >
            </div>
            <template v-else>
                <div
                    class="mt-5 h-2 overflow-hidden rounded-full bg-brand-accent"
                >
                    <div
                        class="h-full bg-brand-primary transition-all"
                        :style="{
                            width: `${((index + 1) / attempt.quiz.questions.length) * 100}%`,
                        }"
                    />
                </div>
                <section class="mt-5 rounded-3xl bg-white p-6 shadow-sm">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.16em] text-brand-primary"
                    >
                        Soal {{ index + 1 }} dari
                        {{ attempt.quiz.questions.length }} ·
                        {{ question.points }} poin
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold text-slate-900">
                        {{ question.prompt }}
                    </h1>
                    <button
                        v-if="question.hint"
                        type="button"
                        class="mt-3 rounded-full bg-brand-accent px-3 py-1 text-xs font-extrabold text-brand-primary"
                        @click="showHint = !showHint"
                    >
                        {{ showHint ? 'Sembunyikan hint' : 'Lihat hint' }}
                    </button>
                    <p
                        v-if="showHint && question.hint"
                        class="mt-2 rounded-xl bg-brand-accent p-3 text-sm font-semibold text-slate-900"
                    >
                        {{ question.hint }}
                    </p>
                    <div v-if="question.options" class="mt-6 grid gap-3">
                        <button
                            v-for="option in question.options"
                            :key="option"
                            class="min-h-14 rounded-2xl border px-4 text-left font-bold"
                            :class="
                                answer.answer === option
                                    ? 'border-brand-primary bg-brand-accent'
                                    : 'border-slate-200 hover:border-brand-primary'
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
                            class="mt-3 min-h-11 rounded-xl bg-brand-primary px-5 font-extrabold text-white"
                            :disabled="answer.processing"
                        >
                            Simpan jawaban
                        </button>
                    </form>
                    <p
                        v-if="existing"
                        class="mt-4 text-sm font-bold text-brand-primary"
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
                        Sebelumnya
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="index < attempt.quiz.questions.length - 1"
                            class="min-h-11 rounded-xl bg-slate-900 px-5 font-extrabold text-white"
                            @click="next"
                        >
                            Berikutnya
                        </button>
                        <button
                            ref="submitButton"
                            class="min-h-11 rounded-xl bg-brand-primary px-5 font-extrabold text-white"
                            :disabled="isSubmitting"
                            @click="submit"
                        >
                            {{ isSubmitting ? 'Mengumpulkan…' : 'Kumpulkan' }}
                        </button>
                    </div>
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
                    <p
                        class="rounded-xl border p-3 font-extrabold"
                        :class="answerState(item.id)"
                    >
                        {{ item.prompt }}
                    </p>
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
                        Pembahasan: {{ item.explanation }}
                    </p>
                </article>
            </section>
        </main>
    
<!-- Submit Confirmation Modal -->
<Modal
    :show="showSubmitConfirm"
    title="Konfirmasi Pengumpulan"
    @close="showSubmitConfirm = false"
>
    <div class="p-6">
        <p class="text-slate-600">
            {{ confirmationMessage }}
        </p>
        <div class="mt-6 flex justify-end gap-3">
            <button
                class="rounded-xl border border-slate-200 px-4 font-bold text-slate-700"
                @click="showSubmitConfirm = false"
            >
                Batal
            </button>
            <button
                class="rounded-xl bg-brand-primary px-4 font-extrabold text-white"
                @click="confirmSubmit"
                :disabled="isSubmitProcessing"
            >
                {{ isSubmitProcessing ? "Mengumpulkan…" : "Kumpulkan" }}
            </button>
        </div>
    </div>
</Modal>

    </AuthenticatedLayout>
</template>






