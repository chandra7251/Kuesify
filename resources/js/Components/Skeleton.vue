<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        variant?: "text" | "rect" | "circle" | "avatar";
        width?: string | number;
        height?: string | number;
        class?: string;
        animation?: "pulse" | "wave";
    }>(),
    {
        variant: "text",
        width: "100%",
        height: "1rem",
        class: "",
        animation: "pulse",
    },
);

const classes = computed(() => {
    const baseClasses = "rounded bg-slate-200";
    const variantClasses = {
        text: "",
        rect: "",
        circle: "rounded-full",
        avatar: "rounded-full",
    }[props.variant];

    const animationClasses = props.animation === "wave" ? "skeleton-wave" : "animate-pulse";

    return `${baseClasses} ${variantClasses} ${animationClasses} ${props.class}`;
});

const styles = computed(() => ({
    width: typeof props.width === "number" ? `${props.width}px` : props.width,
    height: typeof props.height === "number" ? `${props.height}px` : props.height,
}));
</script>

<template>
    <div :class="classes" :style="styles" aria-hidden="true" />
</template>

<style scoped>
.skeleton-wave {
    position: relative;
    overflow: hidden;
}
.skeleton-wave::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    transform: translateX(-100%);
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0,
        rgba(255, 255, 255, 0.2) 20%,
        rgba(255, 255, 255, 0.5) 60%,
        rgba(255, 255, 255, 0)
    );
    animation: skeleton-wave 2s infinite;
}
@keyframes skeleton-wave {
    to {
        transform: translateX(100%);
    }
}
</style>
