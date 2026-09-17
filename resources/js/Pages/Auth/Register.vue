<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

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

const roleMenuOpen = ref(false);
const roleMenu = ref<HTMLElement | null>(null);
const selectedRole = computed(
    () => roles.find((role) => role.value === form.role) ?? roles[0],
);

function selectRole(value: (typeof roles)[number]['value']): void {
    form.role = value;
    roleMenuOpen.value = false;
}

function closeRoleMenu(event: MouseEvent): void {
    if (!roleMenu.value?.contains(event.target as Node)) {
        roleMenuOpen.value = false;
    }
}

function handleRoleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        roleMenuOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeRoleMenu);
    document.addEventListener('keydown', handleRoleKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeRoleMenu);
    document.removeEventListener('keydown', handleRoleKeydown);
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
    <GuestLayout content-class="max-w-xl">
        <Head title="Daftar" />

        <div>
            <p class="text-sm font-semibold text-teal-700">Mulai belajar</p>
            <h1
                class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950"
            >
                Buat akun Kuesify
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Pilih peranmu agar workspace siap dari awal.
            </p>
        </div>

        <form class="mt-7 space-y-6" @submit.prevent="submit">
            <fieldset ref="roleMenu" aria-describedby="role-help">
                <legend class="text-sm font-semibold text-slate-900">
                    Saya bergabung sebagai
                </legend>
                <p id="role-help" class="mt-1 text-xs leading-5 text-slate-500">
                    Peran bisa diubah oleh admin workspace setelah bergabung.
                </p>
                <div class="relative mt-3">
                    <button
                        type="button"
                        class="flex min-h-14 w-full items-center gap-3 rounded-xl border border-teal-600 bg-teal-50/70 px-3 text-left transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                        aria-haspopup="listbox"
                        :aria-expanded="roleMenuOpen"
                        @click.stop="roleMenuOpen = !roleMenuOpen"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-600 text-white"
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
                                    :d="selectedRole.icon"
                                />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-slate-900">
                                {{ selectedRole.title }}
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-slate-600">
                                {{ selectedRole.description }}
                            </span>
                        </span>
                        <svg
                            class="h-5 w-5 shrink-0 text-teal-700 transition"
                            :class="{ 'rotate-180': roleMenuOpen }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <div
                        v-if="roleMenuOpen"
                        class="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-xl shadow-slate-300/40"
                        role="listbox"
                        aria-label="Pilih peran"
                    >
                        <button
                            v-for="role in roles"
                            :key="role.value"
                            type="button"
                            class="group flex min-h-14 w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-inset"
                            :class="{ 'bg-teal-50': form.role === role.value }"
                            role="option"
                            :aria-selected="form.role === role.value"
                            @click="selectRole(role.value)"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-teal-100 group-hover:text-teal-700"
                                :class="{ 'bg-teal-600 text-white': form.role === role.value }"
                            >
                                <svg
                                    class="h-4 w-4"
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
                                <span class="block text-sm font-bold text-slate-900">{{ role.title }}</span>
                                <span class="block truncate text-xs text-slate-600">{{ role.description }}</span>
                            </span>
                            <svg
                                v-if="form.role === role.value"
                                class="h-5 w-5 shrink-0 text-teal-600"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.86a.75.75 0 0 0-1.06-1.06L9 10.88 7.2 9.08a.75.75 0 0 0-1.06 1.06l2.33 2.33a.75.75 0 0 0 1.06 0l4.33-4.33Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </fieldset>

            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm font-bold text-slate-900">Informasi akun</p>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
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
                        <InputError class="mt-1" :message="form.errors.name" />
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
                        <InputError class="mt-1" :message="form.errors.email" />
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
                        <InputError class="mt-1" :message="form.errors.password" />
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
                            class="mt-1"
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                </div>
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
                    class="text-center text-sm font-semibold text-teal-700 hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 sm:text-left"
                >
                    Sudah punya akun? Masuk
                </Link>
                <PrimaryButton
                    class="justify-center bg-teal-600 px-5 py-3 text-sm normal-case tracking-normal hover:bg-teal-700 focus:bg-teal-700 sm:min-w-32"
                    :class="{ 'opacity-60': form.processing }"
                    :disabled="form.processing"
                >
                    Buat akun
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
