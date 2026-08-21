<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Select from '@/Components/Select.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    report: Object,
    period: String,
    year: Number,
    month: Number,
    quarter: Number,
    half: Number,
});

const period = ref(props.period ?? 'monthly');
const year = ref(props.year);
const month = ref(props.month ?? 1);
const quarter = ref(props.quarter ?? 1);
const half = ref(props.half ?? 1);

const periodOptions = [
    { value: 'monthly', label: 'Mensal' },
    { value: 'quarterly', label: 'Trimestral' },
    { value: 'semiannual', label: 'Semestral' },
    { value: 'annual', label: 'Anual' },
];

const yearOptions = computed(() => {
    const years = [];
    for (let y = new Date().getFullYear() + 1; y >= new Date().getFullYear() - 5; y--) years.push({ value: y, label: `Ano ${y}` });
    return years;
});

const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, i) => ({
        value: i + 1,
        label: new Date(year.value, i, 1).toLocaleDateString('pt-BR', { month: 'long' }),
    }))
);

const quarterOptions = [
    { value: 1, label: '1º trimestre' },
    { value: 2, label: '2º trimestre' },
    { value: 3, label: '3º trimestre' },
    { value: 4, label: '4º trimestre' },
];

const halfOptions = [
    { value: 1, label: '1º semestre' },
    { value: 2, label: '2º semestre' },
];

const applyFilters = () => {
    router.get(route('admin.financial.reports.index'), {
        period: period.value,
        year: year.value,
        month: period.value === 'monthly' ? month.value : undefined,
        quarter: period.value === 'quarterly' ? quarter.value : undefined,
        half: period.value === 'semiannual' ? half.value : undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch([period, year, month, quarter, half], applyFilters);

const typeLabel = (type) => (type === 'income' ? 'Receita' : 'Despesa');

const statusColor = (status) => ({
    pending: 'amber',
    paid: 'green',
    received: 'green',
    cancelled: 'slate',
}[status] ?? 'gray');

const statusLabel = (status) => ({
    pending: 'Pendente',
    paid: 'Pago',
    received: 'Recebido',
    cancelled: 'Cancelado',
}[status] ?? status);

const formatDate = (value) => {
    if (!value) return '—';
    const [y, m, d] = value.split('-');
    return `${d}/${m}/${y}`;
};

const balanceColor = (balance) => (balance >= 0 ? 'text-emerald-600' : 'text-red-600');

const totalCards = computed(() => [
    { label: 'Receitas', value: props.report?.income ?? 0, color: 'text-emerald-600' },
    { label: 'Despesas', value: props.report?.expense ?? 0, color: 'text-red-600' },
    { label: 'Saldo', value: props.report?.balance ?? 0, color: balanceColor(props.report?.balance ?? 0) },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Relatórios" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Relatórios financeiros</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ report?.label }} · {{ formatDate(report?.start) }} a {{ formatDate(report?.end) }}</p>
                </div>
                <Link
                    :href="route('admin.financial.reports.export', {
                        period,
                        year,
                        month: period === 'monthly' ? month : undefined,
                        quarter: period === 'quarterly' ? quarter : undefined,
                        half: period === 'semiannual' ? half : undefined,
                    })"
                    class="inline-flex items-center rounded-md border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50"
                >
                    Exportar CSV
                </Link>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Select v-model="period" :options="periodOptions" />
                <Select v-model="year" :options="yearOptions" />
                <Select v-if="period === 'monthly'" v-model="month" :options="monthOptions" class="capitalize" />
                <Select v-else-if="period === 'quarterly'" v-model="quarter" :options="quarterOptions" />
                <Select v-else-if="period === 'semiannual'" v-model="half" :options="halfOptions" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div v-for="card in totalCards" :key="card.label" class="rounded-xl border border-brand-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
                    <p class="mt-1 text-2xl font-extrabold tracking-tight" :class="card.color">{{ formatCurrency(card.value) }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card padding="p-0" class="lg:col-span-1">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Por categoria</h2>
                        <p class="text-xs text-slate-500">Consolidado no período.</p>
                    </div>
                    <ul v-if="report?.categories?.length > 0" class="divide-y divide-slate-100">
                        <li v-for="category in report.categories" :key="category.id" class="flex items-center gap-3 px-6 py-3">
                            <span class="h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: category.color }"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ category.name }}</p>
                                <p class="text-xs text-slate-500">{{ typeLabel(category.type) }} · {{ category.count }} lançamentos</p>
                            </div>
                            <p class="text-sm font-bold" :class="category.type === 'income' ? 'text-emerald-600' : 'text-red-600'">
                                {{ formatCurrency(category.type === 'income' ? category.income : category.expense) }}
                            </p>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-10">
                        <EmptyState title="Sem categorias" description="Nenhum lançamento no período." />
                    </div>
                </Card>

                <Card padding="p-0" class="lg:col-span-2">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Lançamentos do período</h2>
                        <p class="text-xs text-slate-500">
                            {{ report?.statusCounts?.pending ?? 0 }} pendentes · {{ report?.statusCounts?.paid ?? 0 }} pagos ·
                            {{ report?.statusCounts?.received ?? 0 }} recebidos · {{ report?.statusCounts?.cancelled ?? 0 }} cancelados
                        </p>
                    </div>
                    <div class="max-h-[28rem] overflow-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="sticky top-0 bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Lançamento</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell">Período</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="transaction in report?.transactions ?? []" :key="transaction.id" class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-slate-900">{{ transaction.description }}</p>
                                        <p class="text-xs text-slate-500">{{ transaction.category ?? 'Sem categoria' }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 text-sm text-slate-600 sm:table-cell">
                                        {{ transaction.month }}/{{ transaction.year }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge :color="statusColor(transaction.status)">{{ statusLabel(transaction.status) }}</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-bold" :class="transaction.type === 'income' ? 'text-emerald-600' : 'text-slate-900'">
                                        {{ transaction.type === 'income' ? '+' : '−' }} {{ formatCurrency(transaction.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="!report?.transactions?.length">
                                    <td colspan="4" class="px-4 py-3">
                                        <EmptyState title="Nenhum lançamento" description="Sem lançamentos no período selecionado." />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>