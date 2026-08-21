<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { formatDocument, formatPhone, formatZipCode } from '@/Utils/format.js';

const props = defineProps({
    client: Object,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);

const deleting = ref(false);

const deleteClient = () => {
    router.delete(route('admin.clients.destroy', props.client.id), {
        onSuccess: () => {
            deleting.value = false;
        },
    });
};

const displayName = computed(() =>
    props.client.type === 'company' ? props.client.company_name : props.client.name
);

const personalDetails = computed(() => {
    const details = [
        { label: 'Tipo', value: props.client.type === 'company' ? 'Pessoa Jurídica' : 'Pessoa Física' },
        { label: props.client.type === 'company' ? 'CNPJ' : 'CPF', value: formatDocument(props.client.document) },
    ];

    if (props.client.type === 'individual') {
        details.push(
            { label: 'Data de nascimento', value: props.client.birth_date ?? '—' },
            { label: 'Estado civil', value: props.client.marital_status ?? '—' },
            { label: 'Profissão', value: props.client.profession ?? '—' }
        );
    } else {
        details.push(
            { label: 'Razão social', value: props.client.company_name ?? '—' },
            { label: 'Nome fantasia', value: props.client.trade_name ?? '—' },
            { label: 'Inscrição estadual', value: props.client.state_registration ?? '—' }
        );
    }

    return details;
});

const contactDetails = computed(() => [
    { label: 'E-mail', value: props.client.email ?? '—' },
    { label: 'Telefone', value: props.client.phone ? formatPhone(props.client.phone) : '—' },
    { label: 'Celular', value: props.client.mobile ? formatPhone(props.client.mobile) : '—' },
]);

const addressDetails = computed(() => {
    const parts = [
        props.client.address,
        props.client.number ? `nº ${props.client.number}` : null,
        props.client.complement,
        props.client.neighborhood,
        props.client.city,
        props.client.state,
    ].filter(Boolean);

    return [
        {
            label: 'Endereço',
            value: parts.length > 0 ? parts.join(', ') : '—',
        },
        { label: 'CEP', value: props.client.zip_code ? formatZipCode(props.client.zip_code) : '—' },
    ];
});
</script>

<template>
    <AdminLayout>
        <Head title="Detalhes do cliente" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ displayName }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ client.email ?? 'Sem e-mail cadastrado' }}</p>
                </div>
                <Link
                    v-if="can('clients.update')"
                    :href="route('admin.clients.edit', client.id)"
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Editar
                </Link>
            </div>

            <Card>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge color="brand">{{ client.type === 'company' ? 'Pessoa Jurídica' : 'Pessoa Física' }}</Badge>
                    <Badge :color="client.is_active ? 'green' : 'red'">
                        {{ client.is_active ? 'Ativo' : 'Inativo' }}
                    </Badge>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div v-for="detail in personalDetails" :key="detail.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ detail.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ detail.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Contato</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div v-for="detail in contactDetails" :key="detail.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ detail.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ detail.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card>
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Endereço</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div v-for="detail in addressDetails" :key="detail.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ detail.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ detail.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card v-if="client.notes">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-800">Observações</h2>
                <p class="text-sm leading-relaxed whitespace-pre-line text-slate-700">{{ client.notes }}</p>
            </Card>

            <Card v-if="can('clients.delete')" padding="p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-red-600">Zona de perigo</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Excluir este cliente. Esta ação não pode ser desfeita.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="deleting = true"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"
                    >
                        Excluir cliente
                    </button>
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting"
            title="Excluir cliente"
            :message="`Esta ação removerá ${displayName}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteClient"
            @close="deleting = false"
        />
    </AdminLayout>
</template>