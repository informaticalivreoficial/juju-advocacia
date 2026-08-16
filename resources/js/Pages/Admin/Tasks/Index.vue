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
    tasks: Object,
    filters: Object,
    statuses: Array,
    priorities: Array,
    counts: Object,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const tabs = computed(() => [
    { key: '', label: 'Todas', count: null },
    { key: 'pending', label: 'Pendentes', count: props.counts?.pending ?? 0 },
    { key: 'in_progress', label: 'Em andamento', count: props.counts?.in_progress ?? 0 },
    { key: 'completed', label: 'Concluídas', count: props.counts?.completed ?? 0 },
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
        route('admin.tasks.index'),
        {
            search: search.value || undefined,
            status: activeTab.value || undefined,
            priority: priority.value || undefined,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const statusLabels = Object.fromEntries(props.statuses.map((item) => [item.value, item.label]));
const statusColor = (status) => ({
    pending: 'amber',
    in_progress: 'indigo',
    completed: 'green',
}[status] ?? 'gray');

const priorityLabels = Object.fromEntries(props.priorities.map((item) => [item.value, item.label]));
const priorityColor = (priority) => ({
    normal: 'gray',
    high: 'orange',
    urgent: 'red',
}[priority] ?? 'gray');

const formatDate = (value) => {
    if (!value) return '—';
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
};

const deleting = ref(null);

const deleteTask = () => {
    router.delete(route('admin.tasks.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const toggleComplete = (task) => {
    router.patch(route('admin.tasks.toggle-complete', task.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Tarefas" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tarefas</h1>
                    <p class="mt-1 text-sm text-slate-500">Organize as tarefas do escritório.</p>
                </div>
                <Link
                    v-if="can('tasks.create')"
                    :href="route('admin.tasks.create')"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nova tarefa
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
                            placeholder="Buscar por tarefa, processo ou cliente..."
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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tarefa</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell">Responsável</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 xl:table-cell">Prazo interno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Prioridade</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900">{{ task.title }}</p>
                                    <p v-if="task.process" class="mt-0.5 text-xs text-slate-500">
                                        <Link :href="route('admin.processes.show', task.process.id)" class="hover:text-indigo-600 hover:underline">
                                            {{ task.process.title }}
                                        </Link>
                                    </p>
                                    <p v-else-if="task.client" class="mt-0.5 text-xs text-slate-500">{{ task.client.name || task.client.company_name }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 lg:table-cell">
                                    {{ task.responsible_user?.name ?? '—' }}
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 xl:table-cell">
                                    <template v-if="task.deadline">
                                        <span class="block">{{ formatDate(task.deadline.due_date) }}</span>
                                        <span class="block text-xs text-slate-400">{{ task.deadline.title }}</span>
                                    </template>
                                    <span v-else>{{ formatDate(task.due_date) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="statusColor(task.status)">
                                        {{ statusLabels[task.status] ?? task.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="priorityColor(task.priority)">
                                        {{ priorityLabels[task.priority] ?? task.priority }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            v-if="can('tasks.update')"
                                            type="button"
                                            :title="task.status === 'completed' ? 'Reabrir tarefa' : 'Concluir tarefa'"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-green-50 hover:text-green-600"
                                            @click="toggleComplete(task)"
                                        >
                                            <svg v-if="task.status === 'completed'" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <Link
                                            v-if="can('tasks.update')"
                                            :href="route('admin.tasks.edit', task.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('tasks.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = task"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tasks.data.length === 0">
                                <td colspan="6" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhuma tarefa encontrada"
                                        description="Ajuste a busca ou os filtros para encontrar tarefas."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="tasks.links" :from="tasks.from" :to="tasks.to" :total="tasks.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir tarefa"
            :message="`Esta ação removerá a tarefa ${deleting?.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteTask"
            @close="deleting = null"
        />
    </AdminLayout>
</template>