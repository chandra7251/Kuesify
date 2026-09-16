<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const roles = [
    {
        value: 'participant',
        title: 'Siswa / Peserta',
        description: 'Ikuti kuis, tugas, dan lihat hasil belajar.',
        icon: 'M12 6.75a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM6.75 21a5.25 5.25 0 0 1 10.5 0v.75H6.75V21Z',
    },
    {
        value: 'creator',
        title: 'Guru / Pengajar',
        description: 'Buat soal, susun kuis, dan buka sesi live.',
        icon: 'm4.5 19.5 3-3m0 0 3 3m-3-3V4.5m12 15-3-3m0 0-3 3m3-3V4.5M3 4.5h18',
    },
    {
        value: 'organization_admin',
        title: 'Admin Organisasi',
        description: 'Kelola workspace, anggota, dan tim pengajar.',
        icon: 'M3.75 21h16.5M4.5 21V6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25V21M8.25 9h.008v.008H8.25V9Zm3.75 0h.008v.008H12V9Zm3.75 0h.008v.008h-.008V9ZM8.25 12.75h.008v.008H8.25v-.008Zm3.75 0h.008v.008H12v-.008Zm3.75 0h.008v.008h-.008v-.008Z',
    },
] as const;

const form = useForm({
    name: '',
    email: '',
    role: 'participant',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <div>
            <p class="text-sm font-semibold text-indigo-700">Mulai belajar</p>
            <h1
                class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950"
            >
                Buat akun Kuesify
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Pilih peranmu agar workspace siap dari awal.
            </p>
        </div>

        <form class="mt-7 space-y-5" @submit.prevent="submit">
            <fieldset>
                <legend class="text-sm font-semibold text-slate-900">
                    Saya bergabung sebagai
                </legend>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Peran bisa diubah oleh admin workspace setelah bergabung.
                </p>
                <div class="mt-3 grid gap-2">
                    <label
                        v-for="role in roles"
                        :key="role.value"
                        class="group flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2"
                        :class="
                            form.role === role.value
                                ? 'border-indigo-600 bg-indigo-50/70'
                                : 'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50'
                        "
                    >
                        <input
                            v-model="form.role"
                            class="sr-only"
                            type="radio"
                            name="role"
                            :value="role.value"
                            :aria-label="role.title"
                        />
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                            :class="
                                form.role === role.value
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-slate-100 text-slate-600 group-hover:bg-indigo-100 group-hover:text-indigo-700'
                            "
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="role.icon"
                                />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-sm font-bold text-slate-900"
                                >{{ role.title }}</span
                            >
                            <span
                                class="mt-0.5 block text-xs leading-5 text-slate-600"
                                >{{ role.description }}</span
                            >
                        </span>
                        <span
                            class="mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                            :class="
                                form.role === role.value
                                    ? 'border-indigo-600 bg-indigo-600'
                                    : 'border-slate-300 bg-white'
                            "
                            aria-hidden="true"
                        >
                            <svg
                                v-if="form.role === role.value"
                                class="h-2.5 w-2.5 text-white"
                                viewBox="0 0 12 12"
                                fill="none"
                            >
                                <path
                                    d="m2.5 6 2.2 2.2L9.5 3.5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </fieldset>

            <div>
                <InputLabel for="name" value="Nama lengkap" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata sandi" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi kata sandi"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <p
                class="rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600"
            >
                Setelah daftar, pilih avatar lalu verifikasi email sebelum masuk
                workspace.
            </p>

            <div
                class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <Link
                    :href="route('login')"
                    class="text-center text-sm font-semibold text-indigo-700 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-left"
                >
                    Sudah punya akun? Masuk
                </Link>
                <PrimaryButton
                    class="justify-center bg-indigo-700 px-5 py-3 text-sm normal-case tracking-normal hover:bg-indigo-800 focus:bg-indigo-800 sm:min-w-32"
                    :class="{ 'opacity-60': form.processing }"
                    :disabled="form.processing"
                >
                    Buat akun
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
