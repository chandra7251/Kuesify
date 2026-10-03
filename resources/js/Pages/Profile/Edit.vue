<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const avatarOptions = Array.from({ length: 13 }, (_, index) => ({
    key: `profile_${index + 1}`,
    name: `Profil ${index + 1}`,
    description: 'Karakter pilihanmu',
}));

const avatarForm = useForm({
    name: user.value.name,
    email: user.value.email,
    avatar_key: user.value.avatar_key ?? '',
});

const isAvatarModalOpen = ref(false);

function selectAvatar(avatarKey: string): void {
    avatarForm.avatar_key = avatarKey;
}

function saveAvatar(): void {
    avatarForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isAvatarModalOpen.value = false;
        },
    });
}
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
                        <button
                            type="button"
                            class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-brand-secondary text-2xl font-black text-brand-primary shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                            aria-label="Pilih avatar profil"
                            title="Pilih avatar profil"
                            @click="isAvatarModalOpen = true"
                        >
                            <AvatarIcon
                                :avatar-key="user.avatar_key"
                                label="Avatar profil"
                                class="h-12 w-12 p-2"
                            />
                        </button>
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
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-brand-secondary px-4 py-2.5 text-sm font-bold text-brand-primary shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary sm:self-center"
                        aria-label="Edit avatar"
                        @click="isAvatarModalOpen = true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path
                                d="m16.9 3.1 4 4M4 20l3.7-.8L19.8 7.1a2.8 2.8 0 0 0-4-4L3.7 15.2 3 19.8 4 20Z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Edit avatar
                    </button>
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
        <div
            v-if="isAvatarModalOpen"
            class="fixed inset-0 z-50 grid place-items-center bg-slate-950/45 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="avatar-modal-title"
            @click.self="isAvatarModalOpen = false"
        >
            <section
                class="avatar-modal-scroll max-h-[min(720px,calc(100vh-2rem))] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-7"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-secondary">
                            Profil
                        </p>
                        <h2 id="avatar-modal-title" class="mt-1 text-2xl font-black text-slate-950">
                            Ganti avatar
                        </h2>
                    </div>
                    <button
                        type="button"
                        class="grid h-9 w-9 place-items-center rounded-lg text-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-secondary"
                        aria-label="Tutup dialog"
                        @click="isAvatarModalOpen = false"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="mt-2 text-sm text-slate-600">
                    Pilih avatar baru untuk profil kamu.
                </p>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <button
                        v-for="option in avatarOptions"
                        :key="option.key"
                        type="button"
                        class="flex min-h-40 flex-col items-center justify-center rounded-xl border p-4 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                        :class="avatarForm.avatar_key === option.key ? 'border-brand-primary bg-brand-primary/10 text-brand-primary' : 'border-slate-200 bg-white text-slate-700 hover:border-brand-primary'"
                        :aria-pressed="avatarForm.avatar_key === option.key"
                        @click="selectAvatar(option.key)"
                    >
                        <AvatarIcon
                            :avatar-key="option.key"
                            :label="`Avatar ${option.name}`"
                            class="h-24 w-24 p-0"
                        />
                        <span class="mt-2 text-sm font-semibold">{{ option.name }}</span>
                        <span class="mt-0.5 text-xs text-slate-500">{{ option.description }}</span>
                    </button>
                </div>

                <p v-if="avatarForm.errors.avatar_key" class="mt-3 text-sm text-red-600">
                    {{ avatarForm.errors.avatar_key }}
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-secondary"
                        @click="isAvatarModalOpen = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="avatarForm.processing || !avatarForm.avatar_key"
                        @click="saveAvatar"
                    >
                        {{ avatarForm.processing ? 'Menyimpan...' : 'Simpan avatar' }}
                    </button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.avatar-modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(49 84 213 / 0.7) rgb(226 232 240 / 0.8);
}

.avatar-modal-scroll::-webkit-scrollbar {
    width: 10px;
}

.avatar-modal-scroll::-webkit-scrollbar-track {
    border-radius: 9999px;
    background: rgb(226 232 240 / 0.8);
}

.avatar-modal-scroll::-webkit-scrollbar-thumb {
    border: 2px solid rgb(226 232 240 / 0.8);
    border-radius: 9999px;
    background: rgb(49 84 213 / 0.7);
}

.avatar-modal-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(49 84 213);
}
</style>