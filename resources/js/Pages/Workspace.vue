<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Props {
    title: string;
    section?: 'questions' | 'admin' | 'categories' | 'reports';
    tab?: 'questions' | 'admin' | 'categories' | 'reports';
    items?: {
        data: any[];
        links: any[];
    };
    filters?: Record<string, any>;
    types?: string[];
    categories?: any[];
    tags?: any[];
    summary?: {
        total_questions?: number;
        total_quizzes?: number;
        active_sessions?: number;
        active_tab?: string;
        is_super_admin?: boolean;
        super_admin_metrics?: {
            total_users: number;
            total_organizations: number;
            total_attempts: number;
            avg_score: number;
            top_organizations: Array<{
                id: number;
                name: string;
                code: string;
                attempts_count: number;
            }>;
        };
        health?: {
            queued_jobs: number;
            failed_jobs: number;
            ai_failures: number;
            reverb_running: boolean;
            reverb_status: string;
            reverb_server?: string;
            reverb_latency_ms?: number;
        };
        maintenance?: {
            mode: string;
            scheduled_at: string | null;
            message: string;
        };
        ai_config?: {
            active_model: string;
            weekly_limit: number;
            total_generations_all_time: number;
            avg_draft_ratio: number;
        };
        audio_settings?: {
            master_volume?: number;
            contexts?: Record<string, {
                label: string;
                preset: string;
                custom_url?: string;
                volume: number;
                enabled: boolean;
            }>;
        };
        monthly_stats?: {
            active_participants_month?: number;
            ai_generations_month?: number;
            total_organizations?: number;
        };
    };
    tenants?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    items: () => ({ data: [], links: [] }),
    filters: () => ({}),
    types: () => [],
    categories: () => [],
    tags: () => [],
    summary: () => ({}),
    tenants: () => [],
});

const isSuperAdmin = computed(() => Boolean(props.summary?.is_super_admin ?? true));
const tab = computed(() => props.section || props.tab || 'questions');

const maintenanceForm = useForm({
    mode: props.summary.maintenance?.mode ?? 'live',
    scheduled_at: props.summary.maintenance?.scheduled_at ?? '',
    message: props.summary.maintenance?.message ?? '',
});

const aiForm = useForm({
    active_model: props.summary.ai_config?.active_model ?? 'gemini-2.5-flash',
    weekly_limit: props.summary.ai_config?.weekly_limit ?? 10,
});

const defaultContexts: Record<string, { label: string; preset: string; custom_url: string; volume: number; enabled: boolean }> = {
    bgm_lobby: {
        label: 'BGM Lobby & Ruang Tunggu',
        preset: 'Arcade Retro 8-bit (Default)',
        custom_url: '',
        volume: 70,
        enabled: true,
    },
    bgm_gameplay: {
        label: 'BGM Gameplay / Soal Berjalan',
        preset: 'Ticking Pulse Electro',
        custom_url: '',
        volume: 65,
        enabled: true,
    },
    bgm_podium: {
        label: 'BGM Podium Juara & Kemenangan',
        preset: 'Grand Champion Fanfare',
        custom_url: '',
        volume: 80,
        enabled: true,
    },
    sfx_correct: {
        label: 'SFX Jawaban Benar',
        preset: 'Crystal Chime High',
        custom_url: '',
        volume: 90,
        enabled: true,
    },
    sfx_wrong: {
        label: 'SFX Jawaban Salah',
        preset: 'Muted Buzzer Low',
        custom_url: '',
        volume: 75,
        enabled: true,
    },
    sfx_tick: {
        label: 'SFX Detik Kritis (Countdown 5s)',
        preset: 'Clock Tick Fast',
        custom_url: '',
        volume: 80,
        enabled: true,
    },
    sfx_streak: {
        label: 'SFX Streak Combo Juara',
        preset: 'Powerup Spark Chord',
        custom_url: '',
        volume: 85,
        enabled: true,
    },
};

const rawAudioSettings = (props.summary.audio_settings as any) || {};
const initialContexts = rawAudioSettings.contexts || defaultContexts;

const audioForm = useForm({
    master_volume: rawAudioSettings.master_volume ?? 75,
    contexts: {
        bgm_lobby: { ...defaultContexts.bgm_lobby, ...(initialContexts.bgm_lobby || {}) },
        bgm_gameplay: { ...defaultContexts.bgm_gameplay, ...(initialContexts.bgm_gameplay || {}) },
        bgm_podium: { ...defaultContexts.bgm_podium, ...(initialContexts.bgm_podium || {}) },
        sfx_correct: { ...defaultContexts.sfx_correct, ...(initialContexts.sfx_correct || {}) },
        sfx_wrong: { ...defaultContexts.sfx_wrong, ...(initialContexts.sfx_wrong || {}) },
        sfx_tick: { ...defaultContexts.sfx_tick, ...(initialContexts.sfx_tick || {}) },
        sfx_streak: { ...defaultContexts.sfx_streak, ...(initialContexts.sfx_streak || {}) },
    },
});

const categoryForm = useForm({
    name: '',
    description: '',
});

const showAudioModal = ref(false);
const activeAudioPlaying = ref<string | null>(null);
let activeAudioElement: HTMLAudioElement | null = null;

function stopAudio() {
    if (activeAudioElement) {
        activeAudioElement.pause();
        activeAudioElement.currentTime = 0;
        activeAudioElement = null;
    }
    activeAudioPlaying.value = null;
}

function playAudioPreview(contextKey: string) {
    stopAudio();
    const ctx = (audioForm.contexts as any)[contextKey];
    if (!ctx || !ctx.enabled) return;

    if (ctx.preset === 'Custom URL' && ctx.custom_url) {
        try {
            activeAudioElement = new Audio(ctx.custom_url);
            activeAudioElement.volume = Math.max(0, Math.min(1, (ctx.volume / 100) * (audioForm.master_volume / 100)));
            activeAudioPlaying.value = contextKey;
            activeAudioElement.play().catch(() => {
                activeAudioPlaying.value = null;
            });
            activeAudioElement.onended = () => {
                activeAudioPlaying.value = null;
            };
        } catch {
            activeAudioPlaying.value = null;
        }
        return;
    }

    try {
        const AudioCtx = window.AudioContext || (window as any).webkitAudioContext;
        if (!AudioCtx) return;
        const audioCtx = new AudioCtx();
        activeAudioPlaying.value = contextKey;
        const masterVol = (audioForm.master_volume / 100);
        const ctxVol = (ctx.volume / 100);

        if (contextKey === 'sfx_correct') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, audioCtx.currentTime);
            osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1);
            const vol = masterVol * ctxVol * 0.3;
            gain.gain.setValueAtTime(vol, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.4);
            setTimeout(() => { activeAudioPlaying.value = null; }, 400);
        } else if (contextKey === 'sfx_wrong') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(220, audioCtx.currentTime);
            osc.frequency.setValueAtTime(146.83, audioCtx.currentTime + 0.15);
            const vol = masterVol * ctxVol * 0.3;
            gain.gain.setValueAtTime(vol, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.45);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.45);
            setTimeout(() => { activeAudioPlaying.value = null; }, 450);
        } else if (contextKey === 'sfx_tick') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1200, audioCtx.currentTime);
            const vol = masterVol * ctxVol * 0.2;
            gain.gain.setValueAtTime(vol, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.08);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.08);
            setTimeout(() => { activeAudioPlaying.value = null; }, 100);
        } else if (contextKey === 'sfx_streak') {
            const notes = [523.25, 659.25, 783.99, 1046.5];
            notes.forEach((freq, idx) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = freq;
                const vol = masterVol * ctxVol * 0.2;
                gain.gain.setValueAtTime(vol, audioCtx.currentTime + idx * 0.07);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (idx + 1) * 0.07);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + idx * 0.07);
                osc.stop(audioCtx.currentTime + (idx + 1) * 0.07);
            });
            setTimeout(() => { activeAudioPlaying.value = null; }, 350);
        } else {
            const notes = contextKey === 'bgm_podium'
                ? [523.25, 659.25, 783.99, 1046.5, 1318.51]
                : contextKey === 'bgm_gameplay'
                ? [330, 392, 440, 493.88, 587.33]
                : [440, 554.37, 659.25, 880];

            notes.forEach((freq, idx) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'square';
                osc.frequency.value = freq;
                const vol = masterVol * ctxVol * 0.15;
                gain.gain.setValueAtTime(vol, audioCtx.currentTime + idx * 0.1);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (idx + 1) * 0.1);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + idx * 0.1);
                osc.stop(audioCtx.currentTime + (idx + 1) * 0.1);
            });
            setTimeout(() => { activeAudioPlaying.value = null; }, notes.length * 100);
        }
    } catch {
        activeAudioPlaying.value = null;
    }
}

function submitMaintenance() {
    maintenanceForm.post('/admin/settings/maintenance', {
        preserveScroll: true,
    });
}

function submitAi() {
    aiForm.post('/admin/settings/ai-limits', {
        preserveScroll: true,
    });
}

function submitAudio() {
    audioForm.post('/admin/settings/audio', {
        preserveScroll: true,
        onSuccess: () => {
            showAudioModal.value = false;
        }
    });
}

function submitCategory() {
    categoryForm.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => {
            categoryForm.reset();
        }
    });
}

function deleteCategory(categoryId: number) {
    if (confirm('Yakin ingin menghapus kategori ini?')) {
        router.delete(`/admin/categories/${categoryId}`, {
            preserveScroll: true,
        });
    }
}

function probeHealth() {
    router.reload({ only: ['summary'] });
}
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-[#E6F1F5]">
            <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Header Banner (Clean Theme Matching Dashboard) -->
                <section class="overflow-hidden rounded-2xl bg-[#3b5fe1] px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80">
                                <span class="h-2 w-2 rounded-full bg-[#90CB31]"></span>
                                {{ isSuperAdmin ? 'SaaS Platform Owner' : 'Workspace' }} · Ecosystem Control
                            </p>
                            <h1 class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                                {{ title }}
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80 sm:text-base">
                                {{ tab === 'admin' ? 'Pusat kendali arsitektur SaaS, audio live interaktif, kuota AI Gemini, dan status infrastruktur platform.' : 'Ringkasan analitik ekosistem dan laporan metrik platform Kuesify.' }}
                            </p>
                        </div>
                        <div class="grid shrink-0 gap-3 sm:flex">
                            <Link
                                v-if="tab === 'admin'"
                                href="/admin/users"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#3154D5] shadow-sm transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                            >
                                <i class="fa-solid fa-users mr-2 text-sm"></i>
                                Monitoring User
                            </Link>
                            <button
                                v-if="tab === 'admin'"
                                @click="probeHealth"
                                type="button"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20"
                            >
                                <i class="fa-solid fa-rotate mr-2 text-sm"></i>
                                Probe Server
                            </button>
                        </div>
                    </div>
                </section>

                <!-- TAB ADMIN: KONTROL PLATFORM SUPER ADMIN -->
                <div v-if="tab === 'admin'" class="space-y-6">
                    <!-- SECTION TITLE -->
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Konfigurasi & Kontrol Platform</h2>
                        <p class="mt-1 text-sm text-slate-600">Kelola status infrastruktur, suara interaktif kuis, AI, dan taksonomi.</p>
                    </div>

                    <!-- GRID UTAMA 4 KARTU KENDALI -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- KARTU 1: Server & Queue Infrastructure -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#3154D5] flex items-center justify-center">
                                            <i class="fa-solid fa-server text-base"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-base">Infrastruktur & WebSocket</h3>
                                            <p class="text-xs text-slate-500">Reverb WebSocket, Redis & Queue Workers</p>
                                        </div>
                                    </div>
                                    <span
                                        :class="summary.health?.reverb_running ? 'bg-emerald-50 text-[#0AB883] border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5"
                                    >
                                        <span :class="summary.health?.reverb_running ? 'bg-[#0AB883]' : 'bg-rose-500'" class="w-2 h-2 rounded-full"></span>
                                        {{ summary.health?.reverb_running ? 'ONLINE' : 'OFFLINE' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-3 my-4">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">Antrean Job</div>
                                        <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ summary.health?.queued_jobs ?? 0 }}</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">Job Gagal</div>
                                        <div class="text-xl font-extrabold text-rose-600 mt-0.5">{{ summary.health?.failed_jobs ?? 0 }}</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                        <div class="text-xs text-slate-500 font-medium">AI Failures</div>
                                        <div class="text-xl font-extrabold text-amber-600 mt-0.5">{{ summary.health?.ai_failures ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>Latensi Server: <strong class="text-slate-800">{{ summary.health?.reverb_latency_ms ?? 12 }}ms</strong></span>
                                <button @click="probeHealth" class="text-[#3154D5] hover:underline font-bold">Cek Status Sekarang &rarr;</button>
                            </div>
                        </div>

                        <!-- KARTU 2: Mode Maintenance Platform -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                        <i class="fa-solid fa-triangle-exclamation text-base"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-base">Mode Operasional & Maintenance</h3>
                                        <p class="text-xs text-slate-500">Kelola status aktif aplikasi dan jadwal pemeliharaan</p>
                                    </div>
                                </div>

                                <form @submit.prevent="submitMaintenance" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Mode</label>
                                        <select v-model="maintenanceForm.mode" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20">
                                            <option value="live">Live Production (Aplikasi Berjalan Normal)</option>
                                            <option value="scheduled">Scheduled Maintenance (Pemberitahuan Terjadwal)</option>
                                            <option value="emergency">Emergency Maintenance (Blokir Akses User)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pesan untuk Pengguna</label>
                                        <input
                                            v-model="maintenanceForm.message"
                                            type="text"
                                            placeholder="Contoh: Pemeliharaan rutin server setiap Sabtu pukul 02:00 WIB"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                        />
                                    </div>

                                    <div class="pt-2 flex justify-end">
                                        <button
                                            type="submit"
                                            :disabled="maintenanceForm.processing"
                                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition disabled:opacity-50"
                                        >
                                            <i class="fa-solid fa-floppy-disk mr-1.5"></i>
                                            {{ maintenanceForm.processing ? 'Menyimpan...' : 'Simpan Mode' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- KARTU 3: Intelligence & AI Engine Settings -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                        <i class="fa-solid fa-wand-magic-sparkles text-base"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-base">AI Engine & Quota SaaS</h3>
                                        <p class="text-xs text-slate-500">Konfigurasi Gemini AI, Model & Batas Kuota Guru</p>
                                    </div>
                                </div>

                                <form @submit.prevent="submitAi" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Model Gemini Aktif</label>
                                        <select v-model="aiForm.active_model" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20">
                                            <option value="gemini-2.5-flash">Gemini 2.5 Flash (Super Cepat & Efisien)</option>
                                            <option value="gemini-1.5-pro">Gemini 1.5 Pro (Penalaran Kompleks)</option>
                                            <option value="gemini-1.5-flash">Gemini 1.5 Flash (Legacy Stabil)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="text-xs font-semibold text-slate-600">Batas Kuota AI (Kuis/Minggu per Guru)</label>
                                            <span class="text-xs font-bold text-[#3154D5]">{{ aiForm.weekly_limit }} Kuis</span>
                                        </div>
                                        <input
                                            v-model.number="aiForm.weekly_limit"
                                            type="range"
                                            min="1"
                                            max="50"
                                            step="1"
                                            class="w-full accent-[#3154D5]"
                                        />
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs text-slate-600">
                                        <span>Total All-Time: <strong>{{ summary.ai_config?.total_generations_all_time ?? 0 }}</strong></span>
                                        <span>Rasio Diterima: <strong>{{ summary.ai_config?.avg_draft_ratio ?? 85 }}%</strong></span>
                                    </div>

                                    <div class="flex justify-end pt-1">
                                        <button
                                            type="submit"
                                            :disabled="aiForm.processing"
                                            class="px-4 py-2 rounded-xl bg-[#3154D5] hover:bg-[#2744ab] text-white text-xs font-bold transition disabled:opacity-50"
                                        >
                                            <i class="fa-solid fa-floppy-disk mr-1.5"></i>
                                            {{ aiForm.processing ? 'Menyimpan...' : 'Perbarui Kuota AI' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- KARTU 4: STUDIO AUDIO & MUSIK MULTI-KONTEKS -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0AB883] flex items-center justify-center">
                                            <i class="fa-solid fa-music text-base"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-base">Studio Audio Multi-Konteks</h3>
                                            <p class="text-xs text-slate-500">BGM Lobby, Gameplay, Podium & SFX Interaktif</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0AB883] border border-emerald-200">
                                        7 Konteks
                                    </span>
                                </div>

                                <div class="space-y-3 my-2">
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                        <span>Master Volume</span>
                                        <span class="text-[#0AB883] font-bold">{{ audioForm.master_volume }}%</span>
                                    </div>
                                    <input
                                        v-model.number="audioForm.master_volume"
                                        type="range"
                                        min="0"
                                        max="100"
                                        class="w-full accent-[#0AB883]"
                                    />

                                    <!-- Quick Audio Test Chips -->
                                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                            <span class="truncate font-medium text-slate-600">BGM Lobby</span>
                                            <button @click="playAudioPreview('bgm_lobby')" type="button" class="text-xs font-bold text-[#3154D5] hover:underline flex items-center gap-1">
                                                <i class="fa-solid" :class="activeAudioPlaying === 'bgm_lobby' ? 'fa-volume-high' : 'fa-play'"></i>
                                                <span>{{ activeAudioPlaying === 'bgm_lobby' ? 'Playing' : 'Tes' }}</span>
                                            </button>
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                            <span class="truncate font-medium text-slate-600">SFX Benar</span>
                                            <button @click="playAudioPreview('sfx_correct')" type="button" class="text-xs font-bold text-[#0AB883] hover:underline flex items-center gap-1">
                                                <i class="fa-solid" :class="activeAudioPlaying === 'sfx_correct' ? 'fa-volume-high' : 'fa-play'"></i>
                                                <span>{{ activeAudioPlaying === 'sfx_correct' ? 'Ding' : 'Tes' }}</span>
                                            </button>
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                            <span class="truncate font-medium text-slate-600">BGM Gameplay</span>
                                            <button @click="playAudioPreview('bgm_gameplay')" type="button" class="text-xs font-bold text-[#3154D5] hover:underline flex items-center gap-1">
                                                <i class="fa-solid" :class="activeAudioPlaying === 'bgm_gameplay' ? 'fa-volume-high' : 'fa-play'"></i>
                                                <span>{{ activeAudioPlaying === 'bgm_gameplay' ? 'Playing' : 'Tes' }}</span>
                                            </button>
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                            <span class="truncate font-medium text-slate-600">BGM Podium</span>
                                            <button @click="playAudioPreview('bgm_podium')" type="button" class="text-xs font-bold text-[#3154D5] hover:underline flex items-center gap-1">
                                                <i class="fa-solid" :class="activeAudioPlaying === 'bgm_podium' ? 'fa-volume-high' : 'fa-play'"></i>
                                                <span>{{ activeAudioPlaying === 'bgm_podium' ? 'Playing' : 'Tes' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <button
                                    @click="stopAudio"
                                    type="button"
                                    class="text-xs text-rose-600 font-bold hover:underline flex items-center gap-1"
                                >
                                    <i class="fa-solid fa-stop"></i>
                                    <span>Stop Audio</span>
                                </button>
                                <button
                                    @click="showAudioModal = true"
                                    type="button"
                                    class="px-4 py-2 rounded-xl bg-[#0AB883] hover:bg-[#09a072] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
                                >
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>Studio Audio & URL &rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION MASTER KATEGORI SOAL -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Taksonomi Kategori Global</h3>
                                <p class="text-xs text-slate-500">Master kategori untuk pengelompokan bank soal se-platform</p>
                            </div>

                            <form @submit.prevent="submitCategory" class="flex items-center gap-2">
                                <input
                                    v-model="categoryForm.name"
                                    type="text"
                                    placeholder="Nama kategori baru..."
                                    required
                                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20 w-52"
                                />
                                <button
                                    type="submit"
                                    :disabled="categoryForm.processing"
                                    class="px-4 py-2 rounded-xl bg-[#3154D5] hover:bg-[#2744ab] text-white text-xs font-bold transition disabled:opacity-50"
                                >
                                    <i class="fa-solid fa-plus mr-1"></i>
                                    Tambah
                                </button>
                            </form>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <div
                                v-for="cat in categories"
                                :key="cat.id"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700"
                            >
                                <span>{{ cat.name }}</span>
                                <button
                                    @click="deleteCategory(cat.id)"
                                    type="button"
                                    class="text-slate-400 hover:text-rose-600 font-bold p-0.5 transition"
                                    title="Hapus Kategori"
                                >
                                    &times;
                                </button>
                            </div>
                            <div v-if="!categories?.length" class="text-xs text-slate-400 italic">
                                Belum ada kategori kuis dibuat.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB REPORTS: LAPORAN GLOBAL SAAS SUPER ADMIN -->
                <div v-else-if="tab === 'reports' && isSuperAdmin" class="space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Ringkasan Metrik Platform SaaS</h2>
                        <p class="mt-1 text-sm text-slate-600">Data agregat aktivitas multi-tenant dari seluruh sekolah & pengguna.</p>
                    </div>

                    <!-- METRIK KINERJA SAAS (CLEAN CARDS) -->
                    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-4">
                        <article class="border-b border-r border-slate-100 p-5 sm:border-b-0 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Instansi</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ summary.super_admin_metrics?.total_organizations ?? tenants?.length ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Sekolah & Tenant Aktif</p>
                        </article>

                        <article class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengguna</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ summary.super_admin_metrics?.total_users ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Guru, Siswa & Admin</p>
                        </article>

                        <article class="border-r border-slate-100 p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Attempts</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#3154D5]">{{ summary.super_admin_metrics?.total_attempts ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Pengerjaan Selesai</p>
                        </article>

                        <article class="p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-Rata Skor</p>
                            <p class="mt-2 text-3xl font-extrabold text-[#527A12]">{{ summary.super_admin_metrics?.avg_score ?? 0 }}</p>
                            <p class="mt-1 text-xs text-slate-500">Skor Agregat Nasional</p>
                        </article>
                    </div>

                    <!-- TABEL AUDIT TENANT & ORGANISASI SE-PLATFORM -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Daftar Tenant Sekolah / Instansi Terdaftar</h3>
                                <p class="text-xs text-slate-500">Status penggunaan dan member masing-masing instansi</p>
                            </div>
                            <Link
                                href="/admin/users"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition"
                            >
                                <span>Lihat Rincian Pengguna Global &rarr;</span>
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                    <tr>
                                        <th class="py-3 px-4">Nama Instansi / Tenant</th>
                                        <th class="py-3 px-4">Kode Identifikasi</th>
                                        <th class="py-3 px-4">Zona Waktu</th>
                                        <th class="py-3 px-4">Total Member</th>
                                        <th class="py-3 px-4 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="org in tenants" :key="org.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-800">{{ org.name }}</td>
                                        <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ org.code }}</td>
                                        <td class="py-3.5 px-4 text-xs text-slate-600">{{ org.timezone ?? 'Asia/Jakarta' }}</td>
                                        <td class="py-3.5 px-4 text-xs font-bold text-slate-700">{{ org.users_count ?? org.users?.length ?? '-' }} Akun</td>
                                        <td class="py-3.5 px-4 text-right">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0AB883] border border-emerald-200">
                                                Active Tenant
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!tenants?.length">
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs italic">
                                            Belum ada data tenant terdaftar.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB QUESTION BANK / TAB LAINNYA -->
                <div v-else class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-slate-900 text-base">Bank Soal & Materi</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-50/70 border-y border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Prompt Soal</th>
                                    <th class="py-3 px-4">Tipe</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in items.data" :key="item.id">
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ item.prompt }}</td>
                                    <td class="py-3 px-4 text-xs text-slate-500">{{ item.type }}</td>
                                    <td class="py-3 px-4 text-xs text-[#3154D5] font-semibold">{{ item.category?.name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-xs font-bold">{{ item.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="items.links" />
                    </div>
                </div>
            </main>
        </div>

        <!-- MODAL STUDIO AUDIO MULTI-KONTEKS INTERAKTIF -->
        <div v-if="showAudioModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-4xl max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="p-5 bg-[#3b5fe1] text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Studio Audio & Musik Multi-Konteks</span>
                        </h2>
                        <p class="text-xs text-white/80 mt-0.5">Kustomisasi preset atau input URL audio khusus untuk setiap suasana pengerjaan kuis</p>
                    </div>
                    <button
                        @click="showAudioModal = false; stopAudio()"
                        type="button"
                        class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition"
                    >
                        <i class="fa-solid fa-xmark text-lg leading-none"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-5">
                    <!-- Global Master Volume Bar -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Master Gain Volume</div>
                            <div class="text-xs text-slate-500">Volume utama yang mempengaruhi seluruh BGM dan efek suara game</div>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-64">
                            <input
                                v-model.number="audioForm.master_volume"
                                type="range"
                                min="0"
                                max="100"
                                class="w-full accent-[#3154D5]"
                            />
                            <span class="text-sm font-extrabold text-[#3154D5] w-12 text-right">{{ audioForm.master_volume }}%</span>
                        </div>
                    </div>

                    <!-- Contexts Audio List -->
                    <div class="space-y-4">
                        <div
                            v-for="(ctx, key) in audioForm.contexts"
                            :key="key"
                            class="p-4 rounded-xl border border-slate-200 bg-white hover:border-[#3154D5]/40 transition space-y-3"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <input
                                        v-model="ctx.enabled"
                                        type="checkbox"
                                        :id="'chk_' + key"
                                        class="rounded border-slate-300 text-[#3154D5] focus:ring-[#3154D5] w-4 h-4"
                                    />
                                    <label :for="'chk_' + key" class="font-bold text-slate-800 text-sm cursor-pointer">
                                        {{ ctx.label }}
                                    </label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        @click="playAudioPreview(String(key))"
                                        type="button"
                                        :disabled="!ctx.enabled"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-[#3154D5] hover:bg-blue-100 text-xs font-bold border border-blue-200 transition disabled:opacity-40 flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid" :class="activeAudioPlaying === key ? 'fa-volume-high' : 'fa-play'"></i>
                                        <span>{{ activeAudioPlaying === key ? 'Bunyi' : 'Tes Suara' }}</span>
                                    </button>
                                    <button
                                        v-if="activeAudioPlaying === key"
                                        @click="stopAudio"
                                        type="button"
                                        class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-bold border border-rose-200 flex items-center gap-1"
                                    >
                                        <i class="fa-solid fa-stop"></i>
                                        <span>Stop</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <!-- Preset Selector -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Preset Bawaan</label>
                                    <select
                                        v-model="ctx.preset"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                    >
                                        <option value="Arcade Retro 8-bit (Default)">Arcade Retro 8-bit</option>
                                        <option value="Ticking Pulse Electro">Ticking Pulse Electro</option>
                                        <option value="Grand Champion Fanfare">Grand Champion Fanfare</option>
                                        <option value="Crystal Chime High">Crystal Chime High</option>
                                        <option value="Muted Buzzer Low">Muted Buzzer Low</option>
                                        <option value="Clock Tick Fast">Clock Tick Fast</option>
                                        <option value="Powerup Spark Chord">Powerup Spark Chord</option>
                                        <option value="Custom URL">Input Custom Audio URL</option>
                                    </select>
                                </div>

                                <!-- Custom URL Input (if Custom URL selected) -->
                                <div class="sm:col-span-2" v-if="ctx.preset === 'Custom URL'">
                                    <label class="block text-xs font-semibold text-[#3154D5] mb-1">URL Audio Custom (MP3/WAV/CDN)</label>
                                    <input
                                        v-model="ctx.custom_url"
                                        type="url"
                                        placeholder="https://domain.com/audio/my-track.mp3"
                                        class="w-full rounded-xl border border-blue-300 bg-blue-50/20 px-3 py-2 text-xs font-mono text-slate-800 focus:border-[#3154D5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#3154D5]/20"
                                    />
                                </div>

                                <!-- Volume Slider -->
                                <div :class="ctx.preset === 'Custom URL' ? 'sm:col-span-3' : 'sm:col-span-2'">
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="text-xs font-semibold text-slate-500">Volume Konteks Ini</label>
                                        <span class="text-xs font-bold text-[#3154D5]">{{ ctx.volume }}%</span>
                                    </div>
                                    <input
                                        v-model.number="ctx.volume"
                                        type="range"
                                        min="0"
                                        max="100"
                                        class="w-full accent-[#3154D5]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button
                        @click="showAudioModal = false; stopAudio()"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition"
                    >
                        Tutup
                    </button>

                    <div class="flex items-center gap-3">
                        <button
                            @click="submitAudio"
                            type="button"
                            :disabled="audioForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-[#3154D5] hover:bg-[#2744ab] text-white text-xs font-bold transition disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ audioForm.processing ? 'Menyimpan...' : 'Simpan Konfigurasi Audio' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
