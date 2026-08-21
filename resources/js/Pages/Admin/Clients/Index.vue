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
import { formatDocument } from '@/Utils/format.js';

const props = defineProps({
    clients: Object,
    filters: Object,
    types: Array,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const search = ref(props.filters?.search ?? '');
const type = ref(props.filters?.type ?? '');
const status = ref(props.filters?.status ?? '');

let searchTimeout;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters({ search: value || undefined }), 300);
});

watch([type, status], () =>
    applyFilters({
        type: type.value || undefined,
        status: status.value || undefined,
    })
);

const applyFilters = (extra = {}) => {
    router.get(
        route('admin.clients.index'),
        {
            search: search.value || undefined,
            type: type.value || undefined,
            status: status.value || undefined,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const deleting = ref(null);

const deleteClient = () => {
    router.delete(route('admin.clients.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const displayName = (client) =>
    client.type === 'company' ? (client.company_name ?? client.name) : client.name;

const initials = (name) =>
    (name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
</script>

<template>
    <AdminLayout>
        <Head title="Clientes" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Clientes</h1>
                    <p class="mt-1 text-sm text-slate-500">Cadastro de clientes do escritório.</p>
                </div>
                <Link
                    v-if="can('clients.create')"
                    :href="route('admin.clients.create')"
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Novo cliente
                </Link>
            </div>

            <Card>
                <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <TextInput
                            v-model="search"
                            type="search"
                            placeholder="Buscar por nome, documento ou e-mail..."
                            class="mt-0 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500"
                        />
                    </div>
                    <div>
                        <Select v-model="type" :options="types" placeholder="Todos os tipos" />
                    </div>
                    <div>
                        <Select
                            v-model="status"
                            :options="[
                                { value: 'active', label: 'Ativos' },
                                { value: 'inactive', label: 'Inativos' },
                            ]"
                            placeholder="Todos os status"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:table-cell">Documento</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell">E-mail</th>
                                <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 md:table-cell">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                                            {{ initials(displayName(client)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <Link :href="route('admin.clients.show', client.id)" class="block truncate text-sm font-medium text-slate-900 hover:text-brand-600 hover:underline">
                                                {{ displayName(client) }}
                                            </Link>
                                            <p v-if="client.type === 'company' && client.trade_name" class="truncate text-xs text-slate-500">
                                                {{ client.trade_name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 sm:table-cell">
                                    {{ formatDocument(client.document) }}
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-slate-600 lg:table-cell">{{ client.email ?? '—' }}</td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    <Badge :color="client.type === 'company' ? 'slate' : 'brand'">
                                        {{ client.type === 'company' ? 'PJ' : 'PF' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :color="client.is_active ? 'green' : 'red'">
                                        {{ client.is_active ? 'Ativo' : 'Inativo' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.clients.show', client.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Ver"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="can('clients.update')"
                                            :href="route('admin.clients.edit', client.id)"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can('clients.delete')"
                                            type="button"
                                            class="rounded-md p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                                            title="Excluir"
                                            @click="deleting = client"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="clients.data.length === 0">
                                <td colspan="6" class="px-4 py-3">
                                    <EmptyState
                                        title="Nenhum cliente encontrado"
                                        description="Ajuste a busca ou os filtros para encontrar clientes."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <Pagination :links="clients.links" :from="clients.from" :to="clients.to" :total="clients.total" />
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting !== null"
            title="Excluir cliente"
            :message="`Esta ação removerá ${displayName(deleting)}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteClient"
            @close="deleting = null"
        />
    </AdminLayout>
</template>