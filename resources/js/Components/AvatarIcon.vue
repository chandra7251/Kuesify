<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    avatarKey?: string | null | undefined;
    label?: string | undefined;
}

const props = withDefaults(defineProps<Props>(), {
    avatarKey: 'profile_1',
    label: 'Avatar profil',
});

const avatarPath = computed(() => {
    const avatarKey = props.avatarKey?.startsWith('profile_')
        ? props.avatarKey
        : 'profile_1';

    return `/images/photo_profile/${avatarKey}.png`;
});
</script>

<template>
    <span
        class="inline-grid place-items-center overflow-hidden rounded-full"
        role="img"
        :aria-label="label"
    >
        <img
            :src="avatarPath"
            :alt="label"
            class="h-full w-full object-cover"
        />
    </span>
</template>