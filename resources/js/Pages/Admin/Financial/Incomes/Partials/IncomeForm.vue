<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    form: Object,
    categories: Array,
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel value="Descrição" />
            <TextInput
                v-model="form.description"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ex.: Honorários advocatícios"
                autofocus
                autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Categoria" />
                <Select v-model="form.category_id" :options="categories" placeholder="Selecione a categoria..." />
                <InputError class="mt-2" :message="form.errors.category_id" />
            </div>

            <div>
                <InputLabel value="Valor (R$)" />
                <TextInput v-model="form.amount" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="0,00" />
                <InputError class="mt-2" :message="form.errors.amount" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Dia do recebimento" />
                <TextInput v-model="form.receive_day" type="number" min="1" max="31" class="mt-1 block w-full" placeholder="Ex.: 5" />
                <InputError class="mt-2" :message="form.errors.receive_day" />
            </div>

            <div>
                <InputLabel value="Ativo" />
                <div class="mt-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input
                            type="checkbox"
                            v-model="form.active"
                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                        />
                        Gera lançamentos mensais automaticamente
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.active" />
            </div>
        </div>

        <div>
            <InputLabel value="Observações (opcional)" />
            <textarea
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                placeholder="Detalhes adicionais sobre a receita..."
            ></textarea>
            <InputError class="mt-2" :message="form.errors.notes" />
        </div>
    </div>
</template>