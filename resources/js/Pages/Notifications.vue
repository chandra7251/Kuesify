<script setup lang="ts">
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

type NotificationItem = {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
};
const items = ref<NotificationItem[]>([]);
const unreadCount = ref(0);
const loading = ref(true);
function title(item: NotificationItem): string {
    return String(
        item.data.message ??
            item.data.title ??
            item.type.split('\\').pop() ??
            'Notifikasi',
    );
}
async function load(): Promise<void> {
    const response = await fetch(route('notifications.index'), {
        headers: { Accept: 'application/json' },
    });
    if (response.ok) {
        const payload = (await response.json()) as {
            data: NotificationItem[];
            unread_count: number;
        };
        items.value = payload.data;
        unreadCount.value = payload.unread_count;
    }
    loading.value = false;
}
function markRead(item: NotificationItem): void {
    if (item.read_at) return;
    router.patch(
        route('notifications.read', item.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                item.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            },
        },
    );
}
onMounted(load);
</script>

<template>
    <Head title="Notifikasi" />
    <AuthenticatedLayout>
        <main class="min-h-full bg-brand-accent/30 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl space-y-5">
                <section
                    class="relative overflow-hidden rounded-3xl bg-brand-primary p-6 text-white shadow-figma sm:p-8"
                >
                    <div
                        class="pointer-events-none absolute -right-10 -top-12 h-36 w-36 rounded-full bg-brand-secondary/20"
                    />
                    <div class="relative flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-brand-secondary"
                            >
                                Pusat notifikasi
                            </p>
                            <h1
                                class="mt-2 text-3xl font-black tracking-tight sm:text-4xl"
                            >
                                Notifikasi
                            </h1>
                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-white/75"
                            >
                                Pantau kabar terbaru tentang kuis, workspace,
                                dan aktivitas akunmu.
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-brand-secondary px-3 py-1.5 text-sm font-black text-brand-dark"
                            >{{ unreadCount }} belum dibaca</span
                        >
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-figma"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2 class="font-black text-slate-950">
                                Aktivitas terbaru
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Informasi penting untuk langkah berikutnya.
                            </p>
                        </div>
                        <AppIcon
                            name="notifications"
                            class="h-5 w-5 text-brand-primary"
                            :stroke-width="2"
                        />
                    </div>
                    <div class="p-4">
                        <p v-if="loading" class="p-5 text-sm text-slate-500">
                            Memuat notifikasi…
                        </p>
                        <p
                            v-else-if="items.length === 0"
                            class="flex min-h-56 flex-col items-center justify-center rounded-xl bg-brand-accent/40 px-5 text-center"
                        >
                            <span
                                class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-primary text-white shadow-sm"
                            >
                                <AppIcon
                                    name="notifications"
                                    class="h-6 w-6"
                                    :stroke-width="2"
                                />
                            </span>
                            <span class="mt-4 font-black text-slate-900"
                                >Belum ada notifikasi</span
                            >
                            <span class="mt-1 text-sm text-slate-500"
                                >Semua aktivitas baru akan muncul di sini.</span
                            >
                        </p>
                        <button
                            v-for="item in items"
                            v-else
                            :key="item.id"
                            type="button"
                            class="group flex w-full items-start gap-3 rounded-xl border-b border-slate-100 p-4 text-left transition last:border-0 hover:bg-brand-accent/30"
                            :class="
                                item.read_at
                                    ? 'opacity-60'
                                    : 'bg-brand-accent/20'
                            "
                            @click="markRead(item)"
                        >
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"
                                :class="
                                    item.read_at
                                        ? 'bg-slate-300'
                                        : 'bg-brand-secondary'
                                "
                            />
                            <span class="min-w-0 flex-1">
                                <p class="font-bold text-slate-900">
                                    {{ title(item) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{
                                        new Date(
                                            item.created_at,
                                        ).toLocaleString('id-ID')
                                    }}
                                </p>
                            </span>
                        </button>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
