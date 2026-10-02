<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const user = page.props.auth.user;
const userInitials = computed(() =>
    user.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <section
                class="overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
            >
                <div
                    class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-brand-secondary text-2xl font-black text-brand-primary shadow-sm"
                        >
                            {{ userInitials }}
                        </div>
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.2em] text-brand-secondary"
                            >
                                Pengaturan akun
                            </p>
                            <h1
                                class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                            >
                                {{ user.name }}
                            </h1>
                            <p class="mt-1 text-sm text-white/75">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="rounded-2xl border border-l-4 border-slate-200 border-l-brand-primary bg-white p-6 shadow-figma-sm sm:p-7"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </section>

            <section
                class="rounded-2xl border border-l-4 border-slate-200 border-l-brand-primary bg-white p-6 shadow-figma-sm sm:p-7"
            >
                <UpdatePasswordForm class="max-w-xl" />
            </section>

            <section
                class="rounded-2xl border border-l-4 border-red-200 border-l-status-danger bg-white p-6 shadow-figma-sm sm:p-7"
            >
                <DeleteUserForm class="max-w-xl" />
            </section>
        </main>
    </AuthenticatedLayout>
</template>
