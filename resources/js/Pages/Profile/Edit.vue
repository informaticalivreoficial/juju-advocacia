<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user ?? {});

const roleLabels = {
    admin: 'Administrador',
    partner: 'Sócio(a)',
    lawyer: 'Advogado(a)',
    assistant: 'Assistente',
    secretary: 'Secretário(a)',
};

const initials = computed(() =>
    (user.value.name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
);
</script>

<template>
    <AdminLayout>
        <Head title="Perfil" />

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">Perfil</h1>
                <p class="mt-1 text-sm text-slate-500">Gerencie os dados da sua conta e da sua senha.</p>
            </div>

            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 p-6 shadow-lg shadow-brand-900/20 ring-1 ring-white/10 sm:p-8"
            >
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-brand-400/10 blur-2xl"></div>

                <div class="relative flex flex-col items-center gap-5 sm:flex-row sm:items-center">
                    <span
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 text-2xl font-extrabold text-white shadow-lg ring-4 ring-white/10"
                    >
                        {{ initials }}
                    </span>
                    <div class="text-center sm:text-left">
                        <h2 class="text-xl font-bold text-white sm:text-2xl">{{ user.name }}</h2>
                        <p class="mt-0.5 text-sm text-brand-200">{{ user.email }}</p>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <Badge color="brand">{{ roleLabels[user.role] ?? user.role }}</Badge>
                            <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium text-brand-100 ring-1 ring-inset ring-white/15">
                                Membro desde {{ user.created_at ? new Date(user.created_at).getFullYear() : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    <UpdatePasswordForm />
                </div>

                <div>
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>