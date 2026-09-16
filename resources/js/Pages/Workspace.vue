<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { roleLabel } from '@/utils/roleLabel';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    section: string;
    items: { data?: Record<string, unknown>[] } | Record<string, unknown>[];
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
}>();

const sectionMeta: Record<
    string,
    {
        eyebrow: string;
        description: string;
        tone: string;
        action?: [string, string];
    }
> = {
    questions: {
        eyebrow: 'Bank materi',
        description:
            'Simpan, cari, impor, dan gunakan ulang pertanyaan terbaik.',
        tone: 'bg-teal-800 text-white',
        action: ['Export CSV', '/questions/export'],
    },
    quizzes: {
        eyebrow: 'Ruang kreator',
        description:
            'Susun pengalaman kuis dari koleksi pertanyaan organisasi.',
        tone: 'bg-violet-800 text-white',
    },
    live: {
        eyebrow: 'Mode langsung',
        description: 'Kelola lobby, PIN, peserta, dan progres sesi live.',
        tone: 'bg-orange-700 text-white',
    },
    attempts: {
        eyebrow: 'Belajar',
        description: 'Pantau status, nilai, dan jawaban peserta.',
        tone: 'bg-sky-800 text-white',
    },
    reports: {
        eyebrow: 'Insight',
        description: 'Lihat progres kuis dan pola performa peserta.',
        tone: 'bg-slate-800 text-white',
    },
    organization: {
        eyebrow: 'Pengaturan',
        description: 'Atur member, role, dan group organisasi.',
        tone: 'bg-emerald-800 text-white',
    },
    admin: {
        eyebrow: 'Platform',
        description: 'Pantau tenant, kategori, dan kondisi sistem.',
        tone: 'bg-slate-900 text-white',
    },
};

const meta = sectionMeta[props.section] ?? {
    eyebrow: 'Workspace',
    description: 'Kelola data Kuesify.',
    tone: 'bg-teal-800 text-white',
};
const rows = Array.isArray(props.items) ? props.items : props.items.data || [];
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header
            ><div>
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700"
                >
                    {{ meta.eyebrow }}
                </p>
                <h2 class="mt-1 text-xl font-extrabold text-teal-950">
                    {{ title }}
                </h2>
            </div></template
        >
        <main class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-3xl p-6 sm:p-8" :class="meta.tone">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p
                            class="text-sm font-bold uppercase tracking-wide opacity-75"
                        >
                            {{ meta.eyebrow }}
                        </p>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight">
                            {{ title }}
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 opacity-90">
                            {{ meta.description }}
                        </p>
                    </div>
                    <a
                        v-if="meta.action"
                        :href="meta.action[1]"
                        class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-extrabold text-slate-900 transition hover:bg-slate-100"
                        >{{ meta.action[0] }}</a
                    >
                </div>
            </section>

            <section
                v-if="Object.keys(summary).length"
                class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
            >
                <article
                    v-for="(value, key) in summary"
                    :key="String(key)"
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-wide text-slate-500"
                    >
                        {{ String(key).replaceAll('_', ' ') }}
                    </p>
                    <div
                        v-if="typeof value === 'object' && value !== null"
                        class="mt-3 space-y-1.5 text-xs text-slate-700"
                    >
                        <div
                            v-for="(subVal, subKey) in value as Record<
                                string,
                                unknown
                            >"
                            :key="String(subKey)"
                            class="flex items-center justify-between border-b border-slate-50 py-0.5 last:border-none"
                        >
                            <span class="font-medium text-slate-500">{{
                                String(subKey).replaceAll('_', ' ')
                            }}</span>
                            <span
                                v-if="subKey === 'reverb_status'"
                                :class="
                                    subVal === 'online'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-rose-100 text-rose-800'
                                "
                                class="rounded-full px-2 py-0.5 font-extrabold"
                            >
                                {{ subVal }}
                            </span>
                            <span v-else class="font-bold text-slate-900">{{
                                String(subVal ?? '-')
                            }}</span>
                        </div>
                    </div>
                    <p
                        v-else
                        class="mt-3 break-words text-2xl font-extrabold text-slate-900"
                    >
                        {{ value }}
                    </p>
                </article>
            </section>

            <section
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4"
                >
                    <div>
                        <h3 class="font-extrabold text-slate-900">
                            Data workspace
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ rows.length }} item pada halaman ini.
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800"
                        >{{ section }}</span
                    >
                </div>
                <div v-if="rows.length" class="divide-y divide-slate-100">
                    <article
                        v-for="item in rows"
                        :key="String(item.id)"
                        class="flex min-h-20 flex-col justify-center gap-2 px-5 py-4 transition hover:bg-teal-50/40 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-extrabold text-slate-900">
                                {{
                                    item.title ||
                                    item.prompt ||
                                    item.name ||
                                    item.alias ||
                                    `#${item.id}`
                                }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{
                                    item.email ||
                                    item.type ||
                                    item.status ||
                                    'Data workspace'
                                }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="item.status || item.role"
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                                >{{
                                    item.status ||
                                    roleLabel(item.role as string | undefined)
                                }}</span
                            ><span class="text-sm font-bold text-teal-800">{{
                                item.score ??
                                item.questions_count ??
                                item.members_count ??
                                ''
                            }}</span>
                        </div>
                    </article>
                </div>
                <div v-else class="px-5 py-12 text-center">
                    <div
                        class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-teal-50 text-lg font-extrabold text-teal-700"
                    >
                        +
                    </div>
                    <h3 class="mt-4 font-extrabold text-slate-900">
                        Belum ada data
                    </h3>
                    <p
                        class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500"
                    >
                        Mulai dari langkah kecil. Data baru akan muncul di
                        halaman ini.
                    </p>
                    <Link
                        href="/dashboard"
                        class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-teal-700 px-4 text-sm font-extrabold text-white hover:bg-teal-800"
                        >Kembali ke dashboard</Link
                    >
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
