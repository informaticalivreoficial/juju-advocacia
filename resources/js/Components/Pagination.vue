<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    from: {
        type: Number,
        default: null,
    },
    to: {
        type: Number,
        default: null,
    },
    total: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <div v-if="total > 0" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
            Exibindo <span class="font-medium text-slate-700">{{ from }}</span> a
            <span class="font-medium text-slate-700">{{ to }}</span> de
            <span class="font-medium text-slate-700">{{ total }}</span> registros
        </p>

        <nav v-if="links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="(link, index) in links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex min-w-9 items-center justify-center rounded-md border border-slate-300 px-2.5 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    :class="{ 'border-indigo-500 bg-indigo-50 text-indigo-700': link.active }"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex min-w-9 items-center justify-center rounded-md border border-slate-200 px-2.5 py-1.5 text-sm font-medium text-slate-300"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>