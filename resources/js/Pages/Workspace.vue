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
        tone: 'bg-white text-slate-900',
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
const isQuestions = props.section === 'questions';
const rows = Array.isArray(props.items) ? props.items : props.items.data || [];
const summarySize = (value: unknown) =>
    typeof value === 'object' && value !== null ? Object.keys(value).length : 0;
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template v-if="!isQuestions" #header
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
            <section
                class="p-6 sm:p-7"
                :class="[
                    meta.tone,
                    isQuestions
                        ? 'rounded-xl border border-slate-200 shadow-sm'
                        : 'rounded-3xl',
                ]"
            >
                <div class="flex flex-wrap items-center justify-between gap-5">
                    <div>
                        <p
                            class="text-xs font-extrabold uppercase tracking-[0.18em]"
                            :class="
                                isQuestions ? 'text-teal-700' : 'opacity-75'
                            "
                        >
                            {{ meta.eyebrow }}
                        </p>
                        <h1
                            class="mt-2 font-extrabold tracking-tight"
                            :class="
                                isQuestions
                                    ? 'text-2xl sm:text-3xl'
                                    : 'text-3xl sm:text-4xl'
                            "
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6"
                            :class="
                                isQuestions ? 'text-slate-600' : 'opacity-90'
                            "
                        >
                            {{ meta.description }}
                        </p>
                    </div>
                    <a
                        v-if="meta.action"
                        :href="meta.action[1]"
                        class="inline-flex min-h-11 items-center justify-center px-4 text-sm font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                        :class="
                            isQuestions
                                ? 'rounded-md bg-[#3451b5] text-white hover:bg-[#29439d] focus-visible:outline-[#3451b5]'
                                : 'rounded-xl bg-white text-slate-900 hover:bg-slate-100 focus-visible:outline-white'
                        "
                    >
                        {{ meta.action[0] }}
                    </a>
                </div>
            </section>

            <section
                v-if="Object.keys(summary).length"
                class="grid sm:grid-cols-2"
                :class="
                    isQuestions
                        ? 'gap-4 lg:grid-cols-3'
                        : 'gap-3 lg:grid-cols-4'
                "
            >
                <article
                    v-for="(value, key) in summary"
                    :key="String(key)"
                    class="border bg-white"
                    :class="
                        isQuestions
                            ? 'min-h-52 rounded-xl border-t-4 border-slate-200 border-t-[#2dd4bf] p-5 shadow-[0_2px_6px_rgba(15,23,42,0.08)]'
                            : 'rounded-2xl border-slate-100 p-4 shadow-sm'
                    "
                >
                    <div class="flex items-center justify-between gap-3">
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500"
                        >
                            {{ String(key).replaceAll('_', ' ') }}
                        </p>
                        <span
                            v-if="
                                isQuestions &&
                                typeof value === 'object' &&
                                value !== null
                            "
                            class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold tabular-nums text-teal-800"
                        >
                            {{ summarySize(value) }}
                        </span>
                    </div>
                    <div
                        v-if="
                            typeof value === 'object' &&
                            value !== null &&
                            (!isQuestions || summarySize(value))
                        "
                        class="mt-3 text-xs text-slate-700"
                        :class="isQuestions ? 'space-y-2' : 'space-y-1.5'"
                    >
                        <div
                            v-for="(subVal, subKey) in value as Record<
                                string,
                                unknown
                            >"
                            :key="String(subKey)"
                            class="flex items-center justify-between"
                            :class="
                                isQuestions
                                    ? 'rounded-lg bg-slate-50 px-3 py-2'
                                    : 'border-b border-slate-50 py-0.5 last:border-none'
                            "
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
                    <div
                        v-else-if="
                            isQuestions &&
                            typeof value === 'object' &&
                            value !== null
                        "
                        class="grid min-h-32 place-items-center text-center"
                    >
                        <div>
                            <svg
                                class="mx-auto h-8 w-8 text-slate-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 7h14v12H5zM8 4h8v3M9 12h6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p
                                class="mt-2 text-sm font-semibold text-slate-500"
                            >
                                Belum ada data
                            </p>
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
                class="overflow-hidden border bg-white"
                :class="
                    isQuestions
                        ? 'rounded-xl border-slate-200 shadow-[0_2px_7px_rgba(15,23,42,0.09)]'
                        : 'rounded-2xl border-slate-100 shadow-sm'
                "
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100"
                    :class="isQuestions ? 'bg-white px-6 py-5' : 'px-5 py-4'"
                >
                    <div>
                        <h2 class="font-extrabold text-slate-900">
                            Data workspace
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ rows.length }} item pada halaman ini.
                        </p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold text-teal-800"
                        :class="isQuestions ? 'bg-teal-100' : 'bg-teal-50'"
                        >{{ section }}</span
                    >
                </div>
                <div
                    v-if="rows.length"
                    :class="
                        isQuestions
                            ? 'divide-y divide-slate-100 bg-white'
                            : 'divide-y divide-slate-100'
                    "
                >
                    <article
                        v-for="item in rows"
                        :key="String(item.id)"
                        class="group flex min-h-20 gap-3 transition-colors duration-150"
                        :class="
                            isQuestions
                                ? 'question-row items-center border-l-4 border-l-transparent bg-white px-6 py-5'
                                : 'flex-col justify-center border-l-2 border-l-transparent px-5 py-4 hover:bg-teal-50/40 sm:flex-row sm:items-center sm:justify-between'
                        "
                    >
                        <div
                            class="min-w-0 flex-1"
                            :class="
                                isQuestions &&
                                'sm:flex sm:items-center sm:justify-between sm:gap-6'
                            "
                        >
                            <p class="truncate font-extrabold text-slate-900">
                                {{
                                    item.title ||
                                    item.prompt ||
                                    item.name ||
                                    item.alias ||
                                    `#${item.id}`
                                }}
                            </p>
                            <p
                                :class="
                                    isQuestions
                                        ? 'question-type mt-2 inline-flex shrink-0 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800 sm:mt-0'
                                        : 'mt-1 text-sm text-slate-500'
                                "
                            >
                                {{
                                    item.email ||
                                    item.type ||
                                    item.status ||
                                    'Data workspace'
                                }}
                            </p>
                        </div>
                        <div
                            class="flex items-center gap-2"
                            :class="isQuestions && 'ml-auto'"
                        >
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

<style scoped>
.question-row:hover {
    border-left-color: #2dd4bf;
    background-color: rgb(240 253 250 / 0.6);
}

.question-row:hover .question-type {
    background-color: #ccfbf1;
    color: #134e4a;
}
</style>
