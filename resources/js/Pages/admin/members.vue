<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
type Member = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
};
defineProps<{
    members: Member[];
}>();
const invite = useForm({ email: '', role: 'participant' });
const memberReceipt = ref<{ email: string; role: string } | null>(null);
function addMember(): void {
    const email = invite.email;
    const role = invite.role;
    invite.post(route('organization.members.store'), {
        preserveScroll: true,
        onSuccess: () => {
            memberReceipt.value = { email, role };
            invite.reset();
        },
    });
}
function disable(id: number): void {
    useForm({}).patch(route('organization.members.disable', id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Anggota Organisasi" />
    <AuthenticatedLayout>
        <main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="flex items-center gap-2 text-xs font-bold text-slate-500"
            >
                <Link
                    href="/dashboard"
                    class="transition hover:text-brand-primary"
                    >Dashboard</Link
                >
                <span>/</span>
                <span class="text-slate-800">Anggota Organisasi</span>
            </div>
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-extrabold">Anggota Organisasi</h1>
                <p class="mt-2 text-sm font-semibold text-slate-500">
                    Tambahkan guru atau siswa ke organisasimu agar mereka dapat
                    membuat, mengikuti, dan memantau kuis di bawah sekolahmu.
                </p>
                <div
                    v-if="memberReceipt"
                    class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-emerald-500 text-sm font-black text-white"
                        >
                            ✓
                        </span>
                        <div>
                            <h2 class="text-sm font-black text-emerald-950">
                                Anggota berhasil ditambahkan.
                            </h2>
                            <p
                                class="mt-1 text-xs font-semibold leading-relaxed text-emerald-800"
                            >
                                {{ memberReceipt.email }} berhasil didaftarkan
                                sebagai {{ roleLabel(memberReceipt.role) }} di
                                organisasi ini.
                            </p>
                        </div>
                    </div>
                </div>
                <form
                    class="mt-5 flex flex-wrap gap-2"
                    @submit.prevent="addMember"
                >
                    <input
                        v-model="invite.email"
                        required
                        type="email"
                        class="min-h-11 flex-1 rounded-lg border-slate-300"
                        placeholder="Email anggota (guru / siswa)"
                    /><select
                        v-model="invite.role"
                        class="min-h-11 rounded-lg border-slate-300"
                    >
                        <option value="participant">Siswa / Peserta</option>
                        <option value="creator">Guru / Pengajar</option>
                        <option value="organization_admin">
                            Admin Organisasi
                        </option></select
                    ><button
                        class="min-h-11 rounded-lg bg-brand-primary px-4 font-bold text-white disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="invite.processing"
                    >
                        {{
                            invite.processing
                                ? 'Menambahkan...'
                                : 'Tambah anggota'
                        }}
                    </button>
                </form>
                <p
                    v-if="invite.errors.email"
                    class="mt-2 text-sm font-semibold text-red-600"
                >
                    {{ invite.errors.email }}
                </p>
            </section>
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <article
                    v-for="member in members"
                    :key="member.id"
                    class="flex items-center justify-between gap-3 border-b border-slate-100 py-4 last:border-0"
                >
                    <div>
                        <p class="font-bold">{{ member.name }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{
                                member.email
                            }}</span>
                            <span
                                class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700"
                                >{{ roleLabel(member.role) }}</span
                            >
                            <span
                                v-if="!member.is_active"
                                class="rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-600"
                                >Nonaktif</span
                            >
                        </div>
                    </div>
                    <button
                        v-if="member.is_active"
                        class="text-sm font-bold text-red-600"
                        @click="disable(member.id)"
                    >
                        Nonaktifkan
                    </button>
                </article>
                <p
                    v-if="members.length === 0"
                    class="rounded-2xl bg-slate-50 p-5 text-sm font-semibold text-slate-500"
                >
                    Belum ada anggota. Daftarkan siswa atau guru lewat form di
                    atas untuk menghubungkan mereka dengan organisasimu.
                </p>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
