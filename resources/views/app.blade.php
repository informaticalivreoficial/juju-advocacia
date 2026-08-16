<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Juju Adv') }}</title>

        <!-- SEO -->
        <meta name="description" content="Júlia Montanari — Estudante de Direito. Bacharelanda em Direito com experiência em estágios jurídicos e vivência prática no ambiente forense, atuando nas áreas cível e trabalhista.">
        <meta name="keywords" content="Júlia Montanari, estudante de Direito, Direito, advocacia, estagiária, São José dos Campos, currículo jurídico">
        <meta name="author" content="Júlia Montanari">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url('/') }}">

        <!-- Open Graph -->
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:type" content="profile">
        <meta property="og:title" content="Júlia Montanari — Estudante de Direito">
        <meta property="og:description" content="Bacharelanda em Direito com experiência em estágios jurídicos e vivência prática no ambiente forense, buscando crescimento nas áreas cível e trabalhista.">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:image" content="{{ asset('storage/users/julia.jpeg') }}">
        <meta property="og:locale" content="pt_BR">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Júlia Montanari — Estudante de Direito">
        <meta name="twitter:description" content="Bacharelanda em Direito com experiência em estágios jurídicos e vivência prática no ambiente forense, buscando crescimento nas áreas cível e trabalhista.">
        <meta name="twitter:image" content="{{ asset('storage/users/julia.jpeg') }}">

        <!-- Theme -->
        <meta name="theme-color" content="#881337">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('storage/config/favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/config/favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Structured data -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ProfilePage",
            "mainEntity": {
                "@type": "Person",
                "name": "Júlia Montanari",
                "jobTitle": "Estudante de Direito",
                "url": "{{ url('/') }}",
                "image": "{{ asset('storage/users/julia.jpeg') }}",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "São José dos Campos",
                    "addressRegion": "SP",
                    "addressCountry": "BR"
                },
                "sameAs": ["https://linkedin.com/in/jumontanari"]
            }
        }
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-MJRCS9528F"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-MJRCS9528F');
        </script>
    </body>
</html>
