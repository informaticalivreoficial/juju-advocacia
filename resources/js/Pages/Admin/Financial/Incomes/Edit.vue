<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import IncomeForm from './Partials/IncomeForm.vue';

const props = defineProps({
    income: Object,
    categories: Array,
});

const form = useForm({
    description: props.income.description,
    category_id: props.income.category_id ?? '',
    receive_day: props.income.receive_day,
    amount: props.income.amount,
    notes: props.income.notes ?? '',
    active: props.income.active,
});

const submit = () => {
    form.put(route('admin.financial.incomes.update', props.income.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Editar receita — ${income.description}`" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">Editar receita fixa</h1>
                <p class="mt-1 text-sm text-slate-500">Atualize os dados da receita recorrente.</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <IncomeForm :form="form" :categories="categories" />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.financial.incomes.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>