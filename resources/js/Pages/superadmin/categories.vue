<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
type Organization = { id: number; name: string; slug: string; members_count: number };
type Category = { id: number; name: string; theme_key?: string | null };
const props = defineProps<{ organizations: { data: Organization[] }; categories: Category[]; health: Record<string, number> }>();
</script>

<template><Head title="Kategori Global" /><AuthenticatedLayout><main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"><section class="rounded-2xl bg-white p-6 shadow-sm"><h1 class="text-3xl font-extrabold">Kategori Global</h1><p class="mt-2 text-sm text-slate-600">Kategori dan tema yang tersedia lintas tenant.</p></section><section class="flex flex-wrap gap-3 rounded-2xl bg-white p-6 shadow-sm"><span v-for="item in categories" :key="item.id" class="rounded-full bg-brand-accent px-4 py-2 text-sm font-bold text-brand-primary">{{ item.name }}<small v-if="item.theme_key" class="ml-2 font-normal">{{ item.theme_key }}</small></span><p v-if="categories.length === 0" class="text-sm text-slate-500">Belum ada kategori.</p></section></main></AuthenticatedLayout></template>