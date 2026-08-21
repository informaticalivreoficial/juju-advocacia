<script setup>
const model = defineModel({
    type: [String, Number],
    required: true,
});

defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <select
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
        v-model="model"
    >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>