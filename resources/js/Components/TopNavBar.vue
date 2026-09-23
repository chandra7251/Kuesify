<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import Dropdown from '@/Components/Dropdown.vue';
import type { PageProps } from '@/types';
import { roleLabel } from '@/utils/roleLabel';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<PageProps>();

const currentRole = computed(
    () =>
        page.props.currentOrganization?.role ||
        (page.props as any).organization?.role ||
        'creator',
);
</script>

<template>
    <header
        class="sticky top-0 z-20 w-full border-b border-brand-hover bg-brand-primary text-white shadow-sm"
    >
        <div
            class="relative flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8"
        >
            <div class="flex items-center gap-3.5">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-2 lg:hidden"
                >
                    <div
                        class="grid h-8 w-8 place-items-center rounded-lg bg-white shadow-sm"
                    >
                        <ApplicationLogo class="h-5 w-5 text-brand-secondary" />
                    </div>
                    <span class="text-base font-black text-brand-secondary">
                        Kuesify
                    </span>
                </Link>

                <div class="hidden sm:block">
                    <h2
                        class="text-base font-bold uppercase tracking-widest text-brand-secondary sm:text-lg"
                    >
                        WORKSPACE
                    </h2>
                </div>
            </div>

            <div
                class="absolute right-4 top-1/2 z-10 flex -translate-y-1/2 items-center gap-3 sm:right-6 lg:static lg:z-auto lg:translate-y-0"
            >
                <div class="hidden flex-col items-end sm:flex">
                    <p class="text-sm font-bold leading-tight text-white">
                        {{ page.props.auth.user.name }}
                    </p>
                    <p
                        class="text-[11px] font-semibold leading-tight text-brand-secondary"
                    >
                        {{ roleLabel(currentRole) }}
                    </p>
                </div>

                <div
                    class="hidden h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-secondary/20 ring-2 ring-brand-secondary/40 lg:grid"
                >
                    <AvatarIcon
                        :avatar-key="page.props.auth.user.avatar_key"
                        class="h-6 w-6 text-brand-secondary"
                    />
                </div>

                <div
                    class="absolute right-4 top-1/2 z-50 shrink-0 -translate-y-1/2 lg:hidden"
                >
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-secondary/20 ring-2 ring-brand-secondary/40 transition hover:bg-brand-secondary/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                                aria-label="Buka menu profil"
                            >
                                <AvatarIcon
                                    :avatar-key="
                                        page.props.auth.user.avatar_key
                                    "
                                    class="h-6 w-6 text-brand-secondary"
                                />
                            </button>
                        </template>

                        <template #content>
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-brand-primary transition hover:bg-brand-primary/10"
                            >
                                <svg
                                    class="h-5 w-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                                <span>Profile</span>
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-brand-primary transition hover:bg-brand-primary/10"
                            >
                                <svg
                                    class="h-5 w-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                                    />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span>Keluar</span>
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </div>
    </header>
</template>
