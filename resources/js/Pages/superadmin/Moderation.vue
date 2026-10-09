<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type QuestionOption = {
    key: string;
    text: string;
    is_correct?: boolean;
};

type Question = {
    id: number;
    prompt: string;
    type: string;
    points: number;
    options?: QuestionOption[] | null;
};

type Quiz = {
    id: number;
    title: string;
    description?: string | null;
    status: 'pending_moderation' | 'published' | 'rejected' | string;
    creator?: { name: string };
    category?: { name: string };
    questions?: Question[];
    created_at?: string;
    updated_at?: string;
};

const props = defineProps<{
    quizzes: Quiz[];
    history?: Quiz[];
}>();

const activeTab = ref<'pending' | 'history'>('pending');
const quizzes = ref([...props.quizzes]);
const historyQuizzes = ref([...(props.history ?? [])]);
const expandedQuizzes = ref<Record<number, boolean>>({});

function toggleExpand(id: number) {
    expandedQuizzes.value[id] = !expandedQuizzes.value[id];
}

function decide(id: number, action: 'approve' | 'reject'): void {
    router.post(
        route(`admin.moderation.${action}`, id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                const item = quizzes.value.find((q) => q.id === id);
                if (item) {
                    item.status = action === 'approve' ? 'published' : 'rejected';
                    historyQuizzes.value.unshift(item);
                }
                quizzes.value = quizzes.value.filter((quiz) => quiz.id !== id);
            },
        },
    );
}

function formatType(type: string): string {
    if (type === 'multiple_choice') return 'Pilihan Ganda';
    if (type === 'true_false') return 'Benar / Salah';
    if (type === 'essay') return 'Esai';
    return type;
}
</script>

<template>
    <Head title="Moderasi Publik" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent/20 py-6 sm:py-8">
            <main class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Banner Top Header -->
                <section
                    class="relative overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma-sm sm:rounded-3xl sm:p-8"
                >
                    <div
                        class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5 blur-2xl"
                    />
                    <div
                        class="absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-brand-secondary/15 blur-2xl"
                    />

                    <div class="relative z-10 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/30 bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm"
                            >
                                <span class="h-2 w-2 rounded-full bg-brand-secondary" />
                                SUPER ADMIN • MODERASI
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/90 backdrop-blur-sm"
                            >
                                <AppIcon name="moderation" :size="14" class="text-brand-secondary" />
                                {{ quizzes.length }} Menunggu Review
                            </span>
                        </div>

                        <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                            Moderasi Kuis Publik
                        </h1>
                        <p class="max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">
                            Tinjau rincian soal kuis sebelum dipublikasikan atau lihat riwayat moderasi platform Kuesify.
                        </p>
                    </div>
                </section>

                <!-- Navigation Tabs: Menunggu vs Riwayat -->
                <div class="flex items-center gap-3 border-b border-slate-200/80 pb-1">
                    <button
                        type="button"
                        @click="activeTab = 'pending'"
                        class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-black transition"
                        :class="activeTab === 'pending' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        <AppIcon name="clock" :size="16" />
                        <span>Menunggu Review</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-black"
                            :class="activeTab === 'pending' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-100 text-slate-600'"
                        >
                            {{ quizzes.length }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'history'"
                        class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-black transition"
                        :class="activeTab === 'history' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        <AppIcon name="repeat" :size="16" />
                        <span>Riwayat Moderasi</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-black"
                            :class="activeTab === 'history' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-slate-100 text-slate-600'"
                        >
                            {{ historyQuizzes.length }}
                        </span>
                    </button>
                </div>

                <!-- TAB 1: Menunggu Review -->
                <section v-if="activeTab === 'pending'" class="space-y-4">
                    <div
                        v-if="quizzes.length === 0"
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                            <AppIcon name="check" :size="28" />
                        </div>
                        <h3 class="mt-3 text-base font-extrabold text-slate-800">
                            Tidak Ada Kuis Menunggu Moderasi
                        </h3>
                        <p class="mt-1 max-w-sm text-xs font-semibold text-slate-500">
                            Semua pengajuan kuis publik dari kreator telah disetujui atau diproses.
                        </p>
                    </div>

                    <article
                        v-for="quiz in quizzes"
                        :key="quiz.id"
                        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-5 shadow-figma-sm transition sm:p-6"
                    >
                        <!-- Quiz Header Info & Main Actions -->
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md bg-brand-primary/10 px-2.5 py-0.5 text-xs font-bold text-brand-primary">
                                        {{ quiz.category?.name ?? 'Tanpa Kategori' }}
                                    </span>
                                    <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                        {{ quiz.questions?.length ?? 0 }} Soal
                                    </span>
                                </div>

                                <h2 class="text-lg font-black text-slate-900 sm:text-xl">
                                    {{ quiz.title }}
                                </h2>

                                <p class="text-xs font-semibold text-slate-500">
                                    Dibuat oleh <strong class="text-slate-800 font-bold">{{ quiz.creator?.name ?? 'Kreator' }}</strong>
                                </p>

                                <p v-if="quiz.description" class="mt-2 text-xs leading-relaxed text-slate-600 sm:text-sm">
                                    {{ quiz.description }}
                                </p>
                            </div>

                            <!-- Primary Action Buttons -->
                            <div class="flex items-center gap-2 shrink-0 pt-2 sm:pt-0">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95"
                                    @click="decide(quiz.id, 'approve')"
                                >
                                    <AppIcon name="check" :size="15" />
                                    Setujui
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-rose-700 active:scale-95"
                                    @click="decide(quiz.id, 'reject')"
                                >
                                    <AppIcon name="trash" :size="15" />
                                    Tolak
                                </button>
                            </div>
                        </div>

                        <!-- Accordion Trigger Button -->
                        <div class="mt-4 border-t border-slate-100 pt-3 flex items-center justify-between">
                            <button
                                type="button"
                                @click="toggleExpand(quiz.id)"
                                class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-primary hover:text-brand-primary/80 transition"
                            >
                                <AppIcon :name="expandedQuizzes[quiz.id] ? 'chevronUp' : 'chevronDown'" :size="16" />
                                <span>{{ expandedQuizzes[quiz.id] ? 'Sembunyikan Rincian Soal' : 'Lihat Rincian Soal & Jawaban' }}</span>
                            </button>

                            <span class="text-[11px] font-bold text-slate-400">
                                ID Kuis: #{{ quiz.id }}
                            </span>
                        </div>

                        <!-- Questions Detailed Breakdown Section -->
                        <div v-if="expandedQuizzes[quiz.id]" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">
                                Daftar Soal ({{ quiz.questions?.length ?? 0 }})
                            </h3>

                            <div v-if="!quiz.questions || quiz.questions.length === 0" class="text-xs text-slate-400 italic">
                                Belum ada soal disematkan pada kuis ini.
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="(q, idx) in quiz.questions"
                                    :key="q.id"
                                    class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs space-y-2"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-primary text-[10px] font-extrabold text-white">
                                                {{ idx + 1 }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-800">
                                                {{ q.prompt }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-bold">
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">
                                                {{ formatType(q.type) }}
                                            </span>
                                            <span class="rounded-md bg-brand-secondary/20 px-2 py-0.5 text-brand-dark">
                                                {{ q.points }} Poin
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Options Breakdown if Available -->
                                    <div v-if="q.options && q.options.length > 0" class="pl-7 grid gap-1.5 sm:grid-cols-2">
                                        <div
                                            v-for="opt in q.options"
                                            :key="opt.key"
                                            class="flex items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs font-semibold"
                                            :class="opt.is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-900 font-bold' : 'border-slate-100 bg-slate-50 text-slate-600'"
                                        >
                                            <span
                                                class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[9px] font-black"
                                                :class="opt.is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'"
                                            >
                                                {{ opt.key }}
                                            </span>
                                            <span class="truncate">{{ opt.text }}</span>
                                            <span v-if="opt.is_correct" class="ml-auto text-[10px] font-black text-emerald-700">✓ Benar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <!-- TAB 2: Riwayat Moderasi -->
                <section v-else-if="activeTab === 'history'" class="space-y-4">
                    <div
                        v-if="historyQuizzes.length === 0"
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <AppIcon name="repeat" :size="28" />
                        </div>
                        <h3 class="mt-3 text-base font-extrabold text-slate-800">
                            Belum Ada Riwayat Moderasi
                        </h3>
                        <p class="mt-1 max-w-sm text-xs font-semibold text-slate-500">
                            Kuis yang telah disetujui atau ditolak akan dicatat di sini.
                        </p>
                    </div>

                    <article
                        v-for="quiz in historyQuizzes"
                        :key="quiz.id"
                        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs transition sm:p-6"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <!-- Status Badge -->
                                    <span
                                        v-if="quiz.status === 'published'"
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2.5 py-0.5 text-xs font-black text-emerald-800"
                                    >
                                        <AppIcon name="check" :size="13" />
                                        Disetujui (Publik)
                                    </span>
                                    <span
                                        v-else-if="quiz.status === 'rejected'"
                                        class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2.5 py-0.5 text-xs font-black text-rose-800"
                                    >
                                        <AppIcon name="trash" :size="13" />
                                        Ditolak
                                    </span>

                                    <span class="rounded-md bg-brand-primary/10 px-2.5 py-0.5 text-xs font-bold text-brand-primary">
                                        {{ quiz.category?.name ?? 'Tanpa Kategori' }}
                                    </span>
                                    <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                        {{ quiz.questions?.length ?? 0 }} Soal
                                    </span>
                                </div>

                                <h2 class="text-lg font-black text-slate-900 sm:text-xl">
                                    {{ quiz.title }}
                                </h2>

                                <p class="text-xs font-semibold text-slate-500">
                                    Dibuat oleh <strong class="text-slate-800 font-bold">{{ quiz.creator?.name ?? 'Kreator' }}</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Accordion Trigger Button -->
                        <div class="mt-4 border-t border-slate-100 pt-3 flex items-center justify-between">
                            <button
                                type="button"
                                @click="toggleExpand(quiz.id)"
                                class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-primary hover:text-brand-primary/80 transition"
                            >
                                <AppIcon :name="expandedQuizzes[quiz.id] ? 'chevronUp' : 'chevronDown'" :size="16" />
                                <span>{{ expandedQuizzes[quiz.id] ? 'Sembunyikan Rincian Soal' : 'Lihat Rincian Soal & Jawaban' }}</span>
                            </button>

                            <span class="text-[11px] font-bold text-slate-400">
                                ID Kuis: #{{ quiz.id }}
                            </span>
                        </div>

                        <!-- Questions Detailed Breakdown Section -->
                        <div v-if="expandedQuizzes[quiz.id]" class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">
                                Daftar Soal ({{ quiz.questions?.length ?? 0 }})
                            </h3>

                            <div v-if="!quiz.questions || quiz.questions.length === 0" class="text-xs text-slate-400 italic">
                                Belum ada soal disematkan pada kuis ini.
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="(q, idx) in quiz.questions"
                                    :key="q.id"
                                    class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs space-y-2"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-primary text-[10px] font-extrabold text-white">
                                                {{ idx + 1 }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-800">
                                                {{ q.prompt }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-bold">
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">
                                                {{ formatType(q.type) }}
                                            </span>
                                            <span class="rounded-md bg-brand-secondary/20 px-2 py-0.5 text-brand-dark">
                                                {{ q.points }} Poin
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Options Breakdown if Available -->
                                    <div v-if="q.options && q.options.length > 0" class="pl-7 grid gap-1.5 sm:grid-cols-2">
                                        <div
                                            v-for="opt in q.options"
                                            :key="opt.key"
                                            class="flex items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs font-semibold"
                                            :class="opt.is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-900 font-bold' : 'border-slate-100 bg-slate-50 text-slate-600'"
                                        >
                                            <span
                                                class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[9px] font-black"
                                                :class="opt.is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'"
                                            >
                                                {{ opt.key }}
                                            </span>
                                            <span class="truncate">{{ opt.text }}</span>
                                            <span v-if="opt.is_correct" class="ml-auto text-[10px] font-black text-emerald-700">✓ Benar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>