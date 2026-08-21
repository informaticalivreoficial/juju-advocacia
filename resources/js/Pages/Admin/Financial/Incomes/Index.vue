<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency } from '@/Utils/format';

const props = defineProps({
    incomes: Object,
    filters: Object,
    counts: Object,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const search = ref(props.filters?.search ?? '');

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.financial.incomes.index'), { search: value || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

const deleting = ref(null);

const deleteIncome = () => {
    router.delete(route('admin.financial.incomes.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const toggleActive = (income) => {
    router.patch(route('admin.financial.incomes.toggle-active', income.id), {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Receitas fixas" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-brand-950">Receitas fixas</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ counts?.active ?? 0 }} ativas de {{ counts?.total ?? 0 }} cadastradas.
                    </p>
                </div>
                <Link
                    v-if="can('financial.create')"
                    :href="route('admin.financial.incomes.create')"
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nova receita
                </Link>
            </div>

            <Card>
                <div class="mb-4">
                    <TextInput
                        v-model="search"
                        type="search"
                        placeholder="Buscar por descrição ou categoria..."
                        class="mt-0 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Receita</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Categoria</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dia</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Valor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="income in incomes.data" :key="income.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900">{{ income.description }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ income.notes }}</p>
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: income.category?.color ?? '#94a3b8' }"></span>
                                        <span class="text-sm text-slate-600">{{ income.category?.name ?? 'Sem categoria' }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">Dia {{ income.receive_day }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-emerald-600">{{ formatCurrency(income.amount) }}</td>
                                <td class="px-4 py-3">
                                    <Badge :color="income.active ? 'green' : 'gray'">{{ income.active ? 'Ativa' : 'Inativa' }}</Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            v-if="can('financial.update')"
                                            type="button"
                                            :title="income.active ? 'Desativar' : 'Ativar'"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            @click="toggleActive(income)"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path v-if="income.active" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M9.75 12a2.25 2.25 0 015.5 0M9.75 12l-1.5 0m7.5 0l1.5 0M9.75 12l-.75 1.5m1.5-1.5l.75 1.5m1.5-1.5l.75 1.5m1.5-1.5l.75 1.5" />
                                            </svg>
                                        </button>
                                        <Link
                                            v-if="can('financial.update')"
                                            :href="route('admin.financial.incomes.edit', income.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('financial.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = income"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="incomes.data.length === 0">
                                <td colspan="6" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhuma receita encontrada"
                                        description="Cadastre sua primeira receita fixa."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="incomes.links" :from="incomes.from" :to="incomes.to" :total="incomes.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir receita fixa"
            :message="`Esta ação removerá a receita ${deleting?.description}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteIncome"
            @close="deleting = null"
        />
    </AdminLayout>
</template>