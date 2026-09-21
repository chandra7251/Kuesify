<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import type { PageProps } from '@/types';
import { roleLabel } from '@/utils/roleLabel';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<PageProps>();

// Desktop sidebar starts expanded (w-72), can be collapsed to mini-bar (w-20)
const desktopSidebarExpanded = ref(true);
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

    const items = [
        { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
        { label: 'Question Bank', href: '/questions', icon: 'bank' },
        { label: 'Quiz Builder', href: '/quizzes', icon: 'builder' },
        { label: 'Live Quiz', href: '/live-sessions', icon: 'live' },
        { label: 'Hasil', href: '/reports', icon: 'results' },
        { label: 'Materi AI', href: '/materials', icon: 'ai' },
    ];

    if (role === 'organization_admin' || role === 'super_admin') {
        items.push({
            label: 'Organisasi',
            href: '/organization',
            icon: 'organization',
        });
    }

    if (role === 'super_admin') {
        items.push({
            label: 'Platform Admin',
            href: '/admin',
            icon: 'admin',
        });
    }

    return items;
});

const mobileNavigation = [
    {
        label: 'Beranda',
        href: '/dashboard',
        icon: 'm3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z',
    },
    {
        label: 'Soal',
        href: '/questions',
        icon: 'M5 4h14v16H5zM8 8h8M8 12h8M8 16h5',
    },
    {
        label: 'Kuis',
        href: '/quizzes',
        icon: 'M5 3h14v18H5zM8 8h8M8 12h5M8 16h3',
    },
    { label: 'Live', href: '/live-sessions', icon: 'M8 5v14l11-7z' },
    { label: 'Hasil', href: '/attempts', icon: 'M5 20V10m7 10V4m7 16v-7' },
];

function isCurrent(href: string): boolean {
    if (href === '/dashboard') {
        return page.url === '/dashboard';
    }
    return page.url.startsWith(href);
}

function toggleDesktopSidebar(): void {
    desktopSidebarExpanded.value = !desktopSidebarExpanded.value;
}

function openMobileNav(): void {
    mobileSidebarOpen.value = true;
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
            class="hidden select-none bg-brand-primary text-white shadow-xl transition-all duration-300 ease-in-out lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:flex-col"
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
                        <ApplicationLogo class="h-6 w-6 text-brand-primary" />
                    </div>
                    <div class="min-w-0 flex-1 truncate">
                        <span
                            class="block truncate text-xl font-black tracking-tight text-white"
                            >Kuesify</span
                        >
                        <span
                            class="block truncate text-[11px] font-semibold text-blue-200"
                        >
                            {{
                                page.props.currentOrganization?.name ||
                                'Ruang Belajar'
                            }}
                        </span>
                    </div>
                </div>

                <!-- Collapsed: Single Logo Icon -->
                <div
                    v-else
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                >
                    <ApplicationLogo class="h-6 w-6 text-brand-primary" />
                </div>

                <!-- Toggle Collapse Button in Sidebar Header -->
                <button
                    v-if="desktopSidebarExpanded"
                    type="button"
                    class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                    title="Sempitkan sidebar"
                    @click="toggleDesktopSidebar"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Navigation Items Container (Only this scrolls if height is small!) -->
            <div
                class="custom-scrollbar flex-1 space-y-1.5 overflow-y-auto overflow-x-hidden p-3"
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
                            ? 'bg-brand-secondary font-bold text-slate-900 shadow-md shadow-emerald-950/20'
                            : 'font-semibold text-brand-secondary hover:bg-white/10 hover:text-white',
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
                </Link>
            </div>

            <!-- Bottom Profile Section -->
            <div class="shrink-0 border-t border-white/10 p-3">
                <!-- Expanded Profile Card -->
                <div v-if="desktopSidebarExpanded">
                    <div class="flex items-center gap-3 px-2">
                        <div
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-secondary/20 text-brand-secondary"
                        >
                            <AvatarIcon
                                :avatar-key="page.props.auth.user.avatar_key"
                                class="h-7 w-7 text-emerald-300"
                            />
                        </div>
                        <div class="min-w-0 flex-1 truncate">
                            <p class="truncate text-sm font-bold text-white">
                                {{ page.props.auth.user.name }}
                            </p>
                            <p class="truncate text-xs text-blue-200">
                                Role : {{ roleLabel(currentRole) }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-3 flex items-center justify-between px-2 text-xs font-semibold text-blue-200"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="transition hover:text-white"
                            >Settings</Link
                        >
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="transition hover:text-rose-300"
                            >Logout</Link
                        >
                    </div>
                </div>

                <!-- Collapsed Profile Button -->
                <div v-else class="flex flex-col items-center gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white transition hover:bg-white/20"
                        title="Profil pengguna"
                    >
                        <AvatarIcon
                            :avatar-key="page.props.auth.user.avatar_key"
                            class="h-6 w-6 text-emerald-300"
                        />
                    </Link>
                    <button
                        type="button"
                        class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/20"
                        title="Perlebar sidebar"
                        @click="toggleDesktopSidebar"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        >
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
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
            <!-- TOP NAVBAR: ALWAYS STICKY AT TOP (sticky top-0 z-20) -->
            <header
                class="sticky top-0 z-20 w-full border-b border-brand-hover bg-brand-primary text-white shadow-sm"
            >
                <div
                    class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8"
                >
                    <!-- Left Section: Toggle & Workspace Title -->
                    <div class="flex items-center gap-3.5">
                        <!-- Desktop Sidebar Toggle Button (Expands or Collapses sidebar) -->
                        <button
                            type="button"
                            class="hidden h-10 w-10 place-items-center rounded-xl bg-white/10 text-white transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white lg:grid"
                            :title="
                                desktopSidebarExpanded
                                    ? 'Sempitkan sidebar'
                                    : 'Perlebar sidebar'
                            "
                            :aria-label="
                                desktopSidebarExpanded
                                    ? 'Tutup navigasi'
                                    : 'Buka navigasi'
                            "
                            @click="toggleDesktopSidebar"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            >
                                <path
                                    v-if="desktopSidebarExpanded"
                                    d="M15 19l-7-7 7-7"
                                />
                                <path v-else d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Mobile Menu Button (< lg) -->
                        <button
                            type="button"
                            class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white lg:hidden"
                            aria-label="Buka navigasi"
                            @click="openMobileNav"
                        >
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            >
                                <path d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>

                        <!-- Brand Logo on mobile -->
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-2 lg:hidden"
                        >
                            <div
                                class="grid h-8 w-8 place-items-center rounded-lg bg-white shadow-sm"
                            >
                                <ApplicationLogo
                                    class="h-5 w-5 text-brand-primary"
                                />
                            </div>
                            <span class="text-base font-black text-white"
                                >Kuesify</span
                            >
                        </Link>

                        <!-- Workspace Info (Mockup Reference) -->
                        <div class="hidden sm:block">
                            <p
                                class="text-[11px] font-bold uppercase tracking-widest text-brand-secondary"
                            >
                                WORKSPACE
                            </p>
                            <h2
                                class="text-base font-black leading-tight tracking-tight text-brand-secondary sm:text-lg"
                            >
                                Selamat Datang Kembali
                            </h2>
                        </div>
                    </div>

                    <!-- Right Section: User Actions & Profile -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Desktop User Dropdown -->
                        <div class="hidden sm:block">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="flex min-h-11 items-center gap-2.5 rounded-xl border border-white/20 bg-white/10 px-3 py-1 text-sm font-semibold text-white transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                                        aria-label="Avatar pengguna"
                                    >
                                        <AvatarIcon
                                            :avatar-key="
                                                page.props.auth.user.avatar_key
                                            "
                                            class="h-7 w-7 p-1 text-emerald-300"
                                        />
                                        <span class="max-w-32 truncate">{{
                                            page.props.auth.user.name
                                        }}</span>
                                        <svg
                                            class="h-4 w-4 text-blue-200"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"
                                        >Profil</DropdownLink
                                    >
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        >Keluar</DropdownLink
                                    >
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Mobile Avatar Link -->
                        <Link
                            :href="route('profile.edit')"
                            class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white sm:hidden"
                            aria-label="Profil pengguna"
                        >
                            <AvatarIcon
                                :avatar-key="page.props.auth.user.avatar_key"
                                class="h-6 w-6 text-white"
                            />
                        </Link>
                    </div>
                </div>
            </header>

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
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"
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
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col justify-between bg-brand-primary p-4 text-white shadow-2xl lg:hidden"
                aria-label="Navigasi mobile drawer"
            >
                <div class="flex flex-col gap-6">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between px-2 pt-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="grid h-10 w-10 place-items-center rounded-xl bg-white shadow-sm"
                            >
                                <ApplicationLogo
                                    class="h-6 w-6 text-brand-primary"
                                />
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
                    <nav class="flex flex-col gap-1.5" aria-label="Menu drawer">
                        <Link
                            v-for="item in navigationItems"
                            :key="item.label"
                            :href="item.href"
                            class="flex min-h-11 items-center gap-3.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                isCurrent(item.href)
                                    ? 'bg-brand-secondary text-white shadow-md shadow-emerald-950/20'
                                    : 'text-blue-100 hover:bg-white/10 hover:text-white'
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

                <!-- Bottom Profile in Mobile Drawer -->
                <div class="border-t border-white/15 pt-4">
                    <div class="flex items-center gap-3 px-2">
                        <div
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-secondary/20 text-brand-secondary"
                        >
                            <AvatarIcon
                                :avatar-key="page.props.auth.user.avatar_key"
                                class="h-7 w-7 text-emerald-300"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-white">
                                {{ page.props.auth.user.name }}
                            </p>
                            <p class="truncate text-xs text-blue-200">
                                Role : {{ roleLabel(currentRole) }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-3 flex items-center justify-between px-2 text-xs font-semibold text-blue-200"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="transition hover:text-white"
                            @click="closeMobileNav"
                            >Profil</Link
                        >
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="transition hover:text-rose-300"
                            >Keluar</Link
                        >
                    </div>
                </div>
            </aside>
        </transition>

        <!-- ========================================================================= -->
        <!-- 4. MOBILE BOTTOM BAR (Fixed Bottom for Thumb Access)                       -->
        <!-- ========================================================================= -->
        <nav
            class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-slate-200 bg-white px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-lg lg:hidden"
            aria-label="Navigasi bawah"
        >
            <Link
                v-for="item in mobileNavigation"
                :key="item.href"
                :href="item.href"
                class="flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-lg px-1 text-center text-[10px] font-semibold text-slate-600 transition"
                :class="{
                    'bg-brand-primary text-white': $page.url.startsWith(
                        item.href,
                    ),
                }"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path
                        :d="item.icon"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <span>{{ item.label }}</span>
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
