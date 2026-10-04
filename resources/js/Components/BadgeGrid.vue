<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import { computed, onMounted, ref } from 'vue';

type Badge = {
    key: string;
    name: string;
    description?: string | null;
    rarity?: 'common' | 'rare' | 'epic' | 'legendary' | 'mythic' | string;
    progress?: number;
    criteria_value?: number;
    progress_percent?: number;
    earned?: boolean;
    earned_at?: string | null;
};

const props = defineProps<{
    badges: Badge[];
    emptyText?: string;
    limit?: number;
}>();

const activeFilter = ref<'all' | 'earned' | 'locked'>('all');
const inspectedBadge = ref<Badge | null>(null);
const copySuccess = ref(false);
const equipSuccess = ref(false);
const equippedTitle = ref<string>('');

onMounted(() => {
    equippedTitle.value = localStorage.getItem('kuesify_equipped_title') || '';
});

const titlesMap: Record<string, { title: string; lore: string }> = {
    first_attempt: {
        title: 'Petualang Fajar',
        lore: 'Langkah pertama seorang pejuang yang terbangun dari ketidaktahuan.',
    },
    score_100: {
        title: 'Centurion Cendekia',
        lore: 'Kemenangan mutlak di mana setiap soal dijawab tanpa ragu dan tanpa cela.',
    },
    streak_3: {
        title: 'Kesatria Tekad Baja',
        lore: 'Tiga hari disiplin konsisten, fondasi seorang pembelajar tangguh.',
    },
    quizzes_5: {
        title: 'Penakluk Ujian',
        lore: 'Menjelajahi beragam arena latihan untuk memperluas khazanah pemikiran.',
    },
    streak_7: {
        title: 'Pengobar Api Abadi',
        lore: 'Api ketekunan yang membakar kemalasan selama satu pekan penuh.',
    },
    quizzes_15: {
        title: 'Dungeon Conqueror',
        lore: 'Melintasi 15 medan ujian dan keluar sebagai pemenang sejati.',
    },
    xp_1000: {
        title: 'Cendekiawan Agung',
        lore: 'Ribuan tetes ilmu terhimpun menjadi samudra pengetahuan luas.',
    },
    perfect_score: {
        title: 'Sang Eksekutor Mutlak',
        lore: 'Ketepatan 100% tanpa kesalahan dalam pertarungan intelektual sengit.',
    },
    streak_14: {
        title: 'Vanguard Pantang Tunduk',
        lore: 'Dua pekan berdiri tegak tanpa pernah absen sehari pun di medan belajar.',
    },
    xp_5000: {
        title: 'High Archmage',
        lore: 'Penguasa 5.000 XP pengetahuan yang dihormati di seantero akademi.',
    },
    streak_30: {
        title: 'Eternal Titan',
        lore: 'Legenda mutlak: Tiga puluh hari konsistensi tanpa kompromi.',
    },
    xp_10000: {
        title: 'Sovereign of Wisdom',
        lore: 'Puncak tahta tertinggi di mana kecerdasan melampaui batas fana.',
    },
};

const filteredBadges = computed(() => {
    if (activeFilter.value === 'earned')
        return props.badges.filter((b) => b.earned);
    if (activeFilter.value === 'locked')
        return props.badges.filter((b) => !b.earned);
    return props.badges;
});

const displayBadges = computed(() =>
    props.limit
        ? filteredBadges.value.slice(0, props.limit)
        : filteredBadges.value,
);

const earnedCount = computed(() => props.badges.filter((b) => b.earned).length);
const lockedCount = computed(
    () => props.badges.filter((b) => !b.earned).length,
);

const rarityStyles: Record<
    string,
    {
        label: string;
        badgeClass: string;
        cardClass?: string;
        iconClass?: string;
        crestClass?: string;
    }
> = {
    common: {
        label: 'Common',
        badgeClass: 'bg-slate-500 text-white',
        cardClass: 'border-[#e2e8f0]/80 hover:border-slate-400',
        iconClass: 'bg-[#f1f5f9] text-[#64748b]',
        crestClass: 'bg-[#f1f5f9] text-[#475569] border border-[#e2e8f0]',
    },
    rare: {
        label: 'Rare',
        badgeClass: 'bg-blue-500 text-white',
        cardClass: 'border-brand-primary/30 hover:border-brand-primary/60',
        iconClass: 'bg-brand-primary/10 text-brand-primary',
        crestClass:
            'bg-brand-primary/10 text-brand-primary border border-brand-primary/30',
    },
    epic: {
        label: 'Epic',
        badgeClass: 'bg-purple-500 text-white',
        cardClass:
            'rpg-badge-epic border-brand-primary/60 bg-gradient-to-br from-brand-accent/30 via-white to-white',
        iconClass:
            'bg-brand-primary text-brand-secondary ring-2 ring-brand-secondary/40',
        crestClass:
            'rpg-crest-epic bg-brand-primary text-brand-secondary border-2 border-brand-secondary',
    },
    legendary: {
        label: 'Legendary',
        badgeClass: 'bg-orange-500 text-white',
        cardClass:
            'rpg-badge-legendary border-support-1 bg-gradient-to-br from-amber-50/50 via-white to-white',
        iconClass: 'bg-support-1 text-white ring-2 ring-support-1/50',
        crestClass:
            'rpg-crest-legendary bg-support-1 text-white border-2 border-amber-300',
    },
    mythic: {
        label: 'Mythic',
        badgeClass: 'bg-red-600 text-white font-black tracking-widest',
        cardClass:
            'rpg-badge-mythic border-brand-secondary bg-gradient-to-br from-brand-secondary/15 via-white to-brand-accent/30',
        iconClass:
            'bg-brand-dark text-brand-secondary ring-2 ring-brand-secondary/70 animate-pulse',
        crestClass:
            'rpg-crest-mythic bg-brand-dark text-brand-secondary border-2 border-brand-secondary',
    },
};

function dateLabel(value?: string | null): string {
    if (!value) return '';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function openInspect(badge: Badge) {
    inspectedBadge.value = badge;
    copySuccess.value = false;
    equipSuccess.value = false;
}

function copyBragText() {
    if (!inspectedBadge.value) return;
    const badge = inspectedBadge.value;
    const meta = titlesMap[badge.key] || {
        title: badge.name,
        lore: badge.description || '',
    };
    const rarityUpper = (badge.rarity || 'COMMON').toUpperCase();
    const text = [
        `🏆 PENCAPAIAN PRESTASI RPG KUESIFY 🏆`,
        `🎖️ Lencana: [${badge.name}] (${rarityUpper} TIER)`,
        `⚔️ Gelar Kehormatan: [${meta.title}]`,
        `📜 Legenda: "${meta.lore}"`,
        `🔥 Taklukkan kuis dan raih gelarmu di Kuesify!`,
    ].join('\n');

    navigator.clipboard.writeText(text).then(() => {
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 2500);
    });
}

function equipTitle() {
    if (!inspectedBadge.value) return;
    const titleName =
        titlesMap[inspectedBadge.value.key]?.title || inspectedBadge.value.name;
    localStorage.setItem('kuesify_equipped_title', titleName);
    equippedTitle.value = titleName;
    equipSuccess.value = true;
    window.dispatchEvent(
        new CustomEvent('kuesify-title-equipped', {
            detail: { title: titleName },
        }),
    );
    setTimeout(() => {
        equipSuccess.value = false;
    }, 2500);
}
</script>

<template>
    <div class="space-y-2">
        <!-- Filter Tabs -->
        <div
            class="flex items-center gap-1.5 border-b border-slate-100 pb-2 text-xs font-semibold"
        >
            <button
                type="button"
                @click="activeFilter = 'all'"
                class="rounded-lg px-2.5 py-1 transition"
                :class="
                    activeFilter === 'all'
                        ? 'bg-brand-primary text-white'
                        : 'text-[#64748b] hover:bg-[#f1f5f9]'
                "
            >
                Semua ({{ badges.length }})
            </button>
            <button
                type="button"
                @click="activeFilter = 'earned'"
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 transition"
                :class="
                    activeFilter === 'earned'
                        ? 'bg-brand-primary text-white'
                        : 'text-[#64748b] hover:bg-[#f1f5f9]'
                "
            >
                <AppIcon name="badge" :size="13" />
                <span>Terbuka ({{ earnedCount }})</span>
            </button>
            <button
                type="button"
                @click="activeFilter = 'locked'"
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 transition"
                :class="
                    activeFilter === 'locked'
                        ? 'bg-brand-primary text-white'
                        : 'text-brand-primary hover:bg-[#f1f5f9]'
                "
            >
                <AppIcon name="lock" :size="12" />
                <span>Terkunci ({{ lockedCount }})</span>
            </button>
        </div>

        <!-- Badges List -->
        <div v-if="displayBadges.length" class="grid gap-1.5">
            <article
                v-for="badge in displayBadges"
                :key="badge.key"
                @click="openInspect(badge)"
                class="group relative cursor-pointer rounded-xl border p-3 transition duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between h-[116px]"
                :class="[
                    badge.earned
                        ? 'border-[#d9f99d] bg-[#ecfccb] shadow-md'
                        : 'border-[#e2e8f0] bg-[#f8fafc]',
                ]"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="relative grid h-10 w-10 shrink-0 place-items-center rounded-xl transition duration-200"
                        :class="[
                            badge.earned
                                ? 'bg-[#facc15] text-white shadow-sm'
                                : 'bg-[#f1f5f9] text-slate-600',
                        ]"
                    >
                        <AppIcon
                            :name="badge.earned ? 'trophy' : 'lock'"
                            :size="18"
                        />
                        <span
                            v-if="
                                !badge.earned &&
                                (badge.progress_percent ?? 0) > 0
                            "
                            class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-primary text-[8px] font-bold text-white ring-2 ring-white"
                        >
                            {{ Math.round(badge.progress_percent ?? 0) }}%
                        </span>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h3
                                :class="
                                    badge.earned
                                        ? 'truncate text-sm font-black text-[#1e293b]'
                                        : 'truncate text-sm font-bold text-brand-primary transition group-hover:text-brand-hover'
                                "
                            >
                                {{ badge.name }}
                            </h3>
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :class="
                                    rarityStyles[badge.rarity ?? 'common']
                                        ?.badgeClass ??
                                    'bg-slate-100 text-slate-600'
                                "
                            >
                                {{
                                    rarityStyles[badge.rarity ?? 'common']
                                        ?.label ?? badge.rarity
                                }}
                            </span>
                        </div>
                        <p
                            :class="
                                badge.earned
                                    ? 'mt-0.5 line-clamp-2 text-xs font-medium leading-relaxed text-[#334155]'
                                    : 'mt-0.5 line-clamp-2 text-xs leading-relaxed text-[#475569]'
                            "
                        >
                            {{ badge.description }}
                        </p>
                    </div>
                </div>

                <!-- Progress Bar if locked -->
                <div
                    v-if="!badge.earned && badge.criteria_value"
                    class="mt-1.5"
                >
                    <div
                        class="flex justify-between text-[10px] font-medium text-[#64748b]"
                    >
                        <span>Progres Membuka</span>
                        <span
                            >{{ badge.progress ?? 0 }} /
                            {{ badge.criteria_value }}</span
                        >
                    </div>
                    <div
                        class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-200"
                    >
                        <div
                            class="h-full rounded-full bg-brand-secondary transition-all duration-300"
                            :style="{
                                width: `${badge.progress_percent ?? 0}%`,
                            }"
                        />
                    </div>
                </div>

                <!-- Earned Badge Marker -->
                <div
                    v-else-if="badge.earned"
                    class="mt-2 flex items-center justify-between text-[11px] font-medium text-slate-600"
                >
                    <span
                        class="inline-flex items-center gap-1.5 font-bold text-brand-primary"
                    >
                        <AppIcon name="check" :size="13" />
                        <span
                            >Terbuka{{
                                badge.earned_at
                                    ? ` · ${dateLabel(badge.earned_at)}`
                                    : ''
                            }}</span
                        >
                    </span>
                    <span
                        class="text-[10px] font-semibold text-slate-400 transition group-hover:text-brand-primary"
                    >
                        Klik untuk Pamerkan &rarr;
                    </span>
                </div>
            </article>
        </div>

        <p
            v-else
            class="rounded-xl bg-[#f8fafc] px-4 py-6 text-center text-xs font-medium text-[#64748b]"
        >
            {{ emptyText ?? 'Tidak ada badge di kategori ini.' }}
        </p>

        <!-- Inspect / Showcase Modal -->
        <div
            v-if="inspectedBadge"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            @click.self="inspectedBadge = null"
        >
            <div
                class="relative w-full max-w-sm overflow-hidden rounded-3xl border border-white/20 bg-white p-6 text-center shadow-2xl"
            >
                <button
                    type="button"
                    @click="inspectedBadge = null"
                    class=":text-slate-200 absolute right-4 top-4 text-slate-400 hover:text-slate-600"
                >
                    ✕
                </button>

                <!-- Crest -->
                <div
                    class="mx-auto grid h-20 w-20 place-items-center rounded-2xl shadow-xl transition"
                    :class="[
                        rarityStyles[inspectedBadge.rarity ?? 'common']
                            ?.crestClass ?? 'bg-brand-primary text-white',
                    ]"
                >
                    <AppIcon
                        :name="inspectedBadge.earned ? 'trophy' : 'lock'"
                        :size="36"
                    />
                </div>

                <div class="mt-3">
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider"
                        :class="
                            rarityStyles[inspectedBadge.rarity ?? 'common']
                                ?.badgeClass ?? 'bg-slate-100 text-slate-600'
                        "
                    >
                        {{
                            rarityStyles[inspectedBadge.rarity ?? 'common']
                                ?.label ?? inspectedBadge.rarity
                        }}
                        Achievement
                    </span>
                    <h3 class="mt-2 text-xl font-black text-slate-900">
                        {{ inspectedBadge.name }}
                    </h3>
                    <p class="mt-0.5 text-xs font-bold text-brand-primary">
                        Gelar: [{{
                            titlesMap[inspectedBadge.key]?.title ??
                            inspectedBadge.name
                        }}]
                    </p>
                </div>

                <p
                    class="mt-3 rounded-xl border border-slate-100 bg-[#f8fafc] p-3 text-xs italic leading-relaxed text-slate-600"
                >
                    "{{
                        titlesMap[inspectedBadge.key]?.lore ??
                        inspectedBadge.description
                    }}"
                </p>

                <!-- Status & Progress -->
                <div class="mt-3 text-xs text-slate-600">
                    <p
                        v-if="inspectedBadge.earned"
                        class="flex items-center justify-center gap-1 font-bold text-status-success"
                    >
                        <AppIcon name="check" :size="14" />
                        <span
                            >Telah Diraih{{
                                inspectedBadge.earned_at
                                    ? ` · ${dateLabel(inspectedBadge.earned_at)}`
                                    : ''
                            }}</span
                        >
                    </p>
                    <div v-else class="space-y-1">
                        <p class="font-medium text-[#64748b]">
                            Target Membuka: {{ inspectedBadge.criteria_value }}
                        </p>
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200"
                        >
                            <div
                                class="h-full rounded-full bg-brand-secondary"
                                :style="{
                                    width: `${inspectedBadge.progress_percent ?? 0}%`,
                                }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 flex flex-col gap-2">
                    <template v-if="inspectedBadge.earned">
                        <button
                            type="button"
                            @click="copyBragText"
                            class="flex items-center justify-center gap-2 rounded-xl bg-brand-primary px-4 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-brand-hover active:scale-95"
                        >
                            <AppIcon name="badge" :size="15" />
                            <span>{{
                                copySuccess
                                    ? 'Format Pamer Disalin! Siap Disebar ✨'
                                    : 'Salin Format Pamer (WhatsApp/Discord)'
                            }}</span>
                        </button>
                        <button
                            type="button"
                            @click="equipTitle"
                            class="flex items-center justify-center gap-1.5 rounded-xl border border-brand-primary/40 bg-brand-accent/20 px-4 py-2 text-xs font-bold text-brand-primary transition hover:bg-brand-accent/40"
                        >
                            <AppIcon name="trophy" :size="14" />
                            <span>{{
                                equipSuccess
                                    ? 'Gelar Berhasil Dipasang di Dashboard! 👑'
                                    : equippedTitle ===
                                        (titlesMap[inspectedBadge.key]?.title ||
                                            inspectedBadge.name)
                                      ? 'Gelar Ini Sedang Aktif'
                                      : 'Pasang sebagai Gelar Utama'
                            }}</span>
                        </button>
                    </template>
                    <a
                        v-else
                        href="/participant/badges"
                        class="hover flex items-center justify-center gap-1 rounded-xl bg-brand-primary hover:bg-brand-hover px-4 py-2.5 text-xs font-bold text-white shadow-sm transition"
                    >
                        <span>Lihat Panduan & Misi Terkait</span>
                        <AppIcon name="arrow-right" :size="14" />
                    </a>

                    <button
                        type="button"
                        @click="inspectedBadge = null"
                        class="rounded-xl px-4 py-1.5 text-xs font-semibold text-[#64748b] hover:bg-[#f1f5f9]"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
