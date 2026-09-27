<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right';
        width?: '48';
        contentClasses?: string;
    }>(),
    {
        align: 'right',
        width: '48',
        contentClasses: 'py-1 bg-white',
    },
);

const emit = defineEmits(['open', 'close']);

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
        emit('close');
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    if (open.value) {
        open.value = false;
    }
});

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    } else if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    } else {
        return 'origin-top';
    }
});

const open = ref(false);

const toggle = () => {
    open.value = !open.value;
    if (open.value) {
        emit('open');
    } else {
        emit('close');
    }
};

const close = () => {
    if (open.value) {
        open.value = false;
        emit('close');
    }
};

const triggerRef = ref<HTMLElement | null>(null);

const onTriggerKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle();
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!open.value) {
            toggle();
        }
    }
};

const onContentKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        close();
        triggerRef.value?.focus();
    } else if (e.key === 'Tab') {
        close();
    }
};

onMounted(() => {
    if (triggerRef.value) {
        triggerRef.value.addEventListener('keydown', onTriggerKeyDown);
    }
});

onUnmounted(() => {
    if (triggerRef.value) {
        triggerRef.value.removeEventListener('keydown', onTriggerKeyDown);
    }
});
</script>

<template>
    <div class="relative" role="presentation">
        <div
            ref="triggerRef"
            @click="toggle"
            @keydown="onTriggerKeyDown"
            role="button"
            aria-haspopup="true"
            aria-controls="dropdown-content"
            :aria-expanded="open"
            tabindex="0"
            class="cursor-pointer"
        >
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="close"
            tabindex="-1"
        ></div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                id="dropdown-content"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
                style="display: none"
                @click="close"
                role="menu"
                aria-orientation="vertical"
                tabindex="-1"
                @keydown="onContentKeyDown"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                    role="presentation"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
