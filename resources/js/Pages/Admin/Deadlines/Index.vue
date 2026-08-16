<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    deadlines: Object,
    filters: Object,
    statuses: Array,
    priorities: Array,
    counts: Object,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const tabs = computed(() => [
    { key: '', label: 'Todos', count: null },
    { key: 'pending', label: 'Pendentes', count: props.counts?.pending ?? 0 },
    { key: 'today', label: 'Vencem hoje', count: props.counts?.today ?? 0 },
    { key: 'upcoming', label: 'Próximos', count: props.counts?.upcoming ?? 0 },
    { key: 'expired', label: 'Vencidos', count: props.counts?.expired ?? 0 },
    { key: 'completed', label: 'Concluídos', count: props.counts?.completed ?? 0 },
]);

const activeTab = ref(props.filters?.status ?? '');

const search = ref(props.filters?.search ?? '');
const priority = ref(props.filters?.priority ?? '');

let searchTimeout;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters({ search: value || undefined }), 300);
});

watch([activeTab, priority], () =>
    applyFilters({
        status: activeTab.value || undefined,
        priority: priority.value || undefined,
    })
);

const applyFilters = (extra = {}) => {
    router.get(
        route('admin.deadlines.index'),
        {
            search: search.value || undefined,
            status: activeTab.value || undefined,
            priority: priority.value || undefined,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const priorityLabels = Object.fromEntries(props.priorities.map((item) => [item.value, item.label]));
const priorityColor = (priority) => ({
    normal: 'gray',
    high: 'orange',
    urgent: 'red',
}[priority] ?? 'gray');

const deadlineVisual = (deadline) => {
    const effective = deadline.effective_status ?? deadline.status;
    if (effective === 'completed') return { label: 'Concluído', color: 'green' };
    if (effective === 'cancelled') return { label: 'Cancelado', color: 'slate' };

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(`${deadline.due_date}T00:00:00`);
    const diffDays = Math.round((due - today) / 86400000);

    if (effective === 'expired' || diffDays < 0) return { label: 'Vencido', color: 'red' };
    if (diffDays === 0) return { label: 'Vence hoje', color: 'red' };
    if (diffDays === 1) return { label: 'Vence amanhã', color: 'orange' };
    if (diffDays <= 3) return { label: 'Vence em até 3 dias', color: 'amber' };
    if (effective === 'in_progress') return { label: 'Em andamento', color: 'indigo' };

    return { label: 'Pendente', color: 'gray' };
};

const formatDate = (value) => {
    if (!value) return '—';
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
};

const deleting = ref(null);

const deleteDeadline = () => {
    router.delete(route('admin.deadlines.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const toggleComplete = (deadline) => {
    router.patch(route('admin.deadlines.toggle-complete', deadline.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Prazos" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Prazos</h1>
                    <p class="mt-1 text-sm text-slate-500">Acompanhe os prazos processuais e internos.</p>
                </div>
                <Link
                    v-if="can('deadlines.create')"
                    :href="route('admin.deadlines.create')"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Novo prazo
                </Link>
            </div>

            <Card>
                <div class="mb-4 flex flex-wrap items-center gap-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        @click="activeTab = tab.key"
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition',
                            activeTab === tab.key
                                ? 'bg-slate-900 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.count !== null"
                            :class="[
                                'rounded-full px-1.5 text-xs font-semibold',
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : tab.key === 'expired' && tab.count > 0
                                      ? 'bg-red-100 text-red-700'
                                      : 'bg-slate-200 text-slate-600',
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>

                <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <TextInput
                            v-model="search"
                            type="search"
                            placeholder="Buscar por prazo ou processo..."
                            class="mt-0 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <Select v-model="priority" :options="priorities" placeholder="Todas as prioridades" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Prazo</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell">Responsável</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Início</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Vencimento</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Prioridade</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="deadline in deadlines.data" :key="deadline.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900">{{ deadline.title }}</p>
                                    <p v-if="deadline.process" class="mt-0.5 text-xs text-slate-500">
                                        <Link :href="route('admin.processes.show', deadline.process.id)" class="hover:text-indigo-600 hover:underline">
                                            {{ deadline.process.title }}
                                        </Link>
                                    </p>
                                    <p v-else class="mt-0.5 text-xs text-slate-400">Sem processo vinculado</p>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 lg:table-cell">
                                    {{ deadline.responsible_user?.name ?? '—' }}
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">{{ formatDate(deadline.start_date) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ formatDate(deadline.due_date) }}</td>
                                <td class="px-4 py-3">
                                    <Badge :color="deadlineVisual(deadline).color">{{ deadlineVisual(deadline).label }}</Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="priorityColor(deadline.priority)">
                                        {{ priorityLabels[deadline.priority] ?? deadline.priority }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            v-if="can('deadlines.update')"
                                            type="button"
                                            :title="deadline.status === 'completed' ? 'Reabrir prazo' : 'Concluir prazo'"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-green-50 hover:text-green-600"
                                            @click="toggleComplete(deadline)"
                                        >
                                            <svg v-if="deadline.status === 'completed'" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <Link
                                            v-if="can('deadlines.update')"
                                            :href="route('admin.deadlines.edit', deadline.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('deadlines.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = deadline"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="deadlines.data.length === 0">
                                <td colspan="7" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhum prazo encontrado"
                                        description="Ajuste a busca ou os filtros para encontrar prazos."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="deadlines.links" :from="deadlines.from" :to="deadlines.to" :total="deadlines.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir prazo"
            :message="`Esta ação removerá o prazo ${deleting?.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteDeadline"
            @close="deleting = null"
        />
    </AdminLayout>
</template>