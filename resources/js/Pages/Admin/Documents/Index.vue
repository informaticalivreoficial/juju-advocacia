<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    categories: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const categoryLabels = Object.fromEntries(props.categories.map((item) => [item.value, item.label]));
const categoryColor = (category) => ({
    contract: 'indigo',
    petition: 'amber',
    decision: 'green',
    certificate: 'orange',
    power_of_attorney: 'gray',
    report: 'slate',
    other: 'gray',
}[category] ?? 'gray');

const search = ref(props.filters?.search ?? '');
const category = ref(props.filters?.category ?? '');

let searchTimeout;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters({ search: value || undefined }), 300);
});

watch(category, (value) => applyFilters({ category: value || undefined }));

const applyFilters = (extra = {}) => {
    router.get(
        route('admin.documents.index'),
        {
            search: search.value || undefined,
            category: category.value || undefined,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const formatDate = (value) => {
    const date = new Date(value);
    return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};

const fileIcon = (name = '') => {
    const ext = name.split('.').pop()?.toLowerCase();
    if (['doc', 'docx'].includes(ext)) return '📄';
    if (['xls', 'xlsx'].includes(ext)) return '📊';
    if (['jpg', 'jpeg', 'png'].includes(ext)) return '🖼️';
    if (ext === 'zip') return '🗜️';
    return '📑';
};

const deleting = ref(null);

const deleteDocument = () => {
    router.delete(route('admin.documents.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Documentos" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Documentos</h1>
                    <p class="mt-1 text-sm text-slate-500">Arquivos de processos e clientes.</p>
                </div>
                <Link
                    v-if="can('documents.create')"
                    :href="route('admin.documents.create')"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Enviar documento
                </Link>
            </div>

            <Card>
                <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <TextInput
                            v-model="search"
                            type="search"
                            placeholder="Buscar por documento, processo ou cliente..."
                            class="mt-0 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <Select v-model="category" :options="categories" placeholder="Todas as categorias" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Documento</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell">Categoria</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 xl:table-cell">Processo</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Enviado por</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Tamanho</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Data</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="document in documents.data" :key="document.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900">
                                        <span class="mr-1.5">{{ fileIcon(document.file_name) }}</span>
                                        {{ document.title }}
                                    </p>
                                    <p class="mt-0.5 truncate text-xs text-slate-400">{{ document.file_name }}</p>
                                </td>
                                <td class="hidden px-4 py-3 lg:table-cell">
                                    <Badge :color="categoryColor(document.category)">
                                        {{ categoryLabels[document.category] ?? document.category }}
                                    </Badge>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 xl:table-cell">
                                    <template v-if="document.process">
                                        <Link :href="route('admin.processes.show', document.process.id)" class="hover:text-indigo-600 hover:underline">
                                            {{ document.process.title }}
                                        </Link>
                                    </template>
                                    <span v-else>—</span>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">
                                    {{ document.uploader?.name ?? '—' }}
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">{{ document.size_label }}</td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">{{ formatDate(document.created_at) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.documents.download', document.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600"
                                            title="Baixar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('documents.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = document"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="documents.data.length === 0">
                                <td colspan="7" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhum documento encontrado"
                                        description="Envie documentos para processos e clientes."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="documents.links" :from="documents.from" :to="documents.to" :total="documents.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir documento"
            :message="`Esta ação removerá o documento ${deleting?.title}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteDocument"
            @close="deleting = null"
        />
    </AdminLayout>
</template>