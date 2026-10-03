<script setup lang="ts">
import AvatarIcon from '@/Components/AvatarIcon.vue';
import InputError from '@/Components/InputError.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface AvatarOption {
    key: string;
    name: string;
    description: string;
}

const props = defineProps<{ avatarKeys: string[] }>();

const options: AvatarOption[] = Array.from({ length: 13 }, (_, index) => ({
    key: `profile_${index + 1}`,
    name: `Profil ${index + 1}`,
    description: 'Karakter pilihanmu',
}));

const form = useForm({
    avatar_key: options[0]?.key ?? '',
});

function selectAvatar(avatarKey: string): void {
    form.avatar_key = avatarKey;
}

function submit(): void {
    form.post('/choose-avatar');
}
</script>

<template>
    <GuestLayout content-class="max-w-4xl" centered>
        <Head title="Pilih avatar" />

        <div class="relative pb-16">
            <div>
                <p class="text-sm font-semibold text-brand-secondary">
                    Lengkapi profil kamu
                </p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">
                    Pilih avatar kamu
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Pilih satu foto profil untuk melanjutkan ke dashboard.
                </p>
            </div>

            <form class="mt-4" @submit.prevent="submit">
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-900">
                        Avatar tersedia
                    </legend>
                    <div class="avatar-picker-scroll mt-3 max-h-[300px] overflow-y-auto pr-1">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <button
                                v-for="option in options"
                                :key="option.key"
                                type="button"
                                class="focus-visible:outline-brand-secondary-700 flex min-h-28 flex-col items-center justify-center rounded-xl border p-2 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                :class="
                                    form.avatar_key === option.key
                                        ? 'border-brand-secondary-700 bg-brand-secondary-50 text-brand-secondary'
                                        : 'border-slate-200 bg-white text-slate-700 hover:border-brand-secondary'
                                "
                                :aria-pressed="form.avatar_key === option.key"
                                @click="selectAvatar(option.key)"
                            >
                                <AvatarIcon
                                    :avatar-key="option.key"
                                    :label="`Avatar ${option.name}`"
                                    class="h-12 w-12 p-0"
                                />
                                <span class="mt-1 text-xs font-semibold">{{
                                    option.name
                                }}</span>
                                <span class="mt-0.5 text-[11px] text-slate-500">{{
                                    option.description
                                }}</span>
                            </button>
                        </div>
                    </div>
                    <InputError class="mt-3" :message="form.errors.avatar_key" />
                </fieldset>

                <div class="pointer-events-auto absolute bottom-0 right-0 z-20">
                    <button
                        type="submit"
                        class="bg-brand-secondary hover:brightness-95 focus-visible:outline-brand-secondary inline-flex min-h-11 items-center justify-center rounded-xl px-6 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing || !form.avatar_key"
                    >
                        Pilih avatar
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
.avatar-picker-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(49 84 213 / 0.7) rgb(226 232 240 / 0.8);
}

.avatar-picker-scroll::-webkit-scrollbar {
    width: 10px;
}

.avatar-picker-scroll::-webkit-scrollbar-track {
    border-radius: 9999px;
    background: rgb(226 232 240 / 0.8);
}

.avatar-picker-scroll::-webkit-scrollbar-thumb {
    border: 2px solid rgb(226 232 240 / 0.8);
    border-radius: 9999px;
    background: rgb(49 84 213 / 0.7);
}

.avatar-picker-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(49 84 213);
}
</style>
