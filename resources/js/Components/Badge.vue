<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: {
        type: String,
        default: 'gray',
    },
});

const palette = {
    gray: 'bg-slate-100 text-slate-700 ring-slate-200',
    slate: 'bg-slate-100 text-slate-700 ring-slate-200',
    indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    red: 'bg-red-50 text-red-700 ring-red-200',
    orange: 'bg-orange-50 text-orange-700 ring-orange-200',
    amber: 'bg-amber-50 text-amber-800 ring-amber-200',
};

const classes = computed(() => palette[props.color] ?? palette.gray);
</script>

<template>
    <span
        class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
        :class="classes"
    >
        <slot />
    </span>
</template>