<script setup lang="ts">
import { computed } from 'vue';

interface AvatarDefinition {
    color: string;
    path: string;
}

interface Props {
    avatarKey?: string | null;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    avatarKey: 'book',
    label: 'Avatar profil',
});

const avatars: Record<string, AvatarDefinition> = {
    book: {
        color: '#0f766e',
        path: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20v3H6.5A2.5 2.5 0 0 1 4 17.5V5a2 2 0 0 1 2-2h14v14H6.5A2.5 2.5 0 0 0 4 19.5ZM8 7h8m-8 4h6',
    },
    cap: {
        color: '#4338ca',
        path: 'm3 9 9-5 9 5-9 5-9-5Zm4 3.2V16c0 1.6 2.2 3 5 3s5-1.4 5-3v-3.8M21 10v5',
    },
    globe: {
        color: '#0369a1',
        path: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.4 2.5 3.7 5.5 3.7 9S14.4 18.5 12 21M12 3C9.6 5.5 8.3 8.5 8.3 12S9.6 18.5 12 21M3 12h18',
    },
    lamp: {
        color: '#b45309',
        path: 'M9 18h6m-5 3h4m-7-9a5 5 0 1 1 10 0c0 1.8-.9 3-2.1 4.1-.6.5-.9 1.2-.9 1.9H10c0-.7-.3-1.4-.9-1.9C7.9 15 7 13.8 7 12Z',
    },
    microscope: {
        color: '#be185d',
        path: 'M9 3h5v5h-1v4.2a4.8 4.8 0 0 1-4.8 4.8H6m8-14 2 2m-3 3h3m-7 9h8a3 3 0 0 1 3 3v1H4v-1a3 3 0 0 1 3-3h2Z',
    },
    pencil: {
        color: '#c2410c',
        path: 'm4 20 4.2-1 10.6-10.6a2.1 2.1 0 0 0-3-3L5.2 16 4 20Zm10.5-13.5 3 3',
    },
    rocket: {
        color: '#7e22ce',
        path: 'M14 4c2.4-1 4.7-1 6-1 0 1.3 0 3.6-1 6l-5 5-4-4 4-6Zm-4 7-3 1-3 4 4 1 1 4 4-3 1-3m-7.5.5h.01',
    },
    laptop: { color: '#475569', path: 'M5 5h14v10H5V5Zm-3 13h20v2H2v-2Z' },
};

const avatar = computed(
    () => avatars[props.avatarKey ?? 'book'] ?? avatars.book,
);
</script>

<template>
    <span
        class="inline-grid place-items-center rounded-full"
        role="img"
        :aria-label="label"
        :style="{ backgroundColor: `${avatar.color}18`, color: avatar.color }"
    >
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path
                :d="avatar.path"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </span>
</template>
