# Juju Adv

Sistema web construído com **Laravel 10**, **Vue.js**, **Tailwind CSS**, **Inertia.js**, **Vite** e **MySQL**.

## Stack

- Laravel 10 (PHP 8.2+)
- Vue 3 (Composition API)
- Inertia.js
- Tailwind CSS
- Vite
- MySQL (via Laravel Sail / Docker)

## Requisitos

- Docker + Docker Compose
- PHP 8.2+ (somente para executar o Sail localmente)
- Composer
- Node.js + npm

## Instalação

1. Copie o arquivo de ambiente e configure conforme necessário:

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

7. Compile os assets do frontend:

   ```bash
   ./vendor/bin/sail npm run dev
   ```

## Executando

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

Acesse `http://localhost` no navegador.

## Testes

```bash
./vendor/bin/sail artisan test
```

## Dica: alias do Sail

Adicione ao seu `~/.bashrc` (ou equivalente):

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Depois recarregue o shell (`source ~/.bashrc`) e use `sail` diretamente.
