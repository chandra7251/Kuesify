<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import TopNavBar from '@/Components/TopNavBar.vue';
import type { PageProps } from '@/types';
import { roleLabel } from '@/utils/roleLabel';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage<PageProps>();

// Desktop sidebar starts expanded (w-72), can be collapsed to mini-bar (w-20)
const desktopSidebarExpanded = ref(true);

onMounted(() => {
    const savedState = window.localStorage.getItem('kuesify.sidebar-expanded');

    if (savedState !== null) {
        desktopSidebarExpanded.value = savedState === 'true';
    }
});

watch(desktopSidebarExpanded, (expanded) => {
    window.localStorage.setItem('kuesify.sidebar-expanded', String(expanded));
});
// Mobile slide-over drawer state
const mobileSidebarOpen = ref(false);

const currentRole = computed(() => {
    return (
        page.props.currentOrganization?.role ||
        (page.props as any).organization?.role ||
        'creator'
    );
});

const navigationItems = computed(() => {
    const role = currentRole.value;

    if (role === 'participant') {
        return [
            { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
            { label: 'Kuis Mandiri', href: '/attempts', icon: 'results' },
            { label: 'Gabung Live', href: '/join', icon: 'live' },
            { label: 'Hasil Belajar', href: '/attempts', icon: 'results' },
        ];
    }

    if (role === 'super_admin') {
        return [
            { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
            { label: 'Platform Admin', href: '/admin', icon: 'admin' },
            {
                label: 'Monitoring User',
                href: '/admin/users',
                icon: 'organization',
            },
            {
                label: 'Kelola Tenant',
                href: '/organization',
                icon: 'organization',
            },
            { label: 'Laporan Global', href: '/reports', icon: 'results' },
        ];
    }

    if (role === 'organization_admin') {
        return [
            { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
            {
                label: 'Organisasi & Rombel',
                href: '/organization',
                icon: 'organization',
            },
            { label: 'Laporan & Nilai', href: '/reports', icon: 'results' },
            { label: 'Bank Soal', href: '/questions', icon: 'bank' },
            { label: 'Quiz Builder', href: '/quizzes', icon: 'builder' },
            { label: 'Live Quiz', href: '/live-sessions', icon: 'live' },
            { label: 'Materi AI', href: '/materials', icon: 'ai' },
        ];
    }

    return [
        { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
        { label: 'Question Bank', href: '/questions', icon: 'bank' },
        { label: 'Quiz Builder', href: '/quizzes', icon: 'builder' },
        { label: 'Live Quiz', href: '/live-sessions', icon: 'live' },
        { label: 'Hasil & Analitik', href: '/reports', icon: 'results' },
        { label: 'Materi AI', href: '/materials', icon: 'ai' },
    ];
});

const mobileNavigation = computed(() => {
    return navigationItems.value;
});

function isCurrent(href: string): boolean {
    if (href === '/dashboard') {
        return page.url === '/dashboard';
    }
    return page.url.startsWith(href);
}

function toggleDesktopSidebar(): void {
    desktopSidebarExpanded.value = !desktopSidebarExpanded.value;
}

function closeMobileNav(): void {
    mobileSidebarOpen.value = false;
}
</script>

<template>
    <div
        class="min-h-screen w-full max-w-full overflow-x-hidden bg-brand-accent text-slate-900"
    >
        <!-- ========================================================================= -->
        <!-- 1. DESKTOP SIDEBAR: FIXED TO VIEWPORT (Never scrolls with the page!)      -->
        <!-- ========================================================================= -->
        <aside
            id="desktop-sidebar"
            class="hidden select-none overflow-hidden bg-brand-primary text-white shadow-xl transition-all duration-300 ease-in-out lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:flex-col"
            :class="desktopSidebarExpanded ? 'lg:w-72' : 'lg:w-20'"
            aria-label="Navigasi samping"
        >
            <!-- Top Header: Logo & Expand/Collapse Toggle -->
            <div
                class="flex h-16 shrink-0 items-center border-b border-white/10 px-4"
                :class="
                    desktopSidebarExpanded
                        ? 'justify-between'
                        : 'justify-center'
                "
            >
                <!-- Expanded: Logo + Name + Organization -->
                <div
                    v-if="desktopSidebarExpanded"
                    class="flex min-w-0 items-center gap-3"
                >
                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                    >
                        <ApplicationLogo class="h-6 w-6 text-brand-secondary" />
                    </div>
                    <div class="min-w-0 flex-1 truncate">
                        <span
                            class="block truncate text-xl font-black tracking-tight text-brand-secondary"
                            >Kuesify</span
                        >
                        <span
                            class="block truncate text-[11px] font-semibold text-brand-secondary"
                        >
                            {{
                                page.props.currentOrganization?.name ||
                                'Ruang Belajar'
                            }}
                        </span>
                    </div>
                </div>

                <button
                    v-if="desktopSidebarExpanded"
                    type="button"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                    aria-label="Tutup sidebar"
                    :aria-expanded="desktopSidebarExpanded"
                    aria-controls="desktop-sidebar"
                    @click="toggleDesktopSidebar"
                >
                    <svg
                        aria-hidden="true"
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="m15 19-7-7 7-7" />
                    </svg>
                </button>

                <!-- Collapsed: Single Logo Icon -->
                <button
                    v-else
                    type="button"
                    class="group relative grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    aria-label="Buka sidebar"
                    :aria-expanded="desktopSidebarExpanded"
                    aria-controls="desktop-sidebar"
                    @click="toggleDesktopSidebar"
                >
                    <ApplicationLogo class="h-6 w-6 text-brand-secondary" />
                    <span
                        role="tooltip"
                        class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-white px-3 py-2 text-xs font-bold text-brand-primary opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                    >
                        Buka sidebar
                    </span>
                </button>
            </div>

            <!-- Scrollable Navigation Items Container (Only this scrolls vertically if height is small!) -->
            <div
                class="custom-scrollbar min-h-0 flex-1 space-y-1.5 overflow-y-auto overflow-x-hidden p-3"
            >
                <Link
                    v-for="item in navigationItems"
                    :key="item.label"
                    :href="item.href"
                    class="group relative flex min-h-11 items-center rounded-xl transition-all duration-150"
                    :class="[
                        desktopSidebarExpanded
                            ? 'gap-3.5 px-3.5'
                            : 'justify-center px-0',
                        isCurrent(item.href)
                            ? 'bg-brand-secondary font-bold text-white shadow-sm'
                            : 'font-semibold text-white/75 hover:bg-white/10 hover:text-white',
                    ]"
                    :title="!desktopSidebarExpanded ? item.label : undefined"
                >
                    <!-- Navigation Icons -->
                    <svg
                        v-if="item.icon === 'dashboard'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <rect width="7" height="7" x="3" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="14" rx="1" />
                        <rect width="7" height="7" x="3" y="14" rx="1" />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'bank'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v4M12 14v4M16 14v4"
                        />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'builder'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'live'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M4.93 19.07a10 10 0 0 1 0-14.14M2 12h.01M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M8.46 15.54a5 5 0 0 1 0-7.07M12 12a1 1 0 1 0 0 .01"
                        />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'results'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                        />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'ai'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"
                        />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'reports'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M3 3v18h18" />
                        <path d="M18 17V9" />
                        <path d="M13 17V5" />
                        <path d="M8 17v-3" />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'organization'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <svg
                        v-else-if="item.icon === 'admin'"
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 11 14 15 10" />
                    </svg>

                    <!-- Text Label (Visible only when Expanded) -->
                    <span
                        v-if="desktopSidebarExpanded"
                        class="truncate text-sm"
                    >
                        {{ item.label }}
                    </span>
                    <span
                        role="tooltip"
                        class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-white px-3 py-2 text-xs font-bold text-brand-primary opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </div>

            <!-- Bottom Sidebar Actions -->
            <div class="shrink-0 border-t border-white/10 p-3">
                <!-- Expanded: Setting + Logout buttons -->
                <div v-if="desktopSidebarExpanded" class="flex flex-col gap-1">
                    <!-- Settings -->
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 20h9" />
                            <path
                                d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"
                            />
                        </svg>
                        <span>Settings</span>
                    </Link>
                    <!-- Logout -->
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span>Logout</span>
                    </Link>
                </div>
                <!-- Collapsed: Icon only -->
                <div v-else class="flex flex-col items-center gap-1">
                    <Link
                        :href="route('profile.edit')"
                        class="grid h-10 w-10 place-items-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                        title="Settings"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 20h9" />
                            <path
                                d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"
                            />
                        </svg>
                        <span
                            role="tooltip"
                            class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-white px-3 py-2 text-xs font-bold text-brand-primary opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                        >
                            Settings
                        </span>
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="grid h-10 w-10 place-items-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                        title="Logout"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span
                            role="tooltip"
                            class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-white px-3 py-2 text-xs font-bold text-brand-primary opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                        >
                            Logout
                        </span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ========================================================================= -->
        <!-- 2. MAIN VIEWPORT WRAPPER (Offset by sidebar width on desktop)              -->
        <!-- ========================================================================= -->
        <div
            class="flex min-h-screen flex-col transition-all duration-300 ease-in-out"
            :class="desktopSidebarExpanded ? 'lg:pl-72' : 'lg:pl-20'"
        >
            <TopNavBar />

            <!-- Custom Subheader Slot (if page provides one) -->
            <div
                v-if="$slots.header"
                class="border-b border-slate-200 bg-white px-4 py-4 sm:px-8"
            >
                <slot name="header" />
            </div>

            <!-- Main Body: Scrolls naturally while Sidebar & Navbar stay completely pinned! -->
            <main class="flex-1 pb-24 lg:pb-12">
                <slot />
            </main>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. MOBILE SLIDE-OVER DRAWER & BACKDROP (< lg)                             -->
        <!-- ========================================================================= -->
        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-brand-primary/70 backdrop-blur-sm lg:hidden"
                @click="closeMobileNav"
            />
        </transition>

        <transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="mobileSidebarOpen"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col justify-between overflow-hidden bg-brand-primary p-4 text-white shadow-2xl lg:hidden"
                aria-label="Navigasi mobile drawer"
            >
                <div class="flex flex-1 flex-col gap-6 overflow-hidden">
                    <!-- Drawer Header -->
                    <div
                        class="flex shrink-0 items-center justify-between px-2 pt-2"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="grid h-10 w-10 place-items-center rounded-xl bg-brand-secondary shadow-sm"
                            >
                                <ApplicationLogo class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xl font-black text-white"
                                >Kuesify</span
                            >
                        </div>
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 text-white hover:bg-white/20"
                            aria-label="Tutup navigasi"
                            @click="closeMobileNav"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Drawer Nav Links -->
                    <nav
                        class="custom-scrollbar min-h-0 flex-1 space-y-1.5 overflow-y-auto overflow-x-hidden"
                        aria-label="Menu drawer"
                    >
                        <Link
                            v-for="item in navigationItems"
                            :key="item.label"
                            :href="item.href"
                            class="flex min-h-11 items-center gap-3.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                isCurrent(item.href)
                                    ? 'bg-brand-secondary text-white shadow-sm'
                                    : 'text-white/75 hover:bg-white/10 hover:text-white'
                            "
                            @click="closeMobileNav"
                        >
                            <!-- Icons -->
                            <svg
                                v-if="item.icon === 'dashboard'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect width="7" height="7" x="3" y="3" rx="1" />
                                <rect
                                    width="7"
                                    height="7"
                                    x="14"
                                    y="3"
                                    rx="1"
                                />
                                <rect
                                    width="7"
                                    height="7"
                                    x="14"
                                    y="14"
                                    rx="1"
                                />
                                <rect
                                    width="7"
                                    height="7"
                                    x="3"
                                    y="14"
                                    rx="1"
                                />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'bank'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v4M12 14v4M16 14v4"
                                />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'builder'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                                <path
                                    d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"
                                />
                                <path d="M12 17h.01" />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'live'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M4.93 19.07a10 10 0 0 1 0-14.14M2 12h.01M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07M8.46 15.54a5 5 0 0 1 0-7.07M12 12a1 1 0 1 0 0 .01"
                                />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'results'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'ai'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"
                                />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'reports'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M3 3v18h18" />
                                <path d="M18 17V9" />
                                <path d="M13 17V5" />
                                <path d="M8 17v-3" />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'organization'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <svg
                                v-else-if="item.icon === 'admin'"
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                                />
                                <polyline points="9 12 11 14 15 10" />
                            </svg>
                            <span class="truncate">{{ item.label }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- Bottom Actions in Mobile Drawer -->
                <div class="border-t border-white/15 pt-4">
                    <!-- Profile Info -->
                    <div class="flex items-center gap-3 px-2 pb-3">
                        <div
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-secondary/20 ring-2 ring-brand-secondary/40"
                        >
                            <AvatarIcon
                                :avatar-key="page.props.auth.user.avatar_key"
                                class="h-6 w-6 text-brand-secondary"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-white">
                                {{ page.props.auth.user.name }}
                            </p>
                            <p
                                class="truncate text-[11px] font-semibold text-brand-secondary"
                            >
                                {{ roleLabel(currentRole) }}
                            </p>
                        </div>
                    </div>
                    <!-- Settings -->
                    <Link
                        :href="route('profile.edit')"
                        class="grid h-10 w-10 place-items-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                        @click="closeMobileNav"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 20h9" />
                            <path
                                d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"
                            />
                        </svg>
                        <span>Settings</span>
                    </Link>
                    <!-- Logout -->
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="grid h-10 w-10 place-items-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-brand-secondary"
                        @click="closeMobileNav"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span>Keluar</span>
                    </Link>
                </div>
            </aside>
        </transition>

        <!-- ========================================================================= -->
        <!-- 4. MOBILE BOTTOM BAR (Fixed Bottom for Thumb Access)                       -->
        <!-- ========================================================================= -->
        <nav
            class="fixed bottom-4 left-1/2 z-30 flex w-[calc(100%-1.5rem)] max-w-xl -translate-x-1/2 items-center justify-around overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 p-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))] shadow-[0_8px_24px_rgba(15,23,42,0.16)] backdrop-blur lg:hidden"
            aria-label="Navigasi bawah"
        >
            <Link
                v-for="item in mobileNavigation"
                :key="item.href + item.label"
                :href="item.href"
                class="flex min-h-12 min-w-[50px] flex-1 flex-col items-center justify-center gap-0.5 rounded-xl px-1 text-center text-[10px] transition"
                :class="
                    isCurrent(item.href)
                        ? 'bg-brand-primary font-bold text-brand-secondary shadow-sm'
                        : 'font-semibold text-slate-600 hover:bg-slate-100'
                "
            >
                <svg
                    v-if="item.icon === 'dashboard'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect width="7" height="7" x="3" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="14" rx="1" />
                    <rect width="7" height="7" x="3" y="14" rx="1" />
                </svg>
                <svg
                    v-else-if="item.icon === 'bank'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"
                    />
                    <path d="M6 6h10" />
                    <path d="M6 10h10" />
                </svg>
                <svg
                    v-else-if="item.icon === 'builder'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                    />
                    <path
                        d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"
                    />
                </svg>
                <svg
                    v-else-if="item.icon === 'live'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M4.93 4.93a10 10 0 0 1 14.14 0" />
                    <path d="M7.76 7.76a6 6 0 0 1 8.48 0" />
                    <circle cx="12" cy="12" r="2" />
                    <path d="M12 14v7" />
                </svg>
                <svg
                    v-else-if="item.icon === 'results'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                    <path d="M4 22h16" />
                    <path
                        d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"
                    />
                    <path
                        d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"
                    />
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                </svg>
                <svg
                    v-else-if="item.icon === 'ai'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"
                    />
                </svg>
                <svg
                    v-else-if="item.icon === 'organization'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <svg
                    v-else-if="item.icon === 'admin'"
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <polyline points="9 12 11 14 15 10" />
                </svg>
                <span class="max-w-[56px] truncate">{{ item.label }}</span>
            </Link>
        </nav>
    </div>
</template>

<style scoped>
/* Sleek custom scrollbar for sidebar nav if needed */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.4);
}
</style>
