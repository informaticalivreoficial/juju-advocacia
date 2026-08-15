# Juju Adv

Landing page de apresentação profissional da **Júlia Montanari**, estudante de Direito. O projeto exibe seu currículo (formação, experiências, conquistas e idiomas), uma foto de perfil e um formulário de contato funcional em uma interface moderna, responsiva e em tons de rosa escuro.

## Stack

- **Laravel 10** (PHP 8.2+)
- **Vue 3** (Composition API, `<script setup>`)
- **Inertia.js** (SPA)
- **Tailwind CSS**
- **Vite**
- **MySQL** (via Laravel Sail / Docker)
- **Mailpit** (captura de e-mails em desenvolvimento)

## Funcionalidades

- Landing page de currículo responsiva (foto à esquerda, conteúdo à direita no desktop)
- Barra de progresso da formação (cálculo automático até 30/11/2028)
- Modal de contato com formulário (nome, e-mail e mensagem)
- Validação com mensagens em português
- Envio de e-mail de contato (capturado pelo Mailpit em desenvolvimento)
- Link de storage para exibição da foto de perfil

## Requisitos

- Docker + Docker Compose
- PHP 8.2+ (para executar o Sail localmente)
- Composer
- Node.js + npm

## Instalação

1. Copie o arquivo de ambiente:

   ```bash
   cp .env.example .env
   ```

   As credenciais do banco já estão configuradas para o Sail (`sail` / `password`).

2. Suba os containers do Docker:

   ```bash
   ./vendor/bin/sail up -d
   ```

3. Instale as dependências do PHP:

   ```bash
   ./vendor/bin/sail composer install
   ```

4. Instale as dependências do frontend:

   ```bash
   ./vendor/bin/sail npm install
   ```

5. Gere a chave da aplicação:

   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. Execute as migrations:

   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. Crie o link simbólico do storage (exibição da foto de perfil):

   ```bash
   ./vendor/bin/sail artisan storage:link
   ```

8. Compile os assets do frontend:

   ```bash
   ./vendor/bin/sail npm run build
   ```

## Executando

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

Acesse `http://localhost` no navegador.

### Configuração de e-mail de contato

O formulário envia os e-mails para o endereço definido em `CONTACT_EMAIL` no `.env`. Em desenvolvimento, os e-mails são capturados pelo **Mailpit**, disponível em `http://localhost:8025`.

## Estrutura relevante

```
app/
├── Http/
│   ├── Controllers/ContactController.php
│   └── Requests/ContactRequest.php
├── Mail/ContactMail.php
├── Http/Middleware/HandleInertiaRequests.php
resources/
├── js/Pages/Welcome.vue          # Landing page + modal de contato
└── views/emails/contact.blade.php # Template do e-mail de contato
routes/web.php
storage/app/public/users/julia.jpeg # Foto de perfil
```

## Testes

```bash
./vendor/bin/sail artisan test
```

Cobre autenticação, perfil e o fluxo de contato (validação e envio de e-mail).

## Dica: alias do Sail

Adicione ao seu `~/.bashrc` (ou equivalente):

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Depois recarregue o shell (`source ~/.bashrc`) e use `sail` diretamente.
