<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';

withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        accent?: string;
        helper?: string;
        icon?: string;
    }>(),
    {
        accent: 'bg-brand-primary',
        helper: '',
        icon: 'analytics',
    },
);
</script>

<template>
    <article
        class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-figma transition duration-300 focus-within:-translate-y-1 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-figma-hover dark:border-slate-700 dark:bg-slate-800"
    >
        <div class="flex items-start justify-between gap-3">
            <div
                class="h-1 w-8 rounded-full"
                :class="accent"
                aria-hidden="true"
            ></div>
            <span
                class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-accent text-brand-primary transition duration-300 group-hover:rotate-6 group-hover:scale-110"
                :aria-label="label"
            >
                <AppIcon :name="icon ?? 'analytics'" :size="18" />
            </span>
        </div>
        <p
            class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300"
        >
            {{ label }}
        </p>
        <p
            class="mt-1 text-2xl font-black tabular-nums text-slate-900 dark:text-white"
        >
            {{ value }}
        </p>
        <p
            v-if="helper"
            class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-300"
        >
            {{ helper }}
        </p>
    </article>
</template>
