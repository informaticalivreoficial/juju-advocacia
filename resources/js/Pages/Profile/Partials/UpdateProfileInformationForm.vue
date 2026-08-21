<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex items-start gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </span>
            <div>
                <h2 class="text-lg font-bold text-brand-950">Informações do perfil</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    Atualize as informações do perfil e o endereço de e-mail da sua conta.
                </p>
            </div>
        </div>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-7 space-y-5">
            <div>
                <InputLabel for="name" value="Nome" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="E-mail" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm text-brand-900/80">
                    Seu endereço de e-mail não está verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                    >
                        Clique aqui para reenviar o e-mail de verificação.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600">
                    Um novo link de verificação foi enviado para o seu endereço de e-mail.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-medium text-emerald-600">Salvo.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>