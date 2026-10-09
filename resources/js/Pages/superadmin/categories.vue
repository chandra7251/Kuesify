<script setup lang="ts">
import AppIcon from "@/Components/AppIcon.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

type Organization = {
    id: number;
    name: string;
    slug: string;
    members_count: number;
};

type Category = {
    id: number;
    name: string;
    theme_key?: string | null;
};

const props = defineProps<{
    organizations: { data: Organization[] };
    categories: Category[];
    health: Record<string, number>;
}>();

const searchQuery = ref("");

const showCreateModal = ref(false);
const editingCategory = ref<Category | null>(null);

const form = useForm({
    name: "",
    theme_key: "",
});

function openCreateModal() {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
}

function openEditModal(category: Category) {
    editingCategory.value = category;
    form.name = category.name;
    form.theme_key = category.theme_key ?? "";
    form.clearErrors();
    showCreateModal.value = true;
}

function closeModal() {
    showCreateModal.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
}

function submitForm() {
    if (editingCategory.value) {
        form.put(route('superadmin.categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('superadmin.categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function confirmDelete(category: Category) {
    if (confirm(`Apakah kamu yakin ingin menghapus kategori "${category.name}"?`)) {
        router.delete(route('superadmin.categories.destroy', category.id));
    }
}

const filteredCategories = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return props.categories;
    return props.categories.filter(
        (cat) =>
            cat.name.toLowerCase().includes(query) ||
            (cat.theme_key && cat.theme_key.toLowerCase().includes(query)),
    );
});

const themedCount = computed(
    () => props.categories.filter((cat) => Boolean(cat.theme_key)).length,
);

const totalTenants = computed(
    () => props.organizations?.data?.length ?? 0,
);
</script>

<template>
    <Head title="Kategori Global" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4rem)] bg-brand-accent/20 py-6 sm:py-8">
            <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Hero Banner ala Siswa -->
                <section
                    class="relative overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-figma-sm sm:rounded-3xl sm:p-8"
                >
                    <div
                        class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5 blur-2xl"
                    />
                    <div
                        class="absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-brand-secondary/15 blur-2xl"
                    />

                    <div
                        class="relative z-10 grid gap-6 lg:grid-cols-[1fr_20rem] lg:items-center"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full border border-brand-secondary/30 bg-white/10 px-3 py-1 text-xs font-bold text-brand-secondary backdrop-blur-sm"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-brand-secondary"
                                    />
                                    SUPER ADMIN • PLATFORM
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/90 backdrop-blur-sm"
                                >
                                    <AppIcon
                                        name="categories"
                                        :size="14"
                                        class="text-brand-secondary"
                                    />
                                    Taksonomi Kategori
                                </span>
                            </div>

                            <h1
                                class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl"
                            >
                                Kategori Global
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base"
                            >
                                Kelola taksonomi kategori materi dan tema visual
                                terpadu yang dapat digunakan oleh seluruh kreator
                                kuis di setiap tenant organisasi.
                            </p>
                        </div>

                        <!-- CTA Tambah Kategori Baru -->
                        <div class="flex items-center justify-start lg:justify-end">
                            <button
                                type="button"
                                @click="openCreateModal"
                                class="inline-flex items-center gap-2 rounded-2xl bg-brand-secondary px-5 py-3 text-sm font-black text-slate-900 shadow-md transition hover:bg-brand-secondary/90 hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <AppIcon name="plus" :size="18" />
                                Tambah Kategori Baru
                            </button>
                        </div>
                    </div>
                </section>

                <!-- KPI / Stats Row ala Siswa -->
                <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary"
                        >
                            <AppIcon name="categories" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Total Kategori
                            </p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">
                                {{ categories.length }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/30 text-brand-primary"
                        >
                            <AppIcon name="ai" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Tema Khusus
                            </p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">
                                {{ themedCount }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700"
                        >
                            <AppIcon name="organization" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Tenant Terhubung
                            </p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">
                                {{ totalTenants }}
                            </p>
                        </div>
                    </article>

                    <article
                        class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
                        >
                            <AppIcon name="platform" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500">
                                Sistem Job
                            </p>
                            <p class="text-lg font-black text-slate-900 sm:text-xl">
                                {{ health.queued_jobs ?? 0 }}
                            </p>
                        </div>
                    </article>
                </section>

                <!-- Main Card: Koleksi Kategori Global -->
                <section
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-figma-sm sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-primary text-white"
                            >
                                <AppIcon name="categories" :size="18" />
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Daftar Kategori Global
                                </h2>
                                <p class="text-xs font-semibold text-slate-500">
                                    Label taksonomi dan tema yang aktif di
                                    seluruh platform
                                </p>
                            </div>
                        </div>

                        <!-- Search Filter -->
                        <div class="w-full sm:w-72">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama atau tema kategori..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 placeholder-slate-400 transition focus:border-brand-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
                            />
                        </div>
                    </div>

                    <!-- Grid Card Kategori ala Siswa -->
                    <div
                        v-if="filteredCategories.length > 0"
                        class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="item in filteredCategories"
                            :key="item.id"
                            class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/50 hover:shadow-figma-sm"
                        >
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white"
                                        >
                                            <AppIcon
                                                name="categories"
                                                :size="20"
                                            />
                                        </div>
                                        <div class="min-w-0">
                                            <h3
                                                class="truncate text-base font-black text-slate-900 group-hover:text-brand-primary"
                                            >
                                                {{ item.name }}
                                            </h3>
                                            <p
                                                class="truncate text-xs font-bold text-slate-400"
                                            >
                                                Kategori #{{ item.id }}
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500"
                                    >
                                        ID: {{ item.id }}
                                    </span>

                                    <div class="flex items-center gap-1.5 rounded-xl border border-slate-100 bg-slate-50 p-1">
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-white hover:text-brand-primary hover:shadow-xs transition"
                                            title="Edit Kategori"
                                        >
                                            <AppIcon name="pencil" :size="15" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="confirmDelete(item)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                            title="Hapus Kategori"
                                        >
                                            <AppIcon name="trash" :size="15" />
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/80 p-3"
                                >
                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
                                        <span class="font-bold text-slate-500">
                                            Tema Visual
                                        </span>
                                        <span
                                            v-if="item.theme_key"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-brand-secondary/60 bg-brand-secondary/30 px-2.5 py-0.5 text-xs font-black text-brand-dark"
                                        >
                                            <AppIcon name="ai" :size="13" />
                                            {{ item.theme_key }}
                                        </span>
                                        <span
                                            v-else
                                            class="font-semibold text-slate-400 italic text-[11px]"
                                        >
                                            Tema Standar
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 font-bold text-emerald-600"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-emerald-500"
                                    />
                                    Lintas Tenant
                                </span>
                                <span class="font-bold text-slate-400">
                                    Aktif Global
                                </span>
                            </div>
                        </article>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="my-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-secondary/20 text-brand-primary"
                        >
                            <AppIcon name="categories" :size="28" />
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-800">
                            {{
                                searchQuery
                                    ? "Kategori Tidak Ditemukan"
                                    : "Belum Ada Kategori"
                            }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 max-w-sm">
                            {{
                                searchQuery
                                    ? `Tidak ada kategori dengan kata kunci "${searchQuery}". Coba gunakan istilah lain.`
                                    : "Belum ada kategori global yang ditambahkan ke dalam sistem platform."
                            }}
                        </p>
                    </div>
                </section>
            </main>
        </div>

        <!-- Modal Form Create / Edit Category -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-900">
                        {{ editingCategory ? "Edit Kategori" : "Tambah Kategori Baru" }}
                    </h3>
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Nama Kategori</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Contoh: Matematika Dasar"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-800 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">Tema Visual (Opsional)</label>
                        <input
                            v-model="form.theme_key"
                            type="text"
                            placeholder="Contoh: math, science, history"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-800 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20"
                        />
                        <p v-if="form.errors.theme_key" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ form.errors.theme_key }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-brand-primary px-5 py-2 text-xs font-bold text-white hover:bg-brand-primary/90 disabled:opacity-50"
                        >
                            {{ form.processing ? "Menyimpan..." : (editingCategory ? "Perbarui" : "Simpan") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
