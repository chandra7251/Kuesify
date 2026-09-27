<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        questionsCount: number;
        status?: string;
        description?: string | null;
    }>(),
    {
        status: '',
        description: null,
    },
);
</script>

<template>
    <article class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-brand-secondary">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-950">{{ title }}</p>
                <p v-if="description" class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ description }}</p>
                <p class="mt-2 text-xs font-semibold text-slate-500">{{ questionsCount }} soal</p>
            </div>
            <span v-if="status" class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-[#527A12]">{{ status }}</span>
        </div>
        <slot name="actions" />
    </article>
</template>
