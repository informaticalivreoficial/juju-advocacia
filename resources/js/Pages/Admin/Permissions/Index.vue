<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    modules: Object,
    roles: Array,
});

const moduleLabels = {
    clients: 'Clientes',
    processes: 'Processos',
    deadlines: 'Prazos',
    tasks: 'Tarefas',
    calendar: 'Agenda',
    documents: 'Documentos',
    users: 'Usuários',
    audit: 'Auditoria',
};

const roleColor = (role) => ({
    admin: 'red',
    partner: 'brand',
    lawyer: 'slate',
    assistant: 'amber',
    secretary: 'gray',
}[role] ?? 'gray');
</script>

<template>
    <AdminLayout>
        <Head title="Permissões" />

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Permissões</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Relação entre perfis e permissões. As permissões definem o que cada usuário pode fazer no painel.
                </p>
            </div>

            <Card>
                <div class="mb-4 flex flex-wrap gap-2">
                    <Badge v-for="role in roles" :key="role.name" :color="roleColor(role.name)">
                        {{ role.label }}
                    </Badge>
                </div>

                <div v-if="Object.keys(modules).length === 0">
                    <EmptyState title="Nenhuma permissão cadastrada" description="Execute o seeder de permissões." />
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Permissão</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Perfis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template v-for="(permissions, module) in modules" :key="module">
                                <tr class="bg-slate-50/60">
                                    <td colspan="2" class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-slate-500">
                                        {{ moduleLabels[module] ?? module }}
                                    </td>
                                </tr>
                                <tr v-for="permission in permissions" :key="permission.name" class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <span class="block text-sm font-medium text-slate-900">{{ permission.label }}</span>
                                        <span class="block text-xs text-slate-400">{{ permission.name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-1">
                                            <Badge
                                                v-for="role in permission.roles"
                                                :key="role"
                                                :color="roleColor(role)"
                                            >
                                                {{ role }}
                                            </Badge>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>