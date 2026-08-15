<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';

const showContactModal = ref(false);

const contactForm = useForm({
    name: '',
    email: '',
    message: '',
});

const submitContact = () => {
    contactForm.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            contactForm.reset();
            showContactModal.value = false;
        },
    });
};

const startDate = new Date(2024, 0, 1);
const endDate = new Date(2028, 10, 30);

const totalMs = endDate.getTime() - startDate.getTime();
const elapsedMs = Date.now() - startDate.getTime();

const progress = computed(() => {
    const raw = Math.round((elapsedMs / totalMs) * 100);
    return Math.min(100, Math.max(0, raw));
});

const remainingLabel = computed(() => {
    const remainingMs = endDate.getTime() - Date.now();
    if (remainingMs <= 0) return 'Formação concluída';

    const totalDays = Math.ceil(remainingMs / (1000 * 60 * 60 * 24));
    const months = Math.floor(totalDays / 30);
    const days = totalDays % 30;

    if (months <= 0) return `${days} dias restantes`;
    if (days === 0) return `${months} ${months === 1 ? 'mês' : 'meses'} restantes`;
    return `${months} ${months === 1 ? 'mês' : 'meses'} e ${days} dias restantes`;
});
</script>

<template>
    <Head title="Júlia Montanari — Estudante de Direito" />

    <div class="min-h-screen bg-[#faf7f9] text-rose-950 selection:bg-rose-800 selection:text-white">
        <div class="relative overflow-hidden bg-gradient-to-br from-rose-900 via-rose-800 to-rose-950 text-white">
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/5 blur-2xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-rose-400/10 blur-3xl"
            ></div>

            <div class="relative mx-auto max-w-6xl px-6 py-14 sm:py-20">
                <div class="flex flex-col items-center gap-10 md:flex-row md:items-center md:gap-14">
                    <div class="shrink-0">
                        <img
                            src="/storage/users/julia.jpeg"
                            alt="Foto de Júlia Montanari"
                            class="h-44 w-44 rounded-3xl border-4 border-white/30 object-cover shadow-2xl shadow-rose-950/30 sm:h-52 sm:w-52"
                        />
                    </div>

                    <div class="text-center md:text-left">
                        <p
                            class="mb-3 inline-block rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-rose-100"
                        >
                            Estudante de Direito
                        </p>
                        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                            Júlia Montanari
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-rose-100/90 sm:text-lg">
                            Bacharelanda em Direito com experiência em estágios jurídicos e vivência prática no
                            ambiente forense, buscando crescimento nas áreas cível e trabalhista.
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3 md:justify-start">
                            <a
                                href="https://linkedin.com/in/jumontanari"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-rose-900 shadow-lg transition hover:bg-rose-50"
                            >
                                LinkedIn
                            </a>
                            <button
                                type="button"
                                @click="showContactModal = true"
                                class="rounded-full border border-white/30 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                Entrar em contato
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-6xl px-6 py-12 sm:py-16">
            <div class="grid gap-10 lg:grid-cols-5">
                <aside class="lg:col-span-2">
                    <div class="space-y-8 rounded-3xl border border-rose-100 bg-white p-7 shadow-sm sm:sticky sm:top-6">
                        <section>
                            <h2
                                class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                            >
                                <span class="h-px flex-1 bg-rose-200"></span>
                                Contato
                            </h2>
                            <ul class="space-y-4 text-sm">
                                <li class="flex gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-rose-900">Telefone</p>
                                        <a href="tel:+5512982194641" class="text-rose-800 hover:underline">
                                            (12) 98219-4641
                                        </a>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-rose-900">E-mail</p>
                                        <a
                                            href="mailto:Julia@juliamontanari.com.br"
                                            class="break-all text-rose-800 hover:underline"
                                        >
                                            Julia@juliamontanari.com.br
                                        </a>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-rose-900">Residência</p>
                                        <p class="text-rose-800">São José dos Campos/SP</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z" />
                                            <circle cx="4" cy="4" r="2" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-rose-900">LinkedIn</p>
                                        <a
                                            href="https://linkedin.com/in/jumontanari"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="break-all text-rose-800 hover:underline"
                                        >
                                            linkedin.com/in/jumontanari
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2
                                class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                            >
                                <span class="h-px flex-1 bg-rose-200"></span>
                                Idiomas
                            </h2>
                            <div>
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-semibold text-rose-950">Inglês</h3>
                                    <span class="text-xs font-medium text-rose-700">Intermediário</span>
                                </div>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-900/80">
                                    <li>Excelente compreensão e leitura.</li>
                                    <li>Conversação intermediária.</li>
                                    <li>Aulas semanais — EnglishBay.</li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </aside>

                <div class="lg:col-span-3 space-y-10">
                    <section>
                        <h2
                            class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                        >
                            <span class="h-px flex-1 bg-rose-200"></span>
                            Formação acadêmica
                        </h2>
                        <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="font-semibold text-rose-950">Bacharelado em Direito</h3>
                                <span class="text-xs font-medium text-rose-700">jan/2024 – dez/2028</span>
                            </div>
                            <p class="mt-1 text-sm font-medium text-rose-800">Universidade do Vale do Paraíba</p>
                            <p class="mt-3 text-sm leading-relaxed text-rose-900/80">
                                Atualmente cursando o 6º período, com experiência em diferentes estágios jurídicos e
                                vivência prática no ambiente forense.
                            </p>

                            <div class="mt-6 rounded-2xl bg-rose-50/70 p-5">
                                <div class="mb-2 flex items-baseline justify-between text-sm">
                                    <span class="font-semibold text-rose-900">Progresso da formação</span>
                                    <span class="font-bold text-rose-800">{{ progress }}%</span>
                                </div>
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-rose-100">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-rose-600 to-rose-900 transition-all duration-700"
                                        :style="{ width: `${progress}%` }"
                                    ></div>
                                </div>
                                <p class="mt-2 text-xs font-medium text-rose-800">{{ remainingLabel }}</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2
                            class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                        >
                            <span class="h-px flex-1 bg-rose-200"></span>
                            Experiência profissional
                        </h2>
                        <div class="space-y-5">
                            <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-semibold text-rose-950">Estagiária de Direito</h3>
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                        2026 – presente
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-rose-800">
                                    Tribunal de Justiça do Estado de São Paulo
                                </p>
                                <p class="mt-2 text-sm font-medium text-rose-900/90">
                                    4ª Vara de Família e Sucessões — Gabinete do Magistrado
                                </p>
                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-rose-900/80">
                                    <li>Análise, conferência e acompanhamento de processos judiciais.</li>
                                    <li>Elaboração e revisão de termos de audiência e atos processuais.</li>
                                    <li>Microsoft Teams.</li>
                                </ul>
                            </div>

                            <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-semibold text-rose-950">Assistente jurídica</h3>
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                        2025 – 2026
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-rose-800">
                                    Martins e Morais Sociedade de Advogados
                                </p>
                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-rose-900/80">
                                    <li>Atuação nas áreas Cível e Trabalhista.</li>
                                    <li>Elaboração de petições e recursos.</li>
                                    <li>Apoio em negociações e tratativas jurídicas.</li>
                                    <li>Suporte na preparação e acompanhamento de audiências.</li>
                                    <li>Atuação em Compliance e prevenção de riscos jurídicos.</li>
                                    <li>Utilização dos sistemas E-SAJ, E-PROC e PJe.</li>
                                </ul>
                            </div>

                            <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-semibold text-rose-950">Estagiária de Direito</h3>
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                        2024 – 2025
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-rose-800">
                                    Martins e Morais Sociedade de Advogados
                                </p>
                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-rose-900/80">
                                    <li>Elaboração de contratos e procurações.</li>
                                    <li>Pesquisas de legislação, doutrina e jurisprudência.</li>
                                    <li>Participação e apoio em reuniões (Zoom e Google Meet).</li>
                                    <li>Pacote Office.</li>
                                </ul>
                            </div>

                            <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-semibold text-rose-950">Estagiária de Direito</h3>
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                        2024
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-rose-800">
                                    2º Tabelião de Notas de São José dos Campos
                                </p>
                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-rose-900/80">
                                    <li>Auxílio na elaboração e conferência de atos notariais.</li>
                                    <li>Apoio em escrituras públicas, procurações e reconhecimentos de firma.</li>
                                    <li>Acompanhamento dos procedimentos e fluxos internos do Tabelionato.</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2
                            class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                        >
                            <span class="h-px flex-1 bg-rose-200"></span>
                            Cursos e atividades recentes
                        </h2>
                        <div class="space-y-3">
                            <div class="rounded-3xl border border-rose-100 bg-white p-6 shadow-sm">
                                <h3 class="font-semibold text-rose-950">
                                    IA e Produtividade Pessoal no Serviço Público
                                </h3>
                                <p class="mt-1 text-sm text-rose-800">Turma Ago/2026</p>
                                <p class="mt-1 text-sm font-medium text-rose-900/80">
                                    Escola Nacional de Administração Pública — ENAP
                                </p>
                            </div>
                            <div class="rounded-3xl border border-rose-100 bg-white p-6 shadow-sm">
                                <h3 class="font-semibold text-rose-950">Ética em IA</h3>
                                <p class="mt-1 text-sm font-medium text-rose-900/80">
                                    Escola Nacional de Administração Pública — ENAP
                                </p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2
                            class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rose-800"
                        >
                            <span class="h-px flex-1 bg-rose-200"></span>
                            Conquistas e méritos acadêmicos
                        </h2>
                        <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-sm">
                            <ul class="list-disc space-y-2 pl-5 text-sm text-rose-900/80">
                                <li>1º lugar no vestibular da Faculdade de Direito de Sorocaba (Ano 2022).</li>
                                <li>940 pontos na redação do ENEM por dois anos consecutivos.</li>
                                <li>Aprovação na prova teórica da Academia do Barro Branco.</li>
                                <li>Representante e oradora da turma — Ensino Médio.</li>
                                <li>Membro da Comissão de Formatura — Faculdade de Direito.</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <footer class="border-t border-rose-100 bg-white px-6 py-8 text-center">
            <p class="text-sm text-rose-900/70">Júlia Montanari — Estudante de Direito</p>
        </footer>
    </div>

    <Modal :show="showContactModal" max-width="lg" @close="showContactModal = false">
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-rose-950">Entrar em contato</h2>
                    <p class="mt-1 text-sm text-rose-900/70">
                        Preencha o formulário abaixo e retornaremos em breve.
                    </p>
                </div>
                <button
                    type="button"
                    @click="showContactModal = false"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-rose-400 transition hover:bg-rose-50 hover:text-rose-800"
                    aria-label="Fechar"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                v-if="$page.props.flash?.success"
                class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900"
            >
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submitContact" class="mt-6 space-y-5">
                <div>
                    <InputLabel for="contact-name" value="Nome" />
                    <TextInput
                        id="contact-name"
                        type="text"
                        class="mt-1 block w-full border-rose-200 focus:border-rose-500 focus:ring-rose-500"
                        v-model="contactForm.name"
                        required
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="contactForm.errors.name" />
                </div>

                <div>
                    <InputLabel for="contact-email" value="E-mail" />
                    <TextInput
                        id="contact-email"
                        type="email"
                        class="mt-1 block w-full border-rose-200 focus:border-rose-500 focus:ring-rose-500"
                        v-model="contactForm.email"
                        required
                        autocomplete="email"
                    />
                    <InputError class="mt-2" :message="contactForm.errors.email" />
                </div>

                <div>
                    <InputLabel for="contact-message" value="Mensagem" />
                    <textarea
                        id="contact-message"
                        v-model="contactForm.message"
                        rows="5"
                        required
                        class="mt-1 block w-full rounded-md border-rose-200 shadow-sm focus:border-rose-500 focus:ring-rose-500"
                    ></textarea>
                    <InputError class="mt-2" :message="contactForm.errors.message" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="showContactModal = false"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold text-rose-800 transition hover:bg-rose-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="contactForm.processing"
                        :class="{ 'opacity-50': contactForm.processing }"
                        class="rounded-full bg-gradient-to-r from-rose-700 to-rose-900 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:from-rose-800 hover:to-rose-950"
                    >
                        {{ contactForm.processing ? 'Enviando...' : 'Enviar mensagem' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
