<x-mail::message>
# Nova mensagem de contato

Você recebeu uma nova mensagem pelo site.

**Nome:** {{ $name }}

**E-mail:** {{ $email }}

---

**Mensagem:**

{{ $message }}

<x-mail::button :url="'mailto:' . $email">
Responder por e-mail
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
