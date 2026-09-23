<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

withDefaults(defineProps<{ contentClass?: string; compact?: boolean }>(), {
    contentClass: 'max-w-md',
});
</script>

<template>
    <div
        class="min-h-screen overflow-x-hidden bg-brand-accent text-slate-900 lg:grid lg:h-screen lg:grid-cols-[0.95fr_1.05fr] lg:overflow-hidden"
    >
        <section
            class="relative hidden min-h-screen overflow-hidden bg-brand-primary px-12 py-10 text-white lg:flex lg:flex-col xl:px-16"
        >
            <div
                class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-secondary/20"
            />
            <div
                class="absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-brand-dark/70"
            />
            <div
                class="absolute right-20 top-1/3 h-24 w-24 rounded-full border border-brand-secondary/30"
            />

            <Link
                href="/"
                class="relative inline-flex w-fit items-center gap-3"
            >
                <span
                    class="grid h-11 w-11 place-items-center rounded-2xl bg-white shadow-sm"
                >
                    <ApplicationLogo class="h-7 w-7 text-brand-secondary" />
                </span>
                <span class="text-xl font-extrabold tracking-tight"
                    >Kuesify</span
                >
            </Link>

            <div class="relative my-auto max-w-xl py-16">
                <p
                    class="mb-5 inline-flex rounded-full bg-brand-secondary px-3 py-1 text-sm font-extrabold text-[#102449]"
                >
                    Platform belajar interaktif
                </p>
                <h2
                    class="max-w-xl text-4xl font-black leading-tight tracking-tight xl:text-5xl"
                >
                    Belajar lebih hidup, satu kuis setiap waktu.
                </h2>
                <p class="mt-6 max-w-lg text-base leading-7 text-white/80">
                    Buat kuis, jalankan sesi langsung, dan lihat perkembangan
                    belajar dalam satu ruang kerja.
                </p>
            </div>

            <div class="relative grid grid-cols-2 gap-3 text-sm">
                <div
                    class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm"
                >
                    <p class="font-bold text-brand-secondary">Kuis langsung</p>
                    <p class="mt-1 text-white/75">Cepat, seru, realtime.</p>
                </div>
                <div
                    class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm"
                >
                    <p class="font-bold text-brand-secondary">
                        Belajar mandiri
                    </p>
                    <p class="mt-1 text-white/75">Latihan sesuai ritme.</p>
                </div>
            </div>
        </section>

        <main
            class="auth-scroll relative flex min-h-screen justify-center px-4 py-5 sm:px-8 sm:py-10 lg:h-screen lg:min-h-0 lg:items-center"
            :class="
                compact
                    ? 'items-center overflow-hidden'
                    : 'items-start lg:overflow-y-auto'
            "
        >
            <div
                class="pointer-events-none absolute -right-16 top-8 h-40 w-40 rounded-full bg-brand-secondary/10 sm:h-56 sm:w-56"
            />

            <div
                class="pointer-events-none absolute -bottom-20 -left-20 h-52 w-52 rounded-full bg-brand-primary/10 sm:h-72 sm:w-72"
            />

            <div class="relative w-full" :class="contentClass">
                <Link
                    href="/"
                    class="mx-auto mb-5 inline-flex items-center gap-3 sm:mx-0 sm:mb-6 lg:hidden"
                >
                    <span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-brand-primary shadow-sm"
                    >
                        <ApplicationLogo class="h-6 w-6 text-brand-secondary" />
                    </span>
                    <span
                        class="text-lg font-extrabold tracking-tight text-brand-primary"
                        >Kuesify</span
                    >
                </Link>

                <div
                    class="rounded-[1.75rem] border border-white/70 bg-white p-5 shadow-figma sm:rounded-[2rem] sm:p-9"
                >
                    <slot />
                </div>

                <p class="mt-4 text-center text-xs text-slate-500 sm:mt-5">
                    Kuesify · Ruang belajar interaktif untuk semua.
                </p>
            </div>
        </main>
    </div>
</template>

<style scoped>
.auth-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.auth-scroll::-webkit-scrollbar {
    display: none;
}
</style>
