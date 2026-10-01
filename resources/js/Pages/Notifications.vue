<script setup lang="ts">
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
        <main class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <p
                    class="text-xs font-bold uppercase tracking-[0.16em] text-brand-primary"
                >
                    Pusat notifikasi
                </p>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <h1 class="text-3xl font-extrabold text-slate-900">
                        Notifikasi
                    </h1>
                    <span
                        class="rounded-full bg-brand-secondary px-3 py-1 text-sm font-bold"
                        >{{ unreadCount }} belum dibaca</span
                    >
                </div>
            </section>
            <section class="rounded-2xl bg-white p-4 shadow-sm">
                <p v-if="loading" class="p-5 text-sm text-slate-500">
                    Memuat notifikasi…
                </p>
                <p
                    v-else-if="items.length === 0"
                    class="p-5 text-center text-sm text-slate-500"
                >
                    Belum ada notifikasi.
                </p>
                <button
                    v-for="item in items"
                    v-else
                    :key="item.id"
                    type="button"
                    class="block w-full border-b border-slate-100 p-4 text-left last:border-0 hover:bg-slate-50"
                    :class="item.read_at ? 'opacity-60' : ''"
                    @click="markRead(item)"
                >
                    <p class="font-bold text-slate-900">{{ title(item) }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ new Date(item.created_at).toLocaleString('id-ID') }}
                    </p>
                </button>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
