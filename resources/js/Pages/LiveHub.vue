<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    sessions: {
        data: {
            id: number;
            pin: string;
            status: string;
            lobby_locked: boolean;
            quiz: { title: string };
            participants: unknown[];
        }[];
    };
    quizzes: { id: number; title: string }[];
}>();
const form = useForm({
    quiz_id: '',
    question_duration: 30,
    speed_multiplier: 10,
});
function openSession(): void {
    form.post(route('live-sessions.store'));
}
</script>

<template>
    <Head title="Live Quiz" />
    <AuthenticatedLayout>
        <template #header
            ><div>
                <p
                    class="text-xs font-bold uppercase tracking-[0.18em] text-orange-700"
                >
                    Live mode
                </p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-950">
                    Live Quiz
                </h2>
            </div></template
        >
        <main class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-orange-700 p-6 text-white sm:p-8">
                <p
                    class="text-sm font-bold uppercase tracking-wide text-orange-100"
                >
                    Host control
                </p>
                <h1 class="mt-2 text-3xl font-extrabold">Buka sesi baru.</h1>
                <form
                    class="mt-6 grid gap-3 md:grid-cols-[1fr_10rem_10rem_auto]"
                    @submit.prevent="openSession"
                >
                    <select
                        v-model="form.quiz_id"
                        class="min-h-12 rounded-xl border-0 text-slate-900 focus:ring-2 focus:ring-amber-300"
                    >
                        <option value="">Pilih kuis published</option>
                        <option
                            v-for="quiz in quizzes"
                            :key="quiz.id"
                            :value="quiz.id"
                        >
                            {{ quiz.title }}
                        </option></select
                    ><input
                        v-model.number="form.question_duration"
                        min="5"
                        max="300"
                        type="number"
                        aria-label="Durasi per soal"
                        class="min-h-12 rounded-xl border-0 text-slate-900"
                    /><input
                        v-model.number="form.speed_multiplier"
                        min="0"
                        max="1000"
                        type="number"
                        aria-label="Speed multiplier"
                        class="min-h-12 rounded-xl border-0 text-slate-900"
                    /><button
                        class="min-h-12 rounded-xl bg-amber-400 px-5 text-sm font-extrabold text-amber-950 hover:bg-amber-300"
                        :disabled="form.processing"
                    >
                        Buka lobby
                    </button>
                </form>
                <p
                    v-if="form.errors.quiz_id"
                    class="mt-2 text-sm font-bold text-amber-100"
                >
                    {{ form.errors.quiz_id }}
                </p>
            </section>
            <section
                class="rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-extrabold text-slate-900">Sesi terbaru</h3>
                </div>
                <div
                    v-if="sessions.data.length"
                    class="divide-y divide-slate-100"
                >
                    <article
                        v-for="session in sessions.data"
                        :key="session.id"
                        class="flex flex-wrap items-center justify-between gap-3 px-5 py-4"
                    >
                        <div>
                            <p class="font-extrabold text-slate-900">
                                {{ session.quiz.title }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                PIN
                                <strong
                                    class="tracking-[0.2em] text-slate-800"
                                    >{{ session.pin }}</strong
                                >
                                · {{ session.participants.length }} peserta
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-800"
                                >{{ session.status }}</span
                            ><Link
                                :href="route('live-sessions.play', session.id)"
                                class="inline-flex min-h-11 items-center rounded-xl bg-slate-900 px-4 text-sm font-extrabold text-white"
                                >Kelola</Link
                            >
                        </div>
                    </article>
                </div>
                <p v-else class="px-5 py-10 text-center text-sm text-slate-500">
                    Belum ada lobby atau sesi live.
                </p>
            </section>
            <Link
                href="/join"
                class="inline-flex min-h-11 items-center text-sm font-extrabold text-teal-700 hover:text-teal-900"
                >Masuk sebagai peserta dengan PIN</Link
            >
        </main>
    </AuthenticatedLayout>
</template>
