<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    user: Object,
});

const auth = computed(() => usePage().props.auth ?? {});
const can = (permission) => (auth.value.permissions ?? []).includes(permission);
const isSelf = computed(() => auth.value.user?.id === props.user.id);

const deleting = ref(false);

const deleteUser = () => {
    router.delete(route('admin.users.destroy', props.user.id), {
        onSuccess: () => {
            deleting.value = false;
        },
    });
};

const initials = computed(() =>
    props.user.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
);

const details = computed(() => [
    { label: 'E-mail', value: props.user.email },
    { label: 'Telefone', value: props.user.phone ?? '—' },
    { label: 'Perfil', value: props.user.roleDefinition?.label ?? props.user.role },
    { label: 'Status', value: props.user.is_active ? 'Ativo' : 'Inativo' },
    { label: 'Último acesso', value: props.user.last_login_at ?? 'Nunca acessou' },
    { label: 'Criado em', value: props.user.created_at },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Detalhes do usuário" />

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ user.name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ user.email }}</p>
                </div>
                <Link
                    v-if="can('users.update')"
                    :href="route('admin.users.edit', user.id)"
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Editar
                </Link>
            </div>

            <Card>
                <div class="flex items-center gap-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">
                        {{ initials }}
                    </span>
                    <div>
                        <p class="text-lg font-semibold text-slate-900">{{ user.name }}</p>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <Badge color="brand">{{ user.roleDefinition?.label ?? user.role }}</Badge>
                            <Badge :color="user.is_active ? 'green' : 'red'">
                                {{ user.is_active ? 'Ativo' : 'Inativo' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div v-for="detail in details" :key="detail.label">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ detail.label }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ detail.value }}</dd>
                    </div>
                </dl>
            </Card>

            <Card v-if="can('users.delete') && !isSelf" padding="p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-red-600">Zona de perigo</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Excluir permanentemente este usuário. Esta ação não pode ser desfeita.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="deleting = true"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"
                    >
                        Excluir usuário
                    </button>
                </div>
            </Card>
        </div>

        <ConfirmModal
            :show="deleting"
            title="Excluir usuário"
            :message="`Esta ação removerá permanentemente ${user.name}. Continuar?`"
            confirm-text="Excluir"
            @confirm="deleteUser"
            @close="deleting = false"
        />
    </AdminLayout>
</template>