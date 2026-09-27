<script setup lang="ts">
withDefaults(
    defineProps<{
        items: { id: number | string; title: string; subtitle?: string; meta?: string | number }[];
        emptyText?: string;
    }>(),
    {
        emptyText: 'Belum ada aktivitas.',
    },
);
</script>

<template>
    <div v-if="items.length" class="divide-y divide-slate-100">
        <div v-for="item in items" :key="item.id" class="flex items-center justify-between gap-3 py-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">{{ item.title }}</p>
                <p v-if="item.subtitle" class="text-xs text-slate-500">{{ item.subtitle }}</p>
            </div>
            <span v-if="item.meta !== undefined" class="shrink-0 rounded-full bg-brand-primary/10 px-2.5 py-1 text-xs font-bold text-brand-primary">
                {{ item.meta }}
            </span>
        </div>
    </div>
    <p v-else class="rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600">{{ emptyText }}</p>
</template>
