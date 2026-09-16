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
    <main class="overflow-hidden bg-[#f7f7f2] text-[#122a35]">
        <section
            class="relative isolate overflow-hidden px-5 pb-20 pt-5 sm:px-8 lg:px-12"
        >
            <div
                class="pointer-events-none absolute inset-0 -z-10 overflow-hidden"
            >
                <div
                    class="absolute -left-40 -top-48 h-[35rem] w-[35rem] rounded-full border border-[#91dfc0]"
                ></div>
                <div
                    class="absolute -left-20 -top-24 h-[28rem] w-[28rem] rounded-full border border-[#91dfc0]"
                ></div>
                <div
                    class="absolute right-[-12rem] top-20 h-[28rem] w-[28rem] rounded-full bg-[#d7f7e6]"
                ></div>
                <div
                    class="absolute left-1/4 top-20 h-72 w-72 rounded-full bg-[#ffe486] opacity-60 blur-3xl"
                ></div>
            </div>

            <nav
                class="mx-auto flex max-w-7xl items-center justify-between gap-4"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#16775a]"
                >
                    <ApplicationLogo class="h-10 w-10 text-[#16775a]" />
                    <span class="text-xl font-black tracking-tight"
                        >kuesify</span
                    >
                </Link>
                <div
                    class="hidden items-center gap-7 text-sm font-bold text-[#31515d] md:flex"
                >
                    <a href="#fitur" class="hover:text-[#16775a]">Fitur</a
                    ><a href="#cara-kerja" class="hover:text-[#16775a]"
                        >Cara kerja</a
                    ><a href="#untuk-siapa" class="hover:text-[#16775a]"
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
                        class="min-h-11 rounded-xl bg-[#16775a] px-4 py-3 text-sm font-extrabold text-white shadow-[0_7px_0_#0d503e] transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:px-5"
                        >Mulai gratis</Link
                    >
                </div>
            </nav>

            <div
                class="mx-auto grid max-w-7xl items-center gap-12 pt-16 lg:grid-cols-[1.02fr_0.98fr] lg:pb-10 lg:pt-24"
            >
                <div class="max-w-2xl">
                    <p
                        class="inline-flex items-center gap-2 rounded-full border border-[#8ad6b8] bg-white/75 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-[#126649]"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-[#f36f56]"
                        ></span>
                        Kuis yang bikin kelas ikut hidup
                    </p>
                    <h1
                        class="mt-6 text-5xl font-black leading-[0.96] tracking-[-0.065em] sm:text-6xl lg:text-7xl"
                    >
                        Bukan cuma jawab soal.<br /><span class="text-[#16775a]"
                            >Rasakan</span
                        >
                        proses belajarnya.
                    </h1>
                    <p
                        class="mt-6 max-w-xl text-lg leading-8 text-[#49636d] sm:text-xl"
                    >
                        Kuesify menyatukan quiz live, latihan mandiri, dan
                        materi interaktif untuk kelas yang lebih aktif dari awal
                        sampai akhir.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-[#f36f56] px-6 py-4 text-sm font-black text-white shadow-[0_8px_0_#cc483b] transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none"
                            >Buat quiz pertama →</Link
                        >
                        <Link
                            href="/join"
                            class="min-h-13 rounded-2xl border-2 border-[#b7d5c6] bg-white/80 px-6 py-4 text-sm font-black text-[#16775a] hover:border-[#16775a]"
                            >Masuk dengan PIN</Link
                        >
                    </div>
                    <div
                        class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-bold text-[#49636d]"
                    >
                        <span>✓ Tidak perlu kartu kredit</span
                        ><span>⚡ Siap untuk kelas live</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div
                        class="absolute -left-8 top-12 hidden rotate-[-7deg] rounded-2xl bg-[#f5c453] px-4 py-3 text-sm font-black text-[#574000] shadow-lg sm:block"
                    >
                        +100 XP
                    </div>
                    <div
                        class="absolute -right-3 bottom-8 z-10 hidden rotate-[7deg] rounded-2xl bg-[#f36f56] px-4 py-3 text-sm font-black text-white shadow-lg sm:block"
                    >
                        🔥 3 hari streak
                    </div>
                    <article
                        class="rounded-[2rem] border-[7px] border-white bg-[#123845] p-5 shadow-[0_28px_60px_rgba(18,42,53,0.24)] sm:p-7"
                    >
                        <div
                            class="flex items-center justify-between text-white"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-xl bg-[#f5c453] text-lg text-[#734d00]"
                                    >✦</span
                                >
                                <div>
                                    <p class="text-xs font-bold text-[#aad7cc]">
                                        LIVE QUIZ
                                    </p>
                                    <p class="font-black">Ekosistem kelas 8</p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-white/10 px-3 py-2 text-xs font-black"
                                >00:24</span
                            >
                        </div>
                        <div
                            class="mt-7 rounded-[1.5rem] bg-[#fdfdf9] p-5 sm:p-7"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <p
                                    class="text-xs font-black uppercase tracking-[0.14em] text-[#16775a]"
                                >
                                    Soal 3 dari 10
                                </p>
                                <span
                                    class="rounded-full bg-[#e4f6ed] px-3 py-1 text-xs font-black text-[#16775a]"
                                    >100 poin</span
                                >
                            </div>
                            <h2
                                class="mt-5 text-2xl font-black leading-tight tracking-[-0.035em] text-[#19333e] sm:text-3xl"
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
                                            ? 'border-[#16775a] bg-[#daf6e8] text-[#126649]'
                                            : 'border-[#e5ece8] bg-white text-[#34515b] hover:-translate-y-0.5 hover:border-[#8ed4ba]'
                                    "
                                    @click="answer = choice"
                                >
                                    <span
                                        class="mr-3 inline-grid h-7 w-7 place-items-center rounded-lg bg-[#eff4f1] text-xs text-[#6b858c]"
                                        >{{
                                            String.fromCharCode(65 + index)
                                        }}</span
                                    >{{ choice }}
                                </button>
                            </div>
                            <p
                                v-if="answer"
                                class="mt-4 text-sm font-bold text-[#16775a]"
                            >
                                Jawaban tersimpan. Lanjut sebelum timer habis.
                            </p>
                        </div>
                        <div
                            class="mt-5 flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-sm text-white"
                        >
                            <span class="font-bold"
                                >Peserta aktif
                                <strong class="text-[#f5c453]">34</strong></span
                            ><span class="font-bold"
                                >Rani
                                <span class="text-[#f5c453]">1.920</span></span
                            >
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="fitur"
            class="bg-[#123845] px-5 py-20 text-white sm:px-8 lg:px-12"
        >
            <div class="mx-auto max-w-7xl">
                <div class="max-w-2xl">
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-[#8ee2bf]"
                    >
                        Satu platform, tiga mode
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-[-0.05em] sm:text-5xl"
                    >
                        Pilih cara belajar. Tetap terasa seru.
                    </h2>
                </div>
                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <article
                        class="rounded-[1.75rem] bg-[#1b4b59] p-6 transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-[#f5c453] text-2xl text-[#704a00]"
                            >⚡</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Live Quiz</h3>
                        <p class="mt-3 leading-7 text-[#c3ded8]">
                            Host buka room, peserta masuk pakai PIN, skor dan
                            leaderboard bergerak realtime.
                        </p>
                        <Link
                            href="/join"
                            class="mt-7 inline-block text-sm font-black text-[#8ee2bf]"
                            >Masuk room →</Link
                        >
                    </article>
                    <article
                        class="rounded-[1.75rem] bg-[#276574] p-6 transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-[#d8f4e6] text-2xl text-[#16775a]"
                            >◎</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Self-Paced</h3>
                        <p class="mt-3 leading-7 text-[#d3e7e2]">
                            Latihan sesuai ritme peserta, deadline jelas, hasil
                            objektif langsung terlihat.
                        </p>
                        <Link
                            href="/register"
                            class="mt-7 inline-block text-sm font-black text-[#b7f5d4]"
                            >Mulai latihan →</Link
                        >
                    </article>
                    <article
                        class="rounded-[1.75rem] bg-[#f5c453] p-6 text-[#3a351c] transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-white/70 text-2xl text-[#d85e49]"
                            >✦</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Materi ke Soal</h3>
                        <p class="mt-3 leading-7 text-[#5d532b]">
                            Unggah PDF atau PPTX, buat draft soal AI, lalu
                            review sebelum dipakai.
                        </p>
                        <Link
                            href="/materials"
                            class="mt-7 inline-block text-sm font-black text-[#684d00]"
                            >Lihat materi AI →</Link
                        >
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
                        class="text-xs font-black uppercase tracking-[0.18em] text-[#16775a]"
                    >
                        Dari materi sampai podium
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-[-0.05em] sm:text-5xl"
                    >
                        Tidak perlu pindah-pindah aplikasi.
                    </h2>
                    <p class="mt-5 max-w-md text-lg leading-8 text-[#587078]">
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
                                'bg-[#d8f4e6] text-[#16775a]',
                            ],
                            [
                                '02',
                                'Jalankan live atau bagikan latihan',
                                'PIN enam digit untuk sesi live. Deadline untuk tugas mandiri.',
                                'bg-[#fff0bd] text-[#a56a00]',
                            ],
                            [
                                '03',
                                'Pantau hasil dan beri feedback',
                                'Nilai otomatis, essay manual, score, progress, dan gradebook.',
                                'bg-[#ffdcd4] text-[#d9533b]',
                            ],
                        ]"
                        :key="number"
                        class="grid gap-4 rounded-3xl border border-[#dce9e1] bg-white p-5 sm:grid-cols-[3.5rem_1fr] sm:items-center"
                    >
                        <span
                            class="grid h-14 w-14 place-items-center rounded-2xl text-xl font-black"
                            :class="color"
                            >{{ number }}</span
                        >
                        <div>
                            <h3 class="text-xl font-black">{{ title }}</h3>
                            <p class="mt-1 text-[#617980]">{{ copy }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section id="untuk-siapa" class="px-5 pb-20 sm:px-8 lg:px-12">
            <div
                class="mx-auto max-w-7xl rounded-[2rem] bg-[#e4f6ed] p-8 sm:p-12"
            >
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-[#16775a]"
                        >
                            Untuk kelas yang ingin bergerak
                        </p>
                        <h2
                            class="mt-4 max-w-3xl text-4xl font-black tracking-[-0.05em] text-[#123845] sm:text-5xl"
                        >
                            Satu layar untuk creator. Satu PIN untuk seluruh
                            kelas.
                        </h2>
                        <p
                            class="mt-5 max-w-2xl text-lg leading-8 text-[#49636d]"
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
                            class="min-h-13 rounded-2xl bg-[#16775a] px-6 py-4 text-sm font-black text-white shadow-[0_8px_0_#0d503e]"
                            >Buat akun gratis</Link
                        ><Link
                            href="/join"
                            class="min-h-13 rounded-2xl border-2 border-[#8ebfa9] bg-white px-6 py-4 text-sm font-black text-[#16775a]"
                            >Punya PIN? Masuk</Link
                        >
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-[#dbe7df] px-5 py-8 sm:px-8 lg:px-12">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 text-sm text-[#617980]"
            >
                <div class="flex items-center gap-2 font-black text-[#183944]">
                    <ApplicationLogo class="h-7 w-7 text-[#16775a]" /> kuesify
                </div>
                <p>
                    Platform edukasi interaktif untuk belajar yang lebih hidup.
                </p>
                <Link
                    href="/login"
                    class="font-bold text-[#16775a] hover:underline"
                    >Masuk →</Link
                >
            </div>
        </footer>
    </main>
</template>
