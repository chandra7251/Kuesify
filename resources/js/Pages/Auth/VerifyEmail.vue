<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi email" />

        <div>
            <p class="text-sm font-semibold text-brand-primary">
                Satu langkah lagi
            </p>
            <h1
                class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950"
            >
                Verifikasi email kamu
            </h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                Kami mengirim tautan verifikasi ke emailmu. Buka tautan itu
                untuk masuk ke workspace dan mulai belajar.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800"
        >
            Tautan verifikasi baru sudah dikirim ke emailmu.
        </div>

        <form class="mt-7" @submit.prevent="submit">
            <button
                type="submit"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-primary px-4 text-sm font-semibold text-white transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing"
            >
                Kirim ulang email verifikasi
            </button>

            <p class="mt-5 text-center text-sm text-slate-600">
                Salah alamat email?
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="font-semibold text-brand-primary hover:text-brand-hover focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:ring-offset-2"
                >
                    Keluar dan daftar lagi
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
