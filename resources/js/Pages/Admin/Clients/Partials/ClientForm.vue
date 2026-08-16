<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatDocument, formatPhone, formatZipCode, onlyDigits } from '@/Utils/format.js';

defineProps({
    form: Object,
    types: Array,
    maritalStatuses: Array,
    states: Array,
});
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <InputLabel for="type" value="Tipo de cliente" />
                <Select id="type" v-model="form.type" :options="types" />
                <InputError class="mt-2" :message="form.errors.type" />
            </div>

            <div v-if="form.type === 'company'">
                <InputLabel for="company_name" value="Razão social" />
                <TextInput
                    id="company_name"
                    type="text"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.company_name"
                    :required="form.type === 'company'"
                />
                <InputError class="mt-2" :message="form.errors.company_name" />
            </div>

            <div v-else>
                <InputLabel for="name" value="Nome completo" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.name"
                    :required="form.type === 'individual'"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div v-if="form.type === 'company'">
                <InputLabel for="trade_name" value="Nome fantasia" />
                <TextInput
                    id="trade_name"
                    type="text"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.trade_name"
                />
                <InputError class="mt-2" :message="form.errors.trade_name" />
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <InputLabel for="document" :value="form.type === 'company' ? 'CNPJ' : 'CPF'" />
                <TextInput
                    id="document"
                    type="text"
                    inputmode="numeric"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    :value="formatDocument(form.document)"
                    @input="form.document = onlyDigits($event.target.value)"
                    :placeholder="form.type === 'company' ? '00.000.000/0000-00' : '000.000.000-00'"
                    required
                />
                <InputError class="mt-2" :message="form.errors.document" />
            </div>

            <div>
                <InputLabel for="email" value="E-mail" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone" value="Telefone fixo" />
                <TextInput
                    id="phone"
                    type="text"
                    inputmode="numeric"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    :value="formatPhone(form.phone)"
                    @input="form.phone = onlyDigits($event.target.value)"
                    placeholder="(00) 0000-0000"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="mobile" value="Celular" />
                <TextInput
                    id="mobile"
                    type="text"
                    inputmode="numeric"
                    class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    :value="formatPhone(form.mobile)"
                    @input="form.mobile = onlyDigits($event.target.value)"
                    placeholder="(00) 00000-0000"
                />
                <InputError class="mt-2" :message="form.errors.mobile" />
            </div>

            <template v-if="form.type === 'individual'">
                <div>
                    <InputLabel for="birth_date" value="Data de nascimento" />
                    <TextInput
                        id="birth_date"
                        type="date"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.birth_date"
                    />
                    <InputError class="mt-2" :message="form.errors.birth_date" />
                </div>

                <div>
                    <InputLabel for="marital_status" value="Estado civil" />
                    <Select
                        id="marital_status"
                        v-model="form.marital_status"
                        :options="maritalStatuses"
                        placeholder="Selecione"
                    />
                    <InputError class="mt-2" :message="form.errors.marital_status" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel for="profession" value="Profissão" />
                    <TextInput
                        id="profession"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.profession"
                    />
                    <InputError class="mt-2" :message="form.errors.profession" />
                </div>
            </template>

            <template v-else>
                <div>
                    <InputLabel for="state_registration" value="Inscrição estadual" />
                    <TextInput
                        id="state_registration"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.state_registration"
                    />
                    <InputError class="mt-2" :message="form.errors.state_registration" />
                </div>
            </template>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-slate-500">Endereço</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="zip_code" value="CEP" />
                    <TextInput
                        id="zip_code"
                        type="text"
                        inputmode="numeric"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        :value="formatZipCode(form.zip_code)"
                        @input="form.zip_code = onlyDigits($event.target.value)"
                        placeholder="00000-000"
                    />
                    <InputError class="mt-2" :message="form.errors.zip_code" />
                </div>

                <div>
                    <InputLabel for="state" value="UF" />
                    <Select id="state" v-model="form.state" :options="states" placeholder="Selecione" />
                    <InputError class="mt-2" :message="form.errors.state" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel for="address" value="Logradouro" />
                    <TextInput
                        id="address"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.address"
                    />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <div>
                    <InputLabel for="number" value="Número" />
                    <TextInput
                        id="number"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.number"
                    />
                    <InputError class="mt-2" :message="form.errors.number" />
                </div>

                <div>
                    <InputLabel for="complement" value="Complemento" />
                    <TextInput
                        id="complement"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.complement"
                    />
                    <InputError class="mt-2" :message="form.errors.complement" />
                </div>

                <div>
                    <InputLabel for="neighborhood" value="Bairro" />
                    <TextInput
                        id="neighborhood"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.neighborhood"
                    />
                    <InputError class="mt-2" :message="form.errors.neighborhood" />
                </div>

                <div>
                    <InputLabel for="city" value="Cidade" />
                    <TextInput
                        id="city"
                        type="text"
                        class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.city"
                    />
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <InputLabel for="notes" value="Observações" />
            <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.notes" />
        </div>

        <label class="flex items-center gap-2 border-t border-slate-100 pt-5">
            <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="text-sm font-medium text-slate-700">Cliente ativo</span>
        </label>
    </div>
</template>