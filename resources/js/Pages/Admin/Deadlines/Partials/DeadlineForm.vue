<script setup>
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    form: Object,
    processes: Array,
    users: Array,
    statuses: Array,
    priorities: Array,
});

const formStatuses = computed(() =>
    (props.statuses ?? []).filter((status) => !['expired', 'cancelled'].includes(status.value))
);
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel value="Título do prazo" />
            <TextInput
                v-model="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ex.: Contestar prazo"
                autofocus
                autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Processo (opcional)" />
                <Select v-model="form.process_id" :options="processes" placeholder="Selecione o processo..." />
                <InputError class="mt-2" :message="form.errors.process_id" />
            </div>

            <div>
                <InputLabel value="Responsável (opcional)" />
                <Select v-model="form.responsible_user_id" :options="users" placeholder="Selecione o responsável..." />
                <InputError class="mt-2" :message="form.errors.responsible_user_id" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Início" />
                <TextInput v-model="form.start_date" type="date" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.start_date" />
            </div>

            <div>
                <InputLabel value="Vencimento" />
                <TextInput v-model="form.due_date" type="date" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.due_date" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Prioridade" />
                <Select v-model="form.priority" :options="priorities" />
                <InputError class="mt-2" :message="form.errors.priority" />
            </div>

            <div>
                <InputLabel value="Status" />
                <Select v-model="form.status" :options="formStatuses" />
                <InputError class="mt-2" :message="form.errors.status" />
            </div>
        </div>

        <div>
            <InputLabel for="description" value="Descrição" />
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                placeholder="Detalhes, observações e instruções do prazo..."
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>
    </div>
</template>