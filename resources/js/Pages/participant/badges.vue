<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type BadgeItem = {
 key: string;
 name: string;
 description: string | null;
 rarity: string;
 criteria_value: number;
 progress: number;
 progress_percent: number;
 earned: boolean;
 earned_at: string | null;
};

const props = defineProps<{
 organization: { id: number; name: string; role: string | null };
 badges: BadgeItem[];
 stats: {
 xp: number;
 streak: number;
 level: number;
 earnedCount: number;
 totalCount: number;
 };
}>();

const activeFilter = ref<'all' | 'earned' | 'locked'>('all');
const rarityFilter = ref<string>('all');
const inspectedBadge = ref<BadgeItem | null>(null);
const copySuccess = ref(false);
const equipSuccess = ref(false);
const equippedTitle = ref<string>('');

if (typeof window !== 'undefined') {
 equippedTitle.value = localStorage.getItem('kuesify_equipped_title') || '';
}

function equipTitle() {
 if (!inspectedBadge.value) return;
 const titleName = badgeGuideMap[inspectedBadge.value.key]?.titleOfHonor || inspectedBadge.value.name;
 localStorage.setItem('kuesify_equipped_title', titleName);
 equippedTitle.value = titleName;
 equipSuccess.value = true;
 window.dispatchEvent(new CustomEvent('kuesify-title-equipped', { detail: { title: titleName } }));
 setTimeout(() => {
 equipSuccess.value = false;
 }, 2500);
}

const badgeGuideMap: Record<
 string,
 {
 titleOfHonor: string;
 requirement: string;
 relatedMission: string;
 lore: string;
 tips: string;
 actionLabel: string;
 actionHref: string;
 }
> = {
 first_attempt: {
 titleOfHonor: 'Petualang Pemula',
 requirement: 'Selesaikan minimal 1 sesi kuis latihan di organisasi.',
 relatedMission: 'Misi Harian: Ujian Fajar (+25 XP)',
 lore: 'Langkah pertama seorang petualang yang terbangun dari ketidaktahuan.',
 tips: 'Pilih kuis apa pun dari Katalog Kuis dan selesaikan seluruh butir soalnya.',
 actionLabel: 'Pilih Kuis Pertama',
 actionHref: '/participant/quizzes',
 },
 score_100: {
 titleOfHonor: 'Centurion Cendekia',
 requirement: 'Raih nilai 100 sempurna pada satu sesi kuis.',
 relatedMission: 'Misi Harian: Ketepatan Sempurna (+50 XP)',
 lore: 'Diberikan kepada mereka yang menjawab seluruh teka-teki arena tanpa meleset.',
 tips: 'Pelajari ringkasan materi di menu Materi AI sebelum memulai sesi kuis.',
 actionLabel: 'Baca Materi Belajar',
 actionHref: '/participant/materials',
 },
 streak_3: {
 titleOfHonor: 'Kesatria Tekad Baja',
 requirement: 'Selesaikan minimal 1 kuis setiap hari selama 3 hari berurutan.',
 relatedMission: 'Misi Mingguan: Ritme Belajar (+75 XP)',
 lore: 'Disiplin adalah perisai terkuat seorang pembelajar sejati.',
 tips: 'Kerjakan minimal Quiz of the Day di pagi hari agar streak harian tetap menyala.',
 actionLabel: 'Cek Quiz of the Day',
 actionHref: '/dashboard',
 },
 quizzes_5: {
 titleOfHonor: 'Penakluk Ujian',
 requirement: 'Taklukkan dan selesaikan 5 sesi kuis yang berbeda.',
 relatedMission: 'Misi Belajar: Penjelajah Pustaka (+150 XP)',
 lore: 'Menjelajahi beragam arena latihan untuk memperluas khazanah pemikiran.',
 tips: 'Buka katalog kuis dan coba variasi tema soal dari guru maupun publik.',
 actionLabel: 'Buka Katalog Kuis',
 actionHref: '/participant/quizzes',
 },
 streak_7: {
 titleOfHonor: 'Pengobar Api Abadi',
 requirement: 'Pertahankan api streak belajar aktif selama 7 hari berturut-turut.',
 relatedMission: 'Misi Mingguan: Sang Juara Arena (+150 XP)',
 lore: 'Api ketekunan yang membakar keraguan dan kemalasan selama satu pekan penuh.',
 tips: 'Jadikan kuis 5 menit sebagai rutinitas harian setelah selesai jam belajar.',
 actionLabel: 'Jaga Api Belajar',
 actionHref: '/dashboard',
 },
 quizzes_15: {
 titleOfHonor: 'Master Arena Ujian',
 requirement: 'Selesaikan total 15 sesi kuis latihan.',
 relatedMission: 'Jalur Petualangan: Penakluk Pustaka',
 lore: 'Hanya pejuang tangguh yang mampu bertahan melintasi 15 medan ujian.',
 tips: 'Ulangi kuis lama atau coba kuis baru untuk menambah jam terbang pengerjaan.',
 actionLabel: 'Lanjut Kuis',
 actionHref: '/participant/quizzes',
 },
 xp_1000: {
 titleOfHonor: 'Cendekiawan Agung',
 requirement: 'Kumpulkan akumulasi total 1.000 XP dari kuis dan misi.',
 relatedMission: 'Ekspedisi Pengetahuan',
 lore: 'Akumulasi ribuan tetes pengetahuan yang membentuk lautan kebijaksanaan.',
 tips: 'Selesaikan misi mingguan (+75 XP dan +150 XP) untuk lonjakan XP besar.',
 actionLabel: 'Panen XP di Kuis',
 actionHref: '/participant/quizzes',
 },
 perfect_score: {
 titleOfHonor: 'Sang Eksekutor Mutlak',
 requirement: 'Selesaikan kuis dengan akurasi 100% tanpa ada satu pun butir jawaban yang keliru.',
 relatedMission: 'Tantangan Khusus Boss Arena',
 lore: 'Konsentrasi mutlak tanpa celah. Setiap tebakan dan analisa tepat sasaran.',
 tips: 'Jika ada 1 soal salah, gunakan tombol Ulangi Kuis untuk meraih skor sempurna.',
 actionLabel: 'Cari Kuis untuk Disempurnakan',
 actionHref: '/participant/quizzes',
 },
 streak_14: {
 titleOfHonor: 'Garda Tak Tergoyahkan',
 requirement: 'Jaga ketangguhan belajar konsisten selama 14 hari penuh.',
 relatedMission: 'Benteng Ketekunan Dua Pekan',
 lore: 'Konsistensi dua pekan tanpa alpa, membedakan sang juara dari penonton biasa.',
 tips: 'Luangkan 5 menit sebelum tidur jika siang hari belum sempat mengerjakan kuis.',
 actionLabel: 'Pantau Kalender Streak',
 actionHref: '/dashboard',
 },
 xp_5000: {
 titleOfHonor: 'Penyihir Pengetahuan Tertinggi',
 requirement: 'Kumpulkan akumulasi 5.000 XP.',
 relatedMission: 'Mahkota Pembelajar Tingkat Tinggi',
 lore: 'Energi pengetahuan yang meluap, membuka pemahaman mendalam atas berbagai disiplin ilmu.',
 tips: 'Ikuti Live Quiz bersama teman sekelas untuk bonus poin XP berlipat ganda.',
 actionLabel: 'Gabung Sesi Live',
 actionHref: '/join',
 },
 streak_30: {
 titleOfHonor: 'Titan Abadi Pengetahuan',
 requirement: 'Pertahankan streak 30 hari penuh tanpa putus satu hari pun.',
 relatedMission: 'Legenda Satu Bulan Tanpa Celah',
 lore: 'Dewa ketekunan yang telah melampaui batas kebiasaan manusia normal.',
 tips: 'Streak 30 hari membutuhkan dedikasi mutlak. Pastikan selalu cek status streak di dashboard.',
 actionLabel: 'Cek Status Streak',
 actionHref: '/dashboard',
 },
 xp_10000: {
 titleOfHonor: 'Penguasa Takhta Kebijaksanaan',
 requirement: 'Raih 10.000 XP dan buktikan kamu adalah pembelajar terhebat.',
 relatedMission: 'Puncak Tertinggi Pantheon Kuesify',
 lore: 'Hanya ada segelintir manusia terpilih yang mampu mencapai tahta 10.000 XP.',
 tips: 'Kuasai seluruh kuis organisasi dan selesaikan semua misi harian & mingguan tanpa henti.',
 actionLabel: 'Menuju Puncak XP',
 actionHref: '/participant/quizzes',
 },
};

const rarityStyles: Record<
 string,
 { label: string; badgeClass: string; cardClass: string; iconRingClass: string }
> = {
 common: {
 label: 'Common',
 badgeClass: 'bg-slate-100 text-slate-700 ',
 cardClass: 'border-slate-200/90 ',
 iconRingClass: 'bg-slate-100 text-slate-600 ',
 },
 rare: {
 label: 'Rare',
 badgeClass: 'bg-brand-primary/10 text-brand-primary border border-brand-primary/20 ',
 cardClass: 'border-brand-primary/30 ',
 iconRingClass: 'bg-brand-primary/10 text-brand-primary',
 },
 epic: {
 label: 'Epic',
 badgeClass: 'bg-brand-primary text-white border border-brand-dark shadow-sm',
 cardClass: 'rpg-badge-epic border-brand-primary bg-gradient-to-br from-brand-accent/40 via-white to-white ',
 iconRingClass: 'bg-brand-primary text-brand-secondary ring-2 ring-brand-secondary/50',
 },
 legendary: {
 label: 'Legendary',
 badgeClass: 'bg-support-1 text-white border border-amber-600 shadow-md',
 cardClass: 'rpg-badge-legendary border-support-1 bg-gradient-to-br from-amber-50/60 via-white to-white ',
 iconRingClass: 'bg-support-1 text-white ring-4 ring-support-1/30',
 },
 mythic: {
 label: 'Mythic',
 badgeClass: 'bg-gradient-to-r from-brand-primary via-support-1 to-brand-secondary text-brand-dark font-black tracking-widest border border-white shadow-lg',
 cardClass: 'rpg-badge-mythic border-brand-secondary bg-gradient-to-br from-brand-secondary/15 via-white to-brand-accent/40 ',
 iconRingClass: 'bg-brand-dark text-brand-secondary ring-4 ring-brand-secondary/60 animate-pulse',
 },
};

const filteredBadges = computed(() => {
 return props.badges.filter((b) => {
 if (activeFilter.value === 'earned' && !b.earned) return false;
 if (activeFilter.value === 'locked' && b.earned) return false;
 if (rarityFilter.value !== 'all' && b.rarity !== rarityFilter.value) return false;
 return true;
 });
});

function formatDate(value?: string | null): string {
 if (!value) return '';
 return new Intl.DateTimeFormat('id-ID', {
 day: '2-digit',
 month: 'short',
 year: 'numeric',
 }).format(new Date(value));
}

function openInspectModal(badge: BadgeItem): void {
 inspectedBadge.value = badge;
 copySuccess.value = false;
}

function copyProof(): void {
 if (!inspectedBadge.value) return;
 const text = `🏆 Aku berhasil membuka lencana [${inspectedBadge.value.name}] di Kuesify (${props.organization.name})! Ayo belajar bareng!`;
 navigator.clipboard.writeText(text);
 copySuccess.value = true;
 setTimeout(() => {
 copySuccess.value = false;
 }, 3000);
}
</script>

<template>
 <Head title="Katalog & Panduan Lencana RPG" />

 <AuthenticatedLayout>
 <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
 <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
 <!-- Header Hero RPG Style -->
 <section class="rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div>
 <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm">
 <AppIcon name="trophy" :size="14" />
 <span>Hall of Fame & Petualangan Lencana</span>
 </div>
 <h1 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl">
 Gelar & Lencana Prestasi
 </h1>
 <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-white/80">
 Selesaikan misi tantangan, kumpulkan XP, dan raih lencana langka dari tingkat Common hingga Mythic untuk memamerkan reputasi belajarmu!
 </p>
 </div>

 <!-- Progress Summary Card -->
 <div class="grid grid-cols-3 gap-3 rounded-xl border border-white/15 bg-white/10 p-4 text-center backdrop-blur-md">
 <div>
 <p class="text-[11px] font-semibold text-white/70">Terbuka</p>
 <p class="mt-1 text-xl font-black text-brand-secondary tabular-nums">
 {{ stats.earnedCount }}/{{ stats.totalCount }}
 </p>
 </div>
 <div class="border-x border-white/10 px-2">
 <p class="text-[11px] font-semibold text-white/70">Level</p>
 <p class="mt-1 text-xl font-black text-white tabular-nums">
 {{ stats.level }}
 </p>
 </div>
 <div>
 <p class="text-[11px] font-semibold text-white/70">Streak</p>
 <p class="mt-1 text-xl font-black text-white tabular-nums">
 {{ stats.streak }} Hari
 </p>
 </div>
 </div>
 </div>
 </section>

 <!-- Filter Controls -->
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-slate-200 bg-white p-3.5 shadow-figma-sm ">
 <!-- Status Filter Tabs -->
 <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
 <button
 type="button"
 @click="activeFilter = 'all'"
 class="rounded-lg px-3 py-1.5 transition"
 :class="activeFilter === 'all' ? 'bg-brand-primary text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 '"
 >
 Semua Lencana ({{ stats.totalCount }})
 </button>
 <button
 type="button"
 @click="activeFilter = 'earned'"
 class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 transition"
 :class="activeFilter === 'earned' ? 'bg-brand-primary text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 '"
 >
 <AppIcon name="check" :size="13" />
 <span>Terbuka ({{ stats.earnedCount }})</span>
 </button>
 <button
 type="button"
 @click="activeFilter = 'locked'"
 class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 transition"
 :class="activeFilter === 'locked' ? 'bg-brand-primary text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 '"
 >
 <AppIcon name="lock" :size="12" />
 <span>Terkunci ({{ stats.totalCount - stats.earnedCount }})</span>
 </button>
 </div>

 <!-- Rarity Filter -->
 <div class="flex items-center gap-2">
 <label for="rarity-select" class="text-xs font-semibold text-slate-500">Tingkat RPG:</label>
 <select
 id="rarity-select"
 v-model="rarityFilter"
 class="rounded-lg border-slate-200 py-1 pl-2.5 pr-8 text-xs font-bold text-slate-700 focus:border-brand-primary focus:ring-brand-primary "
 >
 <option value="all">Semua Tingkat</option>
 <option value="common">Common</option>
 <option value="rare">Rare</option>
 <option value="epic">Epic ✨</option>
 <option value="legendary">Legendary 👑</option>
 <option value="mythic">Mythic 🌟</option>
 </select>
 </div>
 </div>

 <!-- Badges Detailed Grid -->
 <div v-if="filteredBadges.length" class="grid gap-5 md:grid-cols-2">
 <article
    v-for="badge in filteredBadges"
    :key="badge.key"
    class="group relative flex flex-col justify-between overflow-hidden rounded-xl border bg-[#ffffff] p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md"
    :class="[
        badge.earned
            ? (rarityStyles[badge.rarity]?.cardClass ?? 'border-brand-primary/30')
            : 'border-[#e2e8f0]'
    ]"
>
    <div
        class="absolute inset-x-0 top-0 h-1 origin-left scale-x-75 bg-gradient-to-r from-brand-primary via-brand-secondary to-support-1 opacity-80 transition duration-500 group-hover:scale-x-100"
    />
 <div>
 <!-- Top Badge Header -->
 <div class="flex items-start justify-between gap-3">
 <div class="flex items-center gap-3.5">
 <div
 class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl transition duration-300 group-hover:scale-105"
 :class="[
 badge.earned
 ? (rarityStyles[badge.rarity]?.iconRingClass ?? 'bg-brand-primary text-brand-secondary')
 : 'bg-slate-100 text-slate-400 ',
 ]"
 >
 <AppIcon :name="badge.earned ? 'trophy' : 'lock'" :size="26" />
 </div>
 <div>
 <div class="flex items-center gap-1.5">
 <span class="rounded bg-brand-accent px-1.5 py-0.5 text-[9px] font-black uppercase text-brand-dark ">
 {{ badgeGuideMap[badge.key]?.titleOfHonor ?? 'Petualang' }}
 </span>
 </div>
 <h2 class="mt-0.5 text-base font-black text-slate-900 ">
 {{ badge.name }}
 </h2>
 <p class="text-xs text-slate-500 ">
 {{ badge.description }}
 </p>
 </div>
 </div>

 <span
 class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider"
 :class="rarityStyles[badge.rarity]?.badgeClass ?? 'bg-slate-100 text-slate-600'"
 >
 {{ rarityStyles[badge.rarity]?.label ?? badge.rarity }}
 </span>
 </div>

 <!-- How to Unlock Box -->
 <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 ">
 <div class="flex items-start gap-2">
 <AppIcon name="target" :size="15" class="mt-0.5 shrink-0 text-brand-primary " />
 <div class="text-xs leading-relaxed">
 <span class="font-bold text-slate-900 ">Syarat Quest: </span>
 <span class="text-slate-600 ">
 {{ badgeGuideMap[badge.key]?.requirement ?? badge.description }}
 </span>
 </div>
 </div>

 <div class="mt-2 flex items-start gap-2 border-t border-slate-200/60 pt-2 ">
 <AppIcon name="ai" :size="14" class="mt-0.5 shrink-0 text-support-1" />
 <div class="text-xs leading-relaxed">
 <span class="font-bold text-slate-900 ">Misi Pendukung: </span>
 <span class="text-slate-600 ">
 {{ badgeGuideMap[badge.key]?.relatedMission ?? 'Aktivitas belajar rutin' }}
 </span>
 </div>
 </div>
 </div>

 <!-- Lore Quote -->
 <p class="mt-3 text-xs italic leading-relaxed text-slate-500 ">
 "{{ badgeGuideMap[badge.key]?.lore ?? 'Lencana yang membuktikan ketekunan pembelajar sejati.' }}"
 </p>
 </div>

 <!-- Bottom Progress & CTA -->
 <div class="mt-5 border-t border-slate-100 pt-4 ">
 <!-- In-Progress bar if locked -->
 <div v-if="!badge.earned" class="mb-3">
 <div class="flex items-center justify-between text-xs font-bold text-slate-600 ">
 <span>Progres Quest</span>
 <span class="tabular-nums">{{ badge.progress }} / {{ badge.criteria_value }} ({{ Math.round(badge.progress_percent) }}%)</span>
 </div>
 <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 ">
 <div
 class="h-full rounded-full bg-brand-primary transition-all duration-300 "
 :style="{ width: `${badge.progress_percent}%` }"
 />
 </div>
 </div>

 <!-- Action or Earned Badge -->
 <div class="flex items-center justify-between gap-3">
 <button
 v-if="badge.earned"
 type="button"
 @click="openInspectModal(badge)"
 class="inline-flex items-center gap-1.5 rounded-lg bg-brand-secondary/15 px-3 py-1.5 text-xs font-black text-brand-dark transition hover:bg-brand-secondary/30 "
 >
 <AppIcon name="trophy" :size="14" />
 <span>Pamerkan Lencana 👑</span>
 </button>
 <span v-else class="text-xs font-semibold text-slate-400">
 Quest Belum Selesai
 </span>

 <Link
 v-if="!badge.earned"
 :href="badgeGuideMap[badge.key]?.actionHref ?? '/participant/quizzes'"
 class="inline-flex items-center gap-1.5 rounded-xl bg-brand-primary px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-brand-hover active:scale-95"
 >
 <span>{{ badgeGuideMap[badge.key]?.actionLabel ?? 'Mulai Sekarang' }}</span>
 <AppIcon name="arrowRight" :size="13" />
 </Link>
 <span v-else class="text-[11px] font-semibold text-slate-400">
 Terbuka: {{ formatDate(badge.earned_at) }}
 </span>
 </div>
 </div>
 </article>
 </div>

 <div
 v-else
 class="rounded-2xl border border-slate-200 bg-white p-8 text-center "
 >
 <p class="text-sm font-semibold text-slate-500">
 Tidak ada lencana yang sesuai dengan filter yang dipilih.
 </p>
 </div>

 <!-- Learning Motivation & Guidelines Card -->
 <section class="rounded-2xl border border-brand-primary/20 bg-brand-accent p-6 shadow-figma-sm">
 <div class="flex items-center gap-2">
 <AppIcon name="trophy" :size="20" class="text-brand-primary" />
 <h2 class="text-base font-black text-brand-dark">
 Cara Menaklukkan Lencana Mythic & Legendary
 </h2>
 </div>
 <div class="mt-4 grid gap-4 sm:grid-cols-3">
 <div class="rounded-xl bg-white p-4 shadow-xs">
 <p class="text-xs font-bold text-brand-primary">1. Disiplin Streak 30 Hari</p>
 <p class="mt-1 text-xs leading-relaxed text-slate-600">
 Lencana Mythic [Eternal Titan] hanya bisa diraih oleh petualang yang tidak pernah melewatkan 1 hari pun tanpa kuis selama 30 hari.
 </p>
 </div>
 <div class="rounded-xl bg-white p-4 shadow-xs">
 <p class="text-xs font-bold text-brand-primary">2. Akurasi 100% (Flawless)</p>
 <p class="mt-1 text-xs leading-relaxed text-slate-600">
 Untuk lencana Legendary [Flawless Mastery], pelajari materi terlebih dahulu dan manfaatkan tombol ulangi kuis sampai seluruh jawaban benar.
 </p>
 </div>
 <div class="rounded-xl bg-white p-4 shadow-xs">
 <p class="text-xs font-bold text-brand-primary">3. Panen XP Melalui Misi</p>
 <p class="mt-1 text-xs leading-relaxed text-slate-600">
 Selesaikan setiap misi mingguan untuk membuka gelar Mythic [Sovereign of Wisdom] 10.000 XP lebih cepat dari yang lain.
 </p>
 </div>
 </div>
 </section>
 </main>
 </div>

 <!-- RPG Badge Inspect & Showcase Modal -->
 <div
 v-if="inspectedBadge"
 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
 @click.self="inspectedBadge = null"
 >
 <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-white/20 bg-white p-6 shadow-2xl text-center">
 <button
 type="button"
 @click="inspectedBadge = null"
 class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 "
 >
 ✕
 </button>

 <!-- Crest -->
 <div
 class="mx-auto grid h-24 w-24 place-items-center rounded-3xl shadow-xl transition"
 :class="[
 rarityStyles[inspectedBadge.rarity]?.iconRingClass ?? 'bg-brand-primary text-brand-secondary',
 inspectedBadge.rarity === 'mythic' ? 'rpg-badge-mythic' : (inspectedBadge.rarity === 'legendary' ? 'rpg-badge-legendary' : 'rpg-badge-epic'),
 ]"
 >
 <AppIcon name="trophy" :size="48" />
 </div>

 <div class="mt-4">
 <span
 class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider"
 :class="rarityStyles[inspectedBadge.rarity]?.badgeClass ?? 'bg-slate-100 text-slate-600'"
 >
 {{ rarityStyles[inspectedBadge.rarity]?.label ?? inspectedBadge.rarity }} Achievement
 </span>
 <h3 class="mt-3 text-2xl font-black text-slate-900 ">
 {{ inspectedBadge.name }}
 </h3>
 <p class="mt-1 text-xs font-bold text-brand-primary ">
 Gelar Kehormatan: [{{ badgeGuideMap[inspectedBadge.key]?.titleOfHonor ?? 'Petualang Kuesify' }}]
 </p>
 </div>

 <p class="mt-4 text-xs italic leading-relaxed text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 ">
 "{{ badgeGuideMap[inspectedBadge.key]?.lore ?? inspectedBadge.description }}"
 </p>

 <div class="mt-6 flex flex-col gap-2">
 <button
 type="button"
 @click="copyProof"
 class="btn-shimmer flex items-center justify-center gap-2 rounded-xl bg-brand-primary px-4 py-3 text-xs font-bold text-white shadow-md transition hover:bg-brand-hover active:scale-95"
 >
 <AppIcon name="badge" :size="16" />
 <span>{{ copySuccess ? 'Format Pamer Disalin! Siap Disebar ✨' : 'Salin Format Pamer (WhatsApp/Discord)' }}</span>
 </button>
 <button
 v-if="inspectedBadge.earned"
 type="button"
 @click="equipTitle"
 class="flex items-center justify-center gap-1.5 rounded-xl border border-brand-primary/40 bg-brand-accent/25 px-4 py-2.5 text-xs font-bold text-brand-primary transition hover:bg-brand-accent/50 "
 >
 <AppIcon name="trophy" :size="15" />
 <span>{{ equipSuccess ? 'Gelar Berhasil Dipasang di Dashboard! 👑' : (equippedTitle === (badgeGuideMap[inspectedBadge.key]?.titleOfHonor || inspectedBadge.name) ? 'Gelar Kehormatan Ini Sedang Aktif' : 'Pasang sebagai Gelar Utama') }}</span>
 </button>
 <button
 type="button"
 @click="inspectedBadge = null"
 class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100 "
 >
 Tutup
 </button>
 </div>
 </div>
 </div>
 </AuthenticatedLayout>
</template>
