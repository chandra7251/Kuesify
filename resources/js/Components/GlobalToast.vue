<script setup lang="ts">
import Toast from '@/Components/Toast.vue';
import type { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type FlashProps = {
    success?: string | null;
    error?: string | null;
    status?: string | null;
};

const page = usePage<PageProps & { flash?: FlashProps }>();
const dismissed = ref<string | null>(null);
const isNavigating = ref(false);
let stopNavigationWatch: (() => void) | null = null;

const message = computed(() => {
    const flash = page.props?.flash;

    if (flash?.error) {
        return {
            text: flash.error,
            type: 'error' as const,
            key: `error:${flash.error}`,
        };
    }

    if (flash?.success) {
        return {
            text: flash.success,
            type: 'success' as const,
            key: `success:${flash.success}`,
        };
    }

    if (flash?.status) {
        return {
            text: flash.status,
            type: 'info' as const,
            key: `status:${flash.status}`,
        };
    }

    return null;
});

watch(message, (next) => {
    dismissed.value = next?.key ?? null;
});

onMounted(() => {
    const stopStart = router.on('start', () => {
        isNavigating.value = true;
    });
    const stopFinish = router.on('finish', () => {
        isNavigating.value = false;
    });
    stopNavigationWatch = () => {
        stopStart();
        stopFinish();
    };
});

onBeforeUnmount(() => {
    stopNavigationWatch?.();
});
</script>

<template>
    <slot />
    <div
        v-if="isNavigating"
        class="fixed inset-x-0 top-0 z-[60] h-1 overflow-hidden bg-brand-primary/10"
        role="status"
        aria-live="polite"
        aria-label="Halaman sedang dimuat"
    >
        <div
            class="h-full w-1/2 animate-[pulse_1s_ease-in-out_infinite] bg-brand-secondary"
        />
    </div>
    <div
        class="pointer-events-none fixed inset-x-4 top-4 z-50 flex justify-end"
    >
        <Toast
            v-if="message && dismissed === message.key"
            :message="message.text"
            :type="message.type"
            @close="dismissed = null"
        />
    </div>
</template>
