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

const options: AvatarOption[] = [
    { key: 'book', name: 'Buku', description: 'Suka membaca' },
    { key: 'cap', name: 'Lulusan', description: 'Siap berkembang' },
    { key: 'globe', name: 'Globe', description: 'Suka menjelajah' },
    { key: 'lamp', name: 'Ide', description: 'Penuh gagasan' },
    { key: 'microscope', name: 'Peneliti', description: 'Teliti belajar' },
    { key: 'pencil', name: 'Kreator', description: 'Suka berkarya' },
    { key: 'rocket', name: 'Roket', description: 'Maju cepat' },
    { key: 'laptop', name: 'Digital', description: 'Belajar modern' },
].filter((option) => props.avatarKeys.includes(option.key));

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
    <GuestLayout>
        <Head title="Pilih avatar" />

        <div>
            <p class="text-sm font-semibold text-teal-700">
                Satu langkah terakhir
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">
                Pilih avatar kamu
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Pilih karakter kecil untuk tampil di workspace. Bisa diganti
                nanti.
            </p>
        </div>

        <form class="mt-7" @submit.prevent="submit">
            <fieldset>
                <legend class="text-sm font-semibold text-slate-900">
                    Avatar tersedia
                </legend>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <button
                        v-for="option in options"
                        :key="option.key"
                        type="button"
                        class="flex min-h-28 flex-col items-center justify-center rounded-xl border p-3 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                        :class="
                            form.avatar_key === option.key
                                ? 'border-teal-700 bg-teal-50 text-teal-950'
                                : 'border-slate-200 bg-white text-slate-700 hover:border-teal-300'
                        "
                        :aria-pressed="form.avatar_key === option.key"
                        @click="selectAvatar(option.key)"
                    >
                        <AvatarIcon
                            :avatar-key="option.key"
                            :label="`Avatar ${option.name}`"
                            class="h-12 w-12 p-2"
                        />
                        <span class="mt-2 text-sm font-semibold">{{
                            option.name
                        }}</span>
                        <span class="mt-0.5 text-xs text-slate-500">{{
                            option.description
                        }}</span>
                    </button>
                </div>
                <InputError class="mt-3" :message="form.errors.avatar_key" />
            </fieldset>

            <button
                type="submit"
                class="mt-7 inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing || !form.avatar_key"
            >
                Gunakan avatar ini
            </button>
        </form>
    </GuestLayout>
</template>
