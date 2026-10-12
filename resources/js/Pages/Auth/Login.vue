<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const fillDemo = (email: string) => {
    form.email = email;
    form.password = 'password';
};

const demoAccounts = [
    {
        email: 'admin@kuesify.test',
        title: 'Admin Organisasi',
        description: 'Kelola workspace dan anggota.',
        icon: 'M4 20h16M6 20V5h12v15M9 9h.01M12 9h.01M15 9h.01M9 13h.01M12 13h.01M15 13h.01M9 17h.01M12 17h.01M15 17h.01',
        tone: 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
    },
    {
        email: 'creator@kuesify.test',
        title: 'Guru / Creator',
        description: 'Buat soal dan kuis live.',
        icon: 'M4 20h4L19 9l-4-4L4 16v4ZM14.5 6.5l3 3',
        tone: 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
    },
    {
        email: 'participant@kuesify.test',
        title: 'Siswa / Peserta',
        description: 'Ikuti kuis dan lihat hasil.',
        icon: 'M12 5.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM5.5 20a6.5 6.5 0 0 1 13 0',
        tone: 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
    },
] as const;

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout compact content-class="max-w-xl">
        <Head title="Masuk" />

        <div class="mb-7">
            <p class="text-sm font-semibold text-brand-primary">
                Selamat datang kembali
            </p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Masuk ke Kuesify
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Lanjutkan membuat pengalaman belajar yang lebih interaktif.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-brand-secondary/30 bg-brand-secondary/10 px-3 py-2 text-sm font-medium text-brand-primary"
        >
            {{ status }}
        </div>

        <!-- Quick Live Quiz Access Card -->
        <div
            class="mb-6 rounded-2xl border-2 border-brand-secondary/40 bg-brand-secondary/10 p-4 transition-all hover:bg-brand-secondary/15 sm:p-5"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-brand-primary px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-white"
                    >
                        <span
                            class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-secondary"
                        ></span>
                        Peserta / Siswa
                    </span>
                    <h2 class="mt-1 text-sm font-black text-slate-900">
                        Mau ikut Live Quiz kelas pakai PIN?
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-600">
                        Tidak perlu login akun jika hanya ingin menjawab sesi
                        dari guru.
                    </p>
                </div>
                <Link
                    href="/join"
                    class="shrink-0 rounded-xl bg-brand-primary px-3.5 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-brand-hover active:translate-y-0.5"
                >
                    Masuk PIN →
                </Link>
            </div>
        </div>

        <!-- Quick Demo Fill -->
        <section
            class="mb-6 rounded-2xl border border-brand-primary/15 bg-brand-accent/35 p-3.5 shadow-sm sm:p-5"
            aria-labelledby="demo-account-title"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p
                        id="demo-account-title"
                        class="text-[11px] font-black uppercase tracking-[0.14em] text-brand-primary"
                    >
                        Pilih akun demo
                    </p>
                    <p class="mt-1 text-[11px] leading-5 text-slate-500">
                        Isi email dan kata sandi demo dengan satu klik.
                    </p>
                </div>
                <span
                    class="hidden rounded-full bg-white px-2 py-1 text-[10px] font-bold text-slate-500 sm:inline-flex"
                >
                    1 klik isi
                </span>
            </div>
            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                <button
                    v-for="demo in demoAccounts"
                    :key="demo.email"
                    type="button"
                    class="group flex min-h-[68px] items-center gap-2 rounded-xl border p-2.5 text-left transition hover:-translate-y-0.5 hover:shadow-sm active:translate-y-0"
                    :class="demo.tone"
                    @click="fillDemo(demo.email)"
                >
                    <span
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-white/80"
                        aria-hidden="true"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :d="demo.icon"
                            />
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-[11px] font-black">
                            {{ demo.title }}
                        </span>
                        <span class="mt-0.5 block truncate text-[10px] opacity-75">
                            {{ demo.description }}
                        </span>
                    </span>
                </button>
            </div>
        </section>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Kata sandi" />

                <PasswordInput
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                >
                </PasswordInput>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <div
                class="mt-6 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:ring-offset-2"
                >
                    Lupa kata sandi?
                </Link>

                <PrimaryButton
                    class="w-full justify-center sm:w-auto"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk
                </PrimaryButton>
            </div>

            <p class="mt-6 text-center text-sm text-slate-600">
                Belum punya akun?
                <Link
                    :href="route('register')"
                    class="font-semibold text-brand-primary hover:text-brand-hover"
                >
                    Buat akun gratis
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
