<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const form = useForm({ pin: '', alias: '' });
const root = ref<HTMLElement | null>(null);
const canvasBg = ref<HTMLCanvasElement | null>(null);
const mascotEl = ref<HTMLElement | null>(null);
const speechBubbleEl = ref<HTMLElement | null>(null);
const pinContainerEl = ref<HTMLElement | null>(null);

const mascotSpeech = ref('Halo jagoan! Masukkan 6 digit Game PIN dari gurumu!');
const currentHint = ref('Ketik PIN langsung atau gunakan numpad');

let motionMedia: gsap.MatchMedia | null = null;
let motionContext: gsap.Context | null = null;
let animFrameId: number | null = null;

// Floating canvas particles background
interface Particle {
    x: number;
    y: number;
    radius: number;
    vx: number;
    vy: number;
    color: string;
    alpha: number;
}
let particles: Particle[] = [];

function initCanvas(): void {
    const canvas = canvasBg.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const resize = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    resize();
    window.addEventListener('resize', resize);

    const colors = ['#3154D5', '#90CB31', '#60A5FA', '#A3E635', '#38BDF8'];
    particles = Array.from({ length: 45 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 5 + 2,
        vx: (Math.random() - 0.5) * 0.8,
        vy: (Math.random() - 0.5) * 0.8,
        color: colors[Math.floor(Math.random() * colors.length)],
        alpha: Math.random() * 0.4 + 0.15,
    }));

    const canvasEl = canvas;
    const ctx2d = ctx;

    function loop() {
        if (!canvasEl || !ctx2d) return;
        ctx2d.clearRect(0, 0, canvasEl.width, canvasEl.height);
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < 0) p.x = canvasEl.width;
            if (p.x > canvasEl.width) p.x = 0;
            if (p.y < 0) p.y = canvasEl.height;
            if (p.y > canvasEl.height) p.y = 0;

            ctx2d.beginPath();
            ctx2d.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx2d.fillStyle = p.color;
            ctx2d.globalAlpha = p.alpha;
            ctx2d.fill();
        }
        ctx2d.globalAlpha = 1;
        animFrameId = requestAnimationFrame(loop);
    }
    loop();
}

function sanitizePin(event: Event): void {
    const input = event.target as HTMLInputElement;
    form.pin = input.value.replace(/[^0-9]/g, '').slice(0, 6);
}

function pressKey(key: string): void {
    if (key === 'back') {
        if (form.pin.length > 0) {
            form.pin = form.pin.slice(0, -1);
            triggerMascotReaction('back');
        }
    } else if (key === 'clear') {
        form.pin = '';
        triggerMascotReaction('clear');
    } else if (form.pin.length < 6) {
        form.pin += key;
        triggerMascotReaction('digit');
    }
}

function triggerMascotReaction(
    type: 'digit' | 'back' | 'clear' | 'complete' | 'submit',
): void {
    try {
        if (typeof navigator !== 'undefined' && navigator.vibrate) {
            if (type === 'digit') navigator.vibrate(12);
            else if (type === 'back') navigator.vibrate(18);
            else if (type === 'complete')
                navigator.vibrate([20, 60, 30, 60, 40]);
            else if (type === 'submit') navigator.vibrate(30);
        }
    } catch {}

    if (
        !mascotEl.value ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    )
        return;

    if (type === 'digit') {
        gsap.timeline()
            .to(mascotEl.value, {
                scale: 1.12,
                rotation: 5,
                y: -8,
                duration: 0.1,
                ease: 'power2.out',
            })
            .to(mascotEl.value, {
                scale: 1,
                rotation: 0,
                y: 0,
                duration: 0.35,
                ease: 'elastic.out(1.2, 0.4)',
            });

        // Ripple reaction on speech bubble
        if (speechBubbleEl.value) {
            gsap.fromTo(
                speechBubbleEl.value,
                { scale: 0.98 },
                { scale: 1, duration: 0.25, ease: 'back.out(2)' },
            );
        }
    } else if (type === 'back') {
        gsap.timeline()
            .to(mascotEl.value, { rotation: -6, x: -6, duration: 0.1 })
            .to(mascotEl.value, {
                rotation: 0,
                x: 0,
                duration: 0.3,
                ease: 'power2.out',
            });
    } else if (type === 'complete') {
        // Grand celebration bounce & 360 spin
        gsap.timeline()
            .to(mascotEl.value, {
                y: -25,
                scale: 1.25,
                rotation: 360,
                duration: 0.6,
                ease: 'back.out(1.8)',
            })
            .to(mascotEl.value, {
                y: 0,
                scale: 1,
                duration: 0.4,
                ease: 'bounce.out',
            });
    } else if (type === 'submit') {
        gsap.timeline()
            .to(mascotEl.value, {
                y: -40,
                scale: 1.3,
                rotation: -12,
                duration: 0.3,
                ease: 'power2.out',
            })
            .to(mascotEl.value, {
                y: 0,
                scale: 1,
                rotation: 0,
                duration: 0.5,
                ease: 'elastic.out(1, 0.3)',
            });
    }
}

watch(
    () => form.pin,
    (val) => {
        if (val.length === 0) {
            mascotSpeech.value =
                'Halo! Masukkan 6 digit Game PIN dari host kamu!';
            currentHint.value =
                'Ketik PIN langsung atau gunakan numpad interaktif di bawah';
        } else if (val.length < 6) {
            mascotSpeech.value =
                'Keren! Tinggal ' + (6 - val.length) + ' angka lagi!';
            currentHint.value =
                'Angka ke-' + (val.length + 1) + ' sedang ditunggu...';
        } else if (val.length === 6) {
            mascotSpeech.value = 'PIN lengkap! Sekarang tulis nama pahlawanmu!';
            currentHint.value = 'Siap untuk masuk ke leaderboard!';
            triggerMascotReaction('complete');
        }
    },
);

watch(
    () => form.alias,
    (val) => {
        if (form.pin.length === 6 && val.trim().length > 0) {
            mascotSpeech.value = 'Mantap ' + val + '! Klik tombol Masuk Sesi!';
        }
    },
);

onMounted(() => {
    if (!root.value) return;

    motionMedia = gsap.matchMedia();
    motionMedia.add('(prefers-reduced-motion: no-preference)', () => {
        motionContext = gsap.context(() => {
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

            tl.from('[data-anim-header]', {
                autoAlpha: 0,
                y: -20,
                duration: 0.5,
            });
            tl.from(
                '[data-anim-mascot]',
                {
                    autoAlpha: 0,
                    scale: 0.75,
                    rotation: -15,
                    duration: 0.7,
                    ease: 'back.out(1.7)',
                },
                '-=0.3',
            );
            tl.from(
                '[data-anim-card]',
                { autoAlpha: 0, y: 30, scale: 0.95, duration: 0.65 },
                '-=0.4',
            );
            tl.from(
                '[data-anim-digit]',
                {
                    autoAlpha: 0,
                    y: 15,
                    scale: 0.8,
                    stagger: 0.05,
                    duration: 0.35,
                    ease: 'back.out(2)',
                },
                '-=0.3',
            );
            tl.from(
                '[data-anim-numpad]',
                { autoAlpha: 0, y: 20, duration: 0.4 },
                '-=0.2',
            );

            // Ambient continuous float
            if (mascotEl.value) {
                gsap.to(mascotEl.value, {
                    y: -14,
                    rotation: 3,
                    duration: 2.4,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                });
            }

            // Glow orbit effect on active card
            gsap.to('[data-anim-glow]', {
                rotation: 360,
                duration: 18,
                repeat: -1,
                ease: 'none',
            });
        }, root.value ?? undefined);
    });
});

onUnmounted(() => {
    if (animFrameId) cancelAnimationFrame(animFrameId);
    motionMedia?.revert();
    motionContext?.revert();
});

function join(): void {
    triggerMascotReaction('submit');
    form.post(route('live-sessions.join'));
}
</script>

<template>
    <Head title="Masuk ke Sesi Live" />
    <main
        ref="root"
        class="relative min-h-screen overflow-hidden bg-brand-accent text-brand-primary selection:bg-brand-secondary selection:text-white"
    >
        <!-- Ambient Background Blobs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute -left-[10%] top-[-10%] h-[45vw] w-[45vw] rounded-full bg-brand-secondary/20 mix-blend-multiply blur-[80px]"
            ></div>
            <div
                class="absolute -right-[10%] bottom-[-10%] h-[40vw] w-[40vw] rounded-full bg-brand-primary/10 mix-blend-multiply blur-[80px]"
            ></div>
        </div>

        <!-- Navigation -->
        <div
            class="sticky top-0 z-[9999] w-full bg-brand-primary px-3 py-3 shadow-figma sm:px-8 sm:py-4 lg:px-12"
        >
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between gap-2 sm:gap-4"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded-xl text-white transition-transform hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-secondary"
                >
                    <ApplicationLogo
                        class="h-9 w-9 text-brand-secondary sm:h-10 sm:w-10"
                    />
                    <span
                        class="text-lg font-black tracking-tight text-brand-secondary sm:text-xl"
                        >kuesify</span
                    >
                </Link>
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        v-if="isAuthenticated"
                        :href="route('dashboard')"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-secondary px-3.5 py-2 text-xs font-extrabold leading-none text-brand-primary shadow-figma transition hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:min-h-11 sm:px-5 sm:py-3 sm:text-sm"
                    >
                        Kembali ke Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-primary px-3 py-2 text-xs font-extrabold leading-none text-white transition hover:bg-brand-secondary sm:min-h-11 sm:px-4 sm:py-3 sm:text-sm"
                            >Masuk</Link
                        >
                        <Link
                            href="/register"
                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-secondary px-3.5 py-2 text-xs font-extrabold leading-none text-brand-primary shadow-figma transition hover:-translate-y-0.5 active:translate-y-1 active:shadow-none sm:min-h-11 sm:px-5 sm:py-3 sm:text-sm"
                            >Mulai gratis</Link
                        >
                    </template>
                </div>
            </nav>
        </div>

        <div
            class="relative z-10 mx-auto flex min-h-[calc(100vh-5rem)] max-w-6xl flex-col items-center justify-center p-6 lg:flex-row lg:gap-16 lg:p-8"
        >
            <!-- Line Orbs (Copied from Welcome.vue) -->
            <div
                class="pointer-events-none absolute -left-32 top-0 h-96 w-96 rounded-full border border-brand-secondary/20"
            ></div>
            <div
                class="pointer-events-none absolute right-1/3 top-10 h-40 w-40 rounded-full border border-brand-secondary/15"
            ></div>
            <div
                class="pointer-events-none absolute left-1/2 top-1/2 h-32 w-32 rounded-full border border-brand-secondary/10"
            ></div>

            <!-- Filled Orbs (Added 4 solid fill orbs) -->
            <div
                class="pointer-events-none absolute -left-48 top-[-12rem] h-[34rem] w-[34rem] rounded-full bg-brand-secondary/15"
            ></div>
            <div
                class="pointer-events-none absolute right-[-2rem] top-[4rem] z-0 h-48 w-48 rounded-full bg-brand-primary/10"
            ></div>
            <div
                class="pointer-events-none absolute bottom-0 left-[-10rem] h-96 w-96 rounded-full bg-brand-primary/5"
            ></div>
            <div
                class="pointer-events-none absolute bottom-[-4rem] right-[-6rem] z-0 h-64 w-64 rounded-full bg-brand-secondary/15"
            ></div>
            <div
                class="pointer-events-none absolute bottom-[15%] right-[8%] h-20 w-20 rounded-full bg-brand-secondary/10"
            ></div>

            <!-- Left Panel (Branding / Mascot) -->
            <div
                class="mb-10 flex w-full flex-col items-center text-center lg:mb-0 lg:w-1/2 lg:items-start lg:text-left"
            >
                <!-- Arena Live Aktif Badge Removed -->

                <h1
                    class="text-4xl font-black leading-[1.1] tracking-[-0.05em] text-brand-primary sm:text-5xl lg:text-6xl"
                >
                    Masuk ke <br class="hidden lg:block" />Sesi Live!
                </h1>

                <p
                    class="mt-4 max-w-sm text-base text-brand-primary/70 sm:text-lg"
                >
                    Minta PIN dari gurumu, masukkan namamu, dan jadilah yang
                    tercepat di panggung.
                </p>

                <!-- Mascot -->
                <div
                    class="relative mt-16 max-w-[150px] -translate-x-16 sm:mt-8 sm:max-w-[200px] sm:translate-x-0 lg:max-w-[280px]"
                >
                    <img
                        :src="
                            form.pin.length === 6
                                ? '/images/chibi-male-pin-lengkap.png'
                                : '/images/chibi-male-live-join.png'
                        "
                        alt="Kuesify Mascot"
                        ref="mascotEl"
                        class="relative z-10 drop-shadow-2xl"
                    />

                    <!-- Speech Bubble -->
                    <div
                        ref="speechBubbleEl"
                        class="absolute -right-32 -top-12 z-20 w-44 rounded-2xl bg-white p-3 shadow-figma sm:-right-16 sm:-top-4 lg:-right-24 lg:-top-6"
                    >
                        <div
                            class="absolute -bottom-2 left-6 h-4 w-4 rotate-45 bg-white"
                        ></div>
                        <p
                            class="relative z-10 text-xs font-bold leading-tight text-brand-primary"
                        >
                            {{
                                form.pin.length === 6
                                    ? 'PIN lengkap! Masukkan namamu dan ayo mulai!'
                                    : mascotSpeech
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Form) -->
            <div class="w-full max-w-md lg:w-1/2">
                <section
                    data-anim-card
                    class="relative rounded-3xl bg-white p-6 shadow-figma sm:p-8"
                >
                    <!-- Form -->
                    <form @submit.prevent="join" class="flex flex-col gap-6">
                        <!-- PIN Section -->
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <label
                                    for="live-pin-input"
                                    class="text-sm font-black text-brand-primary"
                                >
                                    PIN Game <span class="text-red-500">*</span>
                                </label>
                                <span
                                    class="text-xs font-bold text-brand-primary/60"
                                >
                                    {{ form.pin.length }} / 6
                                </span>
                            </div>

                            <input
                                id="live-pin-input"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                :value="form.pin"
                                @input="sanitizePin"
                                class="absolute h-0 w-0 opacity-0"
                                ref="hiddenInput"
                            />

                            <!-- Custom PIN Dots -->
                            <div
                                class="flex cursor-text justify-between gap-2 sm:gap-3"
                                @click="$refs.hiddenInput?.focus()"
                                ref="pinContainerEl"
                            >
                                <div
                                    v-for="i in 6"
                                    :key="i"
                                    class="flex h-12 w-full items-center justify-center rounded-xl border-2 font-black transition-all sm:h-14 sm:text-xl"
                                    :class="[
                                        form.pin.length >= i
                                            ? 'border-brand-secondary bg-brand-secondary text-white'
                                            : form.pin.length === i - 1
                                              ? 'scale-105 border-brand-secondary bg-brand-secondary/10 text-brand-primary ring-4 ring-brand-secondary/20'
                                              : 'border-brand-primary/20 bg-brand-accent text-brand-primary/30',
                                    ]"
                                >
                                    {{ form.pin[i - 1] || '' }}
                                </div>
                            </div>

                            <span
                                v-if="form.errors.pin"
                                class="mt-2 block text-xs font-bold text-red-500"
                            >
                                {{ form.errors.pin }}
                            </span>
                        </div>

                        <!-- Numpad -->
                        <div class="rounded-2xl bg-brand-accent/50 p-4">
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <button
                                    v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                                    :key="num"
                                    type="button"
                                    class="sm:h-13 flex h-12 items-center justify-center rounded-xl border border-brand-primary/10 bg-brand-accent text-lg font-black text-brand-primary transition hover:bg-brand-primary hover:text-white active:scale-95"
                                    @click="pressKey(num.toString())"
                                >
                                    {{ num }}
                                </button>
                                <button
                                    type="button"
                                    class="sm:h-13 flex h-12 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-sm font-black text-red-500 transition hover:bg-red-500 hover:text-white active:scale-95"
                                    @click="pressKey('clear')"
                                >
                                    C
                                </button>
                                <button
                                    type="button"
                                    class="sm:h-13 flex h-12 items-center justify-center rounded-xl border border-brand-primary/10 bg-brand-accent text-lg font-black text-brand-primary transition hover:bg-brand-primary hover:text-white active:scale-95"
                                    @click="pressKey('0')"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    class="sm:h-13 flex h-12 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-sm font-black text-amber-600 transition hover:bg-amber-500 hover:text-white active:scale-95"
                                    @click="pressKey('back')"
                                >
                                    ⌫
                                </button>
                            </div>
                        </div>

                        <!-- Nickname Input Section -->
                        <div>
                            <label
                                for="live-alias-input"
                                class="mb-2 block text-sm font-black text-brand-primary"
                            >
                                Nama Panggilan Kamu
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-5 w-5 text-brand-primary/40"
                                    >
                                        <path
                                            d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"
                                        />
                                        <path
                                            d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"
                                        />
                                        <path d="M4 22h16" />
                                        <path
                                            d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"
                                        />
                                        <path
                                            d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"
                                        />
                                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                                    </svg>
                                </div>
                                <input
                                    id="live-alias-input"
                                    v-model="form.alias"
                                    maxlength="25"
                                    placeholder="Contoh: Sang Juara"
                                    autocomplete="nickname"
                                    class="block min-h-12 w-full rounded-xl border-2 border-brand-primary/20 bg-brand-accent/30 pl-11 pr-4 text-sm font-bold text-brand-primary transition placeholder:text-brand-primary/40 focus:border-brand-secondary focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-secondary/20"
                                />
                            </div>
                            <span
                                v-if="form.errors.alias"
                                class="mt-1 block text-xs font-bold text-red-500"
                            >
                                {{ form.errors.alias }}
                            </span>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                class="group relative flex min-h-14 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-brand-primary px-6 text-base font-black text-white shadow-figma transition-all hover:-translate-y-1 hover:bg-brand-secondary hover:text-white hover:shadow-figma-hover active:translate-y-1 active:shadow-none disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    form.processing ||
                                    form.pin.length !== 6 ||
                                    !form.alias.trim()
                                "
                            >
                                <span
                                    v-if="form.processing"
                                    class="flex items-center gap-2"
                                >
                                    <svg
                                        class="h-5 w-5 animate-spin"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v8H4z"
                                        />
                                    </svg>
                                    Menghubungkan...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Masuk dan Mulai
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-5 w-5"
                                    >
                                        <path
                                            d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"
                                        />
                                        <path
                                            d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"
                                        />
                                        <path
                                            d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"
                                        />
                                        <path
                                            d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"
                                        />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
</template>
