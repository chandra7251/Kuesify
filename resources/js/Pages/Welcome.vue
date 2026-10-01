<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{ canLogin: boolean; canRegister: boolean }>();

const answer = ref<string | null>(null);
const answers = ['Produsen', 'Konsumen', 'Pengurai', 'Predator'];
const featureIndex = ref(0);
const roleIndex = ref(2);
const mobileMenuOpen = ref(false);
const roleDetails = [
    {
        label: 'RUANG BELAJAR PERSONAL',
        title: 'Belajar lebih terarah sesuai ritmemu.',
        copy: 'Ikuti kuis, latihan, dan materi dalam satu alur belajar yang mudah dipantau.',
        points: [
            'Mengikuti kuis live dan latihan mandiri',
            'Melihat nilai, progress, dan feedback',
            'Mengulang materi sesuai kebutuhan',
        ],
        image: '/images/study-character-male.png',
        alt: 'Siswa belajar menggunakan tablet',
    },
    {
        label: 'WORKSPACE PENGAJAR',
        title: 'Buat pengalaman belajar yang terasa hidup.',
        copy: 'Susun soal, jalankan sesi live, dan lihat respons kelas tanpa berpindah platform.',
        points: [
            'Membuat dan mengimpor soal',
            'Menjalankan kuis live dengan PIN',
            'Menilai jawaban dan memberi feedback',
        ],
        image: '/images/role-character-female.png',
        alt: 'Guru mengajar menggunakan laptop',
    },
    {
        label: 'RUANG KENDALI ORGANISASI',
        title: 'Jaga seluruh aktivitas belajar tetap teratur.',
        copy: 'Kelola anggota, grup, pengaturan, dan aktivitas pembelajaran organisasi dengan kontrol yang jelas.',
        points: [
            'Mengelola anggota dan grup',
            'Mengatur akses organisasi',
            'Memantau aktivitas pembelajaran',
            'Meninjau ringkasan penggunaan organisasi',
        ],
        image: '/images/role-laptop-mockup.png',
        alt: 'Dashboard organisasi Kuesify',
    },
] as const;

const scrollFeatures = (direction: number) => {
    const nextIndex = Math.max(0, Math.min(2, featureIndex.value + direction));
    featureIndex.value = nextIndex;
};
</script>

<template>
    <Head title="Kuesify — Belajar jadi hidup" />
    <main class="bg-brand-accent text-brand-primary">
        <div
            class="fixed inset-x-0 top-0 z-[9999] bg-brand-primary px-3 py-3 shadow-lg sm:px-8 sm:py-4 lg:px-12"
        >
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between gap-2 sm:gap-4"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-secondary"
                >
                    <ApplicationLogo
                        class="h-9 w-9 text-brand-secondary sm:h-10 sm:w-10"
                    />
                    <span
                        class="text-lg font-black tracking-tight text-brand-secondary sm:text-xl"
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
                <div class="hidden items-center gap-2 sm:gap-4 md:flex">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-primary px-3 py-2 text-xs font-extrabold leading-none text-white transition hover:bg-brand-hover sm:min-h-11 sm:px-4 sm:py-3 sm:text-sm"
                        >Masuk</Link
                    >
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-secondary px-3.5 py-2 text-xs font-extrabold leading-none text-brand-primary shadow-figma transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:min-h-11 sm:px-5 sm:py-3 sm:text-sm"
                        >Mulai gratis</Link
                    >
                </div>
                <button
                    type="button"
                    class="grid h-10 w-10 place-items-center rounded-xl bg-brand-secondary text-brand-primary shadow-figma md:hidden"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Buka menu navigasi"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <span class="sr-only">Menu</span>
                    <span class="flex w-5 flex-col gap-1">
                        <span
                            class="h-0.5 w-full rounded-full bg-brand-primary"
                        />
                        <span
                            class="h-0.5 w-full rounded-full bg-brand-primary"
                        />
                        <span
                            class="h-0.5 w-full rounded-full bg-brand-primary"
                        />
                    </span>
                </button>
            </nav>
            <div
                v-if="mobileMenuOpen"
                class="mt-3 grid gap-0 text-sm font-bold text-white md:hidden"
            >
                <a
                    href="#fitur"
                    class="border-b border-white/15 px-3 py-3 text-white hover:bg-white/10"
                    @click="mobileMenuOpen = false"
                    >Fitur</a
                >
                <a
                    href="#cara-kerja"
                    class="border-b border-white/15 px-3 py-3 text-white hover:bg-white/10"
                    @click="mobileMenuOpen = false"
                    >Cara kerja</a
                >
                <a
                    href="#untuk-siapa"
                    class="border-b border-white/15 px-3 py-3 text-white hover:bg-white/10"
                    @click="mobileMenuOpen = false"
                    >Untuk siapa</a
                >
                <div class="grid grid-cols-2 gap-2 border-0 pt-3">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="rounded-xl bg-brand-primary px-3 py-3 text-center text-white ring-2 ring-white/20 hover:bg-brand-hover"
                        >Masuk</Link
                    >
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="rounded-xl bg-brand-secondary px-3 py-3 text-center font-extrabold text-brand-primary hover:bg-brand-lime"
                        >Mulai gratis</Link
                    >
                </div>
            </div>
        </div>

        <section
            class="relative isolate min-h-0 overflow-hidden px-5 pb-20 pt-5 sm:px-8 lg:min-h-[42rem] lg:px-12"
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

            <div
                class="mx-auto grid max-w-7xl items-center gap-12 pt-16 lg:grid-cols-[1.02fr_0.98fr] lg:pb-10 lg:pt-24"
            >
                <div class="max-w-2xl">
                    <h1
                        class="mt-6 text-5xl font-black leading-[0.96] tracking-[-0.065em] sm:text-6xl lg:text-7xl"
                    >
                        Bukan cuma jawab soal.<br /><span
                            class="text-brand-primary"
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
                    <div
                        class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-bold text-brand-primary/75"
                    >
                        <span class="inline-flex items-center gap-2">
                            <svg
                                class="h-4 w-4 text-brand-secondary"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 12.5 9.5 17 19 7.5"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            Kuis interaktif untuk kelas
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg
                                class="h-4 w-4 text-brand-secondary"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 17V7m0 10 4-4 3 3 5-6 4 4"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            Hasil belajar langsung terlihat
                        </span>
                    </div>
                </div>

                <div
                    class="relative mx-auto flex w-full max-w-2xl items-center justify-center self-center lg:justify-end"
                >
                    <div
                        class="pointer-events-none absolute bottom-8 right-4 h-64 w-64 rounded-full bg-brand-secondary/10 blur-3xl sm:h-80 sm:w-80"
                    />
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
            class="relative min-h-0 overflow-hidden bg-brand-primary px-5 py-20 text-white sm:px-8 lg:min-h-[42rem] lg:px-12 lg:py-24"
        >
            <div
                class="pointer-events-none absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-brand-secondary/10"
            />
            <div
                class="pointer-events-none absolute -right-32 top-10 h-80 w-80 rounded-full border border-white/10"
            />

            <div class="relative mx-auto my-auto max-w-7xl">
                <!-- Desktop: grid 2 kolom (ilustrasi + teks), Mobile: stack -->
                <div
                    class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12"
                >
                    <!-- Ilustrasi Kiri (Desktop), Atas (Mobile) -->
                    <div class="hidden items-center justify-center lg:flex">
                        <img
                            src="/images/study-character-female.png"
                            alt="Siswa belajar menggunakan laptop"
                            class="w-full max-w-md object-contain object-center drop-shadow-[0_22px_26px_rgba(35,62,168,0.24)] lg:-translate-x-10 lg:translate-y-16"
                        />
                    </div>

                    <!-- Konten Teks Kanan (Desktop), Bawah (Mobile) -->
                    <div class="min-w-0">
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-brand-lime"
                        >
                            Satu platform, tiga mode
                        </p>
                        <h2
                            class="mt-4 max-w-xl text-3xl font-black leading-[1.05] tracking-[-0.045em] sm:text-4xl lg:text-5xl lg:leading-[1.02] lg:tracking-[-0.055em]"
                        >
                            Belajar aktif, dengan cara yang terasa pas.
                        </h2>
                        <!-- Ilustrasi Mobile (hidden di desktop) -->
                        <img
                            src="/images/study-character-female.png"
                            alt="Siswa belajar menggunakan laptop"
                            class="mx-auto mt-6 w-48 object-contain object-center drop-shadow-[0_22px_26px_rgba(35,62,168,0.24)] lg:hidden"
                        />
                        <p
                            class="mt-5 max-w-lg text-base leading-7 text-white/70"
                        >
                            Pilih pengalaman belajar yang sesuai dengan energi
                            kelas, waktu peserta, dan materi yang sudah
                            tersedia.
                        </p>

                        <!-- Carousel Mode Cards -->
                        <!-- Carousel Mode Cards -->
                        <div class="mt-8 lg:mt-12">
                            <div
                                class="relative mx-auto h-[23rem] w-full max-w-[54rem]"
                            >
                                <button
                                    v-if="featureIndex > 0"
                                    type="button"
                                    class="feature-nav-prev absolute left-2 top-1/2 z-30 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white text-xl font-black text-brand-primary shadow-figma transition hover:bg-brand-secondary"
                                    aria-label="Mode sebelumnya"
                                    @click="scrollFeatures(-1)"
                                >
                                    ‹
                                </button>
                                <div class="feature-card-stage">
                                    <article
                                        :class="[
                                            'feature-card',
                                            featureIndex === 0
                                                ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma'
                                                : featureIndex === 1
                                                  ? 'feature-card-prev bg-brand-surface z-10 border-white/15 text-brand-secondary'
                                                  : 'feature-card-hidden bg-brand-surface z-0 border-white/15 text-brand-secondary',
                                        ]"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="grid h-10 w-10 place-items-center rounded-xl bg-white/70 text-sm font-black"
                                                >01</span
                                            >
                                            <span
                                                class="text-xs font-black uppercase tracking-[0.16em] text-inherit"
                                                >Realtime</span
                                            >
                                        </div>
                                        <h3
                                            class="mt-12 text-2xl font-black tracking-[-0.03em]"
                                        >
                                            Live Quiz
                                        </h3>
                                        <p
                                            class="mt-3 text-sm leading-6 text-inherit"
                                        >
                                            Buka room, bagikan PIN, dan lihat
                                            kelas merespons soal secara
                                            langsung.
                                        </p>
                                        <Link
                                            href="/join"
                                            class="mt-8 inline-block text-sm font-black text-inherit"
                                            >Masuk room
                                            <span aria-hidden="true"
                                                >→</span
                                            ></Link
                                        >
                                    </article>
                                    <article
                                        :class="[
                                            'feature-card',
                                            featureIndex === 1
                                                ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma'
                                                : featureIndex === 0
                                                  ? 'feature-card-next bg-brand-surface z-10 border-white/15 text-brand-secondary'
                                                  : 'feature-card-prev bg-brand-surface z-10 border-white/15 text-brand-secondary',
                                        ]"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="grid h-10 w-10 place-items-center rounded-xl border border-brand-secondary/60 text-sm font-black text-inherit"
                                                >02</span
                                            >
                                            <span
                                                class="text-xs font-black uppercase tracking-[0.16em] text-inherit"
                                                >Mandiri</span
                                            >
                                        </div>
                                        <h3
                                            class="mt-12 text-2xl font-black tracking-[-0.03em]"
                                        >
                                            Self-Paced
                                        </h3>
                                        <p
                                            class="mt-3 text-sm leading-6 text-inherit"
                                        >
                                            Susun latihan dengan deadline jelas
                                            dan biarkan peserta belajar sesuai
                                            tempo.
                                        </p>
                                        <Link
                                            href="/register"
                                            class="mt-8 inline-block text-sm font-black text-inherit"
                                            >Mulai latihan
                                            <span aria-hidden="true"
                                                >→</span
                                            ></Link
                                        >
                                    </article>
                                    <article
                                        :class="[
                                            'feature-card',
                                            featureIndex === 2
                                                ? 'feature-card-active z-20 border-brand-secondary bg-brand-lime text-brand-primary shadow-figma'
                                                : featureIndex === 1
                                                  ? 'feature-card-next bg-brand-surface z-10 border-white/15 text-brand-secondary'
                                                  : 'feature-card-hidden bg-brand-surface z-0 border-white/15 text-brand-secondary',
                                        ]"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="grid h-10 w-10 place-items-center rounded-xl border border-brand-secondary/60 text-sm font-black text-inherit"
                                                >03</span
                                            >
                                            <span
                                                class="text-xs font-black uppercase tracking-[0.16em] text-inherit"
                                                >Berbantuan AI</span
                                            >
                                        </div>
                                        <h3
                                            class="mt-12 text-2xl font-black tracking-[-0.03em]"
                                        >
                                            Materi ke Soal
                                        </h3>
                                        <p
                                            class="mt-3 text-sm leading-6 text-inherit"
                                        >
                                            Mulai dari PDF atau PPTX, buat draft
                                            soal, lalu review sebelum dibagikan.
                                        </p>
                                        <Link
                                            href="/materials"
                                            class="mt-8 inline-block text-sm font-black text-inherit"
                                            >Lihat materi
                                            <span aria-hidden="true"
                                                >→</span
                                            ></Link
                                        >
                                    </article>
                                </div>
                                <button
                                    v-if="featureIndex < 2"
                                    type="button"
                                    class="feature-nav-next absolute right-2 top-1/2 z-30 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white text-xl font-black text-brand-primary shadow-figma transition hover:bg-brand-secondary"
                                    aria-label="Mode berikutnya"
                                    @click="scrollFeatures(1)"
                                >
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            id="cara-kerja"
            class="relative min-h-0 overflow-hidden px-5 py-20 sm:px-8 lg:min-h-[42rem] lg:px-12 lg:py-24"
        >
            <div
                class="pointer-events-none absolute -right-24 top-10 h-96 w-96 rounded-full border border-brand-secondary/20"
            />
            <div
                class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.08fr_0.92fr] lg:gap-16"
            >
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                    >
                        Dari materi sampai podium
                    </p>
                    <h2
                        class="mt-4 max-w-2xl text-4xl font-black leading-[1.02] tracking-[-0.055em] text-brand-primary sm:text-5xl lg:text-6xl"
                    >
                        Tidak perlu pindah-pindah aplikasi.
                    </h2>
                    <div class="mt-6 flex justify-center lg:hidden">
                        <img
                            src="/images/study-character-male.png"
                            alt="Siswa belajar menggunakan tablet"
                            class="w-full max-w-[14rem] object-contain"
                        />
                    </div>
                    <p
                        class="mt-5 max-w-xl text-lg leading-8 text-brand-primary/70"
                    >
                        Creator menyusun soal. Peserta belajar. Semua hasil
                        kembali ke satu workspace yang rapi.
                    </p>
                    <ol class="mt-10 space-y-4">
                        <li
                            v-for="[icon, title, copy] in [
                                [
                                    'M12 5.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM5.5 20a6.5 6.5 0 0 1 13 0',
                                    'Siswa/Peserta',
                                    'Ikuti kuis live, kerjakan latihan mandiri, dan lihat progres belajar.',
                                ],
                                [
                                    'M4 20h4L19 9l-4-4L4 16v4ZM14.5 6.5l3 3',
                                    'Guru/Pengajar',
                                    'Buat soal, jalankan sesi live, bagikan latihan, dan beri feedback.',
                                ],
                                [
                                    'M4 20h16M6 20V5h12v15M9 9h.01M12 9h.01M15 9h.01M9 13h.01M12 13h.01M15 13h.01M9 17h.01M12 17h.01M15 17h.01',
                                    'Admin Organisasi',
                                    'Kelola anggota, ruang belajar, dan aktivitas pembelajaran organisasi.',
                                ],
                            ]"
                            :key="title"
                            class="grid gap-4 rounded-3xl bg-brand-primary p-5 text-white shadow-figma sm:grid-cols-[4.5rem_1fr] sm:items-center sm:p-6"
                        >
                            <span
                                class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-lime text-brand-primary"
                            >
                                <svg
                                    class="h-7 w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        :d="icon"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-black">{{ title }}</h3>
                                <p class="mt-1 text-white/75">{{ copy }}</p>
                            </div>
                        </li>
                    </ol>
                </div>
                <div
                    class="relative hidden items-center justify-center lg:flex lg:justify-end"
                >
                    <div
                        class="pointer-events-none absolute bottom-8 right-8 h-72 w-72 rounded-full bg-brand-secondary/10 blur-3xl sm:h-96 sm:w-96"
                    />
                    <img
                        src="/images/study-character-male.png"
                        alt="Siswa belajar menggunakan tablet"
                        class="relative z-10 w-full max-w-md object-contain drop-shadow-[0_22px_26px_rgba(35,62,168,0.16)]"
                    />
                </div>
            </div>
        </section>

        <section
            id="peran"
            class="relative min-h-0 bg-brand-primary px-5 py-20 text-white sm:px-8 lg:min-h-[32rem] lg:px-12 lg:py-24"
        >
            <div
                class="pointer-events-none absolute left-[-12rem] top-1/3 h-[30rem] w-[30rem] rounded-full border border-brand-secondary/15"
            />
            <div
                class="pointer-events-none absolute right-[-10rem] top-[-8rem] h-[28rem] w-[28rem] rounded-full bg-brand-secondary/10 blur-3xl"
            />
            <div
                class="pointer-events-none absolute left-1/3 top-24 h-40 w-40 rounded-full border border-white/10"
            />
            <div
                class="pointer-events-none absolute bottom-[-10rem] right-1/4 h-[26rem] w-[26rem] rounded-full bg-white/5 blur-3xl"
            />
            <div
                class="pointer-events-none absolute bottom-20 left-1/2 h-24 w-24 rounded-full border border-brand-secondary/20"
            />
            <div
                class="pointer-events-none absolute -left-40 bottom-0 h-96 w-96 rounded-full bg-white/5"
            />
            <div
                class="pointer-events-none absolute -right-28 top-0 h-80 w-80 rounded-full border border-white/10"
            />
            <div class="relative mx-auto max-w-7xl">
                <div class="mx-auto max-w-3xl text-center">
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-brand-lime"
                    >
                        Satu platform untuk semua peran
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black leading-[1.02] tracking-[-0.055em] sm:text-5xl lg:text-6xl"
                    >
                        Semua punya ruang untuk menjalankan perannya.
                    </h2>
                    <div
                        class="mt-8 grid grid-cols-3 rounded-2xl border border-white/20 bg-white/10 p-1"
                    >
                        <button
                            v-for="(role, index) in [
                                'Siswa & Peserta',
                                'Guru & Pengajar',
                                'Admin Organisasi',
                            ]"
                            :key="role"
                            type="button"
                            class="min-w-0 whitespace-normal rounded-xl px-1 py-3 text-[11px] font-black leading-tight transition sm:px-4 sm:text-base"
                            :class="
                                roleIndex === index
                                    ? 'bg-brand-lime text-brand-primary'
                                    : 'text-white/75 hover:bg-white/10 hover:text-white'
                            "
                            @click="roleIndex = index"
                        >
                            {{ role }}
                        </button>
                    </div>
                </div>
                <div
                    class="mt-0 grid items-center gap-3 lg:mt-16 lg:grid-cols-[0.8fr_1.2fr] lg:gap-12"
                >
                    <div class="order-2 max-w-xl lg:order-1 lg:-translate-x-8">
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-brand-lime"
                        >
                            {{ roleDetails[roleIndex].label }}
                        </p>
                        <h3
                            class="mt-5 text-4xl font-black leading-[1.04] tracking-[-0.05em] sm:text-5xl"
                        >
                            {{ roleDetails[roleIndex].title }}
                        </h3>
                        <p class="mt-6 text-lg leading-8 text-white/70">
                            {{ roleDetails[roleIndex].copy }}
                        </p>
                        <ul class="mt-8 space-y-4">
                            <li
                                v-for="point in roleDetails[roleIndex].points"
                                :key="point"
                                class="flex items-start gap-3 text-base text-white/90"
                            >
                                <span
                                    class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-lime text-sm font-black text-brand-primary"
                                    >✓</span
                                >
                                <span>{{ point }}</span>
                            </li>
                        </ul>
                    </div>
                    <div
                        class="relative order-1 flex min-h-[19rem] items-center justify-center sm:min-h-[30rem] lg:order-2 lg:min-h-[36rem]"
                    >
                        <div
                            class="pointer-events-none absolute inset-8 rounded-[3rem] bg-white/10 blur-3xl"
                        />
                        <img
                            src="/images/role-laptop-mockup.png"
                            alt="Laptop workspace Kuesify"
                            class="relative right-0 z-10 w-[20rem] object-contain drop-shadow-[0_24px_30px_rgba(12,27,92,0.28)] sm:w-[38rem] sm:-translate-y-8 lg:right-[1%] lg:w-[44rem]"
                        />
                        <img
                            v-if="roleIndex === 0 || roleIndex === 2"
                            src="/images/role-character-female.png"
                            alt="Guru berdiri di samping workspace"
                            class="absolute bottom-0 left-[-6%] z-30 w-[6rem] -translate-y-8 object-contain sm:left-0 sm:-translate-y-16 lg:left-[-9%] lg:w-[13rem] lg:-translate-y-20"
                        />
                        <img
                            v-if="roleIndex === 1 || roleIndex === 2"
                            src="/images/role-character-male.png"
                            alt="Pengajar berdiri di samping workspace"
                            class="absolute bottom-0 right-[-6%] z-20 w-[7rem] -translate-y-8 object-contain sm:right-0 sm:w-[11rem] sm:-translate-y-16 lg:right-[-9%] lg:w-[14rem] lg:-translate-y-20"
                        />
                        <div
                            class="pointer-events-none absolute right-[-2rem] top-[4rem] z-0 h-48 w-48 rounded-full bg-brand-secondary/25"
                        />
                    </div>
                </div>
            </div>
        </section>
        <section
            id="untuk-siapa"
            class="relative isolate z-20 -mt-12 min-h-0 overflow-hidden bg-white px-5 py-20 sm:px-8 lg:min-h-[48rem] lg:px-12 lg:py-24"
        >
            <div
                class="pointer-events-none absolute -left-48 top-[-12rem] h-[34rem] w-[34rem] rounded-full bg-brand-secondary/15"
            />
            <div
                class="pointer-events-none absolute right-[-14rem] top-[-10rem] h-[30rem] w-[30rem] rounded-full border border-brand-secondary/20"
            />
            <div
                class="pointer-events-none absolute bottom-10 right-1/3 h-32 w-32 rounded-full border border-brand-secondary/25"
            />
            <div
                class="pointer-events-none absolute left-[8%] top-[18%] h-24 w-24 rounded-full border border-brand-primary/15"
            />
            <div
                class="pointer-events-none absolute bottom-[15%] right-[8%] h-20 w-20 rounded-full bg-brand-secondary/10"
            />
            <div
                class="pointer-events-none absolute left-[22%] top-[20%] h-24 w-24 rounded-full border border-brand-primary/15"
            />
            <div
                class="relative mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16"
            >
                <div
                    class="pointer-events-none absolute -left-32 top-0 h-96 w-96 rounded-full border border-brand-secondary/20"
                />
                <div
                    class="pointer-events-none absolute right-1/3 top-10 h-40 w-40 rounded-full border border-brand-secondary/15"
                />
                <div
                    class="pointer-events-none absolute left-1/2 top-1/2 h-32 w-32 rounded-full border border-brand-secondary/10"
                />
                <div
                    class="relative flex min-h-[20rem] w-full translate-y-6 items-center justify-center lg:min-h-[34rem] lg:translate-y-10"
                >
                    <img
                        src="/images/role-section-characters.png"
                        alt="Siswa dan guru menggunakan Kuesify bersama"
                        class="relative z-10 block w-full max-w-[30rem] -translate-x-6 object-contain lg:-translate-x-12"
                    />
                </div>
                <div
                    class="max-w-2xl translate-y-6 border-l-4 border-brand-secondary pl-6 lg:translate-y-10 lg:pl-8"
                >
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                    >
                        Untuk kelas yang ingin bergerak
                    </p>
                    <h2
                        class="mt-4 max-w-3xl text-4xl font-black leading-[1.02] tracking-[-0.05em] text-brand-primary sm:text-5xl"
                    >
                        Satu layar untuk creator. Satu PIN untuk seluruh kelas.
                    </h2>
                    <p
                        class="mt-5 max-w-2xl text-lg leading-8 text-brand-primary/75"
                    >
                        Dipakai untuk pembelajaran, bimbingan belajar, acara
                        komunitas, dan demo kompetisi yang perlu terasa hidup.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-brand-secondary px-6 py-4 text-sm font-black text-white shadow-figma"
                            >Buat akun gratis</Link
                        >
                        <Link
                            href="/join"
                            class="min-h-13 rounded-2xl bg-brand-primary px-6 py-4 text-sm font-black text-white shadow-figma"
                            >Punya PIN? Masuk</Link
                        >
                    </div>
                </div>
            </div>
        </section>

        <footer
            class="border-t border-brand-primary/15 px-5 py-8 sm:px-8 lg:px-12"
        >
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 text-sm text-brand-primary/65"
            >
                <div
                    class="flex items-center gap-2 font-black text-brand-primary"
                >
                    <ApplicationLogo class="h-7 w-7 text-brand-secondary" />
                    kuesify
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
    transition:
        transform 450ms ease,
        opacity 300ms ease;
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
