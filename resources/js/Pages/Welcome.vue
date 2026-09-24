<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{ canLogin: boolean; canRegister: boolean }>();

const answer = ref<string | null>(null);
const answers = ['Produsen', 'Konsumen', 'Pengurai', 'Predator'];
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

            <nav
                class="mx-auto flex max-w-7xl items-center justify-between gap-4"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-primary"
                >
                    <ApplicationLogo class="h-10 w-10 text-brand-secondary" />
                    <span class="text-xl font-black tracking-tight"
                        >kuesify</span
                    >
                </Link>
                <div
                    class="hidden items-center gap-7 text-sm font-bold text-brand-primary/80 md:flex"
                >
                    <a href="#fitur" class="hover:text-brand-primary">Fitur</a
                    ><a href="#cara-kerja" class="hover:text-brand-primary"
                        >Cara kerja</a
                    ><a href="#untuk-siapa" class="hover:text-brand-primary"
                        >Untuk siapa</a
                    >
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="min-h-11 rounded-xl px-3 py-3 text-sm font-extrabold hover:bg-white/60 sm:px-4"
                        >Masuk</Link
                    >
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="min-h-11 rounded-xl bg-brand-primary px-4 py-3 text-sm font-extrabold text-white shadow-figma transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:px-5"
                        >Mulai gratis</Link
                    >
                </div>
            </nav>

            <div
                class="mx-auto grid max-w-7xl items-center gap-12 pt-16 lg:grid-cols-[1.02fr_0.98fr] lg:pb-10 lg:pt-24"
            >
                <div class="max-w-2xl">
                    <p
                        class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/60 bg-white/75 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-brand-primary"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-brand-secondary"
                        ></span>
                        Kuis yang bikin kelas ikut hidup
                    </p>
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

                <div class="relative mx-auto w-full max-w-xl">                    <div
                        class="absolute -left-8 top-12 hidden rotate-[-7deg] rounded-2xl bg-brand-lime px-4 py-3 text-sm font-black text-brand-primary shadow-lg sm:block"
                    >
                        +100 XP
                    </div>
                    <div
                        class="absolute -right-3 bottom-8 z-10 hidden rotate-[7deg] rounded-2xl bg-brand-lime px-4 py-3 text-sm font-black text-brand-primary shadow-lg sm:block"
                    >
                        🔥 3 hari streak
                    </div>
                    <article
                        class="rounded-[2rem] border-[7px] border-white bg-brand-primary p-5 shadow-figma-hover sm:p-7"
                    >
                        <div
                            class="flex items-center justify-between text-white"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-xl bg-brand-secondary text-lg text-brand-primary"
                                    >✦</span
                                >
                                <div>
                                    <p class="text-xs font-bold text-brand-secondary/70">
                                        LIVE QUIZ
                                    </p>
                                    <p class="font-black">Ekosistem kelas 8</p>
                                </div>
                            </div>

                        </div>
                        <div
                            class="mt-7 rounded-[1.5rem] bg-white p-5 sm:p-7"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <p
                                    class="text-xs font-black uppercase tracking-[0.14em] text-brand-primary"
                                >
                                    Soal 3 dari 10
                                </p>
                                <span
                                    class="rounded-full bg-brand-secondary/10 px-3 py-1 text-xs font-black text-brand-primary"
                                    >100 poin</span
                                >
                            </div>
                            <h2
                                class="mt-5 text-2xl font-black leading-tight tracking-[-0.035em] text-brand-primary sm:text-3xl"
                            >
                                Makhluk hidup yang membuat makanan sendiri
                                disebut?
                            </h2>
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="(choice, index) in answers"
                                    :key="choice"
                                    class="min-h-14 rounded-2xl border-2 px-4 text-left text-sm font-black transition"
                                    :class="
                                        answer === choice
                                            ? 'border-brand-primary bg-brand-secondary/20 text-brand-primary'
                                            : 'border-brand-primary/15 bg-white text-brand-primary/80 hover:-translate-y-0.5 hover:border-brand-secondary/60'
                                    "
                                    @click="answer = choice"
                                >
                                    <span
                                        class="mr-3 inline-grid h-7 w-7 place-items-center rounded-lg text-xs"
                                        :class="answer === choice ? 'bg-brand-primary text-brand-secondary' : 'bg-brand-accent text-brand-primary/60'"
                                    >{{
                                        String.fromCharCode(65 + index)
                                    }}</span
                                    >{{ choice }}
                                </button>
                            </div>
                            <p
                                v-if="answer"
                                class="mt-4 text-sm font-bold text-brand-primary"
                            >
                                Pilihanmu sudah tercatat.
                            </p>
                        </div>
                        <div
                            class="mt-5 flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-sm text-white"
                        >
                            <span class="font-bold">Peserta aktif <strong class="text-brand-secondary">34</strong></span>
                            <span class="font-bold">Rani <span class="text-brand-secondary">1.920</span></span>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="fitur"
            class="relative overflow-hidden bg-brand-primary px-5 py-20 text-white sm:px-8 lg:px-12 lg:py-24"
        >
            <div class="pointer-events-none absolute -right-28 top-12 h-72 w-72 rounded-full bg-brand-secondary/10" />
            <div class="pointer-events-none absolute -bottom-40 left-1/3 h-80 w-80 rounded-full border border-white/10" />

            <div class="relative mx-auto max-w-7xl">
                <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-lime">Satu platform, tiga mode</p>
                        <h2 class="mt-4 text-4xl font-black leading-[1.02] tracking-[-0.055em] sm:text-5xl">Belajar aktif, dengan cara yang terasa pas.</h2>
                    </div>
                    <p class="max-w-sm text-base leading-7 text-white/65">Pilih pengalaman belajar yang sesuai dengan energi kelas, waktu peserta, dan materi yang sudah tersedia.</p>
                </div>

                <div class="mt-12 grid gap-4 md:grid-cols-3">
                    <article class="group rounded-3xl bg-brand-dark p-6 transition duration-300 hover:-translate-y-1 hover:shadow-figma-hover sm:p-7">
                        <div class="flex items-center justify-between"><span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-lime text-sm font-black text-brand-primary">01</span><span class="text-xs font-black uppercase tracking-[0.16em] text-brand-lime">Realtime</span></div>
                        <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Live Quiz</h3>
                        <p class="mt-3 min-h-14 text-sm leading-6 text-white/65">Buka room, bagikan PIN, dan lihat kelas merespons soal secara langsung.</p>
                        <div class="mt-8 flex items-center justify-between border-t border-white/10 pt-4"><span class="text-xs font-bold text-white/50">34 peserta aktif</span><Link href="/join" class="text-sm font-black text-brand-lime">Masuk room <span aria-hidden="true">→</span></Link></div>
                    </article>

                    <article class="group rounded-3xl border border-white/15 bg-white/10 p-6 transition duration-300 hover:-translate-y-1 hover:bg-white/15 hover:shadow-figma-hover sm:p-7">
                        <div class="flex items-center justify-between"><span class="grid h-10 w-10 place-items-center rounded-xl border border-brand-lime/60 text-sm font-black text-brand-lime">02</span><span class="text-xs font-black uppercase tracking-[0.16em] text-white/55">Mandiri</span></div>
                        <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Self-Paced</h3>
                        <p class="mt-3 min-h-14 text-sm leading-6 text-white/65">Susun latihan dengan deadline jelas dan biarkan peserta belajar sesuai tempo.</p>
                        <div class="mt-8 flex items-center justify-between border-t border-white/10 pt-4"><span class="text-xs font-bold text-white/50">Progress terukur</span><Link href="/register" class="text-sm font-black text-brand-lime">Mulai latihan <span aria-hidden="true">→</span></Link></div>
                    </article>

                    <article class="group rounded-3xl bg-brand-lime p-6 text-brand-primary transition duration-300 hover:-translate-y-1 hover:shadow-figma-hover sm:p-7">
                        <div class="flex items-center justify-between"><span class="grid h-10 w-10 place-items-center rounded-xl bg-white/70 text-sm font-black">03</span><span class="text-xs font-black uppercase tracking-[0.16em] text-brand-primary/65">Berbantuan AI</span></div>
                        <h3 class="mt-12 text-2xl font-black tracking-[-0.03em]">Materi ke Soal</h3>
                        <p class="mt-3 min-h-14 text-sm leading-6 text-brand-primary/70">Mulai dari PDF atau PPTX, buat draft soal, lalu review sebelum dibagikan.</p>
                        <div class="mt-8 flex items-center justify-between border-t border-brand-primary/15 pt-4"><span class="text-xs font-bold text-brand-primary/60">Dari file jadi kuis</span><Link href="/materials" class="text-sm font-black text-brand-primary">Lihat materi <span aria-hidden="true">→</span></Link></div>
                    </article>
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
                    <h2
                        class="mt-4 text-4xl font-black tracking-[-0.05em] sm:text-5xl"
                    >
                        Tidak perlu pindah-pindah aplikasi.
                    </h2>
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
