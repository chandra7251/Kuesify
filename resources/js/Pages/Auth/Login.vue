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

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout compact>
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
                        Mau ikut live quiz pakai PIN?
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-600">
                        Tidak perlu login akun jika hanya ingin menjawab kuis
                        panggung.
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

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
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
                    <Checkbox name="remember" v-model:checked="form.remember" />
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
