<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DeadlineForm from './Partials/DeadlineForm.vue';

const props = defineProps({
    deadline: Object,
    processes: Array,
    users: Array,
    statuses: Array,
    priorities: Array,
});

const form = useForm({
    process_id: props.deadline.process_id ?? '',
    responsible_user_id: props.deadline.responsible_user_id ?? '',
    title: props.deadline.title,
    description: props.deadline.description ?? '',
    start_date: props.deadline.start_date ?? '',
    due_date: props.deadline.due_date,
    status: props.deadline.status,
    priority: props.deadline.priority,
});

const submit = () => {
    form.put(route('admin.deadlines.update', props.deadline.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar prazo" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar prazo</h1>
                <p class="mt-1 text-sm text-slate-500">{{ deadline.title }}</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <DeadlineForm
                        :form="form"
                        :processes="processes"
                        :users="users"
                        :statuses="statuses"
                        :priorities="priorities"
                    />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.deadlines.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-25"
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