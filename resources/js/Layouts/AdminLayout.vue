<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const sidebarOpen = ref(false);

const auth = computed(() => usePage().props.auth ?? {});
const permissions = computed(() => auth.value.permissions ?? []);
const user = computed(() => auth.value.user);

const can = (permission) => permissions.value.includes(permission);

const sections = computed(() => {
    const items = (list) =>
        list.filter(
            (item) =>
                (typeof item.permission !== 'string' || can(item.permission)) &&
                route().has(item.href)
        );

    return [
        {
            name: 'Geral',
            items: items([{ label: 'Dashboard', href: 'admin.dashboard' }]),
        },
        {
            name: 'Jurídico',
            items: items([
                { label: 'Clientes', href: 'admin.clients.index', permission: 'clients.view' },
                { label: 'Processos', href: 'admin.processes.index', permission: 'processes.view' },
                { label: 'Prazos', href: 'admin.deadlines.index', permission: 'deadlines.view' },
                { label: 'Tarefas', href: 'admin.tasks.index', permission: 'tasks.view' },
                { label: 'Agenda', href: 'admin.calendar.index', permission: 'calendar.view' },
                { label: 'Documentos', href: 'admin.documents.index', permission: 'documents.view' },
            ]),
        },
        {
            name: 'Administração',
            items: items([
                { label: 'Usuários', href: 'admin.users.index', permission: 'users.view' },
                { label: 'Permissões', href: 'admin.permissions.index', permission: 'users.view' },
                { label: 'Auditoria', href: 'admin.audit.index', permission: 'audit.view' },
            ]),
        },
    ].filter((section) => section.items.length > 0);
});

const current = computed(() => {
    const name = route().current();

    for (const section of sections.value) {
        const found = section.items.find((item) => name && name.startsWith(item.href));

        if (found) {
            return found;
        }
    }

    return null;
});

const isActive = (href) => {
    const name = route().current();

    return name === href || (name && name.startsWith(href));
};

const initials = computed(() => {
    if (!user.value?.name) return '';

    return user.value.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 text-slate-200 transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center justify-between px-5">
                <Link href="/admin" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">
                        JM
                    </span>
                    <span class="text-sm font-semibold tracking-tight text-white">Juju Adv</span>
                </Link>
                <button
                    type="button"
                    class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden"
                    @click="sidebarOpen = false"
                    aria-label="Fechar menu"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
                <div v-for="section in sections" :key="section.name">
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                        {{ section.name }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="item in section.items" :key="item.href">
                            <Link
                                :href="route(item.href)"
                                :class="[
                                    'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition',
                                    isActive(item.href)
                                        ? 'bg-slate-800 text-white'
                                        : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                                ]"
                            >
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="border-t border-slate-800 px-5 py-4">
                <p class="text-xs text-slate-500">Painel Administrativo</p>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex min-h-screen flex-col lg:pl-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Abrir menu"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <nav class="flex items-center gap-1 text-sm" aria-label="Breadcrumb">
                            <span class="text-slate-400">Admin</span>
                            <template v-if="current">
                                <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="font-medium text-slate-900">{{ current.label }}</span>
                            </template>
                        </nav>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Notifications -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="relative rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                    aria-label="Notificações"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 text-sm text-slate-500">Sem notificações</div>
                            </template>
                        </Dropdown>

                        <!-- User menu -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-full p-1 transition hover:bg-slate-100"
                                >
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700"
                                    >
                                        {{ initials }}
                                    </span>
                                    <span class="hidden text-left sm:block">
                                        <span class="block text-sm font-medium text-slate-900">{{ user?.name }}</span>
                                        <span class="block text-xs text-slate-500">{{ user?.email }}</span>
                                    </span>
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 text-sm font-medium text-slate-900">
                                    {{ user?.name }}
                                </div>
                                <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Sair
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>