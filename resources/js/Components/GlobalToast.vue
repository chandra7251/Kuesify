<script setup lang="ts">
import Toast from '@/Components/Toast.vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { computed, ref, watch } from 'vue';

type FlashProps = {
    success?: string | null;
    error?: string | null;
    status?: string | null;
};

const page = usePage<PageProps & { flash?: FlashProps }>();
const dismissed = ref<string | null>(null);

const message = computed(() => {
    const flash = page.props?.flash;

    if (flash?.error) {
        return { text: flash.error, type: 'error' as const, key: `error:${flash.error}` };
    }

    if (flash?.success) {
        return { text: flash.success, type: 'success' as const, key: `success:${flash.success}` };
    }

    if (flash?.status) {
        return { text: flash.status, type: 'info' as const, key: `status:${flash.status}` };
    }

    return null;
});

watch(message, (next) => {
    dismissed.value = next?.key ?? null;
});
</script>

<template>
    <slot />
    <div class="pointer-events-none fixed inset-x-4 top-4 z-50 flex justify-end">
        <Toast
            v-if="message && dismissed === message.key"
            :message="message.text"
            :type="message.type"
            @close="dismissed = null"
        />
    </div>
</template>

