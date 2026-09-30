<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{ canLogin: boolean; canRegister: boolean }>();

const answer = ref<string | null>(null);
const answers = ['Produsen', 'Konsumen', 'Pengurai', 'Predator'];
const featureIndex = ref(0);

const scrollFeatures = (direction: number) => {
    const nextIndex = Math.max(0, Math.min(2, featureIndex.value + direction));
    featureIndex.value = nextIndex;
};
</script>

<template>
    <Head title="Kuesify — Belajar jadi hidup" />
    <main class="overflow-hidden bg-brand-accent text-brand-primary">
        <section
            class="relative isolate overflow-hidden px-5 pb-20 pt-5 sm:px-8 lg:px-12"
        >
            <div
                class="pointer-events-none absolute inset-0 -z-10 overflow-hidden"
            >
                <div
                    class="absolute -left-40 -top-48 h-[35rem] w-[35rem] rounded-full border border-brand-secondary/30"
                ></div>
                <div
                    class="absolute -left-20 -top-24 h-[28rem] w-[28rem] rounded-full border border-brand-secondary/30"
                ></div>
                <div
                    class="absolute right-[-12rem] top-20 h-[28rem] w-[28rem] rounded-full bg-brand-secondary/10"
                ></div>
                <div
                    class="absolute left-1/4 top-20 h-72 w-72 rounded-full bg-brand-secondary/20 opacity-60 blur-3xl"
                ></div>
            </div>

            <div class="-mx-5 -mt-5 bg-brand-primary px-5 py-4 sm:-mx-8 sm:px-8 lg:-mx-12 lg:px-12">
                <nav
                    class="mx-auto flex max-w-7xl items-center justify-between gap-4"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-secondary"
                >
                    <ApplicationLogo class="h-10 w-10 text-brand-secondary" />
                    <span class="text-xl font-black tracking-tight text-brand-secondary"
                        >kuesify</span
                    >
                </Link>
                <div
                    class="hidden items-center gap-7 text-sm font-bold text-white/80 md:flex"
                >
                    <a href="#fitur" class="hover:text-brand-secondary">Fitur</a
                    ><a href="#cara-kerja" class="hover:text-brand-secondary"
                        >Cara kerja</a
                    ><a href="#untuk-siapa" class="hover:text-brand-secondary"
                        >Untuk siapa</a
                    >
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="min-h-11 rounded-xl px-3 py-3 text-sm font-extrabold text-brand-secondary hover:bg-white/10 sm:px-4"
                        >Masuk</Link
                    >
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="min-h-11 rounded-xl bg-brand-secondary px-4 py-3 text-sm font-extrabold text-white shadow-figma transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:px-5"
                        >Mulai gratis</Link
                    >
                </div>
                </nav>
            </div>

            <div
                class="mx-auto grid max-w-7xl items-center gap-12 pt-16 lg:grid-cols-[1.02fr_0.98fr] lg:pb-10 lg:pt-24"
            >
                <div class="max-w-2xl">
                    <h1
                        class="mt-6 text-5xl font-black leading-[0.96] tracking-[-0.065em] sm:text-6xl lg:text-7xl"
                    >
                        Bukan cuma jawab soal.<br /><span class="text-brand-primary"
                            >Rasakan</span
                        >
                        proses belajarnya.
                    </h1>
                    <p
                        class="mt-6 max-w-xl text-lg leading-8 text-brand-primary/75 sm:text-xl"
                    >
                        Kuesify menyatukan quiz live, latihan mandiri, dan
                        materi interaktif untuk kelas yang lebih aktif dari awal
                        sampai akhir.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-brand-secondary px-6 py-4 text-sm font-black text-white shadow-figma transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none"
                            >Buat quiz pertama →</Link
                        >
                        <Link
                            href="/join"
                            class="min-h-13 rounded-2xl bg-brand-primary px-6 py-4 text-sm font-black text-white transition hover:bg-brand-hover"
                            >Masuk dengan PIN</Link
                        >
                    </div>
                    <div class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-bold text-brand-primary/75">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-secondary" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12.5 9.5 17 19 7.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Kuis interaktif untuk kelas
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-secondary" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 17V7m0 10 4-4 3 3 5-6 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Hasil belajar langsung terlihat
                        </span>
                    </div>
                </div>

                <div class="relative mx-auto flex w-full max-w-2xl items-center justify-center self-center lg:justify-end">
                    <div class="pointer-events-none absolute bottom-8 right-4 h-64 w-64 rounded-full bg-brand-secondary/10 blur-3xl sm:h-80 sm:w-80" />
                    <img
                        src="/images/learning-characters.png"
                        alt="Dua siswa belajar bersama menggunakan laptop"
                        class="relative z-10 w-full max-w-2xl object-contain object-bottom drop-shadow-[0_22px_26px_rgba(35,62,168,0.16)]"
                    />
                </div>
            </div>
        </section>

        <section
            id="fitur"
            class="relative overflow-hidden bg-brand-primary px-5 py-20 text-white sm:px-8 lg:px-12 lg:pb-20 lg:pt-30"
        >
            <div class="pointer-events-none absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-brand-secondary/10" />
            <div class="pointer-events-none absolute -right-32 top-10 h-80 w-80 rounded-full border border-white/10" />

            <div class="relative mx-auto my-auto grid max-w-9xl translate-x-8 items-start gap-18 md:grid-cols-[0.78fr_1.22fr] md:gap-16">
                <div class="relative order-2 mx-120 flex w-full max-w-md items-end justify-center md:order-1 md:justify-start">
                    <div class="pointer-events-none absolute bottom-8 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-brand-secondary/15 blur-3xl" />
                    <img
                        src="/images/study-character-female.png"
                        alt="Siswa belajar menggunakan laptop"
                        class="relative z-10 w-full object-contain object-bottom drop-shadow-[0_22px_26px_rgba(35,62,168,0.24)]"
                    />
                </div>

                <div class="order-1 min-w-0 md:order-2">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-lime">Satu platform, tiga mode</p>
                    <h2 class="mt-4 max-w-2xl text-4xl font-black leading-[1.02] tracking-[-0.055em] sm:text-5xl">
                        Belajar aktif, dengan cara yang terasa pas.
                    </h2>
                    <p class="mt-5 max-w-xl text-base leading-7 text-white/70">
                        Pilih pengalaman belajar yang sesuai dengan energi kelas, waktu peserta, dan materi yang sudah tersedia.
                    </p>

                                        <div class="relative mx-auto mt-9 h-[23rem] w-full max-w-[54rem]">
                        <button
                            type="button"
                            class="feature-nav-prev absolute left-2 top-1/2 z-30 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white text-xl font-black text-brand-primary shadow-figma transition hover:bg-brand-secondary"
                            aria-label="Mode sebelumnya"
                            @click="scrollFeatures(-1)"
                        >
                            ‹
                        </button>
                        <div class="feature-card-stage">
                            <article :class="['feature-card', featureIndex === 0 ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma' : featureIndex === 1 ? 'feature-card-prev z-10 border-white/15 bg-brand-surface text-brand-secondary' : 'feature-card-hidden z-0 border-white/15 bg-brand-surface text-brand-secondary']">
                                <div class="flex items-center justify-between">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/70 text-sm font-black">01</span>
                                    <span class="text-xs font-black uppercase tracking-[0.16em] text-inherit">Realtime</span>
                                </div>
                                <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Live Quiz</h3>
                                <p class="mt-3 text-sm leading-6 text-inherit">Buka room, bagikan PIN, dan lihat kelas merespons soal secara langsung.</p>
                                <Link href="/join" class="mt-8 inline-block text-sm font-black text-inherit">Masuk room <span aria-hidden="true">→</span></Link>
                            </article>
                            <article :class="['feature-card', featureIndex === 1 ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma' : featureIndex === 0 ? 'feature-card-next z-10 border-white/15 bg-brand-surface text-brand-secondary' : 'feature-card-prev z-10 border-white/15 bg-brand-surface text-brand-secondary']">
                                <div class="flex items-center justify-between">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl border border-brand-secondary/60 text-sm font-black text-inherit">02</span>
                                    <span class="text-xs font-black uppercase tracking-[0.16em] text-inherit">Mandiri</span>
                                </div>
                                <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Self-Paced</h3>
                                <p class="mt-3 text-sm leading-6 text-inherit">Susun latihan dengan deadline jelas dan biarkan peserta belajar sesuai tempo.</p>
                                <Link href="/register" class="mt-8 inline-block text-sm font-black text-inherit">Mulai latihan <span aria-hidden="true">→</span></Link>
                            </article>
                            <article :class="['feature-card', featureIndex === 2 ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma' : featureIndex === 1 ? 'feature-card-next z-10 border-white/15 bg-brand-surface text-brand-secondary' : 'feature-card-hidden z-0 border-white/15 bg-brand-surface text-brand-secondary']">
                                <div class="flex items-center justify-between">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl border border-brand-secondary/60 text-sm font-black text-inherit">03</span>
                                    <span class="text-xs font-black uppercase tracking-[0.16em] text-inherit">Berbantuan AI</span>
                                </div>
                                <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Materi ke Soal</h3>
                                <p class="mt-3 text-sm leading-6 text-inherit">Mulai dari PDF atau PPTX, buat draft soal, lalu review sebelum dibagikan.</p>
                                <Link href="/materials" class="mt-8 inline-block text-sm font-black text-inherit">Lihat materi <span aria-hidden="true">→</span></Link>
                            </article>
                        </div>
                        <button
                            type="button"
                            class="feature-nav-next absolute right-2 top-1/2 z-30 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white text-xl font-black text-brand-primary shadow-figma transition hover:bg-brand-secondary"
                            aria-label="Mode berikutnya"
                            @click="scrollFeatures(1)"
                        >
                            ›
                        </button>
                    </div>                    <p class="mt-2 text-xs font-bold text-white/45"></p>
                </div>
            </div>
        </section>

        <section id="cara-kerja" class="px-5 py-20 sm:px-8 lg:px-12">
            <div
                class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.8fr_1.2fr]"
            >
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                    >
                        Dari materi sampai podium
                    </p>
                    <h2x
                        class="mt-4 text-4xl font-black tracking-[-0.05em] sm:text-5xl"
                    >
                        Tidak perlu pindah-pindah aplikasi.
                    </h2x>
                    <p class="mt-5 max-w-md text-lg leading-8 text-brand-primary/70">
                        Creator menyusun soal. Peserta belajar. Semua hasil
                        kembali ke satu workspace yang rapi.
                    </p>
                </div>
                <ol class="space-y-4">
                    <li
                        v-for="[number, title, copy, color] in [
                            [
                                '01',
                                'Buat atau impor soal',
                                'Tulis sendiri, import CSV/XLSX, atau mulai dari materi.',
                                'bg-brand-secondary/15 text-brand-primary',
                            ],
                            [
                                '02',
                                'Jalankan live atau bagikan latihan',
                                'PIN enam digit untuk sesi live. Deadline untuk tugas mandiri.',
                                'bg-brand-secondary/20 text-brand-primary',
                            ],
                            [
                                '03',
                                'Pantau hasil dan beri feedback',
                                'Nilai otomatis, essay manual, score, progress, dan gradebook.',
                                'bg-brand-secondary/20 text-brand-primary',
                            ],
                        ]"
                        :key="number"
                        class="grid gap-4 rounded-3xl border border-brand-primary/15 bg-white p-5 sm:grid-cols-[3.5rem_1fr] sm:items-center"
                    >
                        <span
                            class="grid h-14 w-14 place-items-center rounded-2xl text-xl font-black"
                            :class="color"
                            >{{ number }}</span
                        >
                        <div>
                            <h3 class="text-xl font-black">{{ title }}</h3>
                            <p class="mt-1 text-brand-primary/65">{{ copy }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section id="untuk-siapa" class="px-5 pb-20 sm:px-8 lg:px-12">
            <div
                class="mx-auto max-w-7xl rounded-[2rem] bg-brand-secondary/10 p-8 sm:p-12"
            >
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                        >
                            Untuk kelas yang ingin bergerak
                        </p>
                        <h2
                            class="mt-4 max-w-3xl text-4xl font-black tracking-[-0.05em] text-brand-primary sm:text-5xl"
                        >
                            Satu layar untuk creator. Satu PIN untuk seluruh
                            kelas.
                        </h2>
                        <p
                            class="mt-5 max-w-2xl text-lg leading-8 text-brand-primary/75"
                        >
                            Dipakai untuk pembelajaran, bimbingan belajar, acara
                            komunitas, dan demo kompetisi yang perlu terasa
                            hidup.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-brand-primary px-6 py-4 text-sm font-black text-white shadow-figma"
                            >Buat akun gratis</Link
                        ><Link
                            href="/join"
                            class="min-h-13 rounded-2xl border-2 border-brand-primary/35 bg-white px-6 py-4 text-sm font-black text-brand-primary"
                            >Punya PIN? Masuk</Link
                        >
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-brand-primary/15 px-5 py-8 sm:px-8 lg:px-12">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 text-sm text-brand-primary/65"
            >
                <div class="flex items-center gap-2 font-black text-brand-primary">
                    <ApplicationLogo class="h-7 w-7 text-brand-secondary" /> kuesify
                </div>
                <p>
                    Platform edukasi interaktif untuk belajar yang lebih hidup.
                </p>
                <Link
                    href="/login"
                    class="font-bold text-brand-primary hover:underline"
                    >Masuk →</Link
                >
            </div>
        </footer>
    </main>
</template>

<style>
html {
    scroll-behavior: smooth;
}

@media (prefers-reduced-motion: reduce) {
    html {
        scroll-behavior: auto;
    }
}
</style>
<style>


.feature-copy {
    padding-inline-start: max(0rem, calc(50% - 10rem));
}

@media (max-width: 767px) {
    .feature-copy {
        padding-inline-start: 0;
    }
}
.feature-card-stage {
    position: relative;
    height: 100%;
    overflow: hidden;
}

.feature-card {
    position: absolute;
    left: 50%;
    top: 0;
    width: min(76vw, 20rem);
    height: 22rem;
    border-radius: 1.5rem;
    border-width: 1px;
    padding: 1.5rem;
    transition: transform 450ms ease, opacity 300ms ease;
}

.feature-card-active {
    transform: translateX(-50%) scale(1);
    opacity: 1;
}

@media (min-width: 1024px) {
    .feature-nav-prev {
        left: calc(50% - 12rem);
    }

    .feature-nav-next {
        right: calc(50% - 12rem);
    }
}
.feature-card-prev {
    transform: translateX(calc(-50% - 10rem)) rotate(-10deg) scale(0.92);
    opacity: 0.45;
    pointer-events: none;
}

.feature-card-next {
    transform: translateX(calc(-50% + 10rem)) rotate(10deg) scale(0.92);
    opacity: 0.45;
    pointer-events: none;
}

.feature-card-hidden {
    transform: translateX(-50%) scale(0.86);
    opacity: 0;
    pointer-events: none;
}

@media (max-width: 639px) {
    .feature-card {
        width: min(76vw, 20rem);
    }

@media (min-width: 1024px) {
    .feature-nav-prev {
        left: calc(50% - 12rem);
    }

    .feature-nav-next {
        right: calc(50% - 12rem);
    }
}
.feature-card-prev {
        transform: translateX(calc(-50% - 7rem)) rotate(-8deg) scale(0.9);
    }

    .feature-card-next {
        transform: translateX(calc(-50% + 7rem)) rotate(8deg) scale(0.9);
    }
}
</style>