<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        streaks: { date: string; count: number }[];
        label?: string;
    }>(),
    {
        label: 'Aktivitas belajar 30 hari terakhir',
    },
);

function formatDayLabel(dateStr: string): string {
    const d = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
    }).format(d);
}

const todayStr = new Date().toISOString().split('T')[0];
</script>

<template>
    <div class="space-y-3" :aria-label="label">
        <!-- Minimalist GitHub/Linear-grade Streak Matrix -->
        <div class="grid grid-cols-10 gap-1.5">
            <div v-for="day in streaks" :key="day.date" class="group relative">
                <div
                    class="h-6 w-full cursor-pointer rounded transition-all duration-150"
                    :class="[
                        day.count > 0
                            ? 'shadow-xs bg-brand-secondary hover:brightness-110'
                            : 'bg-white/10 hover:bg-white/20',
                        day.date === todayStr
                            ? 'ring-2 ring-white ring-offset-1 ring-offset-brand-primary'
                            : '',
                    ]"
                ></div>

                <!-- Clean Dark Tooltip -->
                <div
                    role="tooltip"
                    class="pointer-events-none absolute bottom-full left-1/2 z-30 mb-1.5 hidden -translate-x-1/2 flex-col items-center whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-semibold text-white shadow-lg group-hover:flex"
                >
                    <span>{{ formatDayLabel(day.date) }}</span>
                    <span
                        class="text-[10px]"
                        :class="
                            day.count > 0
                                ? 'font-bold text-brand-secondary'
                                : 'text-slate-400'
                        "
                    >
                        {{
                            day.count > 0
                                ? `${day.count} aktivitas`
                                : 'Tidak ada aktivitas'
                        }}
                    </span>
                    <div
                        class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-800"
                    />
                </div>
            </div>
        </div>

        <!-- Legend with Vector Icons -->
        <div
            class="flex items-center justify-between text-[11px] font-medium text-white/80"
        >
            <span class="inline-flex items-center gap-1.5">
                <span class="rounded-xs h-2 w-2 bg-white/10"></span>
                <span>Kosong</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="rounded-xs h-2 w-2 bg-brand-secondary"></span>
                <span>Aktif</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span
                    class="rounded-xs h-2 w-2 bg-brand-secondary ring-1 ring-brand-primary"
                ></span>
                <span>Hari Ini</span>
            </span>
        </div>
    </div>
</template>
