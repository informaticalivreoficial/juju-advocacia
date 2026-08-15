# Histórico do Projeto — Juju Adv

> Documento de acompanhamento do progresso. Atualizar ao final de cada etapa para facilitar a continuidade do trabalho.

## Visão geral

Landing page profissional da **Júlia Montanari**, estudante de Direito. Exibe currículo completo, foto de perfil, barra de progresso da formação e formulário de contato funcional. Interface moderna, responsiva, em tons de rosa escuro.

## Stack

- Laravel 10.50.3 (PHP 8.3)
- Vue 3 (Composition API, `<script setup>`) + Inertia.js
- Tailwind CSS + Vite
- MySQL 8.4 (Laravel Sail / Docker)
- Mailpit (captura de e-mails em dev)

---

## Etapa 1 — Criação do projeto (concluída)

- Projeto Laravel 10 criado diretamente na pasta raiz (não em subpasta).
- Breeze instalado com stack Vue (autenticação + perfil).
- Sail configurado com MySQL, Redis e Mailpit (PHP 8.3).
- Banco de dados: `juju_adv` (usuário `sail` / senha `password`).
- Locale configurado para `pt_BR` + traduções via `laravel-lang/common`.
- Componentes de autenticação/perfil do Breeze traduzidos para português.

## Etapa 2 — Landing page de currículo (concluída)

- Página `Welcome.vue` reformulada como currículo da Júlia Montanari.
- Seções: contato, formação acadêmica, experiência profissional, cursos, conquistas e idiomas.
- Paleta rosa escuro (`rose-700/800/900/950`) transmitindo seriedade.

## Etapa 3 — Barra de progresso da formação (concluída)

- Progresso calculado dinamicamente entre jan/2024 e 30/11/2028.
- Exibe porcentagem e tempo restante, atualizados automaticamente pela data do navegador.
- Posicionada dentro do card de formação acadêmica.

## Etapa 4 — Modernização do layout + foto (concluída)

- Layout modernizado: hero com foto à esquerda e texto à direita (desktop), empilhado no mobile.
- Sidebar fixa (sticky) com contato e idiomas, cards com bordas arredondadas e ícones SVG.
- Foto de perfil: `storage/app/public/users/julia.jpeg`, servida via `/storage/users/julia.jpeg`.
- Link de storage criado (`storage:link`).

## Etapa 5 — Modal de contato com formulário (concluída)

- Botão "Entrar em contato" abre modal reutilizando `Modal.vue` do Breeze.
- Formulário: nome, e-mail e mensagem (validação com mensagens em pt-BR).
- Backend:
  - `ContactController@store` + rota `POST /contato` (`contact.store`)
  - `ContactRequest` (validação)
  - `ContactMail` (Mailable markdown)
  - Template `resources/views/emails/contact.blade.php`
  - `mail.contact_address` / variável `CONTACT_EMAIL` no `.env`
  - `HandleInertiaRequests` compartilhando `flash.success`
- Envio de e-mail confirmado no Mailpit (`http://localhost:8025`).

## Etapa 6 — Testes + README (concluída)

- `tests/Feature/ContactTest.php`: envio, validação e renderização da página (3 testes).
- Suíte completa: **28 testes passando**.
- README.md refatorado com stack, funcionalidades, instalação, estrutura e testes.

---

## Estado atual (última sessão)

- Aplicação rodando via Sail (4 containers up).
- Home (`http://localhost`) responde HTTP 200.
- Foto carregada, modal de contato funcional, e-mail entregue ao Mailpit.
- 28 testes passando.
- Git: repositório iniciado com commit inicial (`first commit`) — alterações recentes ainda **não** commitadas.

## Arquivos principais criados/modificados

```
app/
├── Http/Controllers/ContactController.php
├── Http/Requests/ContactRequest.php
├── Http/Middleware/HandleInertiaRequests.php  (share flash.success)
├── Mail/ContactMail.php
config/mail.php                                  (contact_address)
resources/
├── js/Pages/Welcome.vue                         (landing + modal de contato)
├── views/emails/contact.blade.php               (template do e-mail)
lang/pt_BR/                                      (traduções)
routes/web.php                                   (rota contact.store)
storage/app/public/users/julia.jpeg              (foto de perfil)
tests/Feature/ContactTest.php
compose.yaml / .env / .env.example
README.md / AGENTS.md
```

## Comandos úteis

```bash
./vendor/bin/sail up -d            # subir ambiente
./vendor/bin/sail npm run dev      # frontend (dev)
./vendor/bin/sail npm run build    # build de produção
./vendor/bin/sail artisan test     # testes
./vendor/bin/sail artisan storage:link  # link do storage (se necessário)
```

## Possíveis próximos passos

- [ ] Commit das alterações recentes (landing page, contato, testes).
- [ ] Hospedagem / deploy (ajustes para produção).
- [ ] Ajustes de SEO/Open Graph e meta tags da landing page.
- [ ] Substituir conteúdo de exemplo por dados definitivos, se necessário.
- [ ] Melhorias de acessibilidade e performance.
