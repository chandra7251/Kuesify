<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa kata sandi" />

        <div class="mb-7">
            <p class="text-sm font-semibold text-brand-primary">Bantuan akun</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Lupa kata sandi?
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Masukkan email akunmu. Kami akan mengirim tautan untuk membuat
                kata sandi baru.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-brand-secondary/30 bg-brand-secondary/10 px-3 py-2 text-sm font-medium text-brand-primary"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6 flex items-center justify-between gap-3">
                <Link
                    :href="route('login')"
                    class="text-xs font-semibold text-brand-primary transition hover:text-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                >
                    ← Kembali
                </Link>
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim tautan reset
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
