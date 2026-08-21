<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from '@/Components/Select.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    categories: Object,
    year: Number,
    month: Number,
    types: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const month = ref(props.month);
const year = ref(props.year);
const activeType = ref('expense');

const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, i) => ({
        value: i + 1,
        label: new Date(year.value, i, 1).toLocaleDateString('pt-BR', { month: 'long' }),
    }))
);

const yearOptions = computed(() => {
    const years = [];
    for (let y = new Date().getFullYear() + 1; y >= new Date().getFullYear() - 5; y--) years.push({ value: y, label: `Ano ${y}` });
    return years;
});

const applyFilters = () => {
    router.get(route('admin.financial.categories.index'), { year: year.value, month: month.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch([month, year], applyFilters);

const list = computed(() => props.categories?.[activeType.value] ?? []);

const typeLabel = (type) => (type === 'income' ? 'Receita' : 'Despesa');

const budgetPercent = (category) => category.budget?.percent ?? 0;
const budgetLimit = (category) => category.budget?.limit ?? 0;
const budgetUsed = (category) => category.budget?.used ?? 0;

const budgetStatus = (category) => {
    const percent = budgetPercent(category);
    if (budgetLimit(category) <= 0) return { color: 'bg-slate-200', label: 'Sem orçamento' };
    if (percent >= 100) return { color: 'bg-red-500', label: 'Limite atingido' };
    if (percent >= 80) return { color: 'bg-amber-500', label: `${percent}% do limite` };
    return { color: 'bg-emerald-500', label: `${percent}% do limite` };
};

const COLOR_OPTIONS = [
    { value: '#6366f1', label: 'Índigo' },
    { value: '#8b5cf6', label: 'Roxo' },
    { value: '#ec4899', label: 'Rosa' },
    { value: '#ef4444', label: 'Vermelho' },
    { value: '#f59e0b', label: 'Âmbar' },
    { value: '#10b981', label: 'Verde' },
    { value: '#06b6d4', label: 'Ciano' },
    { value: '#64748b', label: 'Cinza' },
];

const categoryModal = ref(false);
const editingCategory = ref(null);
const categoryForm = useForm({
    name: '',
    type: 'expense',
    color: '#6366f1',
    icon: 'tag',
    active: true,
});

const openCreateCategory = () => {
    editingCategory.value = null;
    categoryForm.clearErrors();
    categoryForm.reset();
    Object.assign(categoryForm, { name: '', type: activeType.value, color: '#6366f1', icon: 'tag', active: true });
    categoryModal.value = true;
};

const openEditCategory = (category) => {
    editingCategory.value = category;
    categoryForm.clearErrors();
    categoryForm.reset();
    Object.assign(categoryForm, {
        name: category.name,
        type: category.type,
        color: category.color,
        icon: category.icon,
        active: category.active,
    });
    categoryModal.value = true;
};

const submitCategory = () => {
    if (editingCategory.value) {
        categoryForm.put(route('admin.financial.categories.update', editingCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                categoryModal.value = false;
                editingCategory.value = null;
            },
        });
    } else {
        categoryForm.post(route('admin.financial.categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                categoryModal.value = false;
            },
        });
    }
};

const toggleActive = (category) => {
    router.patch(route('admin.financial.categories.toggle-active', category.id), {}, { preserveScroll: true });
};

const deleting = ref(null);
const deleteCategory = () => {
    router.delete(route('admin.financial.categories.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const budgetModal = ref(false);
const budgetCategory = ref(null);
const budgetForm = useForm({
    amount: '',
});

const openBudget = (category) => {
    budgetCategory.value = category;
    budgetForm.clearErrors();
    budgetForm.reset();
    Object.assign(budgetForm, { amount: budgetLimit(category) > 0 ? budgetLimit(category) : '' });
    budgetModal.value = true;
};

const submitBudget = () => {
    budgetForm.post(
        route('admin.financial.budgets.store'),
        {
            category_id: budgetCategory.value.id,
            year: year.value,
            month: month.value,
            amount: budgetForm.amount,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                budgetModal.value = false;
                budgetCategory.value = null;
            },
        }
    );
};

const removeBudget = (category) => {
    if (!category.budget?.id || !confirm('Remover o orçamento desta categoria?')) return;
    router.delete(route('admin.financial.budgets.destroy', category.budget.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Categorias" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Categorias</h1>
                    <p class="mt-1 text-sm text-slate-500">Organize despesas e receitas por categoria e defina orçamentos mensais.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Select v-model="month" :options="monthOptions" class="w-40 capitalize" />
                    <Select v-model="year" :options="yearOptions" class="w-28" />
                    <button
                        v-if="can('financial.create')"
                        type="button"
                        @click="openCreateCategory"
                        class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nova categoria
                    </button>
                </div>
            </div>

            <div class="flex gap-1">
                <button
                    v-for="type in types"
                    :key="type.value"
                    type="button"
                    @click="activeType = type.value"
                    :class="[
                        'rounded-full px-4 py-1.5 text-sm font-medium transition',
                        activeType === type.value ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    {{ type.label }}
                </button>
            </div>

            <Card>
                <ul v-if="list.length > 0" class="divide-y divide-slate-100">
                    <li v-for="category in list" :key="category.id" class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:gap-4">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white" :style="{ backgroundColor: category.color }">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ category.name }}</p>
                                    <Badge :color="category.active ? 'green' : 'gray'">{{ category.active ? 'Ativa' : 'Inativa' }}</Badge>
                                </div>
                                <p class="text-xs text-slate-500">{{ typeLabel(category.type) }}</p>
                            </div>
                        </div>

                        <div class="w-full sm:w-64">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-600">{{ budgetStatus(category).label }}</span>
                                <span class="text-slate-500">
                                    <template v-if="budgetLimit(category) > 0">
                                        {{ formatCurrency(budgetUsed(category)) }} / {{ formatCurrency(budgetLimit(category)) }}
                                    </template>
                                    <template v-else>{{ formatCurrency(budgetUsed(category)) }}</template>
                                </span>
                            </div>
                            <div class="mt-1 h-2 w-full rounded-full bg-slate-100">
                                <div
                                    class="h-2 rounded-full transition-all"
                                    :class="budgetStatus(category).color"
                                    :style="{ width: Math.min(100, budgetPercent(category)) + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-1">
                            <button
                                v-if="can('financial.update')"
                                type="button"
                                class="rounded-md p-2 text-slate-500 transition hover:bg-brand-50 hover:text-brand-700"
                                title="Definir orçamento"
                                @click="openBudget(category)"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button
                                v-if="budgetLimit(category) > 0 && can('financial.update')"
                                type="button"
                                class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                title="Remover orçamento"
                                @click="removeBudget(category)"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button
                                v-if="can('financial.update')"
                                type="button"
                                :title="category.active ? 'Desativar' : 'Ativar'"
                                class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                @click="toggleActive(category)"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path v-if="category.active" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M9.75 12a2.25 2.25 0 015.5 0M9.75 12l-1.5 0m7.5 0l1.5 0M9.75 12l-.75 1.5m1.5-1.5l.75 1.5m1.5-1.5l.75 1.5m1.5-1.5l.75 1.5" />
                                </svg>
                            </button>
                            <button
                                v-if="can('financial.update')"
                                type="button"
                                class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                title="Editar"
                                @click="openEditCategory(category)"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button
                                v-if="can('financial.delete')"
                                type="button"
                                class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                title="Excluir"
                                @click="deleting = category"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>
                <div v-else class="px-6 py-10">
                    <EmptyState
                        :title="`Nenhuma categoria de ${typeLabel(activeType).toLowerCase()} encontrada`"
                        description="Crie categorias para organizar seus lançamentos."
                    />
                </div>
            </Card>
        </div>

        <Modal :show="categoryModal" @close="categoryModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-brand-950">{{ editingCategory ? 'Editar categoria' : 'Nova categoria' }}</h2>
                <form class="mt-6 space-y-4" @submit.prevent="submitCategory">
                    <div>
                        <InputLabel value="Nome" />
                        <TextInput v-model="categoryForm.name" type="text" class="mt-1 block w-full" placeholder="Ex.: Honorários, Material..." />
                        <InputError class="mt-1" :message="categoryForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel value="Tipo" />
                        <Select v-model="categoryForm.type" :options="types" class="mt-1" />
                        <InputError class="mt-1" :message="categoryForm.errors.type" />
                    </div>

                    <div>
                        <InputLabel value="Cor" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="color in COLOR_OPTIONS"
                                :key="color.value"
                                type="button"
                                :title="color.label"
                                :class="['h-8 w-8 rounded-full ring-2 ring-offset-2 transition', categoryForm.color === color.value ? 'ring-slate-400' : 'ring-transparent']"
                                :style="{ backgroundColor: color.value }"
                                @click="categoryForm.color = color.value"
                            ></button>
                        </div>
                        <InputError class="mt-1" :message="categoryForm.errors.color" />
                    </div>

                    <div>
                        <InputLabel value="Ativa" />
                        <label class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input
                                type="checkbox"
                                v-model="categoryForm.active"
                                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            Usar esta categoria nos lançamentos
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <SecondaryButton type="button" @click="categoryModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton :disabled="categoryForm.processing">
                            {{ categoryForm.processing ? 'Salvando...' : 'Salvar' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="budgetModal" @close="budgetModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-brand-950">Orçamento mensal</h2>
                <p class="text-sm text-slate-500">
                    {{ budgetCategory?.name }} · {{ monthOptions.find((m) => m.value === month)?.label }} de {{ year }}
                </p>

                <form class="mt-6 space-y-4" @submit.prevent="submitBudget">
                    <div>
                        <InputLabel value="Limite do orçamento (R$)" />
                        <TextInput v-model="budgetForm.amount" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="0,00" />
                        <InputError class="mt-1" :message="budgetForm.errors.amount" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <SecondaryButton type="button" @click="budgetModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton :disabled="budgetForm.processing">
                            {{ budgetForm.processing ? 'Salvando...' : 'Definir orçamento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir categoria"
            :message="`Esta ação removerá a categoria ${deleting?.name}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteCategory"
            @close="deleting = null"
        />
    </AdminLayout>
</template>