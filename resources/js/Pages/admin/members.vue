<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
type Member = { id: number; name: string; email: string; role: string; is_active: boolean };
type Group = { id: number; name: string };
const props = defineProps<{ members: Member[]; groups: Group[]; organization: { id: number; name: string } }>();
const invite = useForm({ email: '', role: 'participant' });
const group = useForm({ name: '' });
function addMember(): void { invite.post(route('organization.members.store'), { preserveScroll: true, onSuccess: () => invite.reset() }); }
function addGroup(): void { group.post(route('organization.groups.store'), { preserveScroll: true, onSuccess: () => group.reset() }); }
function disable(id: number): void { useForm({}).patch(route('organization.members.disable', id), { preserveScroll: true }); }
</script>

<template><Head title="Anggota Organisasi" /><AuthenticatedLayout><main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"><section class="rounded-2xl bg-white p-6 shadow-sm"><h1 class="text-3xl font-extrabold">Anggota Organisasi</h1><form class="mt-5 flex flex-wrap gap-2" @submit.prevent="addMember"><input v-model="invite.email" required type="email" class="min-h-11 flex-1 rounded-lg border-slate-300" placeholder="Email anggota" /><select v-model="invite.role" class="min-h-11 rounded-lg border-slate-300"><option value="participant">Participant</option><option value="creator">Creator</option><option value="organization_admin">Admin</option></select><button class="min-h-11 rounded-lg bg-brand-primary px-4 font-bold text-white">Tambah</button></form></section><section class="rounded-2xl bg-white p-5 shadow-sm"><article v-for="member in members" :key="member.id" class="flex items-center justify-between gap-3 border-b border-slate-100 py-4 last:border-0"><div><p class="font-bold">{{ member.name }}</p><p class="text-sm text-slate-500">{{ member.email }} · {{ member.role }}</p></div><button v-if="member.is_active" class="text-sm font-bold text-red-600" @click="disable(member.id)">Nonaktifkan</button></article></section></main></AuthenticatedLayout></template>