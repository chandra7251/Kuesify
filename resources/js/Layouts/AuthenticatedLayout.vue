<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Sidebar from '@/Components/Sidebar.vue';
import TopNavBar from '@/Components/TopNavBar.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);

const primaryNavigation = [
    ['Dashboard', '/dashboard'],
    ['Question Bank', '/questions'],
    ['Quiz Builder', '/quizzes'],
    ['Live Quiz', '/live-sessions'],
    ['Hasil', '/attempts'],
    ['Materi AI', '/materials'],
];

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
</script>

<template>
    <div class="min-h-screen bg-[#f4fbfa] text-slate-900">
        <TopNavBar />
        <div class="lg:grid lg:grid-cols-[15rem_minmax(0,1fr)]">
            <Sidebar />
            <div class="min-w-0">
                <header
                    class="sticky top-0 z-30 border-b border-teal-100 bg-white/95 backdrop-blur lg:hidden"
                >
                    <div
                        class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="flex min-h-11 items-center gap-2 rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                        >
                            <ApplicationLogo class="h-9 w-9 text-teal-700" />
                            <span
                                class="text-lg font-extrabold tracking-tight text-teal-950"
                                >Kuesify</span
                            >
                        </Link>

                        <nav
                            class="hidden items-center gap-1 lg:flex"
                            aria-label="Navigasi utama"
                        >
                            <Link
                                v-for="[label, href] in primaryNavigation"
                                :key="href"
                                :href="href"
                                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-teal-50 hover:text-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-700"
                                :class="{
                                    'bg-teal-100 text-teal-900':
                                        $page.url.startsWith(href),
                                }"
                                >{{ label }}</Link
                            >
                        </nav>

                        <div class="hidden sm:block">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="flex min-h-11 items-center gap-2 rounded-xl border border-teal-100 bg-teal-50 px-3 text-sm font-semibold text-teal-950 transition hover:bg-teal-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-700"
                                    >
                                        <AvatarIcon
                                            :avatar-key="
                                                $page.props.auth.user.avatar_key
                                            "
                                            class="h-7 w-7 p-1.5"
                                        />
                                        <span class="max-w-28 truncate">{{
                                            $page.props.auth.user.name
                                        }}</span>
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
                        <Link
                            :href="route('profile.edit')"
                            class="grid h-11 w-11 place-items-center rounded-xl border border-teal-100 bg-teal-50 text-sm font-bold text-teal-950 transition hover:bg-teal-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-700 sm:hidden"
                            aria-label="Profil pengguna"
                        >
                            <AvatarIcon
                                :avatar-key="$page.props.auth.user.avatar_key"
                                class="h-8 w-8 p-1.5"
                            />
                        </Link>
                        <button
                            type="button"
                            class="grid h-11 w-11 place-items-center rounded-xl text-teal-900 hover:bg-teal-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-700 sm:hidden"
                            aria-label="Buka navigasi"
                            :aria-expanded="showingNavigationDropdown"
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                        >
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 7h16M4 12h16M4 17h16"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </div>
                    <div
                        v-if="showingNavigationDropdown"
                        class="border-t border-teal-100 bg-white px-4 py-3 sm:hidden"
                    >
                        <nav class="grid gap-1" aria-label="Navigasi mobile">
                            <Link
                                v-for="[label, href] in primaryNavigation"
                                :key="href"
                                :href="href"
                                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-teal-50"
                                @click="showingNavigationDropdown = false"
                                >{{ label }}</Link
                            >
                            <Link
                                :href="route('profile.edit')"
                                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-teal-50"
                                >Profil</Link
                            >
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="rounded-xl px-3 py-3 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50"
                                >Keluar</Link
                            >
                        </nav>
                    </div>
                </header>

                <header
                    v-if="$slots.header"
                    class="border-b border-teal-100 bg-white"
                >
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="pb-24 sm:pb-8"><slot /></main>

                <nav
                    class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-teal-100 bg-white px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 lg:hidden"
                    aria-label="Navigasi bawah"
                >
                    <Link
                        v-for="item in mobileNavigation"
                        :key="item.href"
                        :href="item.href"
                        class="flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-lg px-1 text-center text-[10px] font-semibold text-slate-600"
                        :class="{
                            'bg-teal-700 text-white': $page.url.startsWith(
                                item.href,
                            ),
                        }"
                        ><svg
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
                            /></svg
                        ><span>{{ item.label }}</span></Link
                    >
                </nav>
            </div>
        </div>
    </div>
</template>
