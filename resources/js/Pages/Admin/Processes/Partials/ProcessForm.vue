<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    form: Object,
    clients: Array,
    users: Array,
    areas: Array,
    statuses: Array,
    priorities: Array,
    instances: Array,
});
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <InputLabel for="title" value="Título" />
                <TextInput
                    id="title"
                    type="text"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.title"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <div>
                <InputLabel for="process_number" value="Número do processo" />
                <TextInput
                    id="process_number"
                    type="text"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.process_number"
                    placeholder="0000000-00.0000.0.00.0000"
                />
                <InputError class="mt-2" :message="form.errors.process_number" />
            </div>

            <div>
                <InputLabel for="client_id" value="Cliente" />
                <Select id="client_id" v-model="form.client_id" :options="clients" placeholder="Selecione o cliente" />
                <InputError class="mt-2" :message="form.errors.client_id" />
            </div>

            <div>
                <InputLabel for="responsible_user_id" value="Responsável" />
                <Select
                    id="responsible_user_id"
                    v-model="form.responsible_user_id"
                    :options="users"
                    placeholder="Selecione o responsável"
                />
                <InputError class="mt-2" :message="form.errors.responsible_user_id" />
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-slate-500">Classificação</p>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <InputLabel for="area" value="Área" />
                    <Select id="area" v-model="form.area" :options="areas" />
                    <InputError class="mt-2" :message="form.errors.area" />
                </div>

                <div>
                    <InputLabel for="status" value="Status" />
                    <Select id="status" v-model="form.status" :options="statuses" />
                    <InputError class="mt-2" :message="form.errors.status" />
                </div>

                <div>
                    <InputLabel for="priority" value="Prioridade" />
                    <Select id="priority" v-model="form.priority" :options="priorities" />
                    <InputError class="mt-2" :message="form.errors.priority" />
                </div>

                <div>
                    <InputLabel for="instance" value="Instância" />
                    <Select id="instance" v-model="form.instance" :options="instances" placeholder="Selecione" />
                    <InputError class="mt-2" :message="form.errors.instance" />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-slate-500">Dados judiciais</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="action_type" value="Tipo de ação" />
                    <TextInput
                        id="action_type"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.action_type"
                    />
                    <InputError class="mt-2" :message="form.errors.action_type" />
                </div>

                <div>
                    <InputLabel for="court" value="Tribunal" />
                    <TextInput
                        id="court"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.court"
                        placeholder="TJSP, TRT-15..."
                    />
                    <InputError class="mt-2" :message="form.errors.court" />
                </div>

                <div>
                    <InputLabel for="district" value="Comarca/Vara" />
                    <TextInput
                        id="district"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.district"
                    />
                    <InputError class="mt-2" :message="form.errors.district" />
                </div>

                <div>
                    <InputLabel for="court_division" value="Turma/Câmara" />
                    <TextInput
                        id="court_division"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.court_division"
                    />
                    <InputError class="mt-2" :message="form.errors.court_division" />
                </div>

                <div>
                    <InputLabel for="plaintiff" value="Autor" />
                    <TextInput
                        id="plaintiff"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.plaintiff"
                    />
                    <InputError class="mt-2" :message="form.errors.plaintiff" />
                </div>

                <div>
                    <InputLabel for="defendant" value="Réu" />
                    <TextInput
                        id="defendant"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.defendant"
                    />
                    <InputError class="mt-2" :message="form.errors.defendant" />
                </div>

                <div>
                    <InputLabel for="case_value" value="Valor da causa" />
                    <TextInput
                        id="case_value"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.case_value"
                    />
                    <InputError class="mt-2" :message="form.errors.case_value" />
                </div>

                <div>
                    <InputLabel for="distribution_date" value="Data de distribuição" />
                    <TextInput
                        id="distribution_date"
                        type="date"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.distribution_date"
                    />
                    <InputError class="mt-2" :message="form.errors.distribution_date" />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <InputLabel for="description" value="Descrição" />
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <label class="flex items-center gap-2 border-t border-slate-100 pt-5">
            <input
                v-model="form.confidentiality"
                type="checkbox"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="text-sm font-medium text-slate-700">Processo confidencial</span>
        </label>
    </div>
</template>