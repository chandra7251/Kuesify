<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Material = {
    id: number;
    original_name: string;
    visibility: 'organization' | 'public';
    version: number;
    page_count: number | null;
    extracted_text: string | null;
    updated_at: string;
    creator: { id: number; name: string } | null;
};

defineProps<{
    material: Material;
    note: string | null;
}>();

function printPage(): void {
    window.print();
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}
</script>

<template>
    <Head :title="`Cetak ${material.original_name}`" />

    <main
        class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 print:min-h-0 print:bg-white print:px-0 print:py-0"
    >
        <div class="mx-auto max-w-4xl print:max-w-none">
            <section
                class="mb-5 flex flex-wrap items-center justify-between gap-3 print:hidden"
            >
                <Link
                    href="/participant/materials"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                >
                    Kembali materi
                </Link>
                <button
                    type="button"
                    class="rounded-xl bg-brand-primary px-4 py-2 text-sm font-bold text-white hover:bg-brand-hover"
                    @click="printPage"
                >
                    Cetak / Simpan sebagai PDF
                </button>
            </section>

            <article
                class="rounded-2xl bg-white p-6 shadow-figma print:rounded-none print:shadow-none"
            >
                <header class="border-b border-slate-200 pb-5">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-brand-primary"
                    >
                        {{
                            material.visibility === 'public'
                                ? 'Publik'
                                : 'Organisasi'
                        }}
                    </p>
                    <h1 class="mt-2 text-3xl font-black text-slate-950">
                        {{ material.original_name }}
                    </h1>
                    <div
                        class="mt-3 flex flex-wrap gap-3 text-sm font-semibold text-slate-500"
                    >
                        <span v-if="material.creator"
                            >Oleh {{ material.creator.name }}</span
                        >
                        <span>Versi {{ material.version }}</span>
                        <span
                            >Diperbarui
                            {{ formatDate(material.updated_at) }}</span
                        >
                        <span v-if="material.page_count"
                            >{{ material.page_count }} halaman</span
                        >
                    </div>
                </header>

                <section
                    class="prose prose-slate mt-6 max-w-none whitespace-pre-wrap text-sm leading-7 text-slate-800"
                >
                    {{
                        material.extracted_text ||
                        'Belum ada teks hasil ekstraksi untuk dicetak.'
                    }}
                </section>

                <section
                    v-if="note"
                    class="mt-8 rounded-xl bg-brand-accent p-4 print:border print:border-slate-200 print:bg-white"
                >
                    <h2
                        class="text-sm font-black uppercase tracking-[0.14em] text-brand-primary"
                    >
                        Catatan pribadi
                    </h2>
                    <p
                        class="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-800"
                    >
                        {{ note }}
                    </p>
                </section>
            </article>
        </div>
    </main>
</template>
