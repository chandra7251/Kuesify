<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    Loader2,
    Mail,
    RotateCw,
} from '@lucide/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const cooldown = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const startCooldown = (seconds = 60) => {
    if (timer) clearInterval(timer);
    cooldown.value = seconds;
    timer = setInterval(() => {
        if (cooldown.value > 0) {
            cooldown.value--;
        } else if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }, 1000);
};

watch(
    () => props.status,
    (newVal) => {
        if (newVal) {
            startCooldown(60);
        }
    },
);

onMounted(() => {
    if (props.status) {
        startCooldown(60);
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            startCooldown(60);
        },
    });
};

const resend = () => {
    if (cooldown.value === 0 && !form.processing) {
        submit();
    }
};
</script>

<template>
    <GuestLayout compact>
        <Head title="Lupa kata sandi" />

        <div class="mb-6">
            <Link
                :href="route('login')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary transition hover:text-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-secondary"
            >
                <ArrowLeft class="h-3.5 w-3.5" />
                <span>Kembali ke Halaman Masuk</span>
            </Link>

            <div class="mt-4">
                <p
                    class="text-xs font-bold uppercase tracking-wider text-brand-primary"
                >
                    Bantuan Akun
                </p>
                <h1
                    class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"
                >
                    Lupa kata sandi?
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Masukkan email akun Kuesify Anda. Kami akan mengirimkan
                    tautan untuk membuat kata sandi baru.
                </p>
            </div>
        </div>

        <!-- SUCCESS STATE: High-visibility confirmation card with recovery / resend timer -->
        <div
            v-if="status"
            class="mb-6 overflow-hidden rounded-2xl border border-brand-secondary/40 bg-brand-secondary/10 p-5 shadow-figma-sm"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-secondary text-[#102449]"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-slate-900">
                        Tautan Pemulihan Berhasil Dikirim!
                    </h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-700">
                        {{ status }}
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">
                        Periksa kotak masuk (inbox) atau folder spam pada email
                        <strong class="text-slate-800">{{
                            form.email || 'Anda'
                        }}</strong
                        >. Tautan berlaku selama 60 menit.
                    </p>

                    <div
                        class="mt-4 flex flex-wrap items-center gap-3 border-t border-brand-secondary/20 pt-3"
                    >
                        <button
                            v-if="cooldown === 0"
                            type="button"
                            @click="resend"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary transition hover:text-brand-hover focus-visible:outline-none"
                        >
                            <RotateCw class="h-3.5 w-3.5" />
                            <span>Kirim ulang tautan sekarang</span>
                        </button>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500"
                        >
                            <RotateCw
                                class="h-3.5 w-3.5 animate-spin text-slate-400"
                            />
                            <span>Kirim ulang dalam {{ cooldown }} detik</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM & INPUT STATE (Empty/Initial, Loading, and Error Recovery) -->
        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="email" value="Alamat Email" />
                    <span class="text-xs text-slate-500">Email terdaftar</span>
                </div>

                <div class="relative mt-1">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                    >
                        <Mail class="h-4 w-4" />
                    </div>

                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-10 pr-3 text-sm transition"
                        :class="{
                            'border-rose-400 bg-rose-50/20 text-rose-950 focus:border-rose-500 focus:ring-rose-500/20':
                                form.errors.email,
                            'bg-slate-50 opacity-70': form.processing,
                        }"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@sekolah.sch.id"
                        :disabled="form.processing"
                    />
                </div>

                <!-- ERROR STATE: Accessible with alert icon and recovery suggestions -->
                <div v-if="form.errors.email" class="mt-2 space-y-1">
                    <div
                        class="flex items-start gap-1.5 text-xs font-medium text-rose-600"
                    >
                        <AlertCircle class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <p class="text-xs text-slate-500">
                        Belum punya akun?
                        <Link
                            :href="route('register')"
                            class="font-semibold text-brand-primary underline hover:text-brand-hover"
                        >
                            Daftar akun Kuesify di sini
                        </Link>
                    </p>
                </div>
                <p v-else class="mt-1.5 text-xs text-slate-500">
                    Pastikan email yang Anda masukkan aktif dan dapat menerima
                    pesan.
                </p>
            </div>

            <!-- ACTION & LOADING STATE -->
            <div class="pt-1">
                <PrimaryButton
                    type="submit"
                    class="min-h-11 w-full justify-center shadow-figma-sm"
                    :class="{
                        'cursor-not-allowed opacity-60':
                            form.processing || cooldown > 0,
                    }"
                    :disabled="form.processing || cooldown > 0"
                >
                    <template v-if="form.processing">
                        <Loader2
                            class="mr-2 h-4 w-4 animate-spin text-[#102449]"
                        />
                        <span>Mengirim tautan reset...</span>
                    </template>
                    <template v-else-if="cooldown > 0">
                        <span
                            >Tunggu {{ cooldown }} detik untuk kirim ulang</span
                        >
                    </template>
                    <template v-else>
                        <span>Kirim Tautan Reset</span>
                    </template>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
