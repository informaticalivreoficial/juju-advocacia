<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verificação de E-mail" />

        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold tracking-tight text-brand-950">Verifique seu e-mail</h1>
            <p class="mt-1 text-sm text-brand-900/60">
                Antes de começar, clique no link que enviamos para seu endereço de e-mail. Não recebeu? Enviamos
                outro abaixo.
            </p>
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
            Um novo link de verificação foi enviado para o endereço de e-mail informado durante o registro.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="flex items-center justify-between gap-3">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reenviar verificação
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-medium text-brand-700 hover:text-brand-900 hover:underline"
                >
                    Sair
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
