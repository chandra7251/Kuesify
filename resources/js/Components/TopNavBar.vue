<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AvatarIcon from '@/Components/AvatarIcon.vue';
import type { PageProps } from '@/types';
import { roleLabel } from '@/utils/roleLabel';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage<PageProps>();

const emit = defineEmits<{ toggleMobileSidebar: [] }>();

const props = defineProps<{
    darkMode: boolean;
}>();

const localDarkMode = ref(props.darkMode);

watch(
    () => props.darkMode,
    (value) => {
        localDarkMode.value = value;
    },
);

onMounted(() => {
    localDarkMode.value =
        document.documentElement.classList.contains('dark-mode');
});

function handleThemeToggle(): void {
    localDarkMode.value = !localDarkMode.value;
    document.documentElement.classList.toggle('dark-mode', localDarkMode.value);

    try {
        window.localStorage.setItem(
            'kuesify.dark-mode',
            String(localDarkMode.value),
        );
    } catch {
        // Visual preference remains active when storage is unavailable.
    }
}

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
                        <ApplicationLogo class="h-7 w-7 text-brand-secondary" />
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
                    <div class="flex items-center gap-1.5">
                        <span
                            v-if="page.props.currentOrganization?.name"
                            class="rounded-md bg-white/10 px-1.5 py-0.5 text-[10px] font-bold text-brand-secondary"
                        >
                            {{ page.props.currentOrganization.name }}
                        </span>
                        <p class="text-sm font-black leading-tight text-white">
                            {{ page.props.auth.user.name }}
                        </p>
                    </div>
                    <p
                        class="text-[11px] font-bold leading-tight text-brand-secondary"
                    >
                        {{ roleLabel(currentRole) }}
                    </p>
                </div>

                <button
                    type="button"
                    class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-brand-secondary ring-2 ring-brand-secondary/30 transition hover:bg-white/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
                    :aria-label="
                        localDarkMode
                            ? 'Aktifkan mode terang'
                            : 'Aktifkan mode gelap'
                    "
                    :title="localDarkMode ? 'Mode terang' : 'Mode gelap'"
                    @click="handleThemeToggle"
                >
                    <svg
                        v-if="localDarkMode"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2" />
                        <path d="M12 20v2" />
                        <path d="m4.93 4.93 1.41 1.41" />
                        <path d="m17.66 17.66 1.41 1.41" />
                        <path d="M2 12h2" />
                        <path d="M20 12h2" />
                        <path d="m6.34 17.66-1.41 1.41" />
                        <path d="m19.07 4.93-1.41 1.41" />
                    </svg>
                    <svg
                        v-else
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-brand-secondary ring-2 ring-brand-secondary/30 transition hover:bg-white/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary lg:hidden"
                    aria-label="Buka navigasi"
                    :aria-expanded="undefined"
                    @click="emit('toggleMobileSidebar')"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        aria-hidden="true"
                    >
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <Link
                    :href="route('profile.edit')"
                    aria-label="Buka profil"
                    class="hidden h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-secondary/20 ring-2 ring-brand-secondary/40 transition hover:bg-brand-secondary/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary lg:grid"
                >
                    <AvatarIcon
                        :avatar-key="page.props.auth.user.avatar_key"
                        class="h-6 w-6 text-brand-secondary"
                    />
                </Link>
            </div>
        </div>
    </header>
</template>
