<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = withDefaults(
    defineProps<{
        message: string;
        type?: "success" | "error" | "info" | "warning";
        duration?: number;
        onClose?: () => void;
    }>(),
    {
        type: "success",
        duration: 3000,
    },
);

const emit = defineEmits(["close"]);
const visible = ref(false);
const timer = ref<number | null>(null);

const colorClasses = computed(() => {
    const colors = {
        success: "bg-brand-secondary text-[#123f4c]",
        error: "bg-red-600 text-white",
        info: "bg-brand-primary text-white",
        warning: "bg-support-1 text-[#123f4c]",
    };
    return colors[props.type];
});

const iconPath = computed(() => {
    const icons = {
        success: "M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        error: "M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z",
        info: "M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z",
        warning: "M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z",
    };
    return icons[props.type];
});

onMounted(() => {
    visible.value = true;

    if (props.duration > 0) {
        timer.value = window.setTimeout(() => {
            close();
        }, props.duration);
    }
});

onUnmounted(() => {
    if (timer.value) {
        clearTimeout(timer.value);
    }
});

watch(
    () => props.duration,
    (newDuration) => {
        if (timer.value) {
            clearTimeout(timer.value);
        }
        if (newDuration > 0 && visible.value) {
            timer.value = window.setTimeout(() => {
                close();
            }, newDuration);
        }
    },
);

const close = () => {
    if (timer.value) {
        clearTimeout(timer.value);
        timer.value = null;
    }

    visible.value = false;
    emit("close");

    if (props.onClose) {
        props.onClose();
    }
};

defineExpose({
    close,
});
</script>

<template>
    <div
        v-if="visible"
        class="pointer-events-auto flex w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 transition-all duration-300 ease-out"
        role="alert"
        aria-live="polite"
        aria-atomic="true"
    >
        <div :class="`flex items-center gap-3 p-4 ${colorClasses}`">
            <svg
                class="h-6 w-6 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path :d="iconPath" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold">
                    {{ message }}
                </p>
            </div>
            <button
                type="button"
                class="rounded-lg p-1 hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-current"
                @click="close"
                aria-label="Tutup notifikasi"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>
