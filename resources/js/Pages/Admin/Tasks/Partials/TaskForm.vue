<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    form: Object,
    processes: Array,
    clients: Array,
    deadlines: Array,
    users: Array,
    statuses: Array,
    priorities: Array,
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel for="title" value="Título da tarefa" />
            <TextInput
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ex.: Redigir petição inicial"
                autofocus
                autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

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

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel for="deadline_id" value="Prazo vinculado (opcional)" />
                <Select v-model="form.deadline_id" :options="deadlines" placeholder="Selecione o prazo..." />
                <InputError class="mt-2" :message="form.errors.deadline_id" />
            </div>

            <div>
                <InputLabel for="responsible_user_id" value="Responsável (opcional)" />
                <Select v-model="form.responsible_user_id" :options="users" placeholder="Selecione o responsável..." />
                <InputError class="mt-2" :message="form.errors.responsible_user_id" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <InputLabel for="status" value="Status" />
                <Select v-model="form.status" :options="statuses" />
                <InputError class="mt-2" :message="form.errors.status" />
            </div>

            <div>
                <InputLabel for="priority" value="Prioridade" />
                <Select v-model="form.priority" :options="priorities" />
                <InputError class="mt-2" :message="form.errors.priority" />
            </div>

            <div>
                <InputLabel for="due_date" value="Prazo interno" />
                <TextInput id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.due_date" />
            </div>
        </div>

        <div>
            <InputLabel for="description" value="Descrição" />
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                placeholder="Detalhes da tarefa..."
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>
    </div>
</template>