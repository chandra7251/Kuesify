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

function paginationLabel(label: string): string {
    return label
        .replace('&laquo;', '‹')
        .replace('&raquo;', '›')
        .replace(/<[^>]*>/g, '');
}
</script>

<template>
    <div
        v-if="links && links.length > 3"
        class="flex flex-wrap items-center gap-1"
    >
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="select-none rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-300"
            >
                {{ paginationLabel(link.label) }}
            </div>
            <Link
                v-else
                :href="link.url"
                class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                :class="
                    link.active
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                "
                preserve-scroll
            >
                {{ paginationLabel(link.label) }}
            </Link>
        </template>
    </div>
</template>
