<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type Question = { id: number; type: string; prompt: string; hint?: string | null; points: number };
const props = defineProps<{ questions: Question[] }>();
const form = useForm({ type: 'multiple_choice', prompt: '', options: ['', ''], correct_answer: '', hint: '', points: 1000, tags: [] as string[] });
const tagQuery = ref('');
const tagSuggestions = ref<{ id: number; name: string }[]>([]);
let tagTimer: number | undefined;
watch(tagQuery, (value) => {
    if (tagTimer) window.clearTimeout(tagTimer);
    if (!value.trim()) { tagSuggestions.value = []; return; }
    tagTimer = window.setTimeout(async () => { const response = await fetch(`${route('questions.tags')}?q=${encodeURIComponent(value)}`, { headers: { Accept: 'application/json' } }); tagSuggestions.value = response.ok ? (await response.json()).data : []; }, 200);
});
function addTag(name: string): void { if (!form.tags.includes(name)) form.tags.push(name); tagQuery.value = ''; tagSuggestions.value = []; }
function removeTag(name: string): void { form.tags = form.tags.filter((tag) => tag !== name); }
function save(): void { form.post(route('questions.store'), { preserveScroll: true, onSuccess: () => { form.reset(); form.type = 'multiple_choice'; form.options = ['', '']; form.points = 1000; form.tags = []; } }); }
function remove(id: number): void { router.delete(route('questions.destroy', id), { preserveScroll: true }); }
</script>

<template>
    <Head title="Question Bank Creator" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-primary">Creator</p><h1 class="mt-2 text-3xl font-extrabold">Question Bank</h1><p class="mt-2 text-sm text-slate-600">Buat soal reusable dengan tag organisasi.</p></section>
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <form class="space-y-4 rounded-2xl bg-white p-6 shadow-sm" @submit.prevent="save">
                    <h2 class="text-lg font-extrabold">Soal baru</h2>
                    <select v-model="form.type" class="min-h-11 w-full rounded-lg border-slate-300"><option value="multiple_choice">Pilihan ganda</option><option value="true_false">Benar / salah</option><option value="fill_blank">Isian</option><option value="essay">Essay</option></select>
                    <textarea v-model="form.prompt" required rows="4" class="w-full rounded-lg border-slate-300" placeholder="Pertanyaan" />
                    <input v-model="form.correct_answer" required class="min-h-11 w-full rounded-lg border-slate-300" placeholder="Jawaban benar" />
                    <input v-model="form.hint" class="min-h-11 w-full rounded-lg border-slate-300" placeholder="Hint (opsional)" />
                    <div class="relative"><input v-model="tagQuery" class="min-h-11 w-full rounded-lg border-slate-300" placeholder="Cari tag" @keydown.enter.prevent="tagQuery && addTag(tagQuery)" /><div v-if="tagSuggestions.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white p-1 shadow-lg"><button v-for="tag in tagSuggestions" :key="tag.id" type="button" class="block w-full rounded px-3 py-2 text-left text-sm hover:bg-slate-50" @click="addTag(tag.name)">{{ tag.name }}</button></div></div>
                    <div class="flex flex-wrap gap-2"><button v-for="tag in form.tags" :key="tag" type="button" class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-bold" @click="removeTag(tag)">{{ tag }} ×</button></div>
                    <button type="submit" class="min-h-11 w-full rounded-xl bg-brand-primary px-4 font-extrabold text-white" :disabled="form.processing">Simpan soal</button>
                </form>
                <section class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="text-lg font-extrabold">Soal tersimpan</h2><p v-if="questions.length === 0" class="mt-5 rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">Belum ada soal.</p><article v-for="question in questions" :key="question.id" class="flex items-start justify-between gap-3 border-b border-slate-100 py-4 last:border-0"><div><p class="font-bold text-slate-900">{{ question.prompt }}</p><p class="mt-1 text-xs text-slate-500">{{ question.type }} · {{ question.points }} poin</p></div><button type="button" class="text-xs font-bold text-red-600" @click="remove(question.id)">Hapus</button></article></section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
