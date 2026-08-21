<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ClientForm from './Partials/ClientForm.vue';

defineProps({
    types: Array,
    maritalStatuses: Array,
    states: Array,
});

const form = useForm({
    type: 'individual',
    name: '',
    company_name: '',
    trade_name: '',
    document: '',
    email: '',
    phone: '',
    mobile: '',
    birth_date: '',
    marital_status: '',
    profession: '',
    state_registration: '',
    zip_code: '',
    address: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
    state: '',
    notes: '',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.clients.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Novo cliente" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Novo cliente</h1>
                <p class="mt-1 text-sm text-slate-500">Cadastre um cliente pessoa física ou jurídica.</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <ClientForm :form="form" :types="types" :marital-statuses="maritalStatuses" :states="states" />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.clients.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-25"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Cadastrar cliente' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>