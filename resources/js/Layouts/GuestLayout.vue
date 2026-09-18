<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

withDefaults(defineProps<{ contentClass?: string }>(), {
    contentClass: 'max-w-md',
});
</script>

<template>
    <div
        class="min-h-screen bg-[#f4f8fa] text-slate-900 lg:grid lg:grid-cols-2"
    >
        <section
            class="relative hidden overflow-hidden bg-[#3451b5] px-12 py-10 text-white lg:flex lg:flex-col"
        >
            <div
                class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#a8d8f0]/30"
            ></div>
            <div
                class="absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-[#6b63c8]/30"
            ></div>

            <Link
                href="/"
                class="relative inline-flex w-fit items-center gap-3"
            >
                <ApplicationLogo class="h-11 w-11 fill-current text-white" />
                <span class="text-xl font-extrabold tracking-tight"
                    >Kuesify</span
                >
            </Link>

            <div class="relative my-auto max-w-lg">
                <p
                    class="mb-5 inline-flex rounded-full bg-white/15 px-3 py-1 text-sm font-semibold text-[#d9fff7]"
                >
                    Platform belajar interaktif
                </p>
                <h2
                    class="text-4xl font-black leading-tight tracking-tight xl:text-5xl"
                >
                    Belajar lebih hidup, satu kuis setiap waktu.
                </h2>
                <p class="mt-6 max-w-md text-base leading-7 text-[#e0ecff]">
                    Buat kuis, jalankan sesi langsung, dan lihat perkembangan
                    belajar dalam satu ruang kerja.
                </p>
            </div>

            <div class="relative grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="font-bold">Kuis langsung</p>
                    <p class="mt-1 text-[#e0ecff]">Cepat, seru, realtime.</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                    <p class="font-bold">Belajar mandiri</p>
                    <p class="mt-1 text-[#e0ecff]">Latihan sesuai ritme.</p>
                </div>
            </div>
        </section>

        <main
            class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8"
        >
            <div class="w-full" :class="contentClass">
                <Link
                    href="/"
                    class="mb-8 inline-flex items-center gap-2 lg:hidden"
                >
                    <ApplicationLogo
                        class="h-10 w-10 fill-current text-teal-700"
                    />
                    <span class="text-lg font-extrabold tracking-tight"
                        >Kuesify</span
                    >
                </Link>

                <div
                    class="rounded-3xl border border-[#d8e3e8] bg-white p-6 shadow-lg shadow-slate-300/50 sm:p-8"
                >
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
