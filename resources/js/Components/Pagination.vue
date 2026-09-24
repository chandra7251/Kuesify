<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: LinkItem[];
}>();
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-3.5 py-2 text-xs font-semibold text-slate-300 rounded-xl select-none"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all"
                :class="link.active ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </div>
</template>
