<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
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
        icon: 'M12 5.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM5.5 20a6.5 6.5 0 0 1 13 0',
    },
    {
        value: 'creator',
        title: 'Guru / Pengajar',
        description: 'Buat soal, susun kuis, dan buka sesi live.',
        icon: 'M4 20h4L19 9l-4-4L4 16v4ZM14.5 6.5l3 3',
    },
    {
        value: 'organization_admin',
        title: 'Admin Organisasi',
        description: 'Kelola workspace, anggota, dan tim pengajar.',
        icon: 'M4 20h16M6 20V5h12v15M9 9h.01M12 9h.01M15 9h.01M9 13h.01M12 13h.01M15 13h.01M9 17h.01M12 17h.01M15 17h.01',
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
            <p class="text-sm font-semibold text-brand-primary">
                Mulai belajar
            </p>
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
            <fieldset aria-describedby="role-help">
                <div class="flex items-center justify-between">
                    <legend class="text-sm font-black text-slate-900">
                        Pilih Peran Utama Kamu
                    </legend>
                    <span
                        class="rounded-full bg-brand-accent px-2.5 py-0.5 text-[11px] font-extrabold text-brand-primary"
                    >
                        Dapat diubah nanti
                    </span>
                </div>
                <p id="role-help" class="mt-1 text-xs leading-5 text-slate-500">
                    Pilih bagaimana kamu akan menggunakan Kuesify untuk
                    pengalaman terbaik.
                </p>

                <div ref="roleMenu" class="relative mt-3">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl border-2 border-slate-200 bg-white px-3.5 py-3 text-left transition hover:border-brand-primary/50 focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:ring-offset-2"
                        :aria-expanded="roleMenuOpen"
                        aria-haspopup="listbox"
                        @click.stop="roleMenuOpen = !roleMenuOpen"
                    >
                        <span class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-accent text-brand-primary"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        :d="selectedRole.icon"
                                    />
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span
                                    class="block truncate text-sm font-black text-slate-900"
                                    >{{ selectedRole.title }}</span
                                >
                                <span
                                    class="block truncate text-xs text-slate-500"
                                    >{{ selectedRole.description }}</span
                                >
                            </span>
                        </span>
                        <svg
                            class="h-4 w-4 shrink-0 text-slate-500 transition"
                            :class="{ 'rotate-180': roleMenuOpen }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <div
                        v-if="roleMenuOpen"
                        class="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10"
                        role="listbox"
                        aria-label="Pilihan peran"
                    >
                        <button
                            v-for="role in roles"
                            :key="role.value"
                            type="button"
                            role="option"
                            :aria-selected="form.role === role.value"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-brand-accent/60 focus:outline-none focus:ring-2 focus:ring-brand-secondary"
                            :class="
                                form.role === role.value
                                    ? 'bg-brand-accent/50'
                                    : ''
                            "
                            @click="selectRole(role.value)"
                        >
                            <span
                                class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600"
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
                                        :d="role.icon"
                                    />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block text-xs font-black text-slate-900"
                                    >{{ role.title }}</span
                                >
                                <span
                                    class="block truncate text-[11px] text-slate-500"
                                    >{{ role.description }}</span
                                >
                            </span>
                            <svg
                                v-if="form.role === role.value"
                                class="h-4 w-4 shrink-0 text-brand-primary"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293Z"
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
                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        >
                        </PasswordInput>
                        <InputError
                            class="mt-1"
                            :message="form.errors.password"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Konfirmasi kata sandi"
                        />
                        <PasswordInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        >
                        </PasswordInput>
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
                    class="text-center text-sm font-semibold text-brand-primary hover:text-brand-hover focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:ring-offset-2 sm:text-left"
                >
                    Sudah punya akun? Masuk
                </Link>
                <PrimaryButton
                    class="justify-center bg-brand-primary px-5 py-3 text-sm normal-case tracking-normal hover:bg-brand-hover focus:bg-brand-hover sm:min-w-32"
                    :class="{ 'opacity-60': form.processing }"
                    :disabled="form.processing"
                >
                    Buat akun
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
