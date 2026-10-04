<script setup lang="ts">
import {
    ArrowRight,
    Award,
    BarChart3,
    Bell,
    BookOpen,
    BookOpenCheck,
    Building2,
    Calendar,
    CheckCircle2,
    ClipboardCheck,
    ClipboardList,
    Clock,
    Compass,
    Database,
    FileQuestion,
    Files,
    Flame,
    Gauge,
    GraduationCap,
    LayoutDashboard,
    Library,
    LibraryBig,
    ListChecks,
    Lock,
    LogOut,
    MonitorCog,
    PanelTop,
    Radio,
    RotateCcw,
    Settings,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Star,
    Tags,
    Target,
    Trophy,
    UserRound,
    Users,
    UsersRound,
    Zap,
} from '@lucide/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        size?: number | string;
        strokeWidth?: number | string;
        interactive?: boolean;
    }>(),
    {
        size: 20,
        strokeWidth: 1.8,
        interactive: false,
    },
);

const icons = {
    dashboard: LayoutDashboard,
    bank: LibraryBig,
    catalog: Library,
    creatorBank: Files,
    builder: ClipboardList,
    live: Radio,
    results: ClipboardCheck,
    notifications: Bell,
    ai: Sparkles,
    organization: Building2,
    members: Users,
    groups: UsersRound,
    settings: Settings,
    platform: PanelTop,
    moderation: ShieldAlert,
    tenants: Database,
    categories: Tags,
    monitoring: MonitorCog,
    admin: ShieldCheck,
    logout: LogOut,
    user: UserRound,
    users: UsersRound,
    quiz: ListChecks,
    question: FileQuestion,
    material: BookOpen,
    learn: BookOpenCheck,
    xp: Zap,
    streak: Gauge,
    level: GraduationCap,
    badge: Award,
    trophy: Trophy,
    lock: Lock,
    analytics: BarChart3,
    flame: Flame,
    target: Target,
    compass: Compass,
    calendar: Calendar,
    check: CheckCircle2,
    clock: Clock,
    arrowRight: ArrowRight,
    repeat: RotateCcw,
    star: Star,
} as const;

const icon = computed(
    () => icons[props.name as keyof typeof icons] ?? Sparkles,
);
const iconSize = computed(() => Number(props.size));
const iconStrokeWidth = computed(() => Number(props.strokeWidth));
</script>

<template>
    <component
        :is="icon"
        :size="iconSize"
        :stroke-width="iconStrokeWidth"
        :class="['app-icon', { 'app-icon--interactive': interactive }]"
        aria-hidden="true"
    />
</template>
