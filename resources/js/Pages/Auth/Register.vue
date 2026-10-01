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
                    <span class="rounded-full bg-brand-accent px-2.5 py-0.5 text-[11px] font-extrabold text-brand-primary">
                        Dapat diubah nanti
                    </span>
                </div>
                <p id="role-help" class="mt-1 text-xs leading-5 text-slate-500">
                    Pilih bagaimana kamu akan menggunakan Kuesify untuk pengalaman terbaik.
                </p>

                <!-- Responsive Interactive Role Cards -->
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <button
                        v-for="role in roles"
                        :key="role.value"
                        type="button"
                        class="group relative flex flex-col justify-between rounded-2xl border-2 p-3.5 text-left transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:ring-offset-2"
                        :class="[
                            form.role === role.value
                                ? 'border-brand-primary bg-brand-accent/50 shadow-md shadow-brand-primary/10 -translate-y-0.5'
                                : 'border-slate-200 bg-white hover:border-brand-primary/40 hover:bg-slate-50'
                        ]"
                        @click="form.role = role.value"
                    >
                        <!-- Active checkmark badge -->
                        <div
                            v-if="form.role === role.value"
                            class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-white shadow-sm ring-2 ring-white"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div>
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition"
                                :class="[
                                    form.role === role.value
                                        ? 'bg-brand-primary text-brand-secondary'
                                        : 'bg-slate-100 text-slate-600 group-hover:bg-brand-accent group-hover:text-brand-primary'
                                ]"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="role.icon" />
                                </svg>
                            </span>
                            <p class="mt-2.5 text-xs font-black text-slate-900 sm:text-sm">
                                {{ role.title }}
                            </p>
                        </div>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                            {{ role.description }}
                        </p>
                    </button>
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
