<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ProcessForm from './Partials/ProcessForm.vue';

const props = defineProps({
    process: Object,
    clients: Array,
    users: Array,
    areas: Array,
    statuses: Array,
    priorities: Array,
    instances: Array,
});

const form = useForm({
    client_id: props.process.client_id ?? '',
    responsible_user_id: props.process.responsible_user_id ?? '',
    process_number: props.process.process_number ?? '',
    title: props.process.title ?? '',
    area: props.process.area,
    action_type: props.process.action_type ?? '',
    court: props.process.court ?? '',
    district: props.process.district ?? '',
    court_division: props.process.court_division ?? '',
    instance: props.process.instance ?? '',
    plaintiff: props.process.plaintiff ?? '',
    defendant: props.process.defendant ?? '',
    case_value: props.process.case_value ?? '',
    distribution_date: props.process.distribution_date ?? '',
    status: props.process.status,
    priority: props.process.priority,
    confidentiality: props.process.confidentiality,
    description: props.process.description ?? '',
});

const submit = () => {
    form.put(route('admin.processes.update', props.process.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar processo" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar processo</h1>
                <p class="mt-1 text-sm text-slate-500">{{ process.process_number ?? process.title }}</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <ProcessForm
                        :form="form"
                        :clients="clients"
                        :users="users"
                        :areas="areas"
                        :statuses="statuses"
                        :priorities="priorities"
                        :instances="instances"
                    />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.processes.show', process.id)"
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