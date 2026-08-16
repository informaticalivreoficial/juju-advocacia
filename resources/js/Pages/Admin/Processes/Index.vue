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
    processes: Object,
    filters: Object,
    areas: Array,
    statuses: Array,
    priorities: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const statusLabels = Object.fromEntries(props.statuses.map((item) => [item.value, item.label]));
const priorityLabels = Object.fromEntries(props.priorities.map((item) => [item.value, item.label]));
const areaLabels = Object.fromEntries(props.areas.map((item) => [item.value, item.label]));

const statusColor = (status) => ({
    analysis: 'amber',
    active: 'green',
    awaiting_decision: 'indigo',
    suspended: 'gray',
    archived: 'slate',
    closed: 'gray',
}[status] ?? 'gray');

const priorityColor = (priority) => ({
    normal: 'gray',
    high: 'orange',
    urgent: 'red',
}[priority] ?? 'gray');

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');
const area = ref(props.filters?.area ?? '');
const priority = ref(props.filters?.priority ?? '');

let searchTimeout;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters({ search: value || undefined }), 300);
});

watch([status, area, priority], () =>
    applyFilters({
        status: status.value || undefined,
        area: area.value || undefined,
        priority: priority.value || undefined,
    })
);

const applyFilters = (extra = {}) => {
    router.get(
        route('admin.processes.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            area: area.value || undefined,
            priority: priority.value || undefined,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const deleting = ref(null);

const deleteProcess = () => {
    router.delete(route('admin.processes.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const formatCurrency = (value) =>
    value == null
        ? '—'
        : new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const clientName = (process) =>
    process.client ? (process.client.type === 'company' ? process.client.company_name : process.client.name) : '—';
</script>

<template>
    <AdminLayout>
        <Head title="Processos" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Processos</h1>
                    <p class="mt-1 text-sm text-slate-500">Gestão dos processos judiciais do escritório.</p>
                </div>
                <Link
                    v-if="can('processes.create')"
                    :href="route('admin.processes.create')"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Novo processo
                </Link>
            </div>

            <Card>
                <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <TextInput
                            v-model="search"
                            type="search"
                            placeholder="Buscar por número, título, partes ou cliente..."
                            class="mt-0 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <Select v-model="status" :options="statuses" placeholder="Todos os status" />
                    </div>
                    <div>
                        <Select v-model="area" :options="areas" placeholder="Todas as áreas" />
                    </div>
                </div>

                <div class="mb-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <Select v-model="priority" :options="priorities" placeholder="Todas as prioridades" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Processo</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell">Cliente</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Responsável</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 xl:table-cell">Área</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Prioridade</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="process in processes.data" :key="process.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <Link :href="route('admin.processes.show', process.id)" class="block text-sm font-medium text-slate-900 hover:text-indigo-600 hover:underline">
                                        {{ process.title }}
                                    </Link>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ process.process_number ?? 'Sem número' }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 lg:table-cell">{{ clientName(process) }}</td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">
                                    {{ process.responsible_user?.name ?? '—' }}
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 xl:table-cell">
                                    {{ areaLabels[process.area] ?? process.area }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="statusColor(process.status)">
                                        {{ statusLabels[process.status] ?? process.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="priorityColor(process.priority)">
                                        {{ priorityLabels[process.priority] ?? process.priority }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.processes.show', process.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Ver"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="can('processes.update')"
                                            :href="route('admin.processes.edit', process.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('processes.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = process"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="processes.data.length === 0">
                                <td colspan="7" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhum processo encontrado"
                                        description="Ajuste a busca ou os filtros para encontrar processos."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="processes.links" :from="processes.from" :to="processes.to" :total="processes.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir processo"
            :message="`Esta ação removerá o processo ${deleting?.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteProcess"
            @close="deleting = null"
        />
    </AdminLayout>
</template>