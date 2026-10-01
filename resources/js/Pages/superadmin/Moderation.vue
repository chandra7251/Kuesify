<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type Quiz = {
    id: number;
    title: string;
    description?: string | null;
    creator?: { name: string };
    category?: { name: string };
};
const props = defineProps<{ quizzes: Quiz[] }>();
const quizzes = ref([...props.quizzes]);
function decide(id: number, action: 'approve' | 'reject'): void {
    router.post(
        route(`admin.moderation.${action}`, id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                quizzes.value = quizzes.value.filter((quiz) => quiz.id !== id);
            },
        },
    );
}
</script>

<template>
    <Head title="Moderasi Publik" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section
                class="rounded-2xl bg-brand-primary p-6 text-white shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em] text-brand-secondary"
                >
                    Super Admin
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">
                    Moderasi Quiz Publik
                </h1>
                <p class="mt-2 text-sm text-white/75">
                    Tinjau quiz publik sebelum tampil ke peserta.
                </p>
            </section>
            <section class="space-y-3">
                <p
                    v-if="quizzes.length === 0"
                    class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500 shadow-sm"
                >
                    Tidak ada quiz menunggu moderasi.
                </p>
                <article
                    v-for="quiz in quizzes"
                    :key="quiz.id"
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex flex-wrap items-start justify-between gap-4"
                    >
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">
                                {{ quiz.title }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ quiz.creator?.name ?? 'Creator' }} ·
                                {{ quiz.category?.name ?? 'Tanpa kategori' }}
                            </p>
                            <p
                                v-if="quiz.description"
                                class="mt-3 text-sm leading-6 text-slate-600"
                            >
                                {{ quiz.description }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="min-h-10 rounded-lg bg-brand-secondary px-4 text-sm font-bold text-slate-900"
                                @click="decide(quiz.id, 'approve')"
                            >
                                Setujui</button
                            ><button
                                type="button"
                                class="min-h-10 rounded-lg bg-red-600 px-4 text-sm font-bold text-white"
                                @click="decide(quiz.id, 'reject')"
                            >
                                Tolak
                            </button>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
