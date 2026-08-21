<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ExpenseForm from './Partials/ExpenseForm.vue';

const props = defineProps({
    expense: Object,
    categories: Array,
});

const form = useForm({
    description: props.expense.description,
    category_id: props.expense.category_id ?? '',
    due_day: props.expense.due_day,
    amount: props.expense.amount,
    notes: props.expense.notes ?? '',
    active: props.expense.active,
});

const submit = () => {
    form.put(route('admin.financial.expenses.update', props.expense.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Editar despesa — ${expense.description}`" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">Editar despesa fixa</h1>
                <p class="mt-1 text-sm text-slate-500">Atualize os dados da despesa recorrente.</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <ExpenseForm :form="form" :categories="categories" />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.financial.expenses.index')"
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