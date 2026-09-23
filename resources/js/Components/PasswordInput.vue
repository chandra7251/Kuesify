<script setup lang="ts">
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

defineProps<{
    id: string;
    autocomplete?: string;
    autofocus?: boolean;
    required?: boolean;
}>();

const model = defineModel<string>({ required: true });
const visible = ref(false);
</script>

<template>
    <div class="relative">
        <TextInput
            v-model="model"
            :id="id"
            :type="visible ? 'text' : 'password'"
            :autocomplete="autocomplete"
            :autofocus="autofocus"
            :required="required"
            class="mt-1 block w-full pe-12"
        />
        <button
            type="button"
            class="absolute end-3 top-1/2 mt-0.5 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-slate-500 transition hover:bg-brand-secondary/10 hover:text-brand-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
            :aria-label="
                visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
            "
            :aria-pressed="visible"
            @click="visible = !visible"
        >
            <svg
                v-if="visible"
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M3 3l18 18" />
                <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7" />
                <path
                    d="M9.9 4.3A10.8 10.8 0 0 1 12 4c5 0 8.5 4 9.5 6a16.7 16.7 0 0 1-3.1 3.8"
                />
                <path
                    d="M6.1 6.1A16.5 16.5 0 0 0 2.5 10c1 2 4.5 6 9.5 6 1 0 2-.2 2.9-.5"
                />
            </svg>
            <svg
                v-else
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path
                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                />
                <circle cx="12" cy="12" r="2.5" />
            </svg>
        </button>
    </div>
</template>
