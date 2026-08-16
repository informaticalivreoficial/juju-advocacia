<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TaskForm from './Partials/TaskForm.vue';

const props = defineProps({
    task: Object,
    processes: Array,
    clients: Array,
    deadlines: Array,
    users: Array,
    statuses: Array,
    priorities: Array,
});

const form = useForm({
    deadline_id: props.task.deadline_id ?? '',
    client_id: props.task.client_id ?? '',
    process_id: props.task.process_id ?? '',
    responsible_user_id: props.task.responsible_user_id ?? '',
    title: props.task.title,
    description: props.task.description ?? '',
    status: props.task.status,
    priority: props.task.priority,
    due_date: props.task.due_date ?? '',
});

const submit = () => {
    form.put(route('admin.tasks.update', props.task.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar tarefa" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar tarefa</h1>
                <p class="mt-1 text-sm text-slate-500">{{ task.title }}</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <TaskForm
                        :form="form"
                        :processes="processes"
                        :clients="clients"
                        :deadlines="deadlines"
                        :users="users"
                        :statuses="statuses"
                        :priorities="priorities"
                    />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.tasks.index')"
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