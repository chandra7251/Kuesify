<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { gsap } from "gsap";
import { onMounted, onUnmounted, ref, watch } from "vue";

const form = useForm({ pin: "", alias: "" });
const root = ref<HTMLElement | null>(null);
const canvasBg = ref<HTMLCanvasElement | null>(null);
const mascotEl = ref<HTMLElement | null>(null);
const speechBubbleEl = ref<HTMLElement | null>(null);
const pinContainerEl = ref<HTMLElement | null>(null);

const mascotSpeech = ref("Halo jagoan! Masukkan 6 digit Game PIN dari gurumu!");
const currentHint = ref("Ketik PIN langsung atau gunakan numpad");

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
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const resize = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    resize();
    window.addEventListener("resize", resize);

    const colors = ["#3154D5", "#90CB31", "#60A5FA", "#A3E635", "#38BDF8"];
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
    form.pin = input.value.replace(/[^0-9]/g, "").slice(0, 6);
}

function pressKey(key: string): void {
    if (key === "back") {
        if (form.pin.length > 0) {
            form.pin = form.pin.slice(0, -1);
            triggerMascotReaction("back");
        }
    } else if (key === "clear") {
        form.pin = "";
        triggerMascotReaction("clear");
    } else if (form.pin.length < 6) {
        form.pin += key;
        triggerMascotReaction("digit");
    }
}

function triggerMascotReaction(type: "digit" | "back" | "clear" | "complete" | "submit"): void {
    try {
        if (typeof navigator !== "undefined" && navigator.vibrate) {
            if (type === "digit") navigator.vibrate(12);
            else if (type === "back") navigator.vibrate(18);
            else if (type === "complete") navigator.vibrate([20, 60, 30, 60, 40]);
            else if (type === "submit") navigator.vibrate(30);
        }
    } catch {}

    if (!mascotEl.value || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    if (type === "digit") {
        gsap.timeline()
            .to(mascotEl.value, { scale: 1.12, rotation: 5, y: -8, duration: 0.1, ease: "power2.out" })
            .to(mascotEl.value, { scale: 1, rotation: 0, y: 0, duration: 0.35, ease: "elastic.out(1.2, 0.4)" });

        // Ripple reaction on speech bubble
        if (speechBubbleEl.value) {
            gsap.fromTo(speechBubbleEl.value, { scale: 0.98 }, { scale: 1, duration: 0.25, ease: "back.out(2)" });
        }
    } else if (type === "back") {
        gsap.timeline()
            .to(mascotEl.value, { rotation: -6, x: -6, duration: 0.1 })
            .to(mascotEl.value, { rotation: 0, x: 0, duration: 0.3, ease: "power2.out" });
    } else if (type === "complete") {
        // Grand celebration bounce & 360 spin
        gsap.timeline()
            .to(mascotEl.value, { y: -25, scale: 1.25, rotation: 360, duration: 0.6, ease: "back.out(1.8)" })
            .to(mascotEl.value, { y: 0, scale: 1, duration: 0.4, ease: "bounce.out" });
    } else if (type === "submit") {
        gsap.timeline()
            .to(mascotEl.value, { y: -40, scale: 1.3, rotation: -12, duration: 0.3, ease: "power2.out" })
            .to(mascotEl.value, { y: 0, scale: 1, rotation: 0, duration: 0.5, ease: "elastic.out(1, 0.3)" });
    }
}

watch(
    () => form.pin,
    (val) => {
        if (val.length === 0) {
            mascotSpeech.value = "Halo! Masukkan 6 digit Game PIN dari host kamu!";
            currentHint.value = "Ketik PIN langsung atau gunakan numpad interaktif di bawah";
        } else if (val.length < 6) {
            mascotSpeech.value = "Keren! Tinggal " + (6 - val.length) + " angka lagi!";
            currentHint.value = "Angka ke-" + (val.length + 1) + " sedang ditunggu...";
        } else if (val.length === 6) {
            mascotSpeech.value = "PIN lengkap! Sekarang tulis nama pahlawanmu!";
            currentHint.value = "Siap untuk masuk ke leaderboard!";
            triggerMascotReaction("complete");
        }
    }
);

watch(
    () => form.alias,
    (val) => {
        if (form.pin.length === 6 && val.trim().length > 0) {
            mascotSpeech.value = "Mantap " + val + "! Klik tombol Masuk Sesi!";
        }
    }
);

onMounted(() => {
    initCanvas();
    if (!root.value) return;

    motionMedia = gsap.matchMedia();
    motionMedia.add("(prefers-reduced-motion: no-preference)", () => {
        motionContext = gsap.context(() => {
            const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

            tl.from("[data-anim-header]", { autoAlpha: 0, y: -20, duration: 0.5 });
            tl.from("[data-anim-mascot]", { autoAlpha: 0, scale: 0.75, rotation: -15, duration: 0.7, ease: "back.out(1.7)" }, "-=0.3");
            tl.from("[data-anim-card]", { autoAlpha: 0, y: 30, scale: 0.95, duration: 0.65 }, "-=0.4");
            tl.from("[data-anim-digit]", { autoAlpha: 0, y: 15, scale: 0.8, stagger: 0.05, duration: 0.35, ease: "back.out(2)" }, "-=0.3");
            tl.from("[data-anim-numpad]", { autoAlpha: 0, y: 20, duration: 0.4 }, "-=0.2");

            // Ambient continuous float
            if (mascotEl.value) {
                gsap.to(mascotEl.value, {
                    y: -14,
                    rotation: 3,
                    duration: 2.4,
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut",
                });
            }

            // Glow orbit effect on active card
            gsap.to("[data-anim-glow]", {
                rotation: 360,
                duration: 18,
                repeat: -1,
                ease: "none",
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
    triggerMascotReaction("submit");
    form.post(route("live-sessions.join"));
}
</script>

<template>
    <Head title="Masuk Live Quiz — Kuesify" />
    <main
        ref="root"
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-8 font-sans sm:px-6 lg:px-8"
    >
        <!-- Interactive Animated Canvas Background -->
        <canvas ref="canvasBg" class="pointer-events-none absolute inset-0 z-0 h-full w-full opacity-60" />

        <!-- Floating Gradient Orbs -->
        <div class="pointer-events-none absolute -left-28 -top-28 h-96 w-96 rounded-full bg-brand-primary/25 blur-[100px]" />
        <div class="pointer-events-none absolute -bottom-28 -right-28 h-[28rem] w-[28rem] rounded-full bg-brand-secondary/25 blur-[120px]" />
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-sky-500/15 blur-[90px]" />

        <div class="relative z-10 w-full max-w-4xl">
            <!-- Top Navbar Bar -->
            <div data-anim-header class="mb-6 flex items-center justify-between">
                <Link
                    href="/"
                    class="group inline-flex items-center gap-2.5 rounded-2xl border border-white/10 bg-white/10 px-4 py-2.5 shadow-lg backdrop-blur-xl transition hover:border-brand-secondary/50 hover:bg-white/15"
                >
                    <ApplicationLogo class="h-8 w-8 text-brand-secondary transition group-hover:rotate-6 group-hover:scale-110" />
                    <span class="text-xl font-black tracking-tight text-white">kuesify</span>
                    <span class="rounded-full bg-brand-secondary px-2 py-0.5 text-[10px] font-black uppercase text-brand-dark">Live</span>
                </Link>

                <Link
                    href="/login"
                    class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-slate-300 backdrop-blur-lg transition hover:bg-white/15 hover:text-white sm:text-sm"
                >
                    Bukan siswa? Masuk akun →
                </Link>
            </div>

            <!-- Two-Column Hero: Mascot (Left) & PIN Card (Right) -->
            <div class="grid items-center gap-8 lg:grid-cols-[1fr_1.1fr]">
                <!-- LEFT COLUMN: Interactive Mascot Companion -->
                <div data-anim-mascot class="flex flex-col items-center text-center lg:items-start lg:text-left">
                    <div class="relative w-full max-w-md">
                        <!-- Speech Bubble with dynamic animation -->
                        <div
                            ref="speechBubbleEl"
                            class="relative rounded-3xl border-2 border-brand-secondary/40 bg-slate-900/90 p-5 shadow-[0_15px_35px_rgba(0,0,0,0.4)] backdrop-blur-xl transition-all sm:p-6"
                        >
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-secondary opacity-75" />
                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-brand-secondary" />
                                </span>
                                <span class="text-xs font-black uppercase tracking-wider text-brand-secondary">
                                    Kuesi Companion
                                </span>
                            </div>

                            <p class="mt-2.5 text-lg font-black leading-snug text-white sm:text-xl">
                                "{{ mascotSpeech }}"
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-400">
                                {{ currentHint }}
                            </p>

                            <!-- Speech pointer triangle -->
                            <div class="absolute -bottom-3 left-12 h-0 w-0 border-x-8 border-x-transparent border-t-8 border-t-slate-900 lg:left-20" />
                        </div>

                        <!-- Mascot SVG with floating shadow -->
                        <div class="mt-6 flex flex-col items-center justify-center lg:items-start lg:pl-12">
                            <div class="relative">
                                <img
                                    ref="mascotEl"
                                    src="/assets/kuesify/characters/image-14.svg"
                                    alt="Maskot interaktif penyemangat kuis"
                                    class="h-48 w-48 drop-shadow-[0_25px_35px_rgba(144,203,49,0.3)] will-change-transform sm:h-56 sm:w-56"
                                />
                                <!-- Glowing pedestal below mascot -->
                                <div class="mx-auto mt-2 h-4 w-36 rounded-full bg-brand-secondary/30 blur-md" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Cyber-Futuristic PIN Form Card -->
                <section
                    data-anim-card
                    class="relative overflow-hidden rounded-[2.5rem] border-2 border-white/15 bg-slate-900/85 p-6 shadow-[0_25px_60px_rgba(0,0,0,0.6)] backdrop-blur-2xl sm:p-8"
                >
                    <!-- Header with Badge -->
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-secondary/20 px-3 py-1 text-xs font-black uppercase tracking-wider text-brand-secondary">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-brand-secondary" />
                                Arena Live Quiz
                            </span>
                            <h1 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">
                                Masukkan PIN Sesi
                            </h1>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-primary/30 text-2xl shadow-inner ring-1 ring-white/20">
                            ⚡
                        </div>
                    </div>

                    <form class="mt-6 space-y-5" @submit.prevent="join">
                        <!-- PIN Section -->
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="live-pin-input" class="text-xs font-black uppercase tracking-wider text-slate-300">
                                    6 Digit Kode Game
                                </label>
                                <button
                                    v-if="form.pin.length > 0"
                                    type="button"
                                    class="text-xs font-bold text-red-400 transition hover:text-red-300"
                                    @click="pressKey('clear')"
                                >
                                    Reset PIN ↺
                                </button>
                            </div>

                            <!-- Hidden Master Input (accessible & full keyboard support) -->
                            <div class="relative mt-2">
                                <input
                                    id="live-pin-input"
                                    :value="form.pin"
                                    inputmode="numeric"
                                    maxlength="6"
                                    placeholder="••••••"
                                    autocomplete="off"
                                    class="absolute inset-0 z-10 h-full w-full cursor-text opacity-0"
                                    aria-describedby="pin-error"
                                    @input="sanitizePin"
                                />

                                <!-- Visual Big Digit Display Boxes -->
                                <div ref="pinContainerEl" class="grid grid-cols-6 gap-2 sm:gap-3">
                                    <div
                                        v-for="i in 6"
                                        :key="i"
                                        data-anim-digit
                                        class="flex h-14 items-center justify-center rounded-2xl border-2 text-2xl font-black transition-all sm:h-16 sm:text-3xl"
                                        :class="[
                                            form.pin.length === i - 1
                                                ? 'border-brand-secondary bg-brand-secondary/15 text-white ring-4 ring-brand-secondary/25 scale-105 shadow-[0_0_20px_rgba(144,203,49,0.35)]'
                                                : form.pin.length >= i
                                                  ? 'border-brand-primary bg-brand-primary text-white shadow-md shadow-brand-primary/30 scale-100'
                                                  : 'border-white/10 bg-white/5 text-slate-500'
                                        ]"
                                    >
                                        {{ form.pin[i - 1] || "" }}
                                    </div>
                                </div>
                            </div>

                            <span
                                v-if="form.errors.pin"
                                id="pin-error"
                                class="mt-2 block text-xs font-bold text-red-400"
                            >
                                {{ form.errors.pin }}
                            </span>
                        </div>

                        <!-- On-Screen Interactive Numpad for Touch / Mouse -->
                        <div data-anim-numpad class="rounded-2xl border border-white/10 bg-white/5 p-2 sm:p-3">
                            <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Virtual Numpad Interaktif
                            </p>
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                                <button
                                    v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                                    :key="num"
                                    type="button"
                                    class="flex h-10 items-center justify-center rounded-xl bg-white/10 text-base font-black text-white shadow-sm transition hover:bg-brand-primary hover:scale-105 active:scale-95 sm:h-11 sm:text-lg"
                                    @click="pressKey(num.toString())"
                                >
                                    {{ num }}
                                </button>
                                <button
                                    type="button"
                                    class="flex h-10 items-center justify-center rounded-xl bg-red-500/20 text-xs font-black text-red-300 transition hover:bg-red-500/30 active:scale-95 sm:h-11"
                                    @click="pressKey('clear')"
                                >
                                    C
                                </button>
                                <button
                                    type="button"
                                    class="flex h-10 items-center justify-center rounded-xl bg-white/10 text-base font-black text-white shadow-sm transition hover:bg-brand-primary hover:scale-105 active:scale-95 sm:h-11 sm:text-lg"
                                    @click="pressKey('0')"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    class="flex h-10 items-center justify-center rounded-xl bg-amber-500/20 text-xs font-black text-amber-300 transition hover:bg-amber-500/30 active:scale-95 sm:h-11"
                                    @click="pressKey('back')"
                                >
                                    ⌫
                                </button>
                            </div>
                        </div>

                        <!-- Nickname Input Section -->
                        <div>
                            <label for="live-alias-input" class="text-xs font-black uppercase tracking-wider text-slate-300">
                                Nama Panggilan Kamu di Panggung
                            </label>
                            <div class="relative mt-2">
                                <input
                                    id="live-alias-input"
                                    v-model="form.alias"
                                    maxlength="25"
                                    placeholder="Contoh: Sang Juara 🏆"
                                    autocomplete="nickname"
                                    class="block min-h-12 w-full rounded-2xl border-2 border-white/15 bg-white/10 px-4 text-sm font-bold text-white placeholder:text-slate-500 transition focus:border-brand-secondary focus:bg-white/15 focus:outline-none focus:ring-4 focus:ring-brand-secondary/20"
                                    aria-describedby="alias-error"
                                />
                            </div>
                            <span
                                v-if="form.errors.alias"
                                id="alias-error"
                                class="mt-1 block text-xs font-bold text-red-400"
                            >
                                {{ form.errors.alias }}
                            </span>
                        </div>

                        <!-- Submit Button with Pulse Glow -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                class="group relative flex min-h-14 w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary via-[#4364e8] to-brand-primary px-6 text-base font-black text-white shadow-[0_8px_25px_rgba(49,84,213,0.5)] transition-all hover:-translate-y-1 hover:shadow-[0_12px_35px_rgba(49,84,213,0.7)] active:translate-y-1 active:shadow-none disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="form.processing || form.pin.length !== 6 || !form.alias.trim()"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                    </svg>
                                    Menghubungkan ke Panggung...
                                </span>
                                <span v-else class="flex items-center gap-2 font-black tracking-wide">
                                    Masuk dan Mulai Berlaga 🚀
                                </span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
</template>
