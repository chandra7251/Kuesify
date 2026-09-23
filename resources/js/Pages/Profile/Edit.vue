<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
            >
                <p
                    class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#3154D5]"
                >
                    PENGATURAN AKUN
                </p>
                <h1
                    class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl"
                >
                    Profil
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Kelola informasi akun, keamanan, dan preferensi profil Anda.
                </p>
            </section>

            <div class="space-y-5">
                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_2px_7px_rgba(15,23,42,0.09)] sm:p-7"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_2px_7px_rgba(15,23,42,0.09)] sm:p-7"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_2px_7px_rgba(15,23,42,0.09)] sm:p-7"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
