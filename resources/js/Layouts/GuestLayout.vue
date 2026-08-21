<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-4 py-10 selection:bg-brand-500 selection:text-white"
    >
        <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute right-1/4 top-1/4 h-40 w-40 rounded-full bg-brand-400/10 blur-2xl"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-7 text-center">
                <Link href="/" class="inline-flex items-center gap-3">
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 text-lg font-extrabold tracking-tight text-white shadow-lg shadow-brand-950/40 ring-1 ring-white/20"
                    >
                        JM
                    </span>
                    <span class="text-left">
                        <span class="block text-xl font-bold tracking-tight text-white">Juju Adv</span>
                        <span class="block text-xs font-medium uppercase tracking-widest text-brand-300">
                            Painel Administrativo
                        </span>
                    </span>
                </Link>
            </div>

            <div
                class="rounded-2xl bg-white p-7 shadow-2xl shadow-brand-950/50 ring-1 ring-white/20 backdrop-blur sm:p-8"
            >
                <slot />
            </div>

            <p class="mt-7 text-center text-xs text-brand-300">
                <span aria-hidden="true">⚖</span>
                Júlia Montanari — Assessoria Jurídica
            </p>
        </div>
    </div>
</template>