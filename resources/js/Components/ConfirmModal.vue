<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirmar ação',
    },
    message: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: 'Confirmar',
    },
    variant: {
        type: String,
        default: 'danger',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['confirm', 'close']);
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="variant === 'danger' ? 'bg-red-50 text-red-600' : 'bg-indigo-50 text-indigo-600'"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                        />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
                    <p v-if="message" class="mt-1 text-sm text-slate-600">{{ message }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                <button
                    type="button"
                    :disabled="processing"
                    @click="emit('confirm')"
                    :class="
                        variant === 'danger'
                            ? 'bg-red-600 hover:bg-red-500 active:bg-red-700 focus:ring-red-500'
                            : 'bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 focus:ring-indigo-500'
                    "
                    class="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    {{ processing ? 'Aguarde...' : confirmText }}
                </button>
            </div>
        </div>
    </Modal>
</template>