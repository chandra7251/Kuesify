<script setup lang="ts">
defineProps<{
    value?: string;
}>();
</script>

<template>
    <label :for="String($attrs.for || '')" class="block text-sm font-medium text-slate-700">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
