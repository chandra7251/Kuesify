<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        current: number;
        max: number;
        label?: string;
    }>(),
    {
        label: '',
    },
);

const percentage = computed(() => {
    if (props.max <= 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.max(0, Math.round((props.current / props.max) * 100)),
    );
});
</script>

<template>
    <div>
        <div
            v-if="label"
            class="mb-2 flex items-center justify-between gap-3 text-xs font-semibold text-slate-600"
        >
            <span>{{ label }}</span>
            <span>{{ percentage }}%</span>
        </div>
        <div
            class="h-2 rounded-full bg-slate-200"
            role="progressbar"
            :aria-label="label || 'Progress'"
            :aria-valuenow="percentage"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-2 rounded-full bg-brand-secondary"
                :style="{ width: `${percentage}%` }"
            ></div>
        </div>
    </div>
</template>
