<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from '@/Components/Select.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
    role: props.user.role,
    password: '',
    password_confirmation: '',
    is_active: props.user.is_active,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar usuário" />

        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar usuário</h1>
                <p class="mt-1 text-sm text-slate-500">{{ user.name }}</p>
            </div>

            <Card>
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel for="name" value="Nome" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                v-model="form.name"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="E-mail" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                v-model="form.email"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Telefone" />
                            <TextInput
                                id="phone"
                                type="text"
                                class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                v-model="form.phone"
                                placeholder="(12) 99999-9999"
                            />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <div>
                            <InputLabel for="role" value="Perfil" />
                            <Select id="role" v-model="form.role" :options="roles" />
                            <InputError class="mt-2" :message="form.errors.role" />
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center gap-2 pb-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-sm font-medium text-slate-700">Ativo</span>
                            </label>
                        </div>

                        <div>
                            <InputLabel for="password" value="Nova senha" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                v-model="form.password"
                                placeholder="Deixe em branco para manter"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar nova senha" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                v-model="form.password_confirmation"
                                placeholder="Deixe em branco para manter"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <Link
                            :href="route('admin.users.index')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>