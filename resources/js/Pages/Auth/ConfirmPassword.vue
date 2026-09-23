<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout compact>
        <Head title="Konfirmasi kata sandi" />

        <div class="mb-7">
            <p class="text-sm font-semibold text-brand-primary">Area aman</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Konfirmasi kata sandi
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Konfirmasi kata sandimu sebelum melanjutkan ke area ini.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Kata sandi" />
                <PasswordInput
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                >
                </PasswordInput>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6 flex justify-end">
                <PrimaryButton
                    class="ms-0 w-full justify-center sm:ms-4 sm:w-auto"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Konfirmasi
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
