<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ pin: '', alias: '' });

function join(): void {
    form.post(route('live-sessions.join'));
}
</script>

<template>
    <Head title="Masuk Live Quiz" />
    <main class="grid min-h-screen place-items-center bg-[#f4fbfa] px-4 py-8">
        <section
            class="w-full max-w-md rounded-3xl border border-teal-100 bg-white p-6 shadow-sm sm:p-8"
        >
            <Link href="/" class="text-sm font-extrabold text-teal-700"
                >Kuesify</Link
            >
            <p
                class="mt-8 text-xs font-bold uppercase tracking-[0.18em] text-teal-700"
            >
                Live Quiz
            </p>
            <h1
                class="mt-2 text-3xl font-extrabold tracking-tight text-teal-950"
            >
                Masuk ke sesi
            </h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Masukkan PIN dari host dan nama panggilan yang tampil di
                leaderboard.
            </p>
            <form class="mt-7 space-y-5" @submit.prevent="join">
                <label class="block"
                    ><span class="text-sm font-bold text-slate-800"
                        >PIN enam digit</span
                    ><input
                        v-model="form.pin"
                        inputmode="numeric"
                        maxlength="6"
                        autocomplete="one-time-code"
                        class="mt-2 block min-h-12 w-full rounded-xl border-slate-200 text-center text-xl font-extrabold tracking-[0.4em] text-slate-900 focus:border-teal-600 focus:ring-teal-600"
                        aria-describedby="pin-error"
                    /><span
                        v-if="form.errors.pin"
                        id="pin-error"
                        class="mt-1 block text-sm font-semibold text-red-600"
                        >{{ form.errors.pin }}</span
                    ></label
                >
                <label class="block"
                    ><span class="text-sm font-bold text-slate-800"
                        >Nama panggilan</span
                    ><input
                        v-model="form.alias"
                        maxlength="40"
                        autocomplete="nickname"
                        class="mt-2 block min-h-12 w-full rounded-xl border-slate-200 text-slate-900 focus:border-teal-600 focus:ring-teal-600"
                    /><span
                        v-if="form.errors.alias"
                        class="mt-1 block text-sm font-semibold text-red-600"
                        >{{ form.errors.alias }}</span
                    ></label
                >
                <button
                    type="submit"
                    class="min-h-12 w-full rounded-xl bg-teal-700 px-4 text-sm font-extrabold text-white transition hover:bg-teal-800 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Masuk…' : 'Masuk sesi' }}
                </button>
            </form>
        </section>
    </main>
</template>
