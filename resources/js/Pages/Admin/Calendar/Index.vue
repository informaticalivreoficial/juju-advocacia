<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    events: Array,
    month: Number,
    year: Number,
    monthName: String,
    prev: Object,
    next: Object,
    types: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const typeLabels = Object.fromEntries(props.types.map((item) => [item.value, item.label]));
const typeColor = (type) => ({
    hearing: 'indigo',
    appointment: 'green',
    meeting: 'amber',
    other: 'slate',
}[type] ?? 'slate');

const weekDays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

const localDateKey = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

const eventTime = (event) => {
    if (event.all_day) return 'Dia todo';
    const date = new Date(event.start_datetime);
    return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const eventsFor = (iso) =>
    props.events
        .filter((event) => localDateKey(new Date(event.start_datetime)) === iso)
        .sort((a, b) => new Date(a.start_datetime) - new Date(b.start_datetime));

const days = computed(() => {
    const firstDay = new Date(props.year, props.month - 1, 1);
    const startWeekday = (firstDay.getDay() + 6) % 7;
    const daysInMonth = new Date(props.year, props.month, 0).getDate();

    const cells = [];
    for (let i = 0; i < startWeekday; i += 1) {
        cells.push(null);
    }
    for (let d = 1; d <= daysInMonth; d += 1) {
        const iso = `${props.year}-${String(props.month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        cells.push({ date: d, iso, events: eventsFor(iso) });
    }
    while (cells.length % 7 !== 0) {
        cells.push(null);
    }

    return cells;
});

const todayIso = localDateKey(new Date());
const today = ref(todayIso);

const navigate = (target) => {
    router.get(route('admin.calendar.index'), {
        month: target.month,
        year: target.year,
    }, { preserveScroll: true, replace: true });
};

const deleting = ref(null);

const deleteEvent = () => {
    router.delete(route('admin.calendar.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const formatDateTime = (value) => {
    if (!value) return '—';
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};
</script>

<template>
    <AdminLayout>
        <Head title="Agenda" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Agenda</h1>
                    <p class="mt-1 text-sm text-slate-500">Audiências, compromissos e reuniões do escritório.</p>
                </div>
                <Link
                    v-if="can('calendar.create')"
                    :href="route('admin.calendar.create')"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Novo evento
                </Link>
            </div>

            <Card>
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-bold capitalize text-slate-900">{{ monthName }} {{ year }}</h2>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="navigate(prev)"
                        >
                            ← Mês anterior
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="navigate({ month: new Date().getMonth() + 1, year: new Date().getFullYear() })"
                        >
                            Hoje
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="navigate(next)"
                        >
                            Próximo mês →
                        </button>
                    </div>
                </div>

                <div class="mb-2 hidden grid-cols-7 gap-px md:grid">
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="px-2 py-1 text-center text-xs font-semibold uppercase tracking-wider text-slate-500"
                    >
                        {{ day }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-7 md:gap-px md:rounded-lg md:border md:border-slate-200 md:bg-slate-200">
                    <div
                        v-for="(cell, index) in days"
                        :key="index"
                        :class="[
                            'rounded-lg border border-slate-200 bg-white p-2 md:min-h-24 md:rounded-none md:border-0',
                            cell?.iso === today ? 'bg-indigo-50/60' : '',
                        ]"
                    >
                        <template v-if="cell">
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'flex h-6 w-6 items-center justify-center rounded-full text-sm font-medium',
                                        cell.iso === today ? 'bg-indigo-600 text-white' : 'text-slate-700',
                                    ]"
                                >
                                    {{ cell.date }}
                                </span>
                                <Link
                                    v-if="can('calendar.create')"
                                    :href="route('admin.calendar.create', { date: cell.iso })"
                                    class="rounded p-0.5 text-slate-300 transition hover:text-indigo-500"
                                    title="Adicionar evento neste dia"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </Link>
                            </div>

                            <div class="mt-1 space-y-1">
                                <Link
                                    v-for="event in cell.events.slice(0, 3)"
                                    :key="event.id"
                                    v-if="can('calendar.update')"
                                    :href="route('admin.calendar.edit', event.id)"
                                    class="block truncate rounded px-1.5 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-indigo-100 text-indigo-700': event.type === 'hearing',
                                        'bg-green-100 text-green-700': event.type === 'appointment',
                                        'bg-amber-100 text-amber-700': event.type === 'meeting',
                                        'bg-slate-100 text-slate-600': event.type === 'other',
                                    }"
                                    :title="`${event.title} — ${eventTime(event)}`"
                                >
                                    {{ eventTime(event) }} {{ event.title }}
                                </Link>
                                <div
                                    v-if="cell.events.length > 3"
                                    class="rounded px-1.5 py-0.5 text-xs font-medium text-slate-500"
                                >
                                    +{{ cell.events.length - 3 }} eventos
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Eventos do mês</h2>
                <div v-if="events.length > 0">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="event in events" :key="event.id" class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <Badge :color="typeColor(event.type)">{{ typeLabels[event.type] ?? event.type }}</Badge>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ event.title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ formatDateTime(event.start_datetime) }}
                                        <template v-if="event.process">
                                            · <Link :href="route('admin.processes.show', event.process.id)" class="hover:text-indigo-600 hover:underline">{{ event.process.title }}</Link>
                                        </template>
                                        <template v-if="event.client">
                                            · {{ event.client.name || event.client.company_name }}
                                        </template>
                                        <template v-if="event.location">
                                            · {{ event.location }}
                                        </template>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 sm:shrink-0">
                                <Link
                                    v-if="can('calendar.update')"
                                    :href="route('admin.calendar.edit', event.id)"
                                    class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                    title="Editar"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </Link>
                                <button
                                    v-if="can('calendar.delete')"
                                    type="button"
                                    class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                    title="Excluir"
                                    @click="deleting = event"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
                <EmptyState
                    v-else
                    title="Nenhum evento neste mês"
                    description="Adicione audiências, reuniões e compromissos para acompanhar na agenda."
                />
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir evento"
            :message="`Esta ação removerá o evento ${deleting?.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteEvent"
            @close="deleting = null"
        />
    </AdminLayout>
</template>