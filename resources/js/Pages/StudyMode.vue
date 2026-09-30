<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Flashcard = { id: number; prompt: string; answer: string; explanation?: string | null; hint?: string | null };
const props = defineProps<{ quiz: { id: number; title: string; flashcards: Flashcard[] } }>();
const index = ref(0);
const revealed = ref(false);
const card = computed(() => props.quiz.flashcards[index.value]);
function next(): void { if (index.value < props.quiz.flashcards.length - 1) { index.value += 1; revealed.value = false; } }
function previous(): void { if (index.value > 0) { index.value -= 1; revealed.value = false; } }
</script>

<template>
    <Head :title="`Study Mode · ${quiz.title}`" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6">
            <Link :href="route('attempts.index')" class="text-sm font-bold text-teal-700">← Kembali</Link>
            <section class="rounded-3xl bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Study Mode · {{ index + 1 }} / {{ quiz.flashcards.length }}</p>
                <h1 class="mt-3 text-3xl font-extrabold text-slate-900">{{ quiz.title }}</h1>
                <div v-if="card" class="mt-8 rounded-2xl border border-slate-200 p-6">
                    <p class="text-xl font-bold text-slate-900">{{ card.prompt }}</p>
                    <p v-if="card.hint && !revealed" class="mt-4 text-sm text-slate-500">Hint: {{ card.hint }}</p>
                    <div v-if="revealed" class="mt-6 border-t border-slate-200 pt-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Jawaban</p>
                        <p class="mt-2 text-lg font-extrabold text-teal-800">{{ card.answer }}</p>
                        <p v-if="card.explanation" class="mt-3 text-sm leading-6 text-slate-600">{{ card.explanation }}</p>
                    </div>
                    <button v-else type="button" class="mt-6 min-h-11 rounded-xl bg-brand-primary px-5 font-extrabold text-white" @click="revealed = true">Tampilkan jawaban</button>
                </div>
                <div class="mt-6 flex justify-between gap-3">
                    <button type="button" class="min-h-11 rounded-xl border border-slate-300 px-4 font-bold text-slate-700 disabled:opacity-40" :disabled="index === 0" @click="previous">Sebelumnya</button>
                    <button type="button" class="min-h-11 rounded-xl bg-brand-primary px-4 font-bold text-white disabled:opacity-40" :disabled="index === quiz.flashcards.length - 1" @click="next">Berikutnya</button>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
