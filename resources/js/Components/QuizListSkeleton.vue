<script setup lang="ts">
import Skeleton from '@/Components/Skeleton.vue';

withDefaults(
    defineProps<{
        count?: number;
    }>(),
    {
        count: 3,
    },
);
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="i in count"
            :key="i"
            class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4"
        >
            <div class="flex-1 space-y-3">
                <Skeleton
                    variant="rect"
                    height="100"
                    width="150"
                    class="rounded-lg"
                />
                <div class="space-y-2">
                    <Skeleton variant="text" height="20" width="80%" />
                    <Skeleton variant="text" height="16" width="60%" />
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Skeleton
                    variant="rect"
                    height="32"
                    width="80"
                    class="rounded-md"
                />
                <Skeleton
                    variant="rect"
                    height="32"
                    width="32"
                    class="rounded-md"
                />
            </div>
        </div>
    </div>
</template>
