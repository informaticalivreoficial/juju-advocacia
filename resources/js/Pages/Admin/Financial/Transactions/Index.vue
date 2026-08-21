<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Dropdown from '@/Components/Dropdown.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from '@/Components/Select.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    transactions: Object,
    filters: Object,
    year: Number,
    month: Number,
    totals: Object,
    hasGenerated: Boolean,
    statuses: Array,
    types: Array,
    paymentMethods: Array,
    categories: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const month = ref(props.month);
const year = ref(props.year);
const status = ref(props.filters?.status ?? '');
const type = ref(props.filters?.type ?? '');

const yearOptions = computed(() => {
    const years = [];
    for (let y = now().getFullYear() + 1; y >= now().getFullYear() - 5; y--) years.push({ value: y, label: `Ano ${y}` });
    return years;
});

const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, i) => ({
        value: i + 1,
        label: new Date(year.value, i, 1).toLocaleDateString('pt-BR', { month: 'long' }),
    }))
);

function now() {
    return new Date();
}

const applyFilters = () => {
    router.get(
        route('admin.financial.transactions.index'),
        {
            year: year.value,
            month: month.value,
            status: status.value || undefined,
            type: type.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

watch([month, year, status, type], applyFilters);

const generateMonth = () => {
    if (!confirm('Gerar os lançamentos recorrentes (despesas e receitas ativas) para o mês selecionado?')) return;
    router.post(route('admin.financial.transactions.generate'), { year: year.value, month: month.value }, { preserveScroll: true });
};

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

const typeLabel = (type) => (type === 'income' ? 'Receita' : 'Despesa');

const dueVisual = (transaction) => {
    if (transaction.status === 'paid' || transaction.status === 'received') return null;
    if (transaction.status === 'cancelled') return null;
    if (transaction.is_overdue) return { label: 'Vencido', color: 'red' };
    if (transaction.days_until_due === 0) return { label: 'Vence hoje', color: 'red' };
    if (transaction.days_until_due === 1) return { label: 'Vence amanhã', color: 'orange' };
    if (transaction.days_until_due <= 3) return { label: `Vence em ${transaction.days_until_due} dias`, color: 'amber' };
    return null;
};

const formatDate = (value) => {
    if (!value) return '—';
    const [y, m, d] = value.split('-');
    return `${d}/${m}/${y}`;
};

const actionsFor = (transaction) => {
    const actions = [];
    if (transaction.type === 'expense') {
        if (transaction.status === 'pending') actions.push({ action: 'paid', label: 'Marcar como pago', color: 'green' });
        if (transaction.status === 'paid') actions.push({ action: 'undo_paid', label: 'Desfazer pagamento', color: 'slate' });
    }
    if (transaction.type === 'income') {
        if (transaction.status === 'pending') actions.push({ action: 'received', label: 'Marcar como recebido', color: 'green' });
        if (transaction.status === 'received') actions.push({ action: 'undo_received', label: 'Desfazer recebimento', color: 'slate' });
    }
    if (transaction.status === 'pending') actions.push({ action: 'cancel', label: 'Cancelar', color: 'red' });
    return actions;
};

const runAction = (transaction, action) => {
    const item = actionsFor(transaction).find((a) => a.action === action);
    if (action === 'cancel' && !confirm('Cancelar este lançamento?')) return;
    router.patch(route('admin.financial.transactions.status', transaction.id), { action }, { preserveScroll: true });
};

const modalOpen = ref(false);
const editing = ref(null);
const deleting = ref(null);

const emptyForm = () => ({
    type: 'expense',
    category_id: '',
    description: '',
    amount: '',
    year: year.value,
    month: month.value,
    due_date: '',
    payment_method: '',
    status: 'pending',
    notes: '',
    attachment: null,
});

const form = useForm(emptyForm());

const openCreate = () => {
    editing.value = null;
    form.clearErrors();
    form.reset();
    Object.assign(form, emptyForm());
    modalOpen.value = true;
};

const openEdit = (transaction) => {
    editing.value = transaction;
    form.clearErrors();
    form.reset();
    Object.assign(form, {
        type: transaction.type,
        category_id: transaction.category_id ?? '',
        description: transaction.description,
        amount: transaction.amount,
        year: transaction.year,
        month: transaction.month,
        due_date: transaction.due_date ?? '',
        payment_method: transaction.payment_method ?? '',
        status: transaction.status,
        notes: transaction.notes ?? '',
        attachment: null,
    });
    modalOpen.value = true;
};

const categoryOptions = computed(() => props.categories.filter((c) => c.type === form.type));

const submit = () => {
    if (editing.value) {
        form.put(route('admin.financial.transactions.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                modalOpen.value = false;
                editing.value = null;
            },
        });
    } else {
        form.post(route('admin.financial.transactions.store'), {
            preserveScroll: true,
            onSuccess: () => {
                modalOpen.value = false;
            },
        });
    }
};

const deleteTransaction = () => {
    router.delete(route('admin.financial.transactions.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const totalCells = computed(() => [
    { label: 'Receitas', value: formatCurrency(props.totals?.income ?? 0), color: 'text-emerald-600' },
    { label: 'Despesas', value: formatCurrency(props.totals?.expense ?? 0), color: 'text-red-600' },
    { label: 'Saldo', value: formatCurrency(props.totals?.balance ?? 0), color: (props.totals?.balance ?? 0) >= 0 ? 'text-brand-700' : 'text-red-600' },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Lançamentos" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Lançamentos</h1>
                    <p class="mt-1 text-sm text-slate-500">Gerencie os lançamentos financeiros do mês.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('admin.financial.transactions.export', { year, month, status: status || undefined })"
                        class="inline-flex items-center rounded-md border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50"
                    >
                        Exportar CSV
                    </Link>
                    <button
                        type="button"
                        @click="generateMonth"
                        class="inline-flex items-center rounded-md border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50"
                    >
                        Gerar mês
                    </button>
                    <button
                        v-if="can('financial.create')"
                        type="button"
                        @click="openCreate"
                        class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Novo lançamento
                    </button>
                </div>
            </div>

            <div v-if="!hasGenerated" class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>
                    Nenhum lançamento gerado para {{ monthOptions.find((m) => m.value === month)?.label }} de {{ year }}.
                    <button type="button" class="font-semibold underline" @click="generateMonth">Gerar agora</button>
                    para criar automaticamente despesas e receitas fixas.
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div v-for="cell in totalCells" :key="cell.label" class="rounded-xl border border-brand-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">{{ cell.label }}</p>
                    <p class="mt-1 text-xl font-extrabold tracking-tight" :class="cell.color">{{ cell.value }}</p>
                </div>
            </div>

            <Card>
                <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Select v-model="month" :options="monthOptions" class="capitalize" />
                    <Select v-model="year" :options="yearOptions" />
                    <Select v-model="status" :options="statuses" placeholder="Todos os status" />
                    <Select v-model="type" :options="types" placeholder="Todos os tipos" />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Lançamento</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Vencimento</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Valor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 shrink-0 rounded-full" :class="transaction.type === 'income' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-slate-900">{{ transaction.description }}</p>
                                            <p class="truncate text-xs text-slate-500">
                                                {{ typeLabel(transaction.type) }}
                                                <template v-if="transaction.category"> · {{ transaction.category }}</template>
                                                <template v-if="transaction.payment_method"> · {{ transaction.payment_method }}</template>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    <p class="text-sm text-slate-600">{{ formatDate(transaction.due_date) }}</p>
                                    <p v-if="dueVisual(transaction)" class="text-xs font-semibold" :class="{ 'text-red-600': dueVisual(transaction).color === 'red', 'text-orange-600': dueVisual(transaction).color === 'orange', 'text-amber-600': dueVisual(transaction).color === 'amber' }">
                                        {{ dueVisual(transaction).label }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="statusColor(transaction.status)">{{ statusLabel(transaction.status) }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <p class="text-sm font-bold" :class="transaction.type === 'income' ? 'text-emerald-600' : 'text-slate-900'">
                                        {{ transaction.type === 'income' ? '+' : '−' }} {{ formatCurrency(transaction.amount) }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Dropdown
                                            v-if="actionsFor(transaction).length > 0 && can('financial.update')"
                                        >
                                            <template #trigger>
                                                <button type="button" class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" title="Ações">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                    </svg>
                                                </button>
                                            </template>
                                            <template #content>
                                                <div class="py-1">
                                                    <button
                                                        v-for="item in actionsFor(transaction)"
                                                        :key="item.action"
                                                        type="button"
                                                        class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100"
                                                        @click="runAction(transaction, item.action)"
                                                    >
                                                        {{ item.label }}
                                                    </button>
                                                </div>
                                            </template>
                                        </Dropdown>
                                        <Link
                                            v-if="transaction.has_attachment"
                                            :href="route('admin.financial.transactions.download-attachment', transaction.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Comprovante"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('financial.update')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                            @click="openEdit(transaction)"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button
                                            v-if="transaction.is_ad_hoc && can('financial.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = transaction"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="5" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhum lançamento encontrado"
                                        description="Gere o mês ou cadastre um lançamento avulso."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="transactions.links" :from="transactions.from" :to="transactions.to" :total="transactions.total" />
                </div>
            </Card>
        </div>

        <Modal :show="modalOpen" @close="modalOpen = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-brand-950">{{ editing ? 'Editar lançamento' : 'Novo lançamento' }}</h2>
                <p class="text-sm text-slate-500">Lançamento avulso (sem origem recorrente).</p>

                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Tipo" />
                            <div class="mt-1 flex gap-3">
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input type="radio" v-model="form.type" value="expense" class="text-brand-600 focus:ring-brand-500" />
                                    Despesa
                                </label>
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input type="radio" v-model="form.type" value="income" class="text-brand-600 focus:ring-brand-500" />
                                    Receita
                                </label>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Status" />
                            <Select v-model="form.status" :options="statuses" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Categoria" />
                        <Select v-model="form.category_id" :options="categoryOptions" placeholder="Selecione a categoria" class="mt-1" />
                        <InputError class="mt-1" :message="form.errors.category_id" />
                    </div>

                    <div>
                        <InputLabel value="Descrição" />
                        <TextInput v-model="form.description" type="text" class="mt-1 block w-full" placeholder="Ex.: Honorários, material de escritório..." />
                        <InputError class="mt-1" :message="form.errors.description" />
                    </div>

                    <div>
                        <InputLabel value="Valor (R$)" />
                        <TextInput v-model="form.amount" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="0,00" />
                        <InputError class="mt-1" :message="form.errors.amount" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel value="Mês" />
                            <Select v-model="form.month" :options="monthOptions" class="mt-1 capitalize" />
                        </div>
                        <div>
                            <InputLabel value="Ano" />
                            <Select v-model="form.year" :options="yearOptions" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Vencimento" />
                            <TextInput v-model="form.due_date" type="date" class="mt-1 block w-full" />
                            <InputError class="mt-1" :message="form.errors.due_date" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Método de pagamento" />
                            <Select v-model="form.payment_method" :options="paymentMethods" placeholder="Selecione o método" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Comprovante (máx. 2MB)" />
                            <input
                                type="file"
                                accept=".pdf,.png,.jpg,.jpeg,.webp"
                                class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                @input="form.attachment = $event.target.files[0]"
                            />
                            <InputError class="mt-1" :message="form.errors.attachment" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Observações" />
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Observações opcionais"
                        ></textarea>
                        <InputError class="mt-1" :message="form.errors.notes" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <SecondaryButton type="button" @click="modalOpen = false">Cancelar</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Salvar lançamento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir lançamento"
            :message="`Esta ação removerá o lançamento ${deleting?.description}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteTransaction"
            @close="deleting = null"
        />
    </AdminLayout>
</template>