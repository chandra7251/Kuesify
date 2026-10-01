<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const localeForm = useForm({ locale: page.props.auth.user.locale ?? 'id' });
const localeLabel = computed(() =>
    localeForm.locale === 'id' ? 'Bahasa Indonesia' : 'English',
);

function updateLocale(): void {
    localeForm.patch(route('profile.locale'), { preserveScroll: true });
}
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
                    <form class="max-w-xl" @submit.prevent="updateLocale">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            Bahasa / Language
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Preferensi saat ini: {{ localeLabel }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <select
                                v-model="localeForm.locale"
                                class="min-h-11 rounded-lg border-slate-300 text-sm"
                                aria-label="Bahasa"
                            >
                                <option value="id">Bahasa Indonesia</option>
                                <option value="en">English</option>
                            </select>
                            <button
                                type="submit"
                                class="min-h-11 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white"
                                :disabled="localeForm.processing"
                            >
                                Simpan
                            </button>
                        </div>
                    </form>
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
