<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <header
        class="sticky top-0 z-40 hidden h-20 bg-[#3451b5] text-[#2dd4bf] lg:flex"
    >
        <Link
            :href="route('dashboard')"
            class="flex w-[15rem] shrink-0 items-center gap-2 border-r border-[#2dd4bf]/35 px-5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#2dd4bf]"
        >
            <ApplicationLogo class="h-9 w-9 text-[#2dd4bf]" />
            <span class="text-sm font-bold tracking-tight">Kuesify</span>
        </Link>

        <div class="flex min-w-0 flex-1 items-center px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em]">
                    Workspace
                </p>
                <p class="mt-1 text-base font-semibold">
                    Selamat Datang Kembali
                </p>
            </div>
        </div>
    </header>
</template>
