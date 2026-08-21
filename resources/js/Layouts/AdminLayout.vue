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
            icon: 'M3 10.5L12 3l9 7.5M5 9.5V21h14V9.5',
            items: items([{ label: 'Dashboard', href: 'admin.dashboard' }]),
        },
        {
            name: 'Jurídico',
            icon: 'M3 7h18v13H3zM8 7V5a2 2 0 012-2h4a2 2 0 012 2v2',
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
            name: 'Financeiro',
            icon: 'M3 7h18v12H3zM3 7l2-3h14l2 3M16 13h.01',
            items: items([
                { label: 'Visão geral', href: 'admin.financial.dashboard', permission: 'financial.view' },
                { label: 'Lançamentos', href: 'admin.financial.transactions.index', permission: 'financial.view' },
                { label: 'Despesas', href: 'admin.financial.expenses.index', permission: 'financial.view' },
                { label: 'Receitas', href: 'admin.financial.incomes.index', permission: 'financial.view' },
                { label: 'Categorias', href: 'admin.financial.categories.index', permission: 'financial.view' },
                { label: 'Anual', href: 'admin.financial.annual.index', permission: 'financial.view' },
                { label: 'Relatórios', href: 'admin.financial.reports.index', permission: 'financial.view' },
            ]),
        },
        {
            name: 'Administração',
            icon: 'M12 3l7 3v6c0 4-3 7-7 9-4-2-7-5-7-9V6l7-3z',
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

const openGroups = ref({});

const currentSectionName = computed(() => {
    const name = route().current();

    for (const section of sections.value) {
        if (section.items.some((item) => name && name.startsWith(item.href))) {
            return section.name;
        }
    }

    return null;
});

const isGroupActive = (section) =>
    section.items.some((item) => isActive(item.href));

const toggleGroup = (name) => {
    openGroups.value[name] = !openGroups.value[name];
};

const isGroupOpen = (section) => {
    if (openGroups.value[section.name] !== undefined) {
        return openGroups.value[section.name];
    }

    return isGroupActive(section);
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
    <div class="min-h-screen bg-brand-50/60">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-brand-950/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gradient-to-b from-brand-950 via-brand-900 to-brand-800 text-brand-100 shadow-2xl shadow-brand-950/30 transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center justify-between px-5">
                <Link href="/admin" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-800 text-sm font-extrabold text-white shadow-lg ring-1 ring-white/20">
                        JM
                    </span>
                    <span class="text-left">
                        <span class="block text-sm font-bold tracking-tight text-white">Juju Adv</span>
                        <span class="block text-[10px] font-medium uppercase tracking-widest text-brand-300">
                            Painel Jurídico
                        </span>
                    </span>
                </Link>
                <button
                    type="button"
                    class="rounded-md p-1.5 text-brand-300 transition hover:bg-white/10 hover:text-white lg:hidden"
                    @click="sidebarOpen = false"
                    aria-label="Fechar menu"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <template v-for="section in sections" :key="section.name">
                    <Link
                        v-if="section.items.length === 1"
                        :href="route(section.items[0].href)"
                        :class="[
                            'group mb-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                            isActive(section.items[0].href)
                                ? 'bg-white/10 text-white shadow-sm ring-1 ring-white/10'
                                : 'text-brand-200 hover:bg-white/5 hover:text-white',
                        ]"
                    >
                        <svg class="h-4 w-4 shrink-0 text-brand-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="section.icon" />
                        </svg>
                        {{ section.items[0].label }}
                    </Link>

                    <div v-else class="mb-1">
                        <button
                            type="button"
                            @click="toggleGroup(section.name)"
                            :class="[
                                'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition',
                                isGroupActive(section)
                                    ? 'text-white'
                                    : 'text-brand-200 hover:bg-white/5 hover:text-white',
                            ]"
                        >
                            <svg class="h-4 w-4 shrink-0 text-brand-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="section.icon" />
                            </svg>
                            <span class="flex-1 text-left">{{ section.name }}</span>
                            <svg
                                class="h-4 w-4 text-brand-400 transition-transform duration-200"
                                :class="isGroupOpen(section) ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <ul v-show="isGroupOpen(section)" class="mt-0.5 space-y-0.5">
                            <li v-for="item in section.items" :key="item.href">
                                <Link
                                    :href="route(item.href)"
                                    :class="[
                                        'group flex items-center gap-2.5 rounded-lg py-1.5 pl-9 pr-3 text-sm font-medium transition',
                                        isActive(item.href)
                                            ? 'text-white'
                                            : 'text-brand-200/90 hover:text-white',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full transition',
                                            isActive(item.href) ? 'bg-brand-400' : 'bg-brand-500/40 group-hover:bg-brand-400',
                                        ]"
                                    ></span>
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </template>
            </nav>

            <div class="border-t border-white/10 px-5 py-4">
                <p class="text-xs text-brand-300/70">Painel Administrativo</p>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex min-h-screen flex-col lg:pl-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 border-b border-brand-100 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-md p-2 text-slate-500 transition hover:bg-brand-50 hover:text-brand-700 lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Abrir menu"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <nav class="flex items-center gap-1 text-sm" aria-label="Breadcrumb">
                            <span class="text-brand-400">Admin</span>
                            <template v-if="current">
                                <svg class="h-4 w-4 text-brand-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="font-semibold text-brand-900">{{ current.label }}</span>
                            </template>
                        </nav>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Notifications -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="relative rounded-md p-2 text-slate-500 transition hover:bg-brand-50 hover:text-brand-700"
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
                                    class="flex items-center gap-2 rounded-full p-1 transition hover:bg-brand-50"
                                >
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800 ring-2 ring-brand-200"
                                    >
                                        {{ initials }}
                                    </span>
                                    <span class="hidden text-left sm:block">
                                        <span class="block text-sm font-semibold text-brand-950">{{ user?.name }}</span>
                                        <span class="block text-xs text-slate-500">{{ user?.email }}</span>
                                    </span>
                                    <svg class="h-4 w-4 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 text-sm font-semibold text-brand-950">
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