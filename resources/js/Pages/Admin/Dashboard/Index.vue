<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    stats: Object,
    upcomingDeadlines: Array,
    upcomingEvents: Array,
    recentProcesses: Array,
});

const user = computed(() => usePage().props.auth.user ?? {});

const firstName = computed(() => {
    const name = user.value.name ?? '';
    return name.split(' ')[0];
});

const today = computed(() => {
    const date = new Date();
    const options = {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    };
    return date.toLocaleDateString('pt-BR', options);
});

const stats = computed(() => [
    {
        label: 'Clientes ativos',
        value: props.stats?.activeClients ?? 0,
        hint: `${props.stats?.clients ?? 0} no total`,
        href: 'admin.clients.index',
        icon: 'clients',
    },
    {
        label: 'Processos ativos',
        value: props.stats?.activeProcesses ?? 0,
        hint: `${props.stats?.processes ?? 0} no total`,
        href: 'admin.processes.index',
        icon: 'processes',
    },
    {
        label: 'Prazos pendentes',
        value: props.stats?.deadlinesPending ?? 0,
        hint: `${props.stats?.deadlinesToday ?? 0} vencem hoje`,
        href: 'admin.deadlines.index',
        icon: 'deadlines',
    },
    {
        label: 'Tarefas pendentes',
        value: props.stats?.tasksPending ?? 0,
        hint: `${props.stats?.tasks ?? 0} no total`,
        href: 'admin.tasks.index',
        icon: 'tasks',
    },
]);

const icons = {
    clients: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    processes: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    deadlines: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    tasks: 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75',
};

const deadlineVisual = (deadline) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(`${deadline.due_date}T00:00:00`);
    const diffDays = Math.round((due - today) / 86400000);

    if (diffDays < 0) return { label: 'Vencido', color: 'red' };
    if (diffDays === 0) return { label: 'Vence hoje', color: 'red' };
    if (diffDays === 1) return { label: 'Vence amanhã', color: 'orange' };
    if (diffDays <= 3) return { label: `Em ${diffDays} dias`, color: 'amber' };
    return { label: `Em ${diffDays} dias`, color: 'brand' };
};

const formatDate = (value) => {
    if (!value) return '—';
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
};

const eventTime = (event) => {
    if (event.all_day) return 'Dia todo';
    const date = new Date(event.start_datetime);
    return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const eventDate = (event) => {
    const date = new Date(event.start_datetime);
    const options = { weekday: 'short', day: 'numeric', month: 'short' };
    return date.toLocaleDateString('pt-BR', options);
};

const eventTypeColor = (type) => ({
    hearing: 'brand',
    appointment: 'green',
    meeting: 'amber',
    other: 'slate',
}[type] ?? 'slate');

const processStatusColor = (status) => ({
    analysis: 'amber',
    active: 'green',
    awaiting_decision: 'brand',
    suspended: 'gray',
    archived: 'slate',
    closed: 'gray',
}[status] ?? 'gray');

const eventTypeLabels = {
    hearing: 'Audiência',
    appointment: 'Compromisso',
    meeting: 'Reunião',
    other: 'Outro',
};

const processStatusLabels = {
    analysis: 'Em análise',
    active: 'Ativo',
    awaiting_decision: 'Aguardando decisão',
    suspended: 'Suspenso',
    archived: 'Arquivado',
    closed: 'Encerrado',
};

const areaLabels = {
    civil: 'Cível',
    labor: 'Trabalhista',
    family: 'Família',
    criminal: 'Criminal',
    business: 'Empresarial',
    tax: 'Tributário',
    consumer: 'Consumidor',
    social_security: 'Previdenciário',
    other: 'Outro',
};

const clientName = (process) => {
    if (!process.client) return '—';
    if (process.client.type === 'company') {
        return process.client.company_name ?? process.client.name;
    }
    return process.client.name;
};
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">Bem-vindo(a), {{ firstName }}</h1>
                <p class="mt-1 text-sm capitalize text-slate-500">{{ today }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Link
                    v-for="stat in stats"
                    :key="stat.label"
                    :href="route(stat.href)"
                    class="group flex items-center gap-4 rounded-2xl border border-brand-100 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons[stat.icon]" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-2xl font-extrabold tracking-tight text-brand-950">{{ stat.value }}</p>
                        <p class="truncate text-sm font-medium text-brand-900/70">{{ stat.label }}</p>
                        <p class="truncate text-xs text-slate-500">{{ stat.hint }}</p>
                    </div>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card padding="p-0" class="lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-sm font-bold text-brand-950">Próximos prazos</h2>
                            <p class="text-xs text-slate-500">Prazos pendentes com vencimento a partir de hoje.</p>
                        </div>
                        <Link
                            :href="route('admin.deadlines.index')"
                            class="text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                        >
                            Ver todos
                        </Link>
                    </div>

                    <ul v-if="upcomingDeadlines.length > 0" class="divide-y divide-slate-100">
                        <li v-for="deadline in upcomingDeadlines" :key="deadline.id" class="flex items-center gap-4 px-6 py-3.5">
                            <span class="w-12 shrink-0 text-center">
                                <span class="block text-lg font-extrabold text-brand-950">{{ formatDate(deadline.due_date).slice(0, 2) }}</span>
                                <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ formatDate(deadline.due_date).slice(3) }}</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-brand-950">{{ deadline.title }}</p>
                                <Link
                                    v-if="deadline.process"
                                    :href="route('admin.processes.show', deadline.process.id)"
                                    class="truncate text-xs text-slate-500 hover:text-brand-700 hover:underline"
                                >
                                    {{ deadline.process.process_number }} · {{ deadline.process.title }}
                                </Link>
                            </div>
                            <Badge :color="deadlineVisual(deadline).color">{{ deadlineVisual(deadline).label }}</Badge>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-10">
                        <EmptyState
                            title="Nenhum prazo pendente"
                            description="Todos os prazos estão em dia."
                        />
                    </div>
                </Card>

                <Card padding="p-0">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-sm font-bold text-brand-950">Agenda</h2>
                            <p class="text-xs text-slate-500">Próximos compromissos e audiências.</p>
                        </div>
                        <Link
                            :href="route('admin.calendar.index')"
                            class="text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                        >
                            Agenda
                        </Link>
                    </div>

                    <ul v-if="upcomingEvents.length > 0" class="divide-y divide-slate-100">
                        <li v-for="event in upcomingEvents" :key="event.id" class="px-6 py-3.5">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-semibold text-brand-950">{{ event.title }}</p>
                                <Badge :color="eventTypeColor(event.type)">{{ eventTypeLabels[event.type] ?? event.type }}</Badge>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ eventDate(event) }} · {{ eventTime(event) }}
                            </p>
                            <Link
                                v-if="event.process"
                                :href="route('admin.processes.show', event.process.id)"
                                class="mt-0.5 block truncate text-xs text-slate-500 hover:text-brand-700 hover:underline"
                            >
                                {{ event.process.process_number }} — {{ event.process.title }}
                            </Link>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-10">
                        <EmptyState
                            title="Sem compromissos"
                            description="Nenhum evento marcado nos próximos dias."
                        />
                    </div>
                </Card>
            </div>

            <Card padding="p-0">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-sm font-bold text-brand-950">Processos recentes</h2>
                        <p class="text-xs text-slate-500">Últimos processos cadastrados no sistema.</p>
                    </div>
                    <Link
                        :href="route('admin.processes.index')"
                        class="text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                    >
                        Ver todos
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table v-if="recentProcesses.length > 0" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-brand-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-900/60">Processo</th>
                                <th class="hidden px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-900/60 md:table-cell">Cliente</th>
                                <th class="hidden px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-900/60 sm:table-cell">Número</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-900/60">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="process in recentProcesses" :key="process.id" class="hover:bg-brand-50/40">
                                <td class="px-6 py-3">
                                    <Link
                                        :href="route('admin.processes.show', process.id)"
                                        class="block truncate text-sm font-medium text-brand-950 hover:text-brand-700 hover:underline"
                                    >
                                        {{ process.title }}
                                    </Link>
                                    <p class="text-xs text-slate-500">{{ areaLabels[process.area] ?? process.area }}</p>
                                </td>
                                <td class="hidden px-6 py-3 text-sm text-slate-600 md:table-cell">{{ clientName(process) }}</td>
                                <td class="hidden px-6 py-3 font-mono text-sm text-slate-600 sm:table-cell">{{ process.process_number }}</td>
                                <td class="px-6 py-3 text-right">
                                    <Badge :color="processStatusColor(process.status)">{{ processStatusLabels[process.status] ?? process.status }}</Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-6 py-10">
                        <EmptyState
                            title="Nenhum processo cadastrado"
                            description="Comece cadastrando seu primeiro processo."
                        />
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>