<script setup lang="ts">
import AvatarIcon from '@/Components/AvatarIcon.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps } from '@/types';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const user = page.props.auth.user as any;
const currentRole = computed(
    () =>
        page.props.currentOrganization?.role ||
        (page.props as any).organization?.role ||
        'participant',
);

// Tab Navigation State
type TabKey = 'account' | 'preferences' | 'role' | 'security';
const activeTab = ref<TabKey>('account');

// Avatars Definition
const avatarOptions = [
    { key: 'book', name: 'Buku', description: 'Suka membaca materi' },
    { key: 'cap', name: 'Lulusan', description: 'Siap berkembang pesat' },
    { key: 'globe', name: 'Globe', description: 'Suka menjelajah wawasan' },
    { key: 'lamp', name: 'Ide', description: 'Penuh gagasan cemerlang' },
    { key: 'microscope', name: 'Peneliti', description: 'Teliti saat belajar' },
    { key: 'pencil', name: 'Kreator', description: 'Suka berkarya & menulis' },
    { key: 'rocket', name: 'Roket', description: 'Maju cepat berprestasi' },
    {
        key: 'laptop',
        name: 'Digital',
        description: 'Belajar modern serba digital',
    },
];

// 1. Form Akun & Profil
const profileForm = useForm({
    name: user.name,
    email: user.email,
    avatar_key: user.avatar_key || 'book',
});

function submitProfile(): void {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
}

function selectAvatar(key: string): void {
    profileForm.avatar_key = key;
}

// 2. Form Preferensi Umum (Tampilan & Suara)
const initialPrefs = (user.preferences || {}) as Record<string, any>;
const preferencesForm = useForm({
    preferences: {
        sound_effects:
            initialPrefs.sound_effects !== undefined
                ? initialPrefs.sound_effects
                : true,
        reduced_motion:
            initialPrefs.reduced_motion !== undefined
                ? initialPrefs.reduced_motion
                : false,
        time_format: initialPrefs.time_format || '24h',
        question_font_size: initialPrefs.question_font_size || 'normal',
    },
});

function submitPreferences(): void {
    preferencesForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
}

// 3. Form Pengaturan Peran (Siswa / Guru / Admin)
const roleSettingsForm = useForm({
    preferences: {
        leaderboard_privacy: initialPrefs.leaderboard_privacy || 'real_name',
        daily_streak_reminder:
            initialPrefs.daily_streak_reminder !== undefined
                ? initialPrefs.daily_streak_reminder
                : true,
        show_badges_public:
            initialPrefs.show_badges_public !== undefined
                ? initialPrefs.show_badges_public
                : true,
    },
});

function submitRoleSettings(): void {
    roleSettingsForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
}

// 4. Form Password
const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updatePassword(): void {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
}

// 5. Form Delete Account
const confirmingUserDeletion = ref(false);
const deletePasswordInput = ref<HTMLInputElement | null>(null);

const deleteForm = useForm({
    password: '',
});

function confirmUserDeletion(): void {
    confirmingUserDeletion.value = true;
    nextTick(() => deletePasswordInput.value?.focus());
}

function deleteUser(): void {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => deletePasswordInput.value?.focus(),
        onFinish: () => deleteForm.reset(),
    });
}

function closeModal(): void {
    confirmingUserDeletion.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
}

const roleTabTitle = computed(() => {
    if (currentRole.value === 'participant') return 'Pengaturan Siswa';
    if (currentRole.value === 'creator') return 'Pengaturan Guru';
    return 'Pengaturan Organisasi';
});
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Container Card SETTINGS Sesuai Mockup -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-figma sm:p-8"
            >
                <!-- Title Header -->
                <div
                    class="flex flex-col gap-1 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"
                        >
                            SETTINGS
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Kelola profil akun, preferensi belajar, dan
                            konfigurasi peran Anda.
                        </p>
                    </div>
                    <div
                        class="mt-3 flex items-center gap-2 self-start rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 sm:mt-0 sm:self-auto"
                    >
                        <span class="h-2 w-2 rounded-full bg-[#0AB883]"></span>
                        Peran: {{ roleLabel(currentRole) }}
                    </div>
                </div>

                <!-- Capsule / Pill Tabs Navigation -->
                <nav
                    class="scrollbar-none mt-6 flex gap-2.5 overflow-x-auto pb-2 sm:gap-3"
                    aria-label="Sub menu pengaturan"
                >
                    <!-- Tab 1: Account -->
                    <button
                        type="button"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full px-5 py-2.5 text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0AB883]"
                        :class="
                            activeTab === 'account'
                                ? 'bg-[#0AB883] text-slate-950 shadow-sm shadow-[#0AB883]/30'
                                : 'bg-[#3154D5] text-white hover:bg-[#2645B8]'
                        "
                        :aria-selected="activeTab === 'account'"
                        @click="activeTab = 'account'"
                    >
                        Account & Profil
                    </button>

                    <!-- Tab 2: Preferences -->
                    <button
                        type="button"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full px-5 py-2.5 text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0AB883]"
                        :class="
                            activeTab === 'preferences'
                                ? 'bg-[#0AB883] text-slate-950 shadow-sm shadow-[#0AB883]/30'
                                : 'bg-[#3154D5] text-white hover:bg-[#2645B8]'
                        "
                        :aria-selected="activeTab === 'preferences'"
                        @click="activeTab = 'preferences'"
                    >
                        Preferensi
                    </button>

                    <!-- Tab 3: Role Specific -->
                    <button
                        type="button"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full px-5 py-2.5 text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0AB883]"
                        :class="
                            activeTab === 'role'
                                ? 'bg-[#0AB883] text-slate-950 shadow-sm shadow-[#0AB883]/30'
                                : 'bg-[#3154D5] text-white hover:bg-[#2645B8]'
                        "
                        :aria-selected="activeTab === 'role'"
                        @click="activeTab = 'role'"
                    >
                        {{ roleTabTitle }}
                    </button>

                    <!-- Tab 4: Security -->
                    <button
                        type="button"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full px-5 py-2.5 text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0AB883]"
                        :class="
                            activeTab === 'security'
                                ? 'bg-[#0AB883] text-slate-950 shadow-sm shadow-[#0AB883]/30'
                                : 'bg-[#3154D5] text-white hover:bg-[#2645B8]'
                        "
                        :aria-selected="activeTab === 'security'"
                        @click="activeTab = 'security'"
                    >
                        Keamanan
                    </button>
                </nav>

                <!-- Dynamic Tab Content -->
                <div class="mt-8">
                    <!-- ========================================== -->
                    <!-- TAB 1: ACCOUNT & PROFIL                    -->
                    <!-- ========================================== -->
                    <section v-show="activeTab === 'account'" class="space-y-8">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Pilih Avatar Karakter
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Avatar ini akan tampil di samping nama Anda di
                                leaderboard dan workspace.
                            </p>

                            <!-- Avatar Grid Selector -->
                            <div
                                class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8"
                            >
                                <button
                                    v-for="opt in avatarOptions"
                                    :key="opt.key"
                                    type="button"
                                    class="group relative flex flex-col items-center rounded-xl border-2 p-3 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0AB883]"
                                    :class="
                                        profileForm.avatar_key === opt.key
                                            ? 'border-[#0AB883] bg-[#0AB883]/10 ring-2 ring-[#0AB883]/30'
                                            : 'border-slate-200 bg-white hover:border-[#3154D5]/40 hover:bg-slate-50'
                                    "
                                    @click="selectAvatar(opt.key)"
                                >
                                    <AvatarIcon
                                        :avatar-key="opt.key"
                                        class="h-11 w-11 p-1.5 transition group-hover:scale-105"
                                    />
                                    <span
                                        class="mt-2 text-xs font-bold text-slate-800"
                                    >
                                        {{ opt.name }}
                                    </span>
                                    <span
                                        v-if="
                                            profileForm.avatar_key === opt.key
                                        "
                                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-[#0AB883] text-white shadow"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="profileForm.errors.avatar_key"
                            />
                        </div>

                        <!-- Form Biodata -->
                        <form
                            @submit.prevent="submitProfile"
                            class="max-w-xl space-y-5 border-t border-slate-100 pt-6"
                        >
                            <div>
                                <InputLabel for="name" value="Nama Lengkap" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1.5 block w-full"
                                    v-model="profileForm.name"
                                    required
                                    autocomplete="name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="profileForm.errors.name"
                                />
                            </div>

                            <div>
                                <InputLabel for="email" value="Alamat Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1.5 block w-full"
                                    v-model="profileForm.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="profileForm.errors.email"
                                />
                            </div>

                            <!-- Email Verification Notice -->
                            <div
                                v-if="
                                    props.mustVerifyEmail &&
                                    user.email_verified_at === null
                                "
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4"
                            >
                                <p class="text-sm text-amber-800">
                                    Alamat email Anda belum terverifikasi.
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="font-bold underline hover:text-amber-900 focus:outline-none"
                                    >
                                        Kirim ulang email verifikasi.
                                    </Link>
                                </p>
                                <div
                                    v-show="
                                        props.status ===
                                        'verification-link-sent'
                                    "
                                    class="mt-2 text-xs font-semibold text-green-700"
                                >
                                    Tautan verifikasi baru telah dikirim ke
                                    alamat email Anda.
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton
                                    :disabled="profileForm.processing"
                                >
                                    Simpan Profil
                                </PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="profileForm.recentlySuccessful"
                                        class="text-sm font-semibold text-emerald-600"
                                    >
                                        Perubahan profil tersimpan!
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>

                    <!-- ========================================== -->
                    <!-- TAB 2: PREFERENSI (TAMPILAN & SUARA)      -->
                    <!-- ========================================== -->
                    <section
                        v-show="activeTab === 'preferences'"
                        class="max-w-2xl space-y-6"
                    >
                        <header>
                            <h2 class="text-lg font-bold text-slate-900">
                                Tampilan & Aksesibilitas
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Sesuaikan pengalaman visual dan audio saat
                                mengerjakan kuis.
                            </p>
                        </header>

                        <form
                            @submit.prevent="submitPreferences"
                            class="space-y-6"
                        >
                            <!-- Toggle Efek Suara -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-slate-300"
                            >
                                <div class="space-y-0.5">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >Efek Suara (Sound Effects)</span
                                    >
                                    <p class="text-xs text-slate-500">
                                        Putar efek audio saat kuis live
                                        berlangsung (jawaban benar/salah &
                                        countdown).
                                    </p>
                                </div>
                                <label
                                    class="relative inline-flex cursor-pointer items-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            preferencesForm.preferences
                                                .sound_effects
                                        "
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#0AB883] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"
                                    ></div>
                                </label>
                            </div>

                            <!-- Toggle Reduksi Animasi -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-slate-300"
                            >
                                <div class="space-y-0.5">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >Reduksi Animasi (Reduced Motion)</span
                                    >
                                    <p class="text-xs text-slate-500">
                                        Meminimalkan transisi gerak untuk
                                        kenyamanan mata dan performa hemat daya.
                                    </p>
                                </div>
                                <label
                                    class="relative inline-flex cursor-pointer items-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            preferencesForm.preferences
                                                .reduced_motion
                                        "
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#0AB883] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"
                                    ></div>
                                </label>
                            </div>

                            <!-- Format Jam -->
                            <div class="rounded-xl border border-slate-200 p-4">
                                <label
                                    class="block text-sm font-bold text-slate-900"
                                    >Format Waktu</label
                                >
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Pilih format penunjuk jam di seluruh
                                    platform.
                                </p>
                                <div class="mt-3 flex gap-4">
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700"
                                    >
                                        <input
                                            type="radio"
                                            value="24h"
                                            v-model="
                                                preferencesForm.preferences
                                                    .time_format
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        24 Jam (Contoh: 14:30)
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700"
                                    >
                                        <input
                                            type="radio"
                                            value="12h"
                                            v-model="
                                                preferencesForm.preferences
                                                    .time_format
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        12 Jam (Contoh: 02:30 PM)
                                    </label>
                                </div>
                            </div>

                            <!-- Ukuran Teks Soal -->
                            <div class="rounded-xl border border-slate-200 p-4">
                                <label
                                    class="block text-sm font-bold text-slate-900"
                                    >Ukuran Teks Soal Kuis</label
                                >
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Menyesuaikan kenyamanan membaca saat
                                    mengerjakan soal.
                                </p>
                                <div class="mt-3 flex gap-4">
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700"
                                    >
                                        <input
                                            type="radio"
                                            value="normal"
                                            v-model="
                                                preferencesForm.preferences
                                                    .question_font_size
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        Normal (Standar)
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700"
                                    >
                                        <input
                                            type="radio"
                                            value="large"
                                            v-model="
                                                preferencesForm.preferences
                                                    .question_font_size
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        Besar (Lebih Jelas di HP)
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton
                                    :disabled="preferencesForm.processing"
                                >
                                    Simpan Preferensi
                                </PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="
                                            preferencesForm.recentlySuccessful
                                        "
                                        class="text-sm font-semibold text-emerald-600"
                                    >
                                        Preferensi berhasil disimpan!
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>

                    <!-- ========================================== -->
                    <!-- TAB 3: PENGATURAN SISWA / PERAN           -->
                    <!-- ========================================== -->
                    <section
                        v-show="activeTab === 'role'"
                        class="max-w-2xl space-y-6"
                    >
                        <header>
                            <h2 class="text-lg font-bold text-slate-900">
                                {{ roleTabTitle }}
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Pengaturan khusus untuk aktivitas Anda sebagai
                                {{ roleLabel(currentRole) }}.
                            </p>
                        </header>

                        <!-- Skenario 1: Siswa / Participant -->
                        <form
                            v-if="currentRole === 'participant'"
                            @submit.prevent="submitRoleSettings"
                            class="space-y-6"
                        >
                            <!-- Privasi Leaderboard -->
                            <div class="rounded-xl border border-slate-200 p-4">
                                <label
                                    class="block text-sm font-bold text-slate-900"
                                    >Tampilan Nama di Leaderboard Live</label
                                >
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Pilih bagaimana nama Anda ditampilkan kepada
                                    peserta lain saat bermain kuis live.
                                </p>
                                <div class="mt-3 space-y-2.5">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 p-2.5 transition hover:bg-slate-50"
                                    >
                                        <input
                                            type="radio"
                                            value="real_name"
                                            v-model="
                                                roleSettingsForm.preferences
                                                    .leaderboard_privacy
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        <div>
                                            <span
                                                class="block text-xs font-bold text-slate-900"
                                                >Nama Asli Lengkap</span
                                            >
                                            <span
                                                class="text-[11px] text-slate-500"
                                                >Menampilkan nama akun Anda
                                                secara penuh.</span
                                            >
                                        </div>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 p-2.5 transition hover:bg-slate-50"
                                    >
                                        <input
                                            type="radio"
                                            value="alias"
                                            v-model="
                                                roleSettingsForm.preferences
                                                    .leaderboard_privacy
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        <div>
                                            <span
                                                class="block text-xs font-bold text-slate-900"
                                                >Nama Panggilan / Depan</span
                                            >
                                            <span
                                                class="text-[11px] text-slate-500"
                                                >Hanya menampilkan kata pertama
                                                nama Anda.</span
                                            >
                                        </div>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 p-2.5 transition hover:bg-slate-50"
                                    >
                                        <input
                                            type="radio"
                                            value="anonymous"
                                            v-model="
                                                roleSettingsForm.preferences
                                                    .leaderboard_privacy
                                            "
                                            class="text-[#3154D5] focus:ring-[#3154D5]"
                                        />
                                        <div>
                                            <span
                                                class="block text-xs font-bold text-slate-900"
                                                >Mode Anonim</span
                                            >
                                            <span
                                                class="text-[11px] text-slate-500"
                                                >Tampil sebagai "Siswa Anonim"
                                                untuk kenyamanan privasi.</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Reminder Daily Streak -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-slate-300"
                            >
                                <div class="space-y-0.5">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >Pengingat Daily Streak</span
                                    >
                                    <p class="text-xs text-slate-500">
                                        Bantu menjaga rutinitas belajar dengan
                                        notifikasi sebelum streak harian
                                        ter-reset.
                                    </p>
                                </div>
                                <label
                                    class="relative inline-flex cursor-pointer items-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            roleSettingsForm.preferences
                                                .daily_streak_reminder
                                        "
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#0AB883] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"
                                    ></div>
                                </label>
                            </div>

                            <!-- Tampilkan Badge Publik -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-slate-300"
                            >
                                <div class="space-y-0.5">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >Tampilkan Badge di Profil Publik</span
                                    >
                                    <p class="text-xs text-slate-500">
                                        Izinkan teman sekelas melihat pencapaian
                                        badge dan level gamifikasi Anda.
                                    </p>
                                </div>
                                <label
                                    class="relative inline-flex cursor-pointer items-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            roleSettingsForm.preferences
                                                .show_badges_public
                                        "
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#0AB883] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"
                                    ></div>
                                </label>
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton
                                    :disabled="roleSettingsForm.processing"
                                >
                                    Simpan Pengaturan Siswa
                                </PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="
                                            roleSettingsForm.recentlySuccessful
                                        "
                                        class="text-sm font-semibold text-emerald-600"
                                    >
                                        Pengaturan siswa tersimpan!
                                    </p>
                                </Transition>
                            </div>
                        </form>

                        <!-- Skenario 2: Creator / Guru -->
                        <div
                            v-else-if="currentRole === 'creator'"
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5"
                        >
                            <h3 class="text-sm font-bold text-slate-900">
                                Preset Default Pembuatan Kuis
                            </h3>
                            <p class="text-xs text-slate-600">
                                Konfigurasi default saat Anda membuat kuis baru
                                di Quiz Builder:
                            </p>
                            <ul class="space-y-2 text-xs text-slate-700">
                                <li class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#0AB883]"
                                    ></span>
                                    Durasi Timer Default:
                                    <strong>30 Detik per soal</strong>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#0AB883]"
                                    ></span>
                                    Skor Poin Dasar: <strong>1000 Poin</strong>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#0AB883]"
                                    ></span>
                                    AI Question Generator:
                                    <strong>Maksimal 10 generasi/minggu</strong>
                                    (Reset setiap hari Senin)
                                </li>
                            </ul>
                        </div>

                        <!-- Skenario 3: Admin Organisasi -->
                        <div
                            v-else
                            class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5"
                        >
                            <h3 class="text-sm font-bold text-slate-900">
                                Pengaturan Workspace Organisasi
                            </h3>
                            <p class="text-xs text-slate-600">
                                Kebijakan workspace aktif:
                            </p>
                            <ul class="space-y-2 text-xs text-slate-700">
                                <li class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#0AB883]"
                                    ></span>
                                    Timezone Organisasi:
                                    <strong>Asia/Jakarta (WIB)</strong>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#0AB883]"
                                    ></span>
                                    Batas Kuota AI Workspace:
                                    <strong>100 Generasi / Bulan</strong>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- TAB 4: KEAMANAN                           -->
                    <!-- ========================================== -->
                    <section
                        v-show="activeTab === 'security'"
                        class="space-y-10"
                    >
                        <!-- Form Ganti Password -->
                        <form
                            @submit.prevent="updatePassword"
                            class="max-w-xl space-y-5"
                        >
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Perbarui Kata Sandi
                                </h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    Gunakan kombinasi sandi acak untuk menjaga
                                    keamanan akun Anda.
                                </p>
                            </div>

                            <div>
                                <InputLabel
                                    for="current_password"
                                    value="Kata Sandi Saat Ini"
                                />
                                <TextInput
                                    id="current_password"
                                    ref="currentPasswordInput"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="mt-1.5 block w-full"
                                    autocomplete="current-password"
                                />
                                <InputError
                                    :message="
                                        passwordForm.errors.current_password
                                    "
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="password"
                                    value="Kata Sandi Baru"
                                />
                                <TextInput
                                    id="password"
                                    ref="passwordInput"
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="mt-1.5 block w-full"
                                    autocomplete="new-password"
                                />
                                <InputError
                                    :message="passwordForm.errors.password"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    value="Konfirmasi Kata Sandi Baru"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="mt-1.5 block w-full"
                                    autocomplete="new-password"
                                />
                                <InputError
                                    :message="
                                        passwordForm.errors
                                            .password_confirmation
                                    "
                                    class="mt-2"
                                />
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton
                                    :disabled="passwordForm.processing"
                                >
                                    Perbarui Sandi
                                </PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="passwordForm.recentlySuccessful"
                                        class="text-sm font-semibold text-emerald-600"
                                    >
                                        Kata sandi berhasil diperbarui!
                                    </p>
                                </Transition>
                            </div>
                        </form>

                        <!-- Danger Zone: Hapus Akun -->
                        <div class="max-w-xl border-t border-rose-100 pt-8">
                            <h3 class="text-base font-bold text-rose-700">
                                Zona Berbahaya (Hapus Akun)
                            </h3>
                            <p class="mt-1 text-xs text-slate-600">
                                Setelah akun dihapus, seluruh riwayat kuis,
                                poin, dan progres Anda akan terhapus permanen.
                            </p>
                            <div class="mt-4">
                                <DangerButton @click="confirmUserDeletion">
                                    Hapus Akun Saya
                                </DangerButton>
                            </div>

                            <!-- Modal Konfirmasi Hapus Akun -->
                            <Modal
                                :show="confirmingUserDeletion"
                                @close="closeModal"
                            >
                                <div class="p-6">
                                    <h2
                                        class="text-lg font-bold text-slate-900"
                                    >
                                        Apakah Anda yakin ingin menghapus akun?
                                    </h2>
                                    <p class="mt-2 text-sm text-slate-600">
                                        Masukkan kata sandi Anda untuk
                                        mengonfirmasi bahwa Anda ingin menghapus
                                        akun ini secara permanen.
                                    </p>
                                    <div class="mt-6">
                                        <InputLabel
                                            for="delete_password"
                                            value="Kata Sandi"
                                            class="sr-only"
                                        />
                                        <TextInput
                                            id="delete_password"
                                            ref="deletePasswordInput"
                                            v-model="deleteForm.password"
                                            type="password"
                                            class="block w-3/4"
                                            placeholder="Kata Sandi"
                                            @keyup.enter="deleteUser"
                                        />
                                        <InputError
                                            :message="
                                                deleteForm.errors.password
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <SecondaryButton @click="closeModal">
                                            Batal
                                        </SecondaryButton>
                                        <DangerButton
                                            :class="{
                                                'opacity-25':
                                                    deleteForm.processing,
                                            }"
                                            :disabled="deleteForm.processing"
                                            @click="deleteUser"
                                        >
                                            Hapus Akun
                                        </DangerButton>
                                    </div>
                                </div>
                            </Modal>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
