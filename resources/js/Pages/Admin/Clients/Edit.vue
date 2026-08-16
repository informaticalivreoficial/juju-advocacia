<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ClientForm from './Partials/ClientForm.vue';

const props = defineProps({
    client: Object,
    types: Array,
    maritalStatuses: Array,
    states: Array,
});

const form = useForm({
    type: props.client.type,
    name: props.client.name ?? '',
    company_name: props.client.company_name ?? '',
    trade_name: props.client.trade_name ?? '',
    document: props.client.document ?? '',
    email: props.client.email ?? '',
    phone: props.client.phone ?? '',
    mobile: props.client.mobile ?? '',
    birth_date: props.client.birth_date ?? '',
    marital_status: props.client.marital_status ?? '',
    profession: props.client.profession ?? '',
    state_registration: props.client.state_registration ?? '',
    zip_code: props.client.zip_code ?? '',
    address: props.client.address ?? '',
    number: props.client.number ?? '',
    complement: props.client.complement ?? '',
    neighborhood: props.client.neighborhood ?? '',
    city: props.client.city ?? '',
    state: props.client.state ?? '',
    notes: props.client.notes ?? '',
    is_active: props.client.is_active,
});

const submit = () => {
    form.put(route('admin.clients.update', props.client.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar cliente" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar cliente</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ client.type === 'company' ? client.company_name : client.name }}
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <ClientForm :form="form" :types="types" :marital-statuses="maritalStatuses" :states="states" />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.clients.show', client.id)"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
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