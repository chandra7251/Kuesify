<script setup lang="ts">
withDefaults(
    defineProps<{
        streaks: { date: string; count: number }[];
        label?: string;
    }>(),
    {
        label: 'Heatmap streak 30 hari terakhir',
    },
);
</script>

<template>
    <div class="grid grid-cols-10 gap-1.5" :aria-label="label">
        <span
            v-for="day in streaks"
            :key="day.date"
            class="h-5 rounded-md border border-slate-200"
            :class="day.count > 0 ? 'bg-brand-secondary' : 'bg-slate-100'"
            :title="`${day.date}: ${day.count} aktivitas`"
        ></span>
    </div>
</template>
