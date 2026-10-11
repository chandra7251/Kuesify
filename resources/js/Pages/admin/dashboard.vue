<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    organization: { id: number; name: string; role: string | null };
    stats: {
        members: number;
        quizzes: number;
        questions: number;
        liveSessions: number;
        attempts: number;
    };
    recentQuizzes: {
        id: number;
        title: string;
        status: string;
        updated_at: string;
    }[];
    classPerformance: { completed: number; averageScore: number };
}>();

const statCards = computed(() => [
    {
        label: 'Anggota',
        value: props.stats.members,
        accent: 'bg-brand-primary',
        icon: 'users',
    },
    {
        label: 'Kuis',
        value: props.stats.quizzes,
        accent: 'bg-brand-primary',
        icon: 'quiz',
    },
    {
        label: 'Soal',
        value: props.stats.questions,
        accent: 'bg-brand-secondary',
        icon: 'question',
    },
    {
        label: 'Live Aktif',
        value: props.stats.liveSessions,
        accent: 'bg-brand-primary',
        icon: 'live',
    },
    {
        label: 'Attempts',
        value: props.stats.attempts,
        accent: 'bg-brand-secondary',
        icon: 'results',
    },
]);

const wizardSteps = computed(() => {
    const steps = [
        {
            num: 1,
            title: 'Atur nama sekolah / institusi',
            desc: 'Sesuaikan nama resmi organisasimu.',
            href: '/admin/settings',
            done: Boolean(
                props.organization.name &&
                !props.organization.name.includes("'s Institution"),
            ),
            icon: 'settings',
        },
        {
            num: 2,
            title: 'Undang anggota',
            desc: 'Daftarkan siswa dan guru ke sekolah.',
            href: '/admin/members',
            done: props.stats.members > 1,
            icon: 'members',
        },
        {
            num: 3,
            title: 'Pantau laporan',
            desc: 'Cek progress, skor, dan aktivitas kelas.',
            href: '/reports',
            done: props.stats.attempts > 0,
            icon: 'analytics',
        },
    ];
    return steps;
});

const setupCompletedCount = computed(
    () => wizardSteps.value.filter((step) => step.done).length,
);
const setupProgressPercent = computed(() =>
    Math.round((setupCompletedCount.value / wizardSteps.value.length) * 100),
);
const nextWizardStep = computed(
    () =>
        wizardSteps.value.find((step) => !step.done) ??
        wizardSteps.value.at(-1),
);

const showInviteModal = ref(false);
const inviteSuccess = ref(false);
const inviteForm = useForm({
    email: '',
    role: 'participant',
});

function submitInvite(): void {
    inviteForm.post(route('organization.members.store'), {
        preserveScroll: true,
        onSuccess: () => {
            inviteSuccess.value = true;
            inviteForm.reset();
            setTimeout(() => {
                showInviteModal.value = false;
                inviteSuccess.value = false;
            }, 1500);
        },
    });
}

const fmt = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});
</script>

<template>
    <Head title="Admin — Dashboard Organisasi" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent">
            <main
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <section
                    class="overflow-hidden rounded-2xl bg-brand-primary px-6 py-7 text-white shadow-figma sm:px-8"
                >
                    <p
                        class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-brand-secondary"
                            aria-hidden="true"
                        ></span>
                        {{ roleLabel(organization.role) }} ·
                        {{ organization.name }}
                    </p>
                    <h1
                        class="mt-3 max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl"
                    >
                        Dashboard Organisasi
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
                        Kelola anggota, grup, kuis, dan pantau aktivitas belajar
                        di tenant kamu — terpisah dari Platform Admin global.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <Link
                            href="/admin/members"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-secondary px-5 text-sm font-bold text-slate-900 shadow-sm transition hover:brightness-105 active:scale-95"
                        >
                            <span>👥</span>
                            <span>Kelola & Undang Anggota</span>
                        </Link>
                        <Link
                            href="/reports"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15"
                            >Lihat Laporan</Link
                        >
                        <Link
                            href="/admin/settings"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15"
                            >Pengaturan Org</Link
                        >
                    </div>
                </section>

                <section
                    aria-labelledby="org-kpi"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                >
                    <h2 id="org-kpi" class="sr-only">Ringkasan tenant</h2>
                    <StatCard
                        v-for="c in statCards"
                        :key="c.label"
                        :label="c.label"
                        :value="c.value"
                        :accent="c.accent"
                        :icon="c.icon"
                    />
                </section>

                <section class="grid gap-3 sm:grid-cols-2">
                    <!-- Wizard panel -->
                    <section
                        class="col-span-full rounded-2xl border border-brand-secondary/40 bg-white p-5 shadow-figma"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <h2
                                    class="text-sm font-black uppercase tracking-[0.14em] text-brand-primary"
                                >
                                    Panduan setup organisasi
                                </h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    Selesaikan 3 langkah ini untuk workspace
                                    yang siap dipakai.
                                </p>
                            </div>
                            <Link
                                v-if="nextWizardStep"
                                :href="nextWizardStep.href"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-primary px-4 text-xs font-black text-white transition hover:bg-brand-hover active:scale-95"
                            >
                                {{
                                    setupCompletedCount === wizardSteps.length
                                        ? 'Lihat laporan →'
                                        : `Lanjut: ${nextWizardStep.title} →`
                                }}
                            </Link>
                        </div>
                        <div class="mt-4">
                            <div
                                class="flex items-center justify-between text-xs font-bold text-slate-500"
                            >
                                <span
                                    >{{ setupCompletedCount }} /
                                    {{ wizardSteps.length }} langkah
                                    selesai</span
                                >
                                <span>{{ setupProgressPercent }}%</span>
                            </div>
                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full rounded-full bg-brand-secondary transition-all"
                                    :style="{
                                        width: `${setupProgressPercent}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div
                                v-for="step in wizardSteps"
                                :key="step.num"
                                class="p-4.5 flex flex-col justify-between rounded-2xl border shadow-sm transition-all"
                                :class="
                                    step.done
                                        ? 'border-emerald-200/90 bg-emerald-50/40'
                                        : 'border-slate-200 bg-white hover:border-brand-secondary/60'
                                "
                            >
                                <div>
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <span
                                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl"
                                            :class="
                                                step.done
                                                    ? 'bg-emerald-500 text-white shadow-sm'
                                                    : 'bg-brand-primary/10 text-brand-primary'
                                            "
                                        >
                                            <AppIcon
                                                :name="
                                                    step.done
                                                        ? 'check'
                                                        : step.icon
                                                "
                                                :size="20"
                                            />
                                        </span>
                                        <span
                                            v-if="step.done"
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-black text-emerald-700"
                                        >
                                            Selesai ✓
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-black text-amber-800"
                                        >
                                            Langkah {{ step.num }}
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <h3
                                            class="text-sm font-black text-slate-900"
                                        >
                                            {{ step.num }}. {{ step.title }}
                                        </h3>
                                        <p
                                            class="mt-1 text-xs leading-relaxed text-slate-500"
                                        >
                                            {{ step.desc }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3"
                                >
                                    <template v-if="step.num === 2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-xl bg-brand-primary px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-brand-hover active:scale-95"
                                            @click="showInviteModal = true"
                                        >
                                            <span>+ Undang</span>
                                        </button>
                                        <Link
                                            href="/admin/members"
                                            class="text-xs font-bold text-slate-600 transition hover:text-brand-primary"
                                        >
                                            Kelola ({{ stats.members }}) →
                                        </Link>
                                    </template>
                                    <template v-else-if="step.num === 1">
                                        <Link
                                            href="/admin/settings"
                                            class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-slate-800 active:scale-95"
                                        >
                                            Atur Nama →
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <Link
                                            href="/reports"
                                            class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-slate-800 active:scale-95"
                                        >
                                            Buka Laporan →
                                        </Link>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>
                    <article
                        class="ui-card-hover rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Quiz selesai
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-900">
                            {{ classPerformance.completed }}
                        </p>
                    </article>
                    <article
                        class="ui-card-hover rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Rata-rata kelas
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-900">
                            {{ classPerformance.averageScore
                            }}<span class="text-base"> / 100</span>
                        </p>
                    </article>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        class="ui-card-hover rounded-2xl border border-slate-200 bg-white p-5 shadow-figma lg:col-span-2"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    Kuis terbaru di organisasi ini
                                </h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    Hanya data tenant kamu — bukan global.
                                </p>
                            </div>
                            <Link
                                href="/quizzes"
                                class="rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-primary hover:bg-brand-accent"
                                >Lihat semua</Link
                            >
                        </div>
                        <div
                            v-if="recentQuizzes.length"
                            class="mt-4 divide-y divide-slate-100"
                        >
                            <div
                                v-for="q in recentQuizzes"
                                :key="q.id"
                                class="flex items-center justify-between gap-3 py-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ q.title }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Diperbarui
                                        {{ fmt.format(new Date(q.updated_at)) }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-brand-secondary/15 px-2.5 py-1 text-xs font-bold text-[#527A12]"
                                    >{{ q.status }}</span
                                >
                            </div>
                        </div>
                        <p
                            v-else
                            class="mt-6 rounded-xl bg-brand-accent px-4 py-6 text-center text-sm text-slate-600"
                        >
                            Belum ada kuis di tenant ini.
                        </p>
                    </section>
                    <section
                        class="ui-card-hover rounded-2xl border border-slate-200 bg-white p-5 shadow-figma"
                    >
                        <h2 class="text-base font-bold text-slate-900">
                            Aksi cepat Org Admin
                        </h2>
                        <div class="mt-4 grid gap-3">
                            <Link
                                href="/questions"
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-brand-secondary"
                                >Bank Soal
                                <span class="text-brand-primary">→</span></Link
                            >
                            <Link
                                href="/admin/members"
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-brand-secondary"
                                >Kelola Anggota
                                <span class="text-brand-primary">→</span></Link
                            >
                            <Link
                                href="/reports"
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-brand-secondary"
                                >Laporan tenant
                                <span class="text-brand-primary">→</span></Link
                            >
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <Modal
            :show="showInviteModal"
            max-width="md"
            @close="showInviteModal = false"
        >
            <div class="p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <div class="flex items-center gap-2.5">
                        <span
                            class="grid h-10 w-10 place-items-center rounded-xl bg-brand-primary/10 text-brand-primary"
                        >
                            <AppIcon name="members" :size="20" />
                        </span>
                        <div>
                            <h3 class="text-base font-black text-slate-900">
                                Undang Anggota
                            </h3>
                            <p class="text-xs text-slate-500">
                                Daftarkan guru atau siswa ke
                                {{ organization.name }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        @click="showInviteModal = false"
                    >
                        ✕
                    </button>
                </div>

                <div
                    v-if="inviteSuccess"
                    class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold text-emerald-800"
                >
                    ✓ Anggota berhasil ditambahkan ke organisasi!
                </div>

                <form class="mt-4 space-y-3.5" @submit.prevent="submitInvite">
                    <div>
                        <label class="block text-xs font-bold text-slate-700"
                            >Email Anggota</label
                        >
                        <input
                            v-model="inviteForm.email"
                            type="email"
                            required
                            placeholder="nama@sekolah.sch.id"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        />
                        <p
                            v-if="inviteForm.errors.email"
                            class="mt-1 text-xs font-bold text-red-600"
                        >
                            {{ inviteForm.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700"
                            >Peran / Role</label
                        >
                        <select
                            v-model="inviteForm.role"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        >
                            <option value="participant">
                                🎓 Siswa / Peserta
                            </option>
                            <option value="creator">👨‍🏫 Guru / Pengajar</option>
                            <option value="organization_admin">
                                🏢 Admin Organisasi
                            </option>
                        </select>
                    </div>

                    <div
                        class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-2"
                    >
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50"
                            @click="showInviteModal = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="rounded-xl bg-brand-primary px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-brand-hover disabled:opacity-50"
                        >
                            {{
                                inviteForm.processing
                                    ? 'Menambahkan...'
                                    : 'Tambah Anggota'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
