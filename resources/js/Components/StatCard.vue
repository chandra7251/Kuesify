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
        class="group rounded-xl border border-[#e2e8f0] bg-[#ffffff] p-4 shadow-sm transition duration-300 focus-within:-translate-y-1 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-md"
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
            class="mt-3 text-xs font-semibold uppercase tracking-wide text-[#64748b]"
        >
            {{ label }}
        </p>
        <p class="mt-1 text-2xl font-black tabular-nums text-[#0f172a]">
            {{ value }}
        </p>
        <p v-if="helper" class="mt-2 text-xs leading-5 text-[#64748b]">
            {{ helper }}
        </p>
    </article>
</template>
