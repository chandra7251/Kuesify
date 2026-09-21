<script setup lang="ts">
import AvatarIcon from '@/Components/AvatarIcon.vue';
import { Link } from '@inertiajs/vue3';

const navigation = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
    },
    {
        label: 'Question Bank',
        href: '/questions',
        icon: 'M4 20h16M5 20V8h14v12M8 8V5h8v3M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01',
    },
    {
        label: 'Quiz Builder',
        href: '/quizzes',
        icon: 'M12 18h.01M9.6 9a2.5 2.5 0 1 1 3.8 2.15c-.87.5-1.4 1.03-1.4 2.35M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z',
    },
    {
        label: 'Live Quiz',
        href: '/live-sessions',
        icon: 'M8.5 8.5a5 5 0 0 0 0 7M5.5 5.5a9.25 9.25 0 0 0 0 13M15.5 8.5a5 5 0 0 1 0 7M18.5 5.5a9.25 9.25 0 0 1 0 13M12 12h.01',
    },
    {
        label: 'Hasil',
        href: '/attempts',
        icon: 'M6 3h12v18H6zM9 7h6M9 11h6M9 15h3',
    },
    {
        label: 'Materi AI',
        href: '/materials',
        icon: 'M7 7h10a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-5a3 3 0 0 1 3-3ZM8 4v3M16 4v3M20 12h1M3 12H2M9 12h.01M15 12h.01',
    },
];

function isActive(href: string): boolean {
    return href === '/dashboard'
        ? window.location.pathname === href
        : window.location.pathname.startsWith(href);
}
</script>

<template>
    <aside
        class="hidden min-h-[calc(100vh-5rem)] flex-col bg-[#3451b5] px-4 py-5 text-[#2dd4bf] lg:flex"
    >
        <nav class="space-y-1" aria-label="Navigasi utama">
            <Link
                v-for="item in navigation"
                :key="item.href"
                :href="item.href"
                class="group flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2dd4bf]"
                :class="
                    isActive(item.href)
                        ? 'bg-[#2dd4bf] text-[#123f4c] shadow-sm'
                        : 'text-[#2dd4bf] hover:bg-white/10 hover:text-white'
                "
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
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

        <div class="mt-auto border-t border-white/20 pt-4">
            <div class="flex items-center gap-2 px-2">
                <AvatarIcon
                    :avatar-key="$page.props.auth.user.avatar_key"
                    class="h-9 w-9 bg-white/15 p-1.5 text-[#2dd4bf]"
                />
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold">
                        {{ $page.props.auth.user.name }}
                    </p>
                    <p class="truncate text-xs text-[#2dd4bf]/80">
                        Workspace member
                    </p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-1">
                <Link
                    :href="route('profile.edit')"
                    class="min-h-10 rounded-lg px-2 py-2 text-center text-xs font-semibold text-[#2dd4bf] transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-300"
                >
                    Settings
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="min-h-10 rounded-lg px-2 py-2 text-xs font-semibold text-[#2dd4bf] transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#2dd4bf]"
                >
                    Logout
                </Link>
            </div>
        </div>
    </aside>
</template>
