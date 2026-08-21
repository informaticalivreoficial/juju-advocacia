<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    process: Object,
    timeline: Array,
    areas: Array,
    statuses: Array,
    priorities: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const labels = (options) => Object.fromEntries((options ?? []).map((item) => [item.value, item.label]));
const statusLabels = computed(() => labels(props.statuses));
const priorityLabels = computed(() => labels(props.priorities));
const areaLabels = computed(() => labels(props.areas));

const deleting = ref(false);

const deleteProcess = () => {
    router.delete(route('admin.processes.destroy', props.process.id), {
        onSuccess: () => {
            deleting.value = false;
        },
    });
};

const statusColor = (status) => ({
    analysis: 'amber',
    active: 'green',
    awaiting_decision: 'brand',
    suspended: 'gray',
    archived: 'slate',
    closed: 'gray',
}[status] ?? 'gray');

const priorityColor = (priority) => ({
    normal: 'gray',
    high: 'orange',
    urgent: 'red',
}[priority] ?? 'gray');

const clientName = computed(() => {
    const client = props.process.client;
    if (!client) return '—';
    return client.type === 'company' ? client.company_name : client.name;
});

const mainDetails = computed(() => [
    { label: 'Número do processo', value: props.process.process_number ?? '—' },
    { label: 'Tipo de ação', value: props.process.action_type ?? '—' },
    { label: 'Tribunal', value: props.process.court ?? '—' },
    { label: 'Comarca/Vara', value: props.process.district ?? '—' },
    { label: 'Turma/Câmara', value: props.process.court_division ?? '—' },
    { label: 'Instância', value: props.process.instance ?? '—' },
    {
        label: 'Valor da causa',
        value: props.process.case_value == null
            ? '—'
            : new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(props.process.case_value),
    },
    { label: 'Distribuição', value: props.process.distribution_date ?? '—' },
]);

const parties = computed(() => [
    { label: 'Autor', value: props.process.plaintiff ?? '—' },
    { label: 'Réu', value: props.process.defendant ?? '—' },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Detalhes do processo" />

        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ process.title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ process.process_number ?? 'Sem número' }}</p>
                </div>
                <Link
                    v-if="can('processes.update')"
                    :href="route('admin.processes.edit', process.id)"
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Editar
                </Link>
            </div>

            <Card>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge :color="statusColor(process.status)">{{ statusLabels[process.status] ?? process.status }}</Badge>
                    <Badge :color="priorityColor(process.priority)">{{ priorityLabels[process.priority] ?? process.priority }}</Badge>
                    <Badge color="slate">{{ areaLabels[process.area] ?? process.area }}</Badge>
                    <Badge v-if="process.confidentiality" color="red">Confidencial</Badge>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ clientName }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Responsável</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ process.responsible_user?.name ?? '—' }}</dd>
                    </div>
                </div>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Dados principais</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div v-for="detail in mainDetails" :key="detail.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ detail.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ detail.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Partes</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div v-for="party in parties" :key="party.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ party.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ party.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card v-if="process.description">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Descrição</h2>
                <p class="text-sm leading-relaxed whitespace-pre-line text-slate-700">{{ process.description }}</p>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Timeline</h2>
                <div v-if="timeline.length > 0">
                    <ol class="relative space-y-6 border-l border-slate-200 pl-6">
                        <li v-for="(item, index) in timeline" :key="index">
                            <span class="absolute -left-[9px] mt-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-brand-100 ring-4 ring-white"></span>
                            <p class="text-sm font-semibold text-slate-900">{{ item.title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ item.date }}</p>
                        </li>
                    </ol>
                </div>
                <EmptyState
                    v-else
                    title="Sem movimentações registradas"
                    description="As movimentações processuais aparecerão aqui futuramente."
                />
            </Card>

            <Card v-if="can('processes.delete')" padding="p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-red-600">Zona de perigo</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Excluir este processo. Esta ação não pode ser desfeita.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="deleting = true"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"
                    >
                        Excluir processo
                    </button>
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting"
            title="Excluir processo"
            :message="`Esta ação removerá o processo ${process.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteProcess"
            @close="deleting = false"
        />
    </AdminLayout>
</template>