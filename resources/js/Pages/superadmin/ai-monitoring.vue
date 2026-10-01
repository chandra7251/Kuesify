<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
type Organization = {
    id: number;
    name: string;
    slug: string;
    members_count: number;
};
type Category = { id: number; name: string; theme_key?: string | null };
const props = defineProps<{
    organizations: { data: Organization[] };
    categories: Category[];
    health: Record<string, number>;
}>();
</script>

<template>
    <Head title="AI Monitoring" /><AuthenticatedLayout
        ><main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-extrabold">AI Monitoring</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Ringkasan job AI dan kegagalan yang perlu ditinjau.
                </p>
            </section>
            <section class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Queued jobs</p>
                    <p class="mt-2 text-3xl font-black">
                        {{ health.queued_jobs ?? 0 }}
                    </p>
                </article>
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Failed jobs</p>
                    <p class="mt-2 text-3xl font-black">
                        {{ health.failed_jobs ?? 0 }}
                    </p>
                </article>
                <article class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">AI failures</p>
                    <p class="mt-2 text-3xl font-black">
                        {{ health.ai_failures ?? 0 }}
                    </p>
                </article>
            </section>
        </main></AuthenticatedLayout
    >
</template>
