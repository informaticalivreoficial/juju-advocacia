<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CalendarForm from './Partials/CalendarForm.vue';

const props = defineProps({
    event: Object,
    types: Array,
    processes: Array,
    clients: Array,
    users: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const pad = (value) => String(value).padStart(2, '0');

const toLocalInput = (iso) => {
    const d = new Date(iso);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const form = useForm({
    process_id: props.event.process_id ?? '',
    client_id: props.event.client_id ?? '',
    responsible_user_id: props.event.responsible_user_id ?? '',
    title: props.event.title,
    description: props.event.description ?? '',
    type: props.event.type,
    start_datetime: toLocalInput(props.event.start_datetime),
    end_datetime: props.event.end_datetime ? toLocalInput(props.event.end_datetime) : '',
    all_day: props.event.all_day,
    location: props.event.location ?? '',
});

const submit = () => {
    form.put(route('admin.calendar.update', props.event.id));
};

const deleting = ref(false);

const deleteEvent = () => {
    router.delete(route('admin.calendar.destroy', props.event.id), {
        onSuccess: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar evento" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar evento</h1>
                <p class="mt-1 text-sm text-slate-500">{{ event.title }}</p>
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

            <Card v-if="can('calendar.delete')" padding="p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-red-600">Zona de perigo</h2>
                        <p class="mt-1 text-sm text-slate-500">Excluir este evento da agenda.</p>
                    </div>
                    <button
                        type="button"
                        @click="deleting = true"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"
                    >
                        Excluir evento
                    </button>
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting"
            title="Excluir evento"
            :message="`Esta ação removerá o evento ${event.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteEvent"
            @close="deleting = false"
        />
    </AdminLayout>
</template>