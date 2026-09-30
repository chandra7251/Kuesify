<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        accent?: string;
        helper?: string;
    }>(),
    {
        accent: 'bg-brand-primary',
        helper: '',
    },
);
</script>

<template>
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-figma">
        <div class="mb-3 h-1 w-8 rounded-full" :class="accent" aria-hidden="true"></div>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ label }}</p>
        <p class="mt-1 text-2xl font-black tabular-nums text-slate-900">{{ value }}</p>
        <p v-if="helper" class="mt-2 text-xs leading-5 text-slate-500">{{ helper }}</p>
    </article>
</template>
