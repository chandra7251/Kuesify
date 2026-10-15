<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import { Link } from '@inertiajs/vue3';

export interface BreadcrumbItem {
    label: string;
    href?: string;
    active?: boolean;
}

withDefaults(
    defineProps<{
        items: BreadcrumbItem[];
    }>(),
    {
        items: () => [],
    },
);
</script>

<template>
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center text-xs font-medium text-slate-500">
        <ol class="inline-flex flex-wrap items-center gap-1.5 sm:gap-2">
            <!-- Root Platform Admin Link -->
            <li class="inline-flex items-center">
                <Link
                    href="/admin"
                    class="inline-flex items-center gap-1.5 rounded-md px-1.5 py-1 text-slate-600 transition hover:bg-slate-100 hover:text-brand-primary"
                >
                    <AppIcon name="settings" class="h-3.5 w-3.5 text-slate-400" />
                    <span>Platform Admin</span>
                </Link>
            </li>

            <!-- Intermediary / Child Items -->
            <template v-for="(item, index) in items" :key="index">
                <li class="inline-flex items-center gap-1.5 text-slate-400">
                    <AppIcon name="chevronRight" class="h-3 w-3 shrink-0" />
                    <Link
                        v-if="item.href && !item.active"
                        :href="item.href"
                        class="rounded-md px-1.5 py-1 text-slate-600 transition hover:bg-slate-100 hover:text-brand-primary"
                    >
                        {{ item.label }}
                    </Link>
                    <span
                        v-else
                        class="px-1.5 py-1 font-semibold text-slate-900"
                        aria-current="page"
                    >
                        {{ item.label }}
                    </span>
                </li>
            </template>
        </ol>
    </nav>
</template>
