<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CalendarForm from './Partials/CalendarForm.vue';

const props = defineProps({
    types: Array,
    processes: Array,
    clients: Array,
    users: Array,
    date: String,
});

const pad = (value) => String(value).padStart(2, '0');

const defaultStart = computed(() => {
    if (!props.date) return '';
    return `${props.date}T09:00`;
});

const form = useForm({
    process_id: '',
    client_id: '',
    responsible_user_id: '',
    title: '',
    description: '',
    type: 'hearing',
    start_datetime: defaultStart.value,
    end_datetime: '',
    all_day: false,
    location: '',
});

const submit = () => {
    form.post(route('admin.calendar.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Novo evento" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Novo evento</h1>
                <p class="mt-1 text-sm text-slate-500">Agende uma audiência, reunião ou compromisso.</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CalendarForm
                        :form="form"
                        :types="types"
                        :processes="processes"
                        :clients="clients"
                        :users="users"
                    />

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.calendar.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-25"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Cadastrar evento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>