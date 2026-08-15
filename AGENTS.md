# AGENTS.md

Regras específicas do projeto **Juju Adv**.

## Stack

- Laravel 10 / PHP 8.2+
- Vue 3 (Composition API, `<script setup>`)
- Inertia.js (SPA)
- Tailwind CSS
- Vite
- MySQL (Laravel Sail / Docker)

## Banco de dados

- Todas as alterações estruturais devem ser feitas via **migrations**.
- Nome do banco de desenvolvimento: `juju_adv` (configurado em `.env`).
- Credenciais padrão do Sail: usuário `sail`, senha `password`.

## Comandos comuns

Use o Sail para tudo que envolva o ambiente de desenvolvimento:

```bash
./vendor/bin/sail artisan ...   # comandos artisan
./vendor/bin/sail npm run dev   # frontend (Vite)
./vendor/bin/sail artisan test  # testes
```

## Arquitetura

- Controllers devem ser pequenos e delegar regras de negócio complexas a **Services**.
- Use **Form Requests** para validações complexas ou reutilizáveis.
- Use **Policies/Gates** para autorização.
- Mensagens de validação e interface em **português**.
- Use eager loading (`with()`) para evitar queries N+1.

## Frontend

- Vue 3 com Composition API e `<script setup>`.
- Componentes pequenos e reutilizáveis.
- Estilização exclusivamente com Tailwind CSS (evitar CSS customizado e estilos inline).
- Backend sempre valida e protege as operações (nunca confiar apenas no frontend).

## Segurança

- Nunca colocar senhas, tokens, API keys ou secrets no código.
- Manter tudo sensível em `.env` (nunca commitar `.env`).
- Validar e autorizar todas as entradas no backend.

## Dependências

- Antes de adicionar uma dependência, verificar se Laravel/Vue/bibliotecas existentes resolvem o problema.
- Evitar dependências desnecessárias.

## Testes

- Criar testes para regras de negócio importantes, autenticação, autorização e validações.

## Git

- Alterações pequenas e coerentes.
- Não alterar arquivos sem relação com a tarefa.
- Não remover funcionalidades existentes sem autorização.
