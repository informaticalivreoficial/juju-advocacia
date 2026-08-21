<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Select from '@/Components/Select.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    year: Number,
    month: Number,
    totals: Object,
    indicators: Object,
    chart: Object,
    cashFlow: Array,
    comparison: Object,
    months: Array,
});

const month = ref(props.month);
const year = ref(props.year);

let barChart = null;
let doughnutChart = null;
const barCanvas = ref(null);
const doughnutCanvas = ref(null);

const monthsOptions = computed(() => [
    { value: 2026, label: `Ano ${year.value}` },
]);

const indicatorsList = computed(() => [
    { label: 'Pendentes', value: props.indicators?.pending ?? 0, color: 'amber' },
    { label: 'Vencidas', value: props.indicators?.overdue ?? 0, color: 'red' },
    { label: 'Pagas', value: props.indicators?.paid ?? 0, color: 'green' },
    { label: 'Esperadas', value: formatCurrency(props.indicators?.expected ?? 0), color: 'brand' },
    { label: 'Recebidas', value: props.indicators?.received ?? 0, color: 'green' },
]);

const totalCards = computed(() => [
    { label: 'Receitas', value: props.totals?.income ?? 0, color: 'text-emerald-600', bg: 'bg-emerald-50' },
    { label: 'Despesas', value: props.totals?.expense ?? 0, color: 'text-red-600', bg: 'bg-red-50' },
    { label: 'Saldo', value: props.totals?.balance ?? 0, color: props.totals?.balance >= 0 ? 'text-brand-700' : 'text-red-600', bg: props.totals?.balance >= 0 ? 'bg-brand-50' : 'bg-red-50' },
]);

const balanceColor = (balance) => (balance >= 0 ? 'text-emerald-600' : 'text-red-600');

const renderCharts = () => {
    if (barChart) barChart.destroy();
    if (doughnutChart) doughnutChart.destroy();

    const data = props.chart ?? { yearly: {}, byCategory: {} };

    if (barCanvas.value) {
        barChart = new Chart(barCanvas.value, {
            type: 'bar',
            data: {
                labels: data.yearly?.labels ?? [],
                datasets: [
                    {
                        label: 'Receitas',
                        data: data.yearly?.income ?? [],
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                    },
                    {
                        label: 'Despesas',
                        data: data.yearly?.expense ?? [],
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { ticks: { callback: (value) => value.toLocaleString('pt-BR') } },
                },
            },
        });
    }

    if (doughnutCanvas.value) {
        doughnutChart = new Chart(doughnutCanvas.value, {
            type: 'doughnut',
            data: {
                labels: data.byCategory?.labels ?? [],
                datasets: [
                    {
                        data: data.byCategory?.data ?? [],
                        backgroundColor: data.byCategory?.colors ?? [],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => formatCurrency(ctx.parsed) } },
                },
            },
        });
    }
};

const applyFilters = () => {
    router.get(route('admin.financial.dashboard'), { year: year.value, month: month.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const quickMarkAll = (action) => {
    if (!confirm(action === 'expenses' ? 'Marcar todas as despesas pendentes deste mês como pagas?' : 'Marcar todas as receitas pendentes deste mês como recebidas?')) {
        return;
    }

    router.post(
        route(action === 'expenses' ? 'admin.financial.transactions.mark-all-expenses-paid' : 'admin.financial.transactions.mark-all-incomes-received'),
        { year: year.value, month: month.value },
        { preserveScroll: true }
    );
};

watch([month, year], applyFilters);

onMounted(() => {
    if (props.chart) {
        requestAnimationFrame(renderCharts);
    }
});

watch(() => props.chart, () => renderCharts());

onBeforeUnmount(() => {
    if (barChart) barChart.destroy();
    if (doughnutChart) doughnutChart.destroy();
});
</script>

<template>
    <AdminLayout>
        <Head title="Financeiro" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Visão geral financeira</h1>
                    <p class="mt-1 text-sm text-slate-500">Resumo de receitas, despesas e projeções.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Select v-model="month" :options="months" class="w-40" />
                    <Select v-model="year" :options="[{ value: year, label: `Ano ${year}` }]" class="w-28" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div v-for="card in totalCards" :key="card.label" class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
                    <p class="mt-1 text-2xl font-extrabold tracking-tight" :class="card.color">
                        {{ formatCurrency(card.value) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div v-for="item in indicatorsList" :key="item.label" class="rounded-xl border border-slate-100 bg-white p-4 text-center shadow-sm">
                    <Badge :color="item.color">{{ item.label }}</Badge>
                    <p class="mt-2 text-xl font-extrabold text-brand-950">{{ item.value }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card padding="p-0" class="lg:col-span-2">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Receitas × Despesas — {{ year }}</h2>
                        <p class="text-xs text-slate-500">Comparativo mensal de entradas e saídas.</p>
                    </div>
                    <div class="h-72 p-4">
                        <canvas ref="barCanvas"></canvas>
                    </div>
                </Card>

                <Card padding="p-0">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Despesas por categoria</h2>
                        <p class="text-xs text-slate-500">Distribuição do mês selecionado.</p>
                    </div>
                    <div class="h-72 p-4">
                        <canvas v-if="chart?.byCategory?.labels?.length > 0" ref="doughnutCanvas"></canvas>
                        <EmptyState v-else title="Sem despesas" description="Nenhuma despesa neste mês." />
                    </div>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card padding="p-0">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Projeção de fluxo de caixa</h2>
                        <p class="text-xs text-slate-500">Próximos 6 meses, considerando lançamentos pendentes.</p>
                    </div>
                    <ul v-if="cashFlow.length > 0" class="divide-y divide-slate-100">
                        <li v-for="row in cashFlow" :key="row.label" class="flex items-center gap-4 px-6 py-3.5">
                            <span class="w-16 text-sm font-bold text-brand-950">{{ row.label }}</span>
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="h-2.5 flex-1 rounded-full bg-slate-100">
                                        <span class="block h-2.5 rounded-full" :class="row.balance >= 0 ? 'bg-emerald-500' : 'bg-red-500'" :style="{ width: Math.min(100, Math.max(8, (Math.abs(row.balance) / 1) * 5)) + '%' }"></span>
                                    </span>
                                    <span class="w-32 text-right text-sm font-semibold" :class="balanceColor(row.balance)">
                                        {{ formatCurrency(row.balance) }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-10">
                        <EmptyState title="Sem projeções" description="Nenhum lançamento pendente para projetar." />
                    </div>
                </Card>

                <Card padding="p-0">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold text-brand-950">Comparativo anual</h2>
                        <p class="text-xs text-slate-500">Receitas e despesas do ano atual versus anterior.</p>
                    </div>
                    <div class="space-y-4 px-6 py-4">
                        <div v-for="cmp in [comparison?.current, comparison?.previous]" :key="cmp?.year" class="rounded-xl border border-slate-100 p-4">
                            <p class="text-sm font-bold text-brand-950">{{ cmp?.year }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-slate-500">Receitas</p>
                                    <p class="text-sm font-semibold text-emerald-600">{{ formatCurrency(cmp?.income ?? 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Despesas</p>
                                    <p class="text-sm font-semibold text-red-600">{{ formatCurrency(cmp?.expense ?? 0) }}</p>
                                </div>
                            </div>
                            <div class="mt-2 border-t border-slate-100 pt-2">
                                <p class="text-xs text-slate-500">Saldo</p>
                                <p class="text-sm font-extrabold" :class="balanceColor((cmp?.income ?? 0) - (cmp?.expense ?? 0))">
                                    {{ formatCurrency((cmp?.income ?? 0) - (cmp?.expense ?? 0)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.financial.transactions.index')" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">
                        Ver lançamentos
                    </Link>
                    <Link :href="route('admin.financial.expenses.index')" class="rounded-lg border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                        Gerenciar despesas fixas
                    </Link>
                    <Link :href="route('admin.financial.reports.index')" class="rounded-lg border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                        Relatórios
                    </Link>
                </div>
                <div class="ml-auto flex flex-wrap gap-3">
                    <button type="button" @click="quickMarkAll('expenses')" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                        Marcar despesas pagas
                    </button>
                    <button type="button" @click="quickMarkAll('incomes')" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                        Marcar receitas recebidas
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>