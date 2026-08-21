<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    categories: Array,
    processes: Array,
    clients: Array,
});

const form = useForm({
    process_id: '',
    client_id: '',
    title: '',
    description: '',
    category: 'other',
    file: null,
});

const onFileChange = (event) => {
    form.file = event.target.files[0] ?? null;
};

const formatSize = (bytes) => {
    if (!bytes) return '';
    if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
    if (bytes >= 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${bytes} B`;
};

const submit = () => {
    form.post(route('admin.documents.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Enviar documento" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Enviar documento</h1>
                <p class="mt-1 text-sm text-slate-500">Faça upload de um arquivo para o escritório.</p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="title" value="Título do documento" />
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ex.: Contrato de honorários"
                                autofocus
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="category" value="Categoria" />
                                <Select v-model="form.category" :options="categories" />
                                <InputError class="mt-2" :message="form.errors.category" />
                            </div>

                            <div>
                                <InputLabel for="process_id" value="Processo (opcional)" />
                                <Select v-model="form.process_id" :options="processes" placeholder="Selecione o processo..." />
                                <InputError class="mt-2" :message="form.errors.process_id" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="client_id" value="Cliente (opcional)" />
                            <Select v-model="form.client_id" :options="clients" placeholder="Selecione o cliente..." />
                            <InputError class="mt-2" :message="form.errors.client_id" />
                        </div>

                        <div>
                            <InputLabel for="file" value="Arquivo" />
                            <input
                                id="file"
                                type="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                                class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                @change="onFileChange"
                            />
                            <p v-if="form.file" class="mt-2 text-sm text-slate-600">
                                {{ form.file.name }}
                                <span class="text-slate-400">({{ formatSize(form.file.size) }})</span>
                            </p>
                            <InputError class="mt-2" :message="form.errors.file" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Descrição" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                                placeholder="Descrição opcional do documento..."
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.documents.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-25"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Enviando...' : 'Enviar documento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>