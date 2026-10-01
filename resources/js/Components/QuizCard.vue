<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';

const props = withDefaults(
    defineProps<{
        title: string;
        questionsCount: number;
        status?: string;
        description?: string | null;
        state?: 'new' | 'retry' | 'done' | 'locked';
        score?: number | null;
    }>(),
    {
        status: '',
        description: null,
        state: 'new',
        score: null,
    },
);

const stateCopy = {
    new: 'Mulai belajar',
    retry: 'Bisa diulang',
    done: 'Selesai',
    locked: 'Terkunci',
} as const;
</script>

<template>
    <article
        class="ui-card-hover group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-figma-sm transition duration-300 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-figma dark:border-slate-700 dark:bg-slate-800"
    >
        <div
            class="absolute inset-x-0 top-0 h-1 origin-left scale-x-75 bg-gradient-to-r from-brand-primary via-brand-secondary to-support-1 opacity-80 transition duration-500 group-hover:scale-x-100"
        />
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 gap-3">
                <span
                    class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-accent text-brand-primary transition duration-300 group-hover:rotate-[-6deg] group-hover:scale-110"
                >
                    <AppIcon name="quiz" :size="22" :interactive="false" />
                </span>
                <div class="min-w-0">
                    <p
                        class="line-clamp-2 text-sm font-black leading-5 text-slate-950 dark:text-white"
                    >
                        {{ title }}
                    </p>
                    <p
                        v-if="description"
                        class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-300"
                    >
                        {{ description }}
                    </p>
                </div>
            </div>
            <span
                v-if="status"
                class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-brand-dark dark:text-brand-secondary"
                >{{ status }}</span
            >
        </div>
        <div
            class="mt-4 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-300"
        >
            <span class="inline-flex items-center gap-1"
                ><AppIcon name="question" :size="14" :interactive="false" />{{
                    questionsCount
                }}
                soal</span
            >
            <span
                class="rounded-full bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-700 dark:text-slate-200"
                >{{ stateCopy[props.state] }}</span
            >
            <span
                v-if="score !== null"
                class="rounded-full bg-brand-accent px-2 py-1 text-brand-primary"
                >Best {{ score }}</span
            >
        </div>
        <slot name="actions" />
    </article>
</template>
