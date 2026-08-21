<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import Select from '@/Components/Select.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    year: Number,
    months: Array,
    totals: Object,
});

const year = ref(props.year);

const yearOptions = computed(() => {
    const years = [];
    for (let y = new Date().getFullYear() + 1; y >= new Date().getFullYear() - 5; y--) years.push({ value: y, label: `Ano ${y}` });
    return years;
});

watch(year, (value) => {
    router.get(route('admin.financial.annual.index'), { year: value }, { preserveState: true, preserveScroll: true, replace: true });
});

const balanceColor = (balance) => (balance >= 0 ? 'text-emerald-600' : 'text-red-600');

const chartCanvas = ref(null);
let chart = null;

const renderChart = () => {
    if (chart) chart.destroy();

    if (!chartCanvas.value) return;

    chart = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: props.months.map((m) => m.label.slice(0, 3)),
            datasets: [
                {
                    label: 'Receitas',
                    data: props.months.map((m) => m.income),
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                },
                {
                    label: 'Despesas',
                    data: props.months.map((m) => m.expense),
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
};

onMounted(() => requestAnimationFrame(renderChart));
watch(() => props.months, () => renderChart());
onBeforeUnmount(() => {
    if (chart) chart.destroy();
});

const totalCards = computed(() => [
    { label: 'Receitas', value: props.totals?.income ?? 0, color: 'text-emerald-600' },
    { label: 'Despesas', value: props.totals?.expense ?? 0, color: 'text-red-600' },
    { label: 'Saldo', value: props.totals?.balance ?? 0, color: balanceColor(props.totals?.balance ?? 0) },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Visão anual" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Visão anual</h1>
                    <p class="mt-1 text-sm text-slate-500">Resultado financeiro mensal do ano selecionado.</p>
                </div>
                <Select v-model="year" :options="yearOptions" class="w-32" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div v-for="card in totalCards" :key="card.label" class="rounded-xl border border-brand-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
                    <p class="mt-1 text-2xl font-extrabold tracking-tight" :class="card.color">{{ formatCurrency(card.value) }}</p>
                </div>
            </div>

            <Card padding="p-0">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-bold text-brand-950">Receitas × Despesas — {{ year }}</h2>
                    <p class="text-xs text-slate-500">Comparativo mensal de entradas e saídas.</p>
                </div>
                <div class="h-72 p-4">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </Card>

            <Card padding="p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mês</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Receitas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Despesas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Lançamentos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="m in months" :key="m.month" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-sm font-semibold capitalize text-slate-900">{{ m.label }}</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-emerald-600">{{ formatCurrency(m.income) }}</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-red-600">{{ formatCurrency(m.expense) }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold" :class="balanceColor(m.balance)">{{ formatCurrency(m.balance) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <Badge :color="m.transactions > 0 ? 'brand' : 'gray'">{{ m.transactions }}</Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>