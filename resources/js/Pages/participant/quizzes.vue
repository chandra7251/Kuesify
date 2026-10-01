<script setup lang="ts">
import QuizCard from '@/Components/QuizCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { onMounted, onUnmounted, ref } from 'vue';
gsap.registerPlugin(ScrollTrigger);

type Category = { id: number; name: string; theme_key: string | null };
type Quiz = {
    id: number;
    title: string;
    description: string | null;
    deadline_at: string | null;
    max_attempts: number | null;
    allow_retry: boolean;
    latest_score: number | null;
    best_score: number | null;
    questions_count: number;
    my_attempts_count: number;
    category: Category | null;
};
type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    quizzes: Paginator<Quiz>;
    filters: { search?: string | null; category?: number | null };
    categories: Category[];
}>();

const isLoading = ref(false);

const filters = useForm({
    search: props.filters.search ?? '',
    category: props.filters.category ? String(props.filters.category) : '',
});

function applyFilters(): void {
    router.get(
        route('participant.quizzes.index'),
        {
            search: filters.search || undefined,
            category: filters.category || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function clearFilters(): void {
    filters.search = '';
    filters.category = '';
    applyFilters();
}

function attemptLabel(quiz: Quiz): string {
    if (quiz.max_attempts === null) {
        return `${quiz.my_attempts_count} attempt`;
    }

    return `${quiz.my_attempts_count}/${quiz.max_attempts} attempt`;
}

function canStart(quiz: Quiz): boolean {
    if (!quiz.allow_retry && quiz.my_attempts_count > 0) return false;
    return (
        quiz.max_attempts === null || quiz.my_attempts_count < quiz.max_attempts
    );
}

function statusLabel(quiz: Quiz): string {
    if (quiz.my_attempts_count === 0) return 'Belum dikerjakan';
    if (canStart(quiz)) return 'Retry tersedia';
    return 'Sudah selesai';
}

function startQuiz(quiz: Quiz): void {
    if (!canStart(quiz)) {
        return;
    }

    router.post(route('attempts.store', quiz.id));
}

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
});

router.on('start', () => {
    isLoading.value = true;
});
router.on('finish', () => {
    isLoading.value = false;
});

const catalogRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    const mm = gsap.matchMedia();
    mm.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            gsap.from('[data-motion-item]', {
                opacity: 0,
                y: 20,
                duration: 0.4,
                stagger: 0.07,
                ease: 'power2.out',
                clearProps: 'all',
            });
        }, catalogRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    gsapCtx?.revert();
    ScrollTrigger.getAll().forEach((st) => st.kill());
});
</script>

<template>
    <Head title="Katalog Kuis" />

    <AuthenticatedLayout>
        <main
            ref="catalogRoot"
            data-motion="quiz-catalog"
            class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
        >
            <section
                data-motion-item
                class="rounded-2xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-brand-secondary"
                >
                    Katalog siswa
                </p>
                <div
                    class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-black tracking-tight">
                            Cari kuis yang cocok buat belajar
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-white/80"
                        >
                            Temukan kuis berdasarkan judul, deskripsi, atau
                            kategori. Mulai latihan tanpa membuka bank soal
                            guru.
                        </p>
                    </div>
                    <Link
                        href="/dashboard"
                        class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-bold text-brand-primary shadow-sm"
                    >
                        Kembali dashboard
                    </Link>
                </div>
            </section>

            <section
                data-motion-item
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
            >
                <form
                    class="grid gap-3 md:grid-cols-[1fr_16rem_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <label class="sr-only" for="quiz-search">Cari kuis</label>
                    <input
                        id="quiz-search"
                        v-model="filters.search"
                        type="search"
                        class="min-h-11 rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="Cari judul atau deskripsi kuis…"
                    />
                    <label class="sr-only" for="quiz-category">Kategori</label>
                    <select
                        id="quiz-category"
                        v-model="filters.category"
                        class="min-h-11 rounded-xl border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"
                    >
                        <option value="">Semua kategori</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="String(category.id)"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <button
                        type="submit"
                        class="min-h-11 rounded-xl bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-hover"
                    >
                        Cari
                    </button>
                    <button
                        type="button"
                        class="min-h-11 rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 hover:bg-slate-50"
                        @click="clearFilters"
                    >
                        Reset
                    </button>
                </form>
            </section>

            <section data-motion-item class="space-y-4">
                <div
                    v-if="isLoading"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                    aria-busy="true"
                    aria-label="Memuat kuis..."
                >
                    <div
                        v-for="n in 6"
                        :key="n"
                        class="animate-pulse rounded-2xl bg-white p-5 shadow-figma"
                    >
                        <div class="h-3 w-20 rounded-full bg-slate-200"></div>
                        <div
                            class="mt-3 h-5 w-3/4 rounded-full bg-slate-200"
                        ></div>
                        <div
                            class="mt-2 h-4 w-full rounded-full bg-slate-100"
                        ></div>
                        <div
                            class="mt-4 h-10 w-full rounded-xl bg-slate-100"
                        ></div>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-600">
                        Menampilkan {{ quizzes.from ?? 0 }}–{{
                            quizzes.to ?? 0
                        }}
                        dari {{ quizzes.total }} kuis
                    </p>
                </div>

                <div
                    v-if="!isLoading && quizzes.data.length"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <QuizCard
                        v-for="quiz in quizzes.data"
                        :key="quiz.id"
                        :title="quiz.title"
                        :description="quiz.description"
                        :questions-count="quiz.questions_count"
                        :status="quiz.category?.name ?? 'Umum'"
                        :state="
                            quiz.my_attempts_count === 0
                                ? 'new'
                                : canStart(quiz)
                                  ? 'retry'
                                  : 'done'
                        "
                        :score="quiz.best_score"
                    >
                        <template #actions>
                            <div
                                class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500"
                            >
                                <span
                                    >{{ statusLabel(quiz) }} ·
                                    {{ attemptLabel(quiz) }}</span
                                >
                                <span v-if="quiz.deadline_at"
                                    >Deadline
                                    {{
                                        dateFormatter.format(
                                            new Date(quiz.deadline_at),
                                        )
                                    }}</span
                                >
                                <span v-if="quiz.latest_score !== null"
                                    >Skor terakhir:
                                    {{ quiz.latest_score }}</span
                                >
                                <span v-if="quiz.best_score !== null"
                                    >Skor terbaik: {{ quiz.best_score }}</span
                                >
                            </div>
                            <div class="mt-4">
                                <button
                                    type="button"
                                    class="min-h-10 w-full rounded-xl bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-slate-300"
                                    :disabled="!canStart(quiz)"
                                    @click="startQuiz(quiz)"
                                >
                                    {{
                                        canStart(quiz)
                                            ? quiz.my_attempts_count
                                                ? 'Ulangi kuis'
                                                : 'Mulai kuis'
                                            : 'Attempt tidak tersedia'
                                    }}
                                </button>
                            </div>
                        </template>
                    </QuizCard>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-figma"
                >
                    <p class="text-base font-bold text-slate-900">
                        Kuis tidak ditemukan
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Coba kata kunci lain atau reset filter kategori.
                    </p>
                    <button
                        type="button"
                        class="mt-5 rounded-xl bg-brand-primary px-5 py-2.5 text-sm font-bold text-white"
                        @click="clearFilters"
                    >
                        Reset filter
                    </button>
                </div>

                <nav
                    v-if="quizzes.links.length > 3"
                    class="flex flex-wrap gap-2"
                    aria-label="Pagination katalog kuis"
                >
                    <Link
                        v-for="link in quizzes.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded-lg border px-3 py-2 text-sm font-semibold"
                        :class="[
                            link.active
                                ? 'border-brand-primary bg-brand-primary text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                </nav>
            </section>
        </main>
    </AuthenticatedLayout>
</template>

