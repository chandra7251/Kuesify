<script setup lang="ts">
defineProps<{
    badges: { key: string; name: string }[];
    emptyText?: string;
}>();
</script>

<template>
    <div v-if="badges.length" class="grid gap-2">
        <div v-for="badge in badges" :key="badge.key" class="flex items-center gap-3 rounded-xl bg-brand-accent px-3 py-2">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-secondary text-sm font-black text-white" aria-hidden="true">★</span>
            <span class="text-sm font-semibold text-slate-800">{{ badge.name }}</span>
        </div>
    </div>
    <p v-else class="rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600">
        {{ emptyText ?? 'Belum ada badge.' }}
    </p>
</template>
