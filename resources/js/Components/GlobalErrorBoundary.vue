<script setup lang="ts">
import { onErrorCaptured, ref } from 'vue';

const errorMessage = ref<string | null>(null);

function reloadPage(): void {
    window.location.reload();
}

onErrorCaptured((error) => {
    errorMessage.value =
        error instanceof Error ? error.message : 'Terjadi kesalahan.';
    return false;
});
</script>

<template>
    <div
        v-if="errorMessage"
        class="mx-auto mt-8 max-w-xl rounded-xl border border-status-danger/30 bg-white p-6 text-slate-900 shadow-sm"
        role="alert"
    >
        <p
            class="text-sm font-extrabold uppercase tracking-wide text-status-danger"
        >
            Aplikasi bermasalah
        </p>
        <h1 class="mt-2 text-xl font-extrabold">Muat ulang halaman</h1>
        <p class="mt-2 text-sm text-slate-600">
            {{ errorMessage }}
        </p>
        <button
            class="mt-4 min-h-11 rounded-lg bg-brand-primary px-4 text-sm font-bold text-white"
            type="button"
            @click="reloadPage"
        >
            Muat ulang
        </button>
    </div>
    <slot v-else />
</template>
