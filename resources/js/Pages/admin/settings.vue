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
    <Head title="Pengaturan Organisasi" /><AuthenticatedLayout
        ><main class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-extrabold">Pengaturan Organisasi</h1>
                <p class="mt-2 text-slate-600">{{ organization.name }}</p>
                <div class="mt-6 grid gap-3">
                    <a
                        href="/organization"
                        class="rounded-xl border border-slate-200 p-4 font-bold hover:bg-slate-50"
                        >Kelola anggota dan group</a
                    ><a
                        href="/profile"
                        class="rounded-xl border border-slate-200 p-4 font-bold hover:bg-slate-50"
                        >Preferensi akun dan bahasa</a
                    >
                </div>
            </section>
        </main></AuthenticatedLayout
    >
</template>
