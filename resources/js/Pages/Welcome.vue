<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

gsap.registerPlugin(ScrollTrigger);

defineProps<{ canLogin: boolean; canRegister: boolean }>();

const answer = ref<string | null>(null);
const answers = ['Produsen', 'Konsumen', 'Pengurai', 'Predator'];
const activeSection = ref('');
const isScrolled = ref(false);
const landingRoot = ref<HTMLElement | null>(null);
let motionContext: gsap.Context | null = null;
let motionMedia: gsap.MatchMedia | null = null;
let pointerCleanup: (() => void) | null = null;
const interactionCleanups: Array<() => void> = [];

const selectAnswer = async (choice: string) => {
    answer.value = choice;
    await nextTick();

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const feedback = landingRoot.value?.querySelector<HTMLElement>(
        '[data-answer-feedback]',
    );
    if (feedback) {
        gsap.fromTo(
            feedback,
            { autoAlpha: 0, y: 8, scale: 0.96 },
            {
                autoAlpha: 1,
                y: 0,
                scale: 1,
                duration: 0.35,
                ease: 'back.out(1.4)',
            },
        );
    }

    const mascot =
        landingRoot.value?.querySelector<HTMLElement>('[data-mascot-hero]');
    if (mascot) {
        gsap.timeline()
            .to(mascot, {
                scale: 1.12,
                rotation: 6,
                duration: 0.16,
                ease: 'power2.out',
            })
            .to(mascot, {
                scale: 1,
                rotation: 0,
                duration: 0.55,
                ease: 'elastic.out(1, 0.35)',
            });
    }
};

onMounted(() => {
    if (!landingRoot.value) return;

    const handleScroll = () => {
        isScrolled.value = window.scrollY > 24;
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    interactionCleanups.push(() => window.removeEventListener('scroll', handleScroll));

    motionMedia = gsap.matchMedia();
    motionMedia.add('(prefers-reduced-motion: no-preference)', () => {
        motionContext = gsap.context(() => {
            const timeline = gsap.timeline({
                defaults: { ease: 'power3.out' },
            });
            const heroItems =
                gsap.utils.toArray<HTMLElement>('[data-hero-item]');
            const sections = gsap.utils.toArray<HTMLElement>(
                '[data-reveal-section]',
            );
            const featureCards = gsap.utils.toArray<HTMLElement>(
                '[data-feature-card]',
            );
            const processSteps = gsap.utils.toArray<HTMLElement>(
                '[data-process-step]',
            );
            const floaters = gsap.utils.toArray<HTMLElement>('[data-float]');
            const ambientBlobs = gsap.utils.toArray<HTMLElement>(
                '[data-ambient-blob]',
            );
            const mascots = gsap.utils.toArray<HTMLElement>('[data-mascot]');
            const orbit = landingRoot.value?.querySelector('[data-orbit]');
            const quizPreview = landingRoot.value?.querySelector<HTMLElement>(
                '[data-quiz-preview]',
            );

            timeline.from(heroItems, {
                autoAlpha: 0,
                y: 28,
                duration: 0.55,
                stagger: 0.09,
            });
            timeline.from(
                '[data-hero-actions]',
                { autoAlpha: 0, y: 18, duration: 0.45 },
                '-=0.2',
            );
            timeline.from(
                '[data-hero-meta]',
                { autoAlpha: 0, y: 12, duration: 0.4 },
                '-=0.25',
            );
            timeline.from(
                '[data-hero-preview]',
                { autoAlpha: 0, y: 24, scale: 0.96, duration: 0.65 },
                '-=0.38',
            );
            if (orbit)
                gsap.to(orbit, {
                    rotation: 360,
                    duration: 42,
                    repeat: -1,
                    ease: 'none',
                });
            ambientBlobs.forEach((blob, index) => {
                gsap.to(blob, {
                    x: index % 2 ? 18 : -14,
                    y: index % 2 ? -12 : 16,
                    duration: 7 + index * 1.5,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                    delay: index * 0.35,
                });
            });
            floaters.forEach((floater, index) => {
                gsap.to(floater, {
                    y: index % 2 ? 9 : -9,
                    rotation: index % 2 ? 4 : -4,
                    duration: 2.8 + index * 0.4,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                    delay: index * 0.3,
                });
            });
            if (quizPreview) {
                gsap.to(quizPreview, {
                    y: -7,
                    duration: 3.4,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                });
            }
            if (mascots.length) {
                timeline.from(
                    '[data-hero-mascot]',
                    {
                        autoAlpha: 0,
                        y: 42,
                        scale: 0.72,
                        rotation: -10,
                        duration: 0.75,
                        ease: 'back.out(1.6)',
                    },
                    '-=0.45',
                );
                mascots.forEach((mascot, index) => {
                    gsap.to(mascot, {
                        y: index ? -7 : -11,
                        rotation: index ? -3 : 3,
                        duration: index ? 2.8 : 2.5,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                        delay: index ? 0.45 : 1.25,
                    });
                });
            }
            if (featureCards.length) {
                gsap.from(featureCards, {
                    autoAlpha: 0,
                    y: 30,
                    duration: 0.55,
                    stagger: 0.1,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: featureCards[0],
                        start: 'top 82%',
                        once: true,
                    },
                });
                featureCards.forEach((card) => {
                    const lift = () =>
                        gsap.to(card, {
                            y: -8,
                            rotationY: 1.5,
                            duration: 0.25,
                            ease: 'power2.out',
                        });
                    const settle = () =>
                        gsap.to(card, {
                            y: 0,
                            rotationY: 0,
                            duration: 0.35,
                            ease: 'power2.out',
                        });
                    card.addEventListener('pointerenter', lift);
                    card.addEventListener('pointerleave', settle);
                    interactionCleanups.push(() => {
                        card.removeEventListener('pointerenter', lift);
                        card.removeEventListener('pointerleave', settle);
                    });
                });
            }
            if (processSteps.length) {
                gsap.from(processSteps, {
                    autoAlpha: 0,
                    x: 24,
                    duration: 0.55,
                    stagger: 0.12,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: processSteps[0],
                        start: 'top 82%',
                        once: true,
                    },
                });
            }
            sections.forEach((section) => {
                gsap.from(section, {
                    autoAlpha: 0,
                    y: 26,
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: section,
                        start: 'top 84%',
                        once: true,
                    },
                });
                ScrollTrigger.create({
                    trigger: section,
                    start: 'top 55%',
                    end: 'bottom 45%',
                    onEnter: () => {
                        activeSection.value = section.id;
                    },
                    onEnterBack: () => {
                        activeSection.value = section.id;
                    },
                });
            });

            if (quizPreview && window.matchMedia('(pointer: fine)').matches) {
                const xTo = gsap.quickTo(quizPreview, 'rotationY', {
                    duration: 0.45,
                    ease: 'power3.out',
                });
                const yTo = gsap.quickTo(quizPreview, 'rotationX', {
                    duration: 0.45,
                    ease: 'power3.out',
                });
                const handlePointerMove = (event: Event) => {
                    const pointerEvent = event as PointerEvent;
                    const bounds = quizPreview.getBoundingClientRect();
                    const x = gsap.utils.mapRange(
                        bounds.left,
                        bounds.right,
                        -4,
                        4,
                        pointerEvent.clientX,
                    );
                    const y = gsap.utils.mapRange(
                        bounds.top,
                        bounds.bottom,
                        3,
                        -3,
                        pointerEvent.clientY,
                    );
                    xTo(x);
                    yTo(y);
                };
                const resetPointer = () => {
                    xTo(0);
                    yTo(0);
                };
                quizPreview.addEventListener(
                    'pointermove',
                    handlePointerMove as EventListener,
                );
                quizPreview.addEventListener('pointerleave', resetPointer);
                pointerCleanup = () => {
                    quizPreview.removeEventListener(
                        'pointermove',
                        handlePointerMove as EventListener,
                    );
                    quizPreview.removeEventListener(
                        'pointerleave',
                        resetPointer,
                    );
                };
            }

            return () => motionContext?.revert();
        }, landingRoot.value ?? undefined);
    });
});

onUnmounted(() => {
    pointerCleanup?.();
    interactionCleanups.splice(0).forEach((cleanup) => cleanup());
    motionMedia?.revert();
    motionContext?.revert();
});
</script>

<template>
    <Head title="Kuesify — Belajar jadi hidup" />
    <main
        ref="landingRoot"
        class="overflow-hidden bg-brand-accent text-slate-900"
    >
        <section
            data-reveal-section
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
                    data-ambient-blob
                    class="absolute right-[-12rem] top-20 h-[28rem] w-[28rem] rounded-full bg-brand-secondary/20"
                ></div>
                <div
                    data-ambient-blob
                    class="absolute left-1/4 top-20 h-72 w-72 rounded-full bg-support-1/40 opacity-60 blur-3xl"
                ></div>
            </div>

            <nav
                data-site-nav
                :class="[
                'sticky top-3 z-30 mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-2xl px-4 py-3 transition-all duration-300 md:top-5',
                isScrolled
                    ? 'border border-slate-200/80 bg-white/85 shadow-[0_8px_30px_rgb(0,0,0,0.06)] backdrop-blur-md'
                    : 'bg-transparent'
            ]"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-primary"
                >
                    <ApplicationLogo class="h-10 w-10 text-brand-primary" />
                    <span class="text-xl font-black tracking-tight"
                        >kuesify</span
                    >
                </Link>
                <div
                    class="hidden items-center gap-7 text-sm font-bold text-slate-600 md:flex"
                >
                    <a
                        href="#fitur"
                        :class="
                            activeSection === 'fitur'
                                ? 'text-brand-primary'
                                : 'hover:text-brand-primary'
                        "
                        >Fitur</a
                    ><a
                        href="#cara-kerja"
                        :class="
                            activeSection === 'cara-kerja'
                                ? 'text-brand-primary'
                                : 'hover:text-brand-primary'
                        "
                        >Cara kerja</a
                    ><a
                        href="#untuk-siapa"
                        :class="
                            activeSection === 'untuk-siapa'
                                ? 'text-brand-primary'
                                : 'hover:text-brand-primary'
                        "
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
                        class="min-h-11 rounded-xl bg-brand-primary px-4 py-3 text-sm font-extrabold text-white shadow-[0_7px_0_#233EA8] transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:px-5"
                        >Mulai gratis</Link
                    >
                </div>
            </nav>

            <div
                data-reveal-section
                class="mx-auto grid max-w-7xl items-center gap-12 pt-16 lg:grid-cols-[1.02fr_0.98fr] lg:pb-10 lg:pt-24"
            >
                <div class="max-w-2xl">
                    <p
                        data-hero-item
                        class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/40 bg-white/75 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-brand-dark"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-brand-primary"
                        ></span>
                        Kuis yang bikin kelas ikut hidup
                    </p>
                    <h1
                        data-hero-item
                        class="mt-6 text-5xl font-black leading-[0.96] tracking-[-0.065em] sm:text-6xl lg:text-7xl"
                    >
                        Bukan cuma jawab soal.<br /><span
                            class="text-brand-primary"
                            >Rasakan</span
                        >
                        proses belajarnya.
                    </h1>
                    <p
                        data-hero-item
                        class="mt-6 max-w-xl text-lg leading-8 text-slate-600 sm:text-xl"
                    >
                        Kuesify menyatukan quiz live, latihan mandiri, dan
                        materi interaktif untuk kelas yang lebih aktif dari awal
                        sampai akhir.
                    </p>
                    <div data-hero-actions class="mt-8 flex flex-wrap gap-3">
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-brand-primary px-6 py-4 text-sm font-black text-white shadow-[0_8px_0_#233EA8] transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none"
                            >Buat quiz pertama →</Link
                        >
                        <Link
                            href="/join"
                            class="min-h-13 rounded-2xl border-2 border-brand-primary/30 bg-white/80 px-6 py-4 text-sm font-black text-brand-primary hover:border-brand-primary"
                            >Masuk dengan PIN</Link
                        >
                    </div>
                    <div
                        data-hero-meta
                        class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-bold text-slate-600"
                    >
                        <span>✓ Tidak perlu kartu kredit</span
                        ><span>⚡ Siap untuk kelas live</span>
                    </div>
                </div>

                <div
                    data-hero-preview
                    class="relative mx-auto w-full max-w-xl [perspective:1000px]"
                >
                    <div
                        data-hero-mascot
                        class="pointer-events-none absolute -bottom-10 -left-16 z-20 hidden w-32 will-change-transform sm:block lg:-left-20 lg:w-40"
                    >
                        <img
                            data-mascot
                            src="/assets/kuesify/characters/image-14.svg"
                            alt="Karakter siswa Kuesify sedang belajar"
                            class="w-full drop-shadow-[0_18px_18px_rgba(35,62,168,0.18)]"
                        />
                    </div>
                    <div
                        data-float
                        class="absolute -left-8 top-12 hidden rotate-[-7deg] rounded-2xl bg-support-1 px-4 py-3 text-sm font-black text-slate-900 shadow-lg sm:block"
                    >
                        +100 XP
                    </div>
                    <div
                        data-float
                        class="absolute -right-3 bottom-8 z-10 hidden rotate-[7deg] rounded-2xl bg-brand-primary px-4 py-3 text-sm font-black text-white shadow-lg sm:block"
                    >
                        🔥 3 hari streak
                    </div>
                    <article
                        data-quiz-preview
                        class="rounded-[2rem] border-[7px] border-white bg-brand-dark p-5 shadow-[0_28px_60px_rgba(18,42,53,0.24)] will-change-transform sm:p-7"
                    >
                        <div
                            class="flex items-center justify-between text-white"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-xl bg-support-1 text-lg text-slate-900"
                                    >✦</span
                                >
                                <div>
                                    <p
                                        class="text-xs font-bold text-brand-secondary/70"
                                    >
                                        LIVE QUIZ
                                    </p>
                                    <p class="font-black">Ekosistem kelas 8</p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-secondary/20 px-2 py-0.5 text-[10px] font-extrabold text-brand-secondary">
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-secondary"></span>
                                        Simulasi Live (Klik Pilihan)
                                    </span>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-white/10 px-3 py-2 text-xs font-black"
                                >00:24</span
                            >
                        </div>
                        <div class="mt-7 rounded-[1.5rem] bg-white p-5 sm:p-7">
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <p
                                    class="text-xs font-black uppercase tracking-[0.14em] text-brand-primary"
                                >
                                    Soal 3 dari 10
                                </p>
                                <span
                                    class="rounded-full bg-brand-accent px-3 py-1 text-xs font-black text-brand-primary"
                                    >100 poin</span
                                >
                            </div>
                            <h2
                                class="mt-5 text-2xl font-black leading-tight tracking-[-0.035em] text-slate-900 sm:text-3xl"
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
                                            ? 'border-brand-primary bg-brand-secondary/15 text-brand-dark'
                                            : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-brand-secondary'
                                    "
                                    data-answer-choice
                                    @click="selectAnswer(choice)"
                                >
                                    <span
                                        class="mr-3 inline-grid h-7 w-7 place-items-center rounded-lg bg-brand-accent text-xs text-slate-500"
                                        >{{
                                            String.fromCharCode(65 + index)
                                        }}</span
                                    >{{ choice }}
                                </button>
                            </div>
                            <p
                                v-if="answer"
                                data-answer-feedback
                                class="mt-4 text-sm font-bold text-brand-primary"
                            >
                                Jawaban tersimpan. Lanjut sebelum timer habis.
                            </p>
                        </div>
                        <div
                            class="mt-5 flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-sm text-white"
                        >
                            <span class="font-bold"
                                >Peserta aktif
                                <strong class="text-support-1">34</strong></span
                            ><span class="font-bold"
                                >Rani
                                <span class="text-support-1">1.920</span></span
                            >
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section
            data-reveal-section
            id="fitur"
            class="bg-brand-dark px-5 py-20 text-white sm:px-8 lg:px-12"
        >
            <div class="mx-auto max-w-7xl">
                <div class="max-w-2xl">
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-brand-secondary"
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
                        data-feature-card
                        class="rounded-[1.75rem] bg-brand-dark p-6 transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-support-1 text-2xl text-slate-900"
                            >⚡</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Live Quiz</h3>
                        <p class="mt-3 leading-7 text-white/75">
                            Host buka room, peserta masuk pakai PIN, skor dan
                            leaderboard bergerak realtime.
                        </p>
                        <Link
                            href="/join"
                            class="mt-7 inline-block text-sm font-black text-brand-secondary"
                            >Masuk room →</Link
                        >
                    </article>
                    <article
                        class="rounded-[1.75rem] bg-brand-primary p-6 transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-secondary/20 text-2xl text-brand-primary"
                            >◎</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Self-Paced</h3>
                        <p class="mt-3 leading-7 text-white/75">
                            Latihan sesuai ritme peserta, deadline jelas, hasil
                            objektif langsung terlihat.
                        </p>
                        <Link
                            href="/register"
                            class="mt-7 inline-block text-sm font-black text-brand-secondary"
                            >Mulai latihan →</Link
                        >
                    </article>
                    <article
                        class="rounded-[1.75rem] bg-support-1 p-6 text-slate-900 transition hover:-translate-y-1"
                    >
                        <span
                            class="grid h-12 w-12 place-items-center rounded-2xl bg-white/70 text-2xl text-status-danger"
                            >✦</span
                        >
                        <h3 class="mt-8 text-2xl font-black">Materi ke Soal</h3>
                        <p class="mt-3 leading-7 text-slate-700">
                            Unggah PDF atau PPTX, buat draft soal AI, lalu
                            review sebelum dipakai.
                        </p>
                        <Link
                            href="/materials"
                            class="mt-7 inline-block text-sm font-black text-slate-700"
                            >Lihat materi AI →</Link
                        >
                    </article>
                </div>
            </div>
        </section>

        <section
            data-reveal-section
            id="cara-kerja"
            class="px-5 py-20 sm:px-8 lg:px-12"
        >
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
                    <p class="mt-5 max-w-md text-lg leading-8 text-slate-500">
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
                                'bg-brand-secondary/20 text-brand-primary',
                            ],
                            [
                                '02',
                                'Jalankan live atau bagikan latihan',
                                'PIN enam digit untuk sesi live. Deadline untuk tugas mandiri.',
                                'bg-support-1/20 text-support-1',
                            ],
                            [
                                '03',
                                'Pantau hasil dan beri feedback',
                                'Nilai otomatis, essay manual, score, progress, dan gradebook.',
                                'bg-status-danger/15 text-status-danger',
                            ],
                        ]"
                        :key="number"
                        data-process-step
                        class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-5 sm:grid-cols-[3.5rem_1fr] sm:items-center"
                    >
                        <span
                            class="grid h-14 w-14 place-items-center rounded-2xl text-xl font-black"
                            :class="color"
                            >{{ number }}</span
                        >
                        <div>
                            <h3 class="text-xl font-black">{{ title }}</h3>
                            <p class="mt-1 text-slate-500">{{ copy }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section
            data-reveal-section
            id="untuk-siapa"
            class="relative px-5 pb-20 sm:px-8 lg:px-12"
        >
            <div
                class="mx-auto max-w-7xl rounded-[2rem] bg-brand-accent p-8 sm:p-12"
            >
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-brand-primary"
                        >
                            Untuk kelas yang ingin bergerak
                        </p>
                        <h2
                            class="mt-4 max-w-3xl text-4xl font-black tracking-[-0.05em] text-slate-900 sm:text-5xl"
                        >
                            Satu layar untuk creator. Satu PIN untuk seluruh
                            kelas.
                        </h2>
                        <p
                            class="mt-5 max-w-2xl text-lg leading-8 text-slate-600"
                        >
                            Dipakai untuk pembelajaran, bimbingan belajar, acara
                            komunitas, dan demo kompetisi yang perlu terasa
                            hidup.
                        </p>
                    </div>
                    <div
                        class="relative flex flex-wrap items-end gap-3 pt-14 sm:pt-0"
                    >
                        <img
                            data-mascot
                            src="/assets/kuesify/characters/image-5.svg"
                            alt="Karakter creator Kuesify sedang menyusun kuis"
                            class="absolute -top-1 right-8 w-24 will-change-transform sm:-top-12 sm:right-14 sm:w-28"
                        />
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="min-h-13 rounded-2xl bg-brand-primary px-6 py-4 text-sm font-black text-white shadow-[0_8px_0_#233EA8]"
                            >Buat akun gratis</Link
                        ><Link
                            href="/join"
                            class="min-h-13 rounded-2xl border-2 border-brand-secondary/40 bg-white px-6 py-4 text-sm font-black text-brand-primary"
                            >Punya PIN? Masuk</Link
                        >
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-slate-200 px-5 py-8 sm:px-8 lg:px-12">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 text-sm text-slate-500"
            >
                <div class="flex items-center gap-2 font-black text-slate-900">
                    <ApplicationLogo class="h-7 w-7 text-brand-primary" />
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

