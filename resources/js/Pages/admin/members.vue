<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

const page = usePage();
const currentUserId = computed(() => (page.props.auth as any)?.user?.id);

const invite = useForm({ email: '', role: 'participant' });
const memberReceipt = ref<{ email: string; role: string } | null>(null);
const motionRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    const mm = gsap.matchMedia();
    mm.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            gsap.from('[data-motion-item]', {
                opacity: 0,
                y: 22,
                duration: 0.4,
                stagger: 0.07,
                ease: 'power2.out',
                clearProps: 'all',
            });
        }, motionRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    gsapCtx?.revert();
});
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
    if (confirm('Yakin ingin menonaktifkan akun anggota ini dari organisasi?')) {
        useForm({}).patch(route('organization.members.disable', id), {
            preserveScroll: true,
        });
    }
}

function restore(id: number): void {
    useForm({}).patch(route('organization.members.restore', id), {
        preserveScroll: true,
    });
}

function removeMember(id: number, name: string): void {
    if (confirm(`Yakin ingin menghapus ${name} dari organisasi ini?`)) {
        useForm({}).delete(route('organization.members.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Anggota Organisasi" />
    <AuthenticatedLayout>
        <main ref="motionRoot" data-motion="organization-members" class="relative min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="pointer-events-none fixed inset-0 -z-0 bg-brand-accent/30"
                aria-hidden="true"
            ></div>
            <div class="relative z-10 mx-auto max-w-[90rem] space-y-6">
                <section
                    data-motion-item
                    class="overflow-hidden rounded-2xl bg-brand-primary p-5 text-white shadow-figma sm:p-7"
                >
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-secondary"
                            >
                                Workspace anggota
                            </p>
                            <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                                Anggota Organisasi
                            </h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-white/75">
                                Tambahkan guru atau siswa untuk membuat, mengikuti,
                                dan memantau kuis di organisasi ini.
                            </p>
                        </div>
                        <span
                            class="inline-flex w-fit items-center rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white"
                        >
                            {{ members.length }} anggota
                        </span>
                    </div>

                    <form
                        class="mt-6 grid gap-3 rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-sm lg:grid-cols-[minmax(0,1fr)_13rem_auto]"
                        @submit.prevent="addMember"
                    >
                        <label for="member-email" class="sr-only"
                            >Email anggota</label
                        >
                        <input
                            id="member-email"
                            v-model="invite.email"
                            required
                            type="email"
                            class="min-h-11 w-full rounded-xl border-0 bg-white text-sm text-slate-900 placeholder:text-slate-400 shadow-none focus:ring-2 focus:ring-brand-secondary"
                            placeholder="Email anggota (guru / siswa)"
                        />
                        <label for="member-role" class="sr-only"
                            >Peran anggota</label
                        >
                        <select
                            id="member-role"
                            v-model="invite.role"
                            class="min-h-11 rounded-xl border-0 bg-white text-sm text-slate-900 shadow-none focus:ring-2 focus:ring-brand-secondary"
                        >
                            <option value="participant">Siswa / Peserta</option>
                            <option value="creator">Guru / Pengajar</option>
                            <option value="organization_admin">
                                Admin Organisasi
                            </option>
                        </select>
                        <button
                            type="submit"
                            class="min-h-11 rounded-xl bg-brand-secondary px-5 text-sm font-black text-white shadow-sm transition hover:brightness-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary disabled:cursor-not-allowed disabled:opacity-50"
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
                        class="mt-2 text-xs font-semibold text-red-200"
                    >
                        {{ invite.errors.email }}
                    </p>
                </section>

                <div
                    v-if="memberReceipt"
                    data-motion-item
                    class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 shadow-sm"
                >
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-500 text-sm font-black text-white"
                    >
                        ✓
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-emerald-950">
                            Anggota berhasil ditambahkan.
                        </h2>
                        <p class="mt-1 text-xs font-semibold leading-relaxed text-emerald-800">
                            {{ memberReceipt.email }} berhasil didaftarkan sebagai
                            {{ roleLabel(memberReceipt.role) }} di organisasi ini.
                        </p>
                    </div>
                </div>

                <section
                    data-motion-item
                    class="overflow-hidden rounded-2xl border border-brand-primary/20 bg-white shadow-figma"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-brand-accent/40 px-5 py-4 sm:px-6"
                    >
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.14em] text-brand-primary"
                            >
                                Daftar anggota
                            </p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">
                                Anggota aktif di organisasi
                            </h2>
                        </div>
                        <span
                            class="rounded-full bg-brand-secondary px-3 py-1 text-xs font-black text-white"
                        >
                            {{ members.length }} akun
                        </span>
                    </div>

                    <div v-if="members.length" class="divide-y divide-slate-100">
                        <article
                            v-for="member in members"
                            :key="member.id"
                            class="group flex flex-col gap-4 px-5 py-4 transition hover:bg-brand-accent/30 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-primary text-sm font-black text-white"
                                    aria-hidden="true"
                                >
                                    {{ member.name.slice(0, 1).toUpperCase() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">
                                        {{ member.name }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="truncate text-xs text-slate-500">
                                            {{ member.email }}
                                        </span>
                                        <span
                                            class="rounded-full bg-brand-accent px-2.5 py-0.5 text-[11px] font-black text-brand-primary"
                                        >
                                            {{ roleLabel(member.role) }}
                                        </span>
                                        <span
                                            v-if="!member.is_active"
                                            class="rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-black text-red-700"
                                        >
                                            Nonaktif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:shrink-0">
                                <template v-if="member.id !== currentUserId">
                                    <button
                                        v-if="!member.is_active"
                                        type="button"
                                        class="inline-flex min-h-9 items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 px-3 text-xs font-black text-emerald-700 transition hover:bg-emerald-100"
                                        @click="restore(member.id)"
                                    >
                                        Aktifkan
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        class="inline-flex min-h-9 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-black text-amber-700 transition hover:bg-amber-100"
                                        @click="disable(member.id)"
                                    >
                                        Nonaktifkan
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex min-h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-black text-red-600 transition hover:bg-red-100"
                                        @click="removeMember(member.id, member.name)"
                                    >
                                        Hapus
                                    </button>
                                </template>
                                <span v-else class="text-[11px] font-bold text-slate-400 italic px-2">
                                    (Akun Anda)
                                </span>
                            </div>
                        </article>
                    </div>
                    <p
                        v-else
                        class="m-5 rounded-xl bg-brand-accent p-6 text-center text-sm font-semibold text-slate-600"
                    >
                        Belum ada anggota. Daftarkan siswa atau guru lewat form
                        di atas.
                    </p>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
