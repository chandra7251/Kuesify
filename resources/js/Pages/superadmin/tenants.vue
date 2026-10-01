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
    <Head title="Tenants" /><AuthenticatedLayout
        ><main class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-brand-primary p-6 text-white">
                <p
                    class="text-xs font-bold uppercase tracking-wider text-brand-secondary"
                >
                    Super Admin
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">Tenant Organisasi</h1>
            </section>
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <article
                    v-for="item in organizations.data"
                    :key="item.id"
                    class="flex items-center justify-between border-b border-slate-100 py-4 last:border-0"
                >
                    <div>
                        <p class="font-bold">{{ item.name }}</p>
                        <p class="text-sm text-slate-500">/{{ item.slug }}</p>
                    </div>
                    <span
                        class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-bold"
                        >{{ item.members_count }} anggota</span
                    >
                </article>
                <p
                    v-if="organizations.data.length === 0"
                    class="p-5 text-center text-sm text-slate-500"
                >
                    Belum ada tenant.
                </p>
            </section>
        </main></AuthenticatedLayout
    >
</template>
