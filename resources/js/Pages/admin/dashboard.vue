<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

const motionRoot = ref<HTMLElement | null>(null);
let gsapCtx: gsap.Context | null = null;

onMounted(() => {
    const mm = gsap.matchMedia();

    mm.add('(prefers-reduced-motion: no-preference)', () => {
        gsapCtx = gsap.context(() => {
            gsap.from('[data-motion-item]', {
                opacity: 0,
                y: 22,
                duration: 0.4,
                stagger: 0.07,
                ease: 'power2.out',
                clearProps: 'all',
            });
        }, motionRoot.value ?? undefined);
    });

});

onUnmounted(() => {
    gsapCtx?.revert();
});
</script>

<template>
    <Head title="Admin — Dashboard Organisasi" />
    <AuthenticatedLayout>
        <main ref="motionRoot" data-motion="organization-dashboard" class="relative min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="pointer-events-none fixed inset-0 -z-0 bg-brand-accent/30"
                aria-hidden="true"
            ></div>
            <div class="relative z-10 mx-auto max-w-7xl space-y-8">
                <section
                    data-motion-item
                    class="relative overflow-hidden rounded-xl bg-brand-primary px-6 py-7 text-white shadow-sm sm:px-8"
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
                    data-motion-item
                    aria-labelledby="org-kpi"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"
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
                        data-motion-item
                        class="col-span-full rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm"
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
                                class="flex flex-col justify-between rounded-xl border p-4 shadow-sm transition-all"
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
                                            class="inline-flex items-center gap-1 rounded-xl bg-brand-primary px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-brand-hover active:scale-95"
                                        >
                                            Atur Nama →
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <Link
                                            href="/reports"
                                            class="inline-flex items-center gap-1 rounded-xl bg-brand-primary px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-brand-hover active:scale-95"
                                        >
                                            Buka Laporan →
                                        </Link>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>
                    <article
                        class="ui-card-hover rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm"
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
                        class="ui-card-hover rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm"
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
                        data-motion-item
                        class="ui-card-hover rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm lg:col-span-2"
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
                        data-motion-item
                        class="ui-card-hover rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm"
                    >
                        <h2 class="text-base font-bold text-slate-900">
                            Aksi cepat Org Admin
                        </h2>
                        <div class="mt-4 grid gap-3">
                            <Link
                                href="/questions"
                                class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-brand-accent/50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-brand-secondary hover:bg-brand-accent"
                                >Bank Soal
                                <span class="text-brand-primary">→</span></Link
                            >
                            <Link
                                href="/admin/members"
                                class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-brand-accent/50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-brand-secondary hover:bg-brand-accent"
                                >Kelola Anggota
                                <span class="text-brand-primary">→</span></Link
                            >
                            <Link
                                href="/reports"
                                class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-brand-accent/50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-brand-secondary hover:bg-brand-accent"
                                >Laporan tenant
                                <span class="text-brand-primary">→</span></Link
                            >
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <Modal
            :show="showInviteModal"
            max-width="lg"
            @close="showInviteModal = false"
        >
            <section
                class="overflow-hidden rounded-2xl border border-brand-primary/10 bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="invite-member-title"
            >
                <div
                    class="flex items-start justify-between gap-4 border-b border-brand-primary/10 bg-brand-accent/35 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-primary text-white shadow-sm"
                            aria-hidden="true"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 20a6 6 0 0 0-12 0m6-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-1v6m3-3h-6"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-primary"
                            >
                                Kelola anggota
                            </p>
                            <h2
                                id="invite-member-title"
                                class="mt-1 text-xl font-black text-slate-950"
                            >
                                Undang anggota
                            </h2>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-accent hover:text-brand-primary"
                        aria-label="Tutup undang anggota"
                        @click="showInviteModal = false"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 6l12 12M18 6L6 18"
                            />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <p class="text-sm leading-6 text-slate-600">
                        Daftarkan guru atau siswa ke
                        <span class="font-black text-slate-900">{{ organization.name }}</span>.
                    </p>

                    <div
                        v-if="inviteSuccess"
                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold leading-5 text-emerald-800"
                    >
                        Anggota berhasil ditambahkan ke organisasi.
                    </div>

                    <form class="space-y-4" @submit.prevent="submitInvite">
                        <div>
                            <label
                                for="invite-email"
                                class="block text-xs font-black text-slate-700"
                            >
                                Email anggota
                            </label>
                            <input
                                id="invite-email"
                                v-model="inviteForm.email"
                                type="email"
                                required
                                placeholder="nama@sekolah.sch.id"
                                class="mt-1.5 block min-h-11 w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-brand-primary focus:ring-brand-primary"
                            />
                            <p
                                v-if="inviteForm.errors.email"
                                class="mt-1 text-xs font-bold text-red-600"
                            >
                                {{ inviteForm.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="invite-role"
                                class="block text-xs font-black text-slate-700"
                            >
                                Peran / role
                            </label>
                            <select
                                id="invite-role"
                                v-model="inviteForm.role"
                                class="mt-1.5 block min-h-11 w-full rounded-xl border-slate-300 text-sm shadow-none focus:border-brand-primary focus:ring-brand-primary"
                            >
                                <option value="participant">Siswa / Peserta</option>
                                <option value="creator">Guru / Pengajar</option>
                                <option value="organization_admin">
                                    Admin Organisasi
                                </option>
                            </select>
                        </div>

                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                class="min-h-11 rounded-xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 transition hover:border-slate-300 hover:bg-slate-100"
                                @click="showInviteModal = false"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="min-h-11 rounded-xl bg-brand-primary px-5 text-sm font-black text-white shadow-sm transition hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    inviteForm.processing
                                        ? 'Menambahkan...'
                                        : 'Tambah anggota'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </Modal>
    </AuthenticatedLayout>
</template>
