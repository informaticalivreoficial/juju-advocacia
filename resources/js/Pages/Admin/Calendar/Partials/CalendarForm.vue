<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    form: Object,
    types: Array,
    processes: Array,
    clients: Array,
    users: Array,
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel for="title" value="Título do evento" />
            <TextInput
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ex.: Audiência de conciliação"
                autofocus
                autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="type" value="Tipo" />
                <Select v-model="form.type" :options="types" />
                <InputError class="mt-2" :message="form.errors.type" />
            </div>

            <div>
                <InputLabel for="location" value="Local" />
                <TextInput
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Ex.: Fórum Central"
                />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="start_datetime" value="Início" />
                <TextInput id="start_datetime" v-model="form.start_datetime" type="datetime-local" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.start_datetime" />
            </div>

            <div>
                <InputLabel for="end_datetime" value="Fim (opcional)" />
                <TextInput id="end_datetime" v-model="form.end_datetime" type="datetime-local" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.end_datetime" />
            </div>
        </div>

        <label class="flex items-center gap-2">
            <input
                v-model="form.all_day"
                type="checkbox"
                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            <span class="text-sm font-medium text-slate-700">Dia todo</span>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="process_id" value="Processo (opcional)" />
                <Select v-model="form.process_id" :options="processes" placeholder="Selecione o processo..." />
                <InputError class="mt-2" :message="form.errors.process_id" />
            </div>

            <div>
                <InputLabel for="client_id" value="Cliente (opcional)" />
                <Select v-model="form.client_id" :options="clients" placeholder="Selecione o cliente..." />
                <InputError class="mt-2" :message="form.errors.client_id" />
            </div>
        </div>

        <div>
            <InputLabel for="responsible_user_id" value="Responsável (opcional)" />
            <Select v-model="form.responsible_user_id" :options="users" placeholder="Selecione o responsável..." />
            <InputError class="mt-2" :message="form.errors.responsible_user_id" />
        </div>

        <div>
            <InputLabel for="description" value="Descrição" />
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                placeholder="Detalhes do evento..."
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>
    </div>
</template>