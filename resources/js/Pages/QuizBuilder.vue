<script setup lang="ts">
import QuizListSkeleton from '@/Components/QuizListSkeleton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import gsap from 'gsap';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

type Question = {
    id: number;
    prompt: string;
    type: string;
    points: number;
    category?: { name: string; theme_key?: string | null } | null;
};
type Quiz = {
    id: number;
    creator_id?: number;
    title: string;
    description?: string | null;
    status: string;
    visibility: string;
    category_id?: number | null;
    category?: { name: string; theme_key?: string | null } | null;
    max_attempts?: number | null;
    allow_retry?: boolean;
    deadline_at?: string | null;
    show_explanations: boolean;
    questions: Question[];
    questions_count: number;
    collaborators?: { id: number; name: string }[];
};

const props = defineProps<{
    quizzes: Quiz[];
    questions: Question[];
    categories: { id: number; name: string }[];
    members: { id: number; name: string; role: string }[];
    filters?: { search?: string; status?: string; category?: number | string };
}>();
const createForm = useForm({ title: '' });
const loadingQuizzes = ref(false);
const showDetails = ref(false);
const pendingDeleteQuiz = ref<Quiz | null>(null);
const detailMode = ref<'read' | 'manage'>('read');
const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const filters = ref({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    category: props.filters?.category ? String(props.filters.category) : '',
});
const openFilter = ref<'status' | 'category' | null>(null);
const openDetailDropdown = ref<
    'category' | 'visibility' | 'collaborator' | null
>(null);
const statusOptions = [
    { value: '', label: 'Semua status' },
    { value: 'draft', label: 'Draft' },
    { value: 'pending_moderation', label: 'Menunggu moderasi' },
    { value: 'published', label: 'Published' },
    { value: 'rejected', label: 'Ditolak' },
    { value: 'archived', label: 'Diarsipkan' },
];
const categoryOptions = computed(() => [
    { value: '', label: 'Semua kategori' },
    ...props.categories.map((category) => ({
        value: String(category.id),
        label: category.name,
    })),
]);
const visibilityOptions = [
    { value: 'private', label: 'Private' },
    { value: 'organization', label: 'Organisasi' },
    { value: 'public', label: 'Public' },
];
const selectedQuizId = ref<number | null>(props.quizzes[0]?.id ?? null);
const selectedQuestionIds = ref<number[]>([]);
const metadata = useForm({
    title: '',
    description: '',
    visibility: 'organization',
    category_id: '',
    max_attempts: '',
    allow_retry: false,
    deadline_at: '',
    show_explanations: false,
});

const selectedQuiz = computed(
    () =>
        props.quizzes.find((quiz) => quiz.id === selectedQuizId.value) ?? null,
);
const collaboratorOptions = computed(() =>
    props.members.filter(
        (member) =>
            member.role === 'creator' &&
            member.id !== selectedQuiz.value?.creator_id &&
            !(selectedQuiz.value?.collaborators ?? []).some(
                (collaborator) => collaborator.id === member.id,
            ),
    ),
);
const themeClass = computed(() => {
    const theme = selectedQuiz.value?.category?.theme_key;
    return theme === 'ocean'
        ? 'ring-2 ring-sky-200'
        : theme === 'forest'
          ? 'ring-2 ring-brand-secondary'
          : theme === 'sunset'
            ? 'ring-2 ring-orange-200'
            : '';
});

const selectedQuestions = computed(() =>
    selectedQuestionIds.value
        .map((id) => props.questions.find((question) => question.id === id))
        .filter((question): question is Question => Boolean(question)),
);
const fieldClass =
    'mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm shadow-none transition-[border-color,box-shadow,transform] duration-200 ease-out focus:-translate-y-px focus:border-brand-secondary focus:ring-brand-secondary';

function selectQuiz(quiz: Quiz): void {
    selectedQuizId.value = quiz.id;
    selectedQuestionIds.value = quiz.questions.map((question) => question.id);
    metadata.title = quiz.title;
    metadata.description = quiz.description ?? '';
    metadata.visibility = quiz.visibility;
    metadata.category_id = quiz.category_id ? String(quiz.category_id) : '';
    metadata.max_attempts = quiz.max_attempts ? String(quiz.max_attempts) : '';
    metadata.allow_retry = quiz.allow_retry ?? false;
    metadata.deadline_at = quiz.deadline_at?.slice(0, 16) ?? '';
    metadata.show_explanations = quiz.show_explanations;
}

if (selectedQuiz.value) selectQuiz(selectedQuiz.value);

function applyFilters(): void {
    loadingQuizzes.value = true;
    router.get(
        route('quizzes.index'),
        {
            search: filters.value.search || undefined,
            status: filters.value.status || undefined,
            category: filters.value.category || undefined,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loadingQuizzes.value = false;
            },
        },
    );
}

function clearFilters(): void {
    filters.value = { search: '', status: '', category: '' };
    applyFilters();
}

function selectFilter(
    filter: 'status' | 'category',
    value: string,
    details: HTMLDetailsElement,
): void {
    filters.value[filter] = value;
    details.open = false;
    openFilter.value = null;
}

function selectFilterFromEvent(
    filter: 'status' | 'category',
    value: string,
    event: Event,
): void {
    const button = event.currentTarget;
    if (!(button instanceof HTMLElement)) return;

    const details = button.closest('details');
    if (!(details instanceof HTMLDetailsElement)) return;

    selectFilter(filter, value, details);
}

function syncFilterDropdown(filter: 'status' | 'category', event: Event): void {
    const details = event.currentTarget as HTMLDetailsElement;
    openFilter.value = details.open ? filter : null;
}

function syncDetailDropdown(
    filter: 'category' | 'visibility' | 'collaborator',
    event: Event,
): void {
    const details = event.currentTarget as HTMLDetailsElement;
    openDetailDropdown.value = details.open ? filter : null;
}

function selectDetailDropdown(
    filter: 'category' | 'visibility',
    value: string,
    details: HTMLDetailsElement,
): void {
    metadata[filter === 'category' ? 'category_id' : 'visibility'] = value;
    details.open = false;
    openDetailDropdown.value = null;
}

function selectDetailDropdownFromEvent(
    filter: 'category' | 'visibility',
    value: string,
    event: Event,
): void {
    const button = event.currentTarget;
    if (!(button instanceof HTMLElement)) return;

    const details = button.closest('details');
    if (!(details instanceof HTMLDetailsElement)) return;

    selectDetailDropdown(filter, value, details);
}

function openQuizDetails(quiz: Quiz): void {
    selectQuiz(quiz);
    detailMode.value = 'read';
    showDetails.value = true;
}

function openQuizEditor(quiz: Quiz): void {
    selectQuiz(quiz);
    detailMode.value = 'manage';
    showDetails.value = true;
}

function closeQuizDetails(): void {
    showDetails.value = false;
    detailMode.value = 'read';
}

function parseDeadline(value?: string | null): Date | null {
    if (!value) return null;

    const localValue = value.slice(0, 19).replace(' ', 'T');
    const deadline = new Date(localValue);

    return Number.isNaN(deadline.getTime()) ? null : deadline;
}

function canDeleteQuiz(quiz: Quiz): boolean {
    const deadline = parseDeadline(quiz.deadline_at);

    return (
        quiz.status === 'published' &&
        deadline !== null &&
        deadline < new Date()
    );
}

function formatDeadline(value?: string | null): string {
    const deadline = parseDeadline(value);
    if (!deadline) return 'Tanpa deadline';

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(deadline);
}

function deleteQuiz(quiz: Quiz): void {
    if (!canDeleteQuiz(quiz)) return;
    pendingDeleteQuiz.value = quiz;
}

function cancelDeleteQuiz(): void {
    pendingDeleteQuiz.value = null;
}

function confirmDeleteQuiz(): void {
    const quiz = pendingDeleteQuiz.value;
    if (!quiz || !canDeleteQuiz(quiz)) return;

    pendingDeleteQuiz.value = null;
    router.delete(route('quizzes.destroy', quiz.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedQuizId.value === quiz.id) closeQuizDetails();
        },
    });
}

watch(showDetails, async (isOpen) => {
    if (!isOpen || prefersReducedMotion()) return;
    await nextTick();
    gsap.from('[data-quiz-detail]', {
        duration: 0.45,
        opacity: 0,
        y: 18,
        ease: 'power2.out',
    });
});

onMounted(async () => {
    if (prefersReducedMotion()) return;
    await nextTick();
    gsap.from('[data-quiz-motion]', {
        duration: 0.5,
        opacity: 0,
        y: 16,
        stagger: 0.08,
        ease: 'power2.out',
    });
});
function createQuiz(): void {
    if (!prefersReducedMotion()) {
        gsap.to('[data-quiz-create-button]', {
            duration: 0.1,
            scale: 0.96,
            yoyo: true,
            repeat: 1,
            ease: 'power1.inOut',
        });
    }

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

const showUnsavedOrderModal = ref(false);
const isSavingAndPublishing = ref(false);

const hasUnsavedQuestions = computed(() => {
    if (!selectedQuiz.value) return false;
    const savedIds = (selectedQuiz.value.questions ?? []).map((q) => q.id);
    const currentIds = selectedQuestionIds.value;
    if (savedIds.length !== currentIds.length) return true;
    return currentIds.some((id, index) => id !== savedIds[index]);
});

const hasSavedQuestionsInDb = computed(() => {
    return (selectedQuiz.value?.questions?.length ?? 0) > 0;
});

function handlePublishClick(): void {
    if (!selectedQuiz.value) return;
    if (selectedQuestionIds.value.length === 0) return;

    if (hasUnsavedQuestions.value || !hasSavedQuestionsInDb.value) {
        showUnsavedOrderModal.value = true;
        return;
    }

    publishDirectly();
}

function publishDirectly(): void {
    if (!selectedQuiz.value) return;
    showUnsavedOrderModal.value = false;
    useForm({}).post(route('quizzes.publish', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}

function saveOrderAndPublish(): void {
    if (!selectedQuiz.value || selectedQuestionIds.value.length === 0) return;
    isSavingAndPublishing.value = true;
    router.put(
        route('quizzes.questions.sync', selectedQuiz.value.id),
        { question_ids: selectedQuestionIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSavingAndPublishing.value = false;
                showUnsavedOrderModal.value = false;
                publishDirectly();
            },
            onError: () => {
                isSavingAndPublishing.value = false;
            },
        },
    );
}

function cancelUnsavedOrderModal(): void {
    if (isSavingAndPublishing.value) return;
    showUnsavedOrderModal.value = false;
}

function publish(): void {
    handlePublishClick();
}

function cloneQuiz(): void {
    if (!selectedQuiz.value) return;
    useForm({}).post(route('quizzes.clone', selectedQuiz.value.id), {
        preserveScroll: true,
    });
}

function addCollaborator(userId: number): void {
    if (!selectedQuiz.value || !userId) return;
    useForm({ user_id: userId }).post(
        route('quizzes.collaborators.store', selectedQuiz.value.id),
        { preserveScroll: true },
    );
}

function removeCollaborator(userId: number): void {
    if (!selectedQuiz.value) return;
    useForm({}).delete(
        route('quizzes.collaborators.destroy', [selectedQuiz.value.id, userId]),
        { preserveScroll: true },
    );
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
        <main class="relative min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="pointer-events-none fixed inset-0 -z-0 bg-brand-accent/30"
                aria-hidden="true"
            ></div>
            <div class="relative z-10 mx-auto max-w-[90rem] space-y-6">
                <header
                    data-quiz-motion
                    class="flex flex-col gap-5 rounded-2xl bg-brand-primary p-6 text-white shadow-figma sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-secondary"
                        >
                            Creator workspace
                        </p>
                        <h1
                            class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                        >
                            Quiz Builder
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-white/75"
                        >
                            Susun quiz, kelola soal, dan siapkan pengalaman
                            belajar yang lebih terarah.
                        </p>
                    </div>
                    <form
                        class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-72"
                        @submit.prevent="createQuiz"
                    >
                        <label
                            for="new-quiz"
                            class="text-xs font-bold text-white/75"
                            >Buat draft baru</label
                        >
                        <div class="flex gap-2">
                            <input
                                id="new-quiz"
                                v-model="createForm.title"
                                class="min-h-11 min-w-0 flex-1 rounded-xl border-0 bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-secondary"
                                placeholder="Contoh: Kuis Ekosistem"
                                required
                            />
                            <button
                                type="submit"
                                data-quiz-create-button
                                class="min-h-11 rounded-xl bg-brand-secondary px-4 text-sm font-black text-white shadow-sm transition hover:brightness-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="createForm.processing"
                            >
                                Buat
                            </button>
                        </div>
                        <p
                            v-if="createForm.errors.title"
                            class="text-xs font-semibold text-red-200"
                        >
                            {{ createForm.errors.title }}
                        </p>
                    </form>
                </header>

                <section
                    v-if="!showDetails"
                    data-quiz-motion
                    class="overflow-hidden rounded-2xl border border-brand-primary/20 bg-white shadow-figma"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6"
                    >
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.14em] text-brand-primary"
                            >
                                Workspace quiz
                            </p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">
                                Daftar quiz
                            </h2>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                            >
                                Pilih quiz untuk mulai mengedit. Klik salah satu
                                quiz di daftar untuk membuka detail, mengatur
                                soal, dan mempublikasikannya.
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-black text-white"
                            >{{ quizzes.length }} quiz</span
                        >
                    </div>
                    <div
                        class="border-b border-brand-primary/20 bg-brand-primary/5 px-5 py-4 sm:px-6"
                    >
                        <form
                            class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]"
                            @submit.prevent="applyFilters"
                        >
                            <label for="quiz-search" class="sr-only"
                                >Cari quiz</label
                            >
                            <input
                                id="quiz-search"
                                v-model="filters.search"
                                class="min-h-11 w-full rounded-xl border-slate-300 bg-white text-sm transition-[border-color,box-shadow,transform] duration-200 ease-out focus:-translate-y-px focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Cari judul quiz"
                            />
                            <details
                                class="quiz-filter-dropdown relative"
                                @toggle="syncFilterDropdown('status', $event)"
                            >
                                <summary
                                    class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 transition-colors duration-200 hover:border-brand-primary focus:outline-none [&::-webkit-details-marker]:hidden"
                                    aria-label="Filter status"
                                >
                                    <span>{{
                                        statusOptions.find(
                                            (option) =>
                                                option.value === filters.status,
                                        )?.label ?? 'Semua status'
                                    }}</span>
                                    <span aria-hidden="true">⌄</span>
                                </summary>
                                <div
                                    v-if="openFilter === 'status'"
                                    class="quiz-filter-panel absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-brand-primary/20 bg-white p-1 shadow-lg"
                                >
                                    <button
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        type="button"
                                        class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-brand-accent"
                                        :class="
                                            filters.status === option.value
                                                ? 'bg-brand-primary/10 font-bold text-brand-primary'
                                                : ''
                                        "
                                        @click="
                                            selectFilterFromEvent(
                                                'status',
                                                option.value,
                                                $event,
                                            )
                                        "
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </details>
                            <details
                                class="quiz-filter-dropdown relative"
                                @toggle="syncFilterDropdown('category', $event)"
                            >
                                <summary
                                    class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 transition-colors duration-200 hover:border-brand-primary focus:outline-none [&::-webkit-details-marker]:hidden"
                                    aria-label="Filter kategori"
                                >
                                    <span>{{
                                        categoryOptions.find(
                                            (option) =>
                                                option.value ===
                                                filters.category,
                                        )?.label ?? 'Semua kategori'
                                    }}</span>
                                    <span aria-hidden="true">⌄</span>
                                </summary>
                                <div
                                    v-if="openFilter === 'category'"
                                    class="quiz-filter-panel absolute left-0 right-0 top-full z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-brand-primary/20 bg-white p-1 shadow-lg"
                                >
                                    <button
                                        v-for="option in categoryOptions"
                                        :key="option.value"
                                        type="button"
                                        class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-brand-accent"
                                        :class="
                                            filters.category === option.value
                                                ? 'bg-brand-primary/10 font-bold text-brand-primary'
                                                : ''
                                        "
                                        @click="
                                            selectFilterFromEvent(
                                                'category',
                                                option.value,
                                                $event,
                                            )
                                        "
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </details>
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="min-h-11 flex-1 rounded-xl bg-brand-primary px-4 text-sm font-bold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary"
                                >
                                    Filter
                                </button>
                                <button
                                    type="button"
                                    class="min-h-11 rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white transition hover:brightness-105"
                                    @click="clearFilters"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                    <div v-if="loadingQuizzes" class="p-5">
                        <QuizListSkeleton :count="4" />
                    </div>
                    <div v-else-if="quizzes.length" class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead
                                class="bg-white text-xs font-black uppercase tracking-wide text-brand-primary"
                            >
                                <tr>
                                    <th class="px-5 py-3 sm:px-6">Quiz</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Soal</th>
                                    <th class="px-4 py-3">Akses</th>
                                    <th class="px-5 py-3 text-center sm:px-6">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="quiz in quizzes"
                                    :key="quiz.id"
                                    class="cursor-pointer transition hover:bg-brand-accent/60"
                                    :class="
                                        quiz.id === selectedQuizId
                                            ? 'bg-brand-secondary/10'
                                            : 'bg-white'
                                    "
                                    @click="selectQuiz(quiz)"
                                >
                                    <td class="max-w-[22rem] px-5 py-4 sm:px-6">
                                        <p
                                            class="truncate text-sm font-black text-slate-900"
                                        >
                                            {{ quiz.title }}
                                        </p>
                                        <p
                                            class="mt-1 truncate text-xs text-slate-500"
                                        >
                                            {{
                                                quiz.category?.name ??
                                                'Tanpa kategori'
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-black"
                                            :class="
                                                quiz.status === 'published'
                                                    ? 'bg-brand-secondary text-white'
                                                    : quiz.status === 'rejected'
                                                      ? 'bg-red-100 text-red-700'
                                                      : 'bg-slate-100 text-slate-700'
                                            "
                                            >{{ quiz.status }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-4 py-4 text-sm font-bold text-slate-700"
                                    >
                                        {{ quiz.questions_count }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-sm font-semibold capitalize text-slate-600"
                                    >
                                        {{ quiz.visibility }}
                                    </td>
                                    <td class="px-5 py-4 text-center sm:px-6">
                                        <div class="flex justify-center gap-2">
                                            <button
                                                type="button"
                                                class="rounded-lg border border-brand-primary px-3 py-2 text-xs font-black text-brand-primary transition hover:bg-brand-primary hover:text-white"
                                                @click.stop="
                                                    openQuizDetails(quiz)
                                                "
                                            >
                                                Detail
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg bg-brand-primary px-3 py-2 text-xs font-black text-white transition hover:bg-brand-hover"
                                                @click.stop="
                                                    openQuizEditor(quiz)
                                                "
                                            >
                                                Kelola
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-lg px-3 py-2 text-xs font-black transition"
                                                :class="
                                                    canDeleteQuiz(quiz)
                                                        ? 'bg-red-600 text-white hover:bg-red-700'
                                                        : 'cursor-not-allowed bg-slate-300 text-white/60'
                                                "
                                                :disabled="!canDeleteQuiz(quiz)"
                                                :aria-label="`Hapus ${quiz.title}`"
                                                @click.stop="deleteQuiz(quiz)"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p
                        v-else
                        class="px-5 py-12 text-center text-sm text-slate-500 sm:px-6"
                    >
                        Belum ada quiz. Buat draft dari header halaman untuk
                        mulai menyusun soal.
                    </p>
                </section>

                <section
                    v-if="showDetails && selectedQuiz && detailMode === 'read'"
                    data-quiz-detail
                    class="space-y-5"
                    :class="themeClass"
                >
                    <header
                        class="flex flex-col gap-4 rounded-2xl bg-brand-primary p-5 text-white shadow-figma sm:flex-row sm:items-center sm:justify-between sm:p-6"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/25 text-white transition hover:bg-white/10"
                                aria-label="Kembali ke daftar quiz"
                                @click="closeQuizDetails"
                            >
                                <span
                                    aria-hidden="true"
                                    class="text-xl leading-none"
                                    >←</span
                                >
                            </button>
                            <div class="min-w-0">
                                <p
                                    class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-secondary"
                                >
                                    Detail quiz · hanya baca
                                </p>
                                <h2
                                    class="mt-2 truncate text-xl font-black sm:text-2xl"
                                >
                                    {{ selectedQuiz.title }}
                                </h2>
                                <p class="mt-1 text-sm text-white/75">
                                    {{ selectedQuiz.questions_count }} soal ·
                                    {{ selectedQuiz.visibility }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="min-h-11 rounded-xl bg-white px-4 text-sm font-black text-brand-primary transition hover:bg-brand-secondary hover:text-white"
                            @click="openQuizEditor(selectedQuiz)"
                        >
                            Kelola quiz
                        </button>
                    </header>

                    <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
                        <section
                            class="overflow-hidden rounded-2xl border border-brand-primary/20 bg-white shadow-figma"
                        >
                            <div
                                class="border-b border-slate-200 bg-brand-primary/5 px-5 py-4"
                            >
                                <p
                                    class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-primary"
                                >
                                    Ringkasan
                                </p>
                                <h3
                                    class="mt-1 text-lg font-black text-slate-950"
                                >
                                    Informasi quiz
                                </h3>
                            </div>
                            <dl class="divide-y divide-slate-100 px-5">
                                <div
                                    class="flex items-center justify-between gap-4 py-4"
                                >
                                    <dt class="text-sm text-slate-500">
                                        Status
                                    </dt>
                                    <dd
                                        class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-black text-white"
                                    >
                                        {{ selectedQuiz.status }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 py-4"
                                >
                                    <dt class="text-sm text-slate-500">
                                        Kategori
                                    </dt>
                                    <dd
                                        class="text-right text-sm font-bold text-slate-800"
                                    >
                                        {{
                                            selectedQuiz.category?.name ??
                                            'Tanpa kategori'
                                        }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 py-4"
                                >
                                    <dt class="text-sm text-slate-500">
                                        Akses
                                    </dt>
                                    <dd
                                        class="text-sm font-bold capitalize text-slate-800"
                                    >
                                        {{ selectedQuiz.visibility }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 py-4"
                                >
                                    <dt class="text-sm text-slate-500">
                                        Deadline
                                    </dt>
                                    <dd
                                        class="text-right text-sm font-bold text-slate-800"
                                    >
                                        {{
                                            formatDeadline(
                                                selectedQuiz.deadline_at,
                                            )
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section
                            class="overflow-hidden rounded-2xl border border-brand-primary/20 bg-white shadow-figma"
                        >
                            <div
                                class="flex items-center justify-between gap-3 border-b border-slate-200 bg-brand-primary/5 px-5 py-4"
                            >
                                <div>
                                    <p
                                        class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-primary"
                                    >
                                        Bank soal
                                    </p>
                                    <h3
                                        class="mt-1 text-lg font-black text-slate-950"
                                    >
                                        Daftar soal
                                    </h3>
                                </div>
                                <span
                                    class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-black text-white"
                                    >{{ selectedQuestions.length }} soal</span
                                >
                            </div>
                            <div
                                v-if="selectedQuestions.length"
                                class="divide-y divide-slate-100 px-5"
                            >
                                <article
                                    v-for="(
                                        question, index
                                    ) in selectedQuestions"
                                    :key="question.id"
                                    class="flex items-start gap-3 py-4"
                                >
                                    <span
                                        class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-primary text-xs font-black text-white"
                                        >{{ index + 1 }}</span
                                    >
                                    <div class="min-w-0">
                                        <p
                                            class="font-bold leading-6 text-slate-900"
                                        >
                                            {{ question.prompt }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ question.type }} ·
                                            {{ question.points }} poin
                                        </p>
                                    </div>
                                </article>
                            </div>
                            <p
                                v-else
                                class="m-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
                            >
                                Belum ada soal di quiz ini.
                            </p>
                        </section>
                    </div>
                </section>

                <section
                    v-if="
                        showDetails && selectedQuiz && detailMode === 'manage'
                    "
                    data-quiz-detail
                    class="space-y-5"
                    :class="themeClass"
                >
                    <header
                        class="flex flex-col gap-4 rounded-2xl bg-brand-primary p-5 text-white shadow-figma sm:flex-row sm:items-center sm:justify-between sm:p-6"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/25 text-white transition hover:bg-white/10"
                                aria-label="Kembali ke daftar quiz"
                                @click="closeQuizDetails"
                            >
                                <span
                                    aria-hidden="true"
                                    class="text-xl leading-none"
                                    >←</span
                                >
                            </button>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-secondary"
                                    >
                                        Quiz aktif
                                    </p>
                                    <span
                                        class="rounded-full bg-brand-secondary px-2.5 py-1 text-xs font-black text-white"
                                        >{{ selectedQuiz.status }}</span
                                    >
                                </div>
                                <h2
                                    class="mt-2 truncate text-xl font-black sm:text-2xl"
                                >
                                    {{ selectedQuiz.title }}
                                </h2>
                                <p class="mt-1 text-sm text-white/75">
                                    {{ selectedQuestionIds.length }} soal ·
                                    {{ metadata.visibility }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="min-h-11 rounded-xl bg-white px-4 text-sm font-black text-brand-primary transition hover:bg-brand-secondary hover:text-white"
                                @click="cloneQuiz"
                            >
                                Duplikat</button
                            ><button
                                type="button"
                                class="min-h-11 rounded-xl border border-white/25 px-4 text-sm font-bold text-white transition hover:bg-white/10"
                                @click="archiveQuiz"
                            >
                                Arsipkan
                            </button>
                        </div>
                    </header>

                    <div
                        class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(19rem,0.65fr)]"
                    >
                        <form
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                            @submit.prevent="saveMetadata"
                        >
                            <div
                                class="border-b border-slate-200 bg-brand-accent/70 px-5 py-4 sm:px-6"
                            >
                                <h2 class="text-base font-black text-slate-950">
                                    Detail quiz
                                </h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    Atur identitas, akses, dan batas pengerjaan.
                                </p>
                            </div>
                            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                                <label class="text-sm font-bold text-slate-800"
                                    >Judul<input
                                        v-model="metadata.title"
                                        :class="fieldClass"
                                        placeholder="Masukkan judul quiz"
                                        required
                                /></label>
                                <label class="text-sm font-bold text-slate-800"
                                    >Kategori
                                    <details
                                        class="quiz-filter-dropdown relative mt-1.5"
                                        @toggle="
                                            syncDetailDropdown(
                                                'category',
                                                $event,
                                            )
                                        "
                                    >
                                        <summary
                                            class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-700 transition-colors duration-200 hover:border-brand-secondary focus:outline-none [&::-webkit-details-marker]:hidden"
                                        >
                                            <span>{{
                                                categoryOptions.find(
                                                    (option) =>
                                                        option.value ===
                                                        metadata.category_id,
                                                )?.label ?? 'Tanpa kategori'
                                            }}</span>
                                            <span aria-hidden="true">⌄</span>
                                        </summary>
                                        <div
                                            v-if="
                                                openDetailDropdown ===
                                                'category'
                                            "
                                            class="quiz-filter-panel absolute left-0 right-0 top-full z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-brand-primary/20 bg-white p-1 shadow-lg"
                                        >
                                            <button
                                                v-for="option in categoryOptions"
                                                :key="option.value"
                                                type="button"
                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm font-normal text-slate-700 transition-colors hover:bg-brand-accent"
                                                :class="
                                                    metadata.category_id ===
                                                    option.value
                                                        ? 'bg-brand-primary/10 font-bold text-brand-primary'
                                                        : ''
                                                "
                                                @click="
                                                    selectDetailDropdownFromEvent(
                                                        'category',
                                                        option.value,
                                                        $event,
                                                    )
                                                "
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </details>
                                </label>
                                <label
                                    class="text-sm font-bold text-slate-800 sm:col-span-2"
                                    >Deskripsi<textarea
                                        v-model="metadata.description"
                                        :class="fieldClass"
                                        class="min-h-24 resize-y"
                                        placeholder="Masukkan deskripsi quiz"
                                    />
                                </label>
                                <label class="text-sm font-bold text-slate-800"
                                    >Visibilitas
                                    <details
                                        class="quiz-filter-dropdown relative mt-1.5"
                                        @toggle="
                                            syncDetailDropdown(
                                                'visibility',
                                                $event,
                                            )
                                        "
                                    >
                                        <summary
                                            class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-700 transition-colors duration-200 hover:border-brand-secondary focus:outline-none [&::-webkit-details-marker]:hidden"
                                        >
                                            <span>{{
                                                visibilityOptions.find(
                                                    (option) =>
                                                        option.value ===
                                                        metadata.visibility,
                                                )?.label ?? 'Private'
                                            }}</span>
                                            <span aria-hidden="true">⌄</span>
                                        </summary>
                                        <div
                                            v-if="
                                                openDetailDropdown ===
                                                'visibility'
                                            "
                                            class="quiz-filter-panel absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-brand-primary/20 bg-white p-1 shadow-lg"
                                        >
                                            <button
                                                v-for="option in visibilityOptions"
                                                :key="option.value"
                                                type="button"
                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm font-normal text-slate-700 transition-colors hover:bg-brand-accent"
                                                :class="
                                                    metadata.visibility ===
                                                    option.value
                                                        ? 'bg-brand-primary/10 font-bold text-brand-primary'
                                                        : ''
                                                "
                                                @click="
                                                    selectDetailDropdownFromEvent(
                                                        'visibility',
                                                        option.value,
                                                        $event,
                                                    )
                                                "
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </details>
                                </label>
                                <label class="text-sm font-bold text-slate-800"
                                    >Maksimal percobaan<input
                                        v-model="metadata.max_attempts"
                                        :class="fieldClass"
                                        type="number"
                                        min="1"
                                        placeholder="Tanpa batas"
                                /></label>
                                <label
                                    class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:border-brand-secondary hover:bg-brand-secondary/10"
                                    ><input
                                        v-model="metadata.allow_retry"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-slate-300 text-brand-secondary focus:ring-brand-secondary"
                                    />Izinkan peserta mengulang kuis</label
                                >
                                <label class="text-sm font-bold text-slate-800"
                                    >Deadline<input
                                        v-model="metadata.deadline_at"
                                        :class="fieldClass"
                                        class="quiz-deadline-input"
                                        type="datetime-local"
                                /></label>
                                <label
                                    class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:border-brand-secondary hover:bg-brand-secondary/10"
                                    ><input
                                        v-model="metadata.show_explanations"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-slate-300 text-brand-secondary focus:ring-brand-secondary"
                                    />Tampilkan pembahasan</label
                                >
                            </div>
                            <div
                                class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6"
                            >
                                <button
                                    type="submit"
                                    class="min-h-11 rounded-xl bg-brand-secondary px-5 text-sm font-black text-white transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="metadata.processing"
                                >
                                    Simpan detail
                                </button>
                            </div>
                        </form>

                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                        >
                            <div
                                class="border-b border-slate-200 bg-brand-accent/70 px-5 py-4"
                            >
                                <h2 class="text-base font-black text-slate-950">
                                    Co-creator
                                </h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    Kolaborator yang bisa membantu mengedit
                                    quiz.
                                </p>
                            </div>
                            <div class="p-5">
                                <label for="co-creator" class="sr-only"
                                    >Pilih co-creator</label
                                >
                                <details
                                    id="co-creator"
                                    class="quiz-filter-dropdown relative"
                                    @toggle="
                                        syncDetailDropdown(
                                            'collaborator',
                                            $event,
                                        )
                                    "
                                >
                                    <summary
                                        class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 transition-colors duration-200 hover:border-brand-primary focus:outline-none [&::-webkit-details-marker]:hidden"
                                    >
                                        <span>Tambah co-creator</span>
                                        <span aria-hidden="true">⌄</span>
                                    </summary>
                                    <div
                                        v-if="
                                            openDetailDropdown ===
                                            'collaborator'
                                        "
                                        class="quiz-filter-panel absolute left-0 right-0 top-full z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-brand-primary/20 bg-white p-1 shadow-lg"
                                    >
                                        <button
                                            v-for="member in collaboratorOptions"
                                            :key="member.id"
                                            type="button"
                                            class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-brand-accent"
                                            @click="
                                                addCollaborator(member.id);
                                                openDetailDropdown = null;
                                            "
                                        >
                                            {{ member.name }}
                                        </button>
                                        <p
                                            v-if="
                                                collaboratorOptions.length === 0
                                            "
                                            class="px-3 py-2 text-sm text-slate-500"
                                        >
                                            Tidak ada co-creator tersedia.
                                        </p>
                                    </div>
                                </details>
                                <div
                                    v-if="selectedQuiz.collaborators?.length"
                                    class="mt-4 space-y-2"
                                >
                                    <div
                                        v-for="collaborator in selectedQuiz.collaborators"
                                        :key="collaborator.id"
                                        class="flex items-center justify-between gap-3 rounded-xl bg-brand-accent px-3 py-2 text-sm font-bold text-slate-800"
                                    >
                                        <span class="truncate">{{
                                            collaborator.name
                                        }}</span
                                        ><button
                                            type="button"
                                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-slate-500 transition hover:bg-red-100 hover:text-red-700"
                                            :aria-label="`Hapus ${collaborator.name}`"
                                            @click="
                                                removeCollaborator(
                                                    collaborator.id,
                                                )
                                            "
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500"
                                >
                                    Belum ada co-creator.
                                </p>
                            </div>
                        </section>
                    </div>

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-brand-accent/70 px-5 py-4 sm:px-6"
                        >
                            <div>
                                <h2 class="text-base font-black text-slate-950">
                                    Susunan soal
                                </h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    Pilih soal, atur urutan, lalu publikasikan.
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-black text-white"
                                >{{ selectedQuestionIds.length }} dipilih</span
                            >
                        </div>
                        <div
                            class="grid xl:grid-cols-[minmax(0,1fr)_minmax(22rem,0.82fr)]"
                        >
                            <div
                                class="p-5 sm:p-6 xl:border-r xl:border-slate-200"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <h3 class="font-black text-slate-900">
                                        Bank soal
                                    </h3>
                                    <span
                                        class="text-xs font-semibold text-slate-500"
                                        >{{ questions.length }} tersedia</span
                                    >
                                </div>
                                <div
                                    v-if="questions.length"
                                    class="mt-4 max-h-[32rem] space-y-2 overflow-y-auto pr-1"
                                >
                                    <label
                                        v-for="question in questions"
                                        :key="question.id"
                                        class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-secondary hover:bg-brand-secondary/10 has-[:checked]:border-brand-secondary has-[:checked]:bg-brand-secondary/15"
                                        ><input
                                            v-model="selectedQuestionIds"
                                            :value="question.id"
                                            type="checkbox"
                                            class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-brand-secondary focus:ring-brand-secondary"
                                        /><span class="min-w-0"
                                            ><span
                                                class="block font-bold leading-6 text-slate-900"
                                                >{{ question.prompt }}</span
                                            ><span
                                                class="mt-1 block text-xs text-slate-500"
                                                >{{ question.type }} ·
                                                {{ question.points }} poin</span
                                            ></span
                                        ></label
                                    >
                                </div>
                                <p
                                    v-else
                                    class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    Belum ada soal di bank.
                                </p>
                                <button
                                    type="button"
                                    class="mt-4 min-h-11 w-full rounded-xl border border-brand-primary px-4 text-sm font-black text-brand-primary transition hover:bg-brand-primary hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="selectedQuestionIds.length === 0"
                                    @click="syncQuestions"
                                >
                                    Simpan susunan soal
                                </button>
                            </div>
                            <div
                                class="border-t border-slate-200 bg-slate-50/70 p-5 sm:p-6 xl:border-t-0"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <h3 class="font-black text-slate-900">
                                        Urutan soal
                                    </h3>
                                    <span
                                        class="text-xs font-semibold text-slate-500"
                                        >Gunakan tombol panah</span
                                    >
                                </div>
                                <div
                                    v-if="selectedQuestions.length"
                                    class="mt-4 space-y-2"
                                >
                                    <article
                                        v-for="(
                                            question, index
                                        ) in selectedQuestions"
                                        :key="question.id"
                                        class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <span
                                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-primary text-xs font-black text-white"
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
                                                class="grid h-9 w-9 place-items-center rounded-lg text-slate-600 transition hover:bg-brand-accent disabled:cursor-not-allowed disabled:opacity-30"
                                                :disabled="index === 0"
                                                :aria-label="`Naikkan soal ${index + 1}`"
                                                @click="moveQuestion(index, -1)"
                                            >
                                                ↑</button
                                            ><button
                                                type="button"
                                                class="grid h-9 w-9 place-items-center rounded-lg text-slate-600 transition hover:bg-brand-accent disabled:cursor-not-allowed disabled:opacity-30"
                                                :disabled="
                                                    index ===
                                                    selectedQuestions.length - 1
                                                "
                                                :aria-label="`Turunkan soal ${index + 1}`"
                                                @click="moveQuestion(index, 1)"
                                            >
                                                ↓
                                            </button>
                                        </div>
                                    </article>
                                </div>
                                <p
                                    v-else
                                    class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    Pilih soal dari bank di sebelah kiri.
                                </p>
                                <div
                                    v-if="hasUnsavedQuestions || !hasSavedQuestionsInDb"
                                    class="mt-4 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs font-bold text-amber-800"
                                >
                                    <svg
                                        class="h-4 w-4 shrink-0 text-amber-600"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                        />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                    </svg>
                                    <span>
                                        {{ !hasSavedQuestionsInDb ? 'Soal belum disimpan ke kuis. Simpan susunan soal sebelum publish.' : 'Perubahan susunan soal belum disimpan.' }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="min-h-11 w-full rounded-xl bg-brand-secondary px-4 text-sm font-black text-white transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="(hasUnsavedQuestions || !hasSavedQuestionsInDb) ? 'mt-3' : 'mt-4'"
                                    :disabled="selectedQuestionIds.length === 0"
                                    @click="publish"
                                >
                                    Publish quiz
                                </button>
                            </div>
                        </div>
                    </section>
                </section>
            </div>
        </main>
        <Teleport to="body">
            <Transition name="delete-confirm">
                <div
                    v-if="pendingDeleteQuiz"
                    class="fixed inset-0 z-[100] grid place-items-center bg-slate-500/35 px-4 backdrop-blur-sm"
                    role="presentation"
                    @click.self="cancelDeleteQuiz"
                >
                    <section
                        class="w-full max-w-lg overflow-hidden rounded-2xl border border-red-100 bg-white shadow-2xl"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="delete-quiz-title"
                    >
                        <div
                            class="flex items-start justify-between gap-4 border-b border-red-100 bg-red-50/80 px-6 py-5"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-600 text-white shadow-sm"
                                    aria-hidden="true"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v5M14 11v5" />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-extrabold uppercase tracking-[0.16em] text-red-600"
                                    >
                                        Konfirmasi hapus
                                    </p>
                                    <h2
                                        id="delete-quiz-title"
                                        class="mt-1 text-xl font-black text-slate-950"
                                    >
                                        Hapus quiz ini?
                                    </h2>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-red-100 hover:text-red-600"
                                aria-label="Tutup konfirmasi hapus"
                                @click="cancelDeleteQuiz"
                            >
                                <span class="text-xl leading-none"
                                    >&times;</span
                                >
                            </button>
                        </div>
                        <div class="px-6 py-5">
                            <p class="text-base leading-7 text-slate-600">
                                Quiz
                                <span class="font-black text-slate-900"
                                    >“{{ pendingDeleteQuiz.title }}”</span
                                >
                                akan dihapus dari daftar quiz.
                            </p>
                            <div
                                class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-800"
                            >
                                Quiz tidak akan tampil di workspace setelah
                                dihapus.
                            </div>
                        </div>
                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition hover:border-slate-300 hover:bg-slate-100"
                                @click="cancelDeleteQuiz"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="min-h-11 rounded-xl bg-red-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                @click="confirmDeleteQuiz"
                            >
                                Hapus quiz
                            </button>
                        </div>
                    </section>
                </div>
            </Transition>

            <Transition name="delete-confirm">
                <div
                    v-if="showUnsavedOrderModal && selectedQuiz"
                    class="fixed inset-0 z-[100] grid place-items-center bg-slate-500/35 px-4 backdrop-blur-sm"
                    role="presentation"
                    @click.self="cancelUnsavedOrderModal"
                >
                    <section
                        class="w-full max-w-lg overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-2xl"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="unsaved-order-modal-title"
                    >
                        <div
                            class="flex items-start justify-between gap-4 border-b border-amber-100 bg-amber-50/80 px-6 py-5"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-500 text-white shadow-sm"
                                    aria-hidden="true"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                        />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-extrabold uppercase tracking-[0.16em] text-amber-700"
                                    >
                                        {{ !hasSavedQuestionsInDb ? 'Soal Belum Disimpan' : 'Peringatan Susunan Soal' }}
                                    </p>
                                    <h2
                                        id="unsaved-order-modal-title"
                                        class="mt-1 text-xl font-black text-slate-950"
                                    >
                                        {{ !hasSavedQuestionsInDb ? 'Simpan soal terlebih dahulu' : 'Susunan soal belum disimpan' }}
                                    </h2>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-amber-100 hover:text-amber-700"
                                aria-label="Tutup peringatan susunan soal"
                                @click="cancelUnsavedOrderModal"
                            >
                                <span class="text-xl leading-none">&times;</span>
                            </button>
                        </div>
                        <div class="px-6 py-5">
                            <p class="text-base leading-7 text-slate-600">
                                <template v-if="!hasSavedQuestionsInDb">
                                    Quiz <span class="font-black text-slate-900">“{{ selectedQuiz.title }}”</span> belum memiliki soal yang tersimpan di server. Anda perlu menyimpan susunan soal yang telah dipilih agar kuis dapat dipublikasikan.
                                </template>
                                <template v-else>
                                    Anda telah mengubah susunan atau pemilihan soal untuk quiz <span class="font-black text-slate-900">“{{ selectedQuiz.title }}”</span>, tetapi belum menekan tombol <strong class="font-bold text-slate-900">“Simpan susunan soal”</strong>.
                                </template>
                            </p>
                            <div
                                class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-800"
                            >
                                <span v-if="!hasSavedQuestionsInDb">
                                    Klik tombol di bawah untuk otomatis menyimpan seluruh soal yang dipilih dan langsung mempublikasikan quiz.
                                </span>
                                <span v-else>
                                    Jika dipublikasikan sekarang tanpa menyimpan, perubahan urutan terbaru tidak akan diterapkan pada kuis yang dikerjakan peserta.
                                </span>
                            </div>
                        </div>
                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition hover:border-slate-300 hover:bg-slate-100"
                                :disabled="isSavingAndPublishing"
                                @click="cancelUnsavedOrderModal"
                            >
                                Kembali ke editor
                            </button>
                            <button
                                v-if="hasSavedQuestionsInDb"
                                type="button"
                                class="min-h-11 rounded-xl border border-amber-300 bg-amber-50 px-4 text-sm font-black text-amber-900 transition hover:bg-amber-100"
                                :disabled="isSavingAndPublishing"
                                @click="publishDirectly"
                            >
                                Tetap publish tanpa simpan
                            </button>
                            <button
                                type="button"
                                class="min-h-11 rounded-xl bg-brand-primary px-5 text-sm font-black text-white shadow-sm transition hover:brightness-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:opacity-50"
                                :disabled="isSavingAndPublishing"
                                @click="saveOrderAndPublish"
                            >
                                {{ isSavingAndPublishing ? 'Menyimpan & Menerbitkan...' : 'Simpan & Publish Quiz' }}
                            </button>
                        </div>
                    </section>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.quiz-filter-panel {
    animation: quiz-filter-reveal 180ms ease-out;
    transform-origin: top center;
}

.quiz-deadline-input {
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        transform 180ms ease;
}

@keyframes quiz-filter-reveal {
    from {
        opacity: 0;
        transform: translateY(-6px) scaleY(0.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scaleY(1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .quiz-filter-panel,
    .quiz-deadline-input {
        animation: none;
        transition: none;
    }
}

.delete-confirm-enter-active,
.delete-confirm-leave-active {
    transition: opacity 180ms ease;
}

.delete-confirm-enter-active section,
.delete-confirm-leave-active section {
    transition:
        transform 180ms ease,
        opacity 180ms ease;
}

.delete-confirm-enter-from,
.delete-confirm-leave-to {
    opacity: 0;
}

.delete-confirm-enter-from section,
.delete-confirm-leave-to section {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}
</style>
