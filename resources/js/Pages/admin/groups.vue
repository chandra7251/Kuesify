<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
type Member = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
};
type Group = { id: number; name: string };
const props = defineProps<{
    members: Member[];
    groups: Group[];
    organization: { id: number; name: string };
}>();
const invite = useForm({ email: '', role: 'participant' });
const group = useForm({ name: '' });
function addMember(): void {
    invite.post(route('organization.members.store'), {
        preserveScroll: true,
        onSuccess: () => invite.reset(),
    });
}
function addGroup(): void {
    group.post(route('organization.groups.store'), {
        preserveScroll: true,
        onSuccess: () => group.reset(),
    });
}
function disable(id: number): void {
    useForm({}).patch(route('organization.members.disable', id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Group Organisasi" /><AuthenticatedLayout
        ><main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-extrabold">Group / Departemen</h1>
                <form class="mt-5 flex gap-2" @submit.prevent="addGroup">
                    <input
                        v-model="group.name"
                        required
                        class="min-h-11 flex-1 rounded-lg border-slate-300"
                        placeholder="Nama group"
                    /><button
                        class="min-h-11 rounded-lg bg-brand-primary px-4 font-bold text-white"
                    >
                        Tambah
                    </button>
                </form>
            </section>
            <section class="grid gap-3 sm:grid-cols-2">
                <article
                    v-for="item in groups"
                    :key="item.id"
                    class="rounded-2xl bg-white p-5 shadow-sm"
                >
                    <h2 class="font-extrabold">{{ item.name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Kelola anggota dari halaman Organisasi.
                    </p>
                </article>
                <p
                    v-if="groups.length === 0"
                    class="rounded-2xl bg-white p-5 text-sm text-slate-500"
                >
                    Belum ada group.
                </p>
            </section>
        </main></AuthenticatedLayout
    >
</template>
