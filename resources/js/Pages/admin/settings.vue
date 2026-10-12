<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    organization: {
        id: number;
        name: string;
        slug?: string;
        timezone?: string;
    };
}>();

const page = usePage();
const successMessage = ref('');

const form = useForm({
    name: props.organization.name,
    timezone: props.organization.timezone || 'Asia/Jakarta',
});

function updateOrganization(): void {
    form.patch(route('organization.update'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value =
                'Nama dan identitas organisasi berhasil disimpan!';
            setTimeout(() => {
                successMessage.value = '';
            }, 4000);
        },
    });
}
</script>

<template>
    <Head title="Pengaturan Organisasi" />
    <AuthenticatedLayout>
        <main class="relative min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="pointer-events-none fixed inset-0 -z-0 bg-brand-accent/30"
                aria-hidden="true"
            ></div>
            <div class="relative z-10 mx-auto max-w-5xl space-y-6">
                <!-- Hero Header -->
                <section
                    class="relative overflow-hidden rounded-xl bg-brand-primary px-6 py-6 text-white shadow-sm sm:px-8 sm:py-7"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <Link
                            href="/dashboard"
                            class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-3 text-xs font-black text-white transition hover:bg-white/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                            Kembali
                        </Link>
                        <span
                            class="hidden rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-brand-secondary sm:inline-flex"
                        >
                            Pengaturan organisasi
                        </span>
                    </div>
                    <p
                        class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-brand-secondary"
                            aria-hidden="true"
                        ></span>
                        Admin Organisasi · Pengaturan
                    </p>
                    <h1
                        class="mt-3 text-2xl font-black tracking-tight sm:text-3xl"
                    >
                        Identitas & Nama Organisasi
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                        Atur nama sekolah atau institusimu. Nama ini akan tampil
                        di seluruh dashboard, sertifikat kuis, dan badge guru
                        serta siswa yang bergabung.
                    </p>
                </section>

                <!-- Success Banner -->
                <div
                    v-if="successMessage || page.props.flash?.success"
                    class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900 shadow-sm transition-all"
                >
                    <div
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-emerald-500 text-white"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                    <p class="text-sm font-bold">
                        {{ successMessage || page.props.flash?.success }}
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Form Edit Identitas -->
                    <section
                        class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm sm:p-7 lg:col-span-2"
                    >
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-black text-slate-900">
                                Ubah Nama Organisasi
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Masukkan nama resmi sekolah, bimbingan belajar,
                                atau kampusmu.
                            </p>
                        </div>

                        <form
                            class="mt-6 space-y-5"
                            @submit.prevent="updateOrganization"
                        >
                            <div>
                                <InputLabel
                                    for="org_name"
                                    value="Nama Sekolah / Institusi"
                                    class="font-bold"
                                />
                                <TextInput
                                    id="org_name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1.5 block w-full text-base font-bold text-slate-900"
                                    placeholder="Contoh: SMA Negeri 1 Jakarta"
                                />
                                <InputError
                                    class="mt-1.5"
                                    :message="form.errors.name"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="org_timezone"
                                    value="Zona Waktu"
                                    class="font-bold"
                                />
                                <select
                                    id="org_timezone"
                                    v-model="form.timezone"
                                    class="mt-1.5 block min-h-11 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-800 shadow-sm focus:border-brand-secondary focus:ring-brand-secondary"
                                >
                                    <option value="Asia/Jakarta">
                                        WIB (Asia/Jakarta - UTC+7)
                                    </option>
                                    <option value="Asia/Makassar">
                                        WITA (Asia/Makassar - UTC+8)
                                    </option>
                                    <option value="Asia/Jayapura">
                                        WIT (Asia/Jayapura - UTC+9)
                                    </option>
                                </select>
                                <InputError
                                    class="mt-1.5"
                                    :message="form.errors.timezone"
                                />
                            </div>

                            <div class="pt-2">
                                <PrimaryButton
                                    type="submit"
                                    class="min-h-11 px-6 font-black"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing"
                                        >Menyimpan...</span
                                    >
                                    <span v-else>Simpan Perubahan</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </section>

                    <!-- Sidebar Info / Shortcut -->
                    <div class="space-y-6">
                        <!-- Kartu Informasi Ruang Kerja -->
                        <section
                            class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="text-sm font-black uppercase tracking-wider text-slate-500"
                            >
                                Ringkasan Tenant
                            </h2>
                            <div class="mt-4 space-y-3 text-sm">
                                <div
                                    class="flex items-center justify-between border-b border-slate-100 pb-2.5"
                                >
                                    <span class="text-slate-500"
                                        >ID Organisasi</span
                                    >
                                    <span
                                        class="font-mono font-bold text-slate-900"
                                        >#{{ organization.id }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between border-b border-slate-100 pb-2.5"
                                >
                                    <span class="text-slate-500"
                                        >Slug Sistem</span
                                    >
                                    <span
                                        class="truncate pl-3 font-mono text-xs font-semibold text-brand-primary"
                                    >
                                        {{ organization.slug || '—' }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between pb-1"
                                >
                                    <span class="text-slate-500"
                                        >Peran Akun</span
                                    >
                                    <span
                                        class="rounded-full bg-brand-secondary/20 px-2.5 py-0.5 text-xs font-black text-brand-dark"
                                    >
                                        Admin Organisasi
                                    </span>
                                </div>
                            </div>
                        </section>

                        <!-- Tautan Cepat -->
                        <section
                            class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="text-sm font-black uppercase tracking-wider text-slate-500"
                            >
                                Manajemen Langsung
                            </h2>
                            <div class="mt-4 grid gap-2.5">
                                <Link
                                    href="/admin/members"
                                    class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-brand-accent/30 p-3.5 text-sm font-bold text-slate-800 transition hover:border-brand-primary hover:bg-brand-accent/50"
                                >
                                    <span>Kelola Anggota</span>
                                    <svg
                                        class="h-4 w-4 text-brand-primary"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </Link>
                                <Link
                                    href="/reports"
                                    class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-brand-accent/30 p-3.5 text-sm font-bold text-slate-800 transition hover:border-brand-primary hover:bg-brand-accent/50"
                                >
                                    <span>Pantau Laporan</span>
                                    <svg
                                        class="h-4 w-4 text-brand-primary"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </Link>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
