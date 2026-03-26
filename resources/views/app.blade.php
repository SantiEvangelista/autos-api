<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arg Autos API — Precios de autos en Argentina | Consulta por marca, modelo y año</title>
    <meta name="description" content="API REST pública y gratuita con precios actualizados del mercado automotor argentino. Consultá valuaciones de más de 60 marcas, 600 modelos y 5.800 versiones de autos 0km y usados. Conversión en tiempo real de USD a ARS con cotización dólar oficial vía Bluelytics. Ideal para concesionarias, apps de compraventa, seguros y desarrolladores.">
    <meta name="keywords" content="precios autos argentina, valuación automotor, API autos argentina, precios 0km, autos usados argentina, cotización autos, mercado automotor, consulta precios vehículos, API REST automotriz, precios por marca modelo año">
    <meta name="author" content="Santiago Evangelista">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="theme-color" content="#0a0f1c">

    {{-- #6 Canonical dinamico basado en la URL actual --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- #2 Open Graph con imagen --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:title" content="Arg Autos API — Precios de autos en Argentina">
    <meta property="og:description" content="API pública y gratuita para consultar precios del mercado automotor argentino. Más de 60 marcas, 600 modelos y 5.800 versiones con conversión USD/ARS en tiempo real. Para concesionarias, apps y desarrolladores.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Arg Autos API">
    <meta property="og:image" content="{{ config('app.url') }}/logo-api.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Arg Autos API — precios del mercado automotor argentino">

    {{-- #2 Twitter Card con imagen --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Arg Autos API — Precios de autos en Argentina">
    <meta name="twitter:description" content="API REST gratuita con precios actualizados de más de 5.800 versiones de autos en Argentina. Consulta por marca, modelo y año con conversión USD/ARS.">
    <meta name="twitter:image" content="{{ config('app.url') }}/logo-api.jpg">
    <meta name="twitter:image:alt" content="Arg Autos API — precios del mercado automotor argentino">

    {{-- #5 Schema.org JSON-LD: WebAPI --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebAPI",
        "name": "Arg Autos API",
        "description": "API REST pública para consultar precios del mercado automotor argentino por marca, modelo, versión y año-modelo.",
        "url": "{{ config('app.url') }}",
        "documentation": "{{ config('app.url') }}/docs/api",
        "provider": {
            "@@type": "Organization",
            "name": "Arg Autos API",
            "url": "{{ config('app.url') }}",
            "logo": "{{ config('app.url') }}/logo-api.jpg",
            "sameAs": [
                "https://github.com/SantiEvangelista/autos-api"
            ],
            "founder": {
                "@@type": "Person",
                "name": "Santiago Evangelista",
                "url": "https://github.com/SantiEvangelista"
            }
        },
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Acceso gratuito a la API"
        },
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "All"
    }
    </script>

    {{-- #5 Schema.org JSON-LD: Organization (Knowledge Graph / Autoridad de entidad) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "Arg Autos API",
        "url": "{{ config('app.url') }}",
        "logo": "{{ config('app.url') }}/logo-api.jpg",
        "description": "API REST pública y gratuita con precios del mercado automotor argentino.",
        "sameAs": [
            "https://github.com/SantiEvangelista/autos-api"
        ],
        "founder": {
            "@@type": "Person",
            "name": "Santiago Evangelista",
            "url": "https://github.com/SantiEvangelista"
        }
    }
    </script>

    {{-- #5 Schema.org JSON-LD: BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "Inicio",
                "item": "{{ config('app.url') }}"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "Documentación API",
                "item": "{{ config('app.url') }}/docs/api"
            }
        ]
    }
    </script>

    {{-- #12 AEO - FAQPage schema para Answer Engine Optimization --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "¿Cómo consultar el precio de un auto en Argentina?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Podés consultar precios actualizados del mercado automotor argentino usando la API REST gratuita de Arg Autos API. Buscá por marca, modelo, versión y año-modelo con conversión USD/ARS en tiempo real."
                }
            },
            {
                "@@type": "Question",
                "name": "¿La API de precios de autos es gratuita?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Sí, Arg Autos API es completamente gratuita. Ofrece acceso a valuaciones de más de 60 marcas, 600 modelos y 5.800 versiones de vehículos 0km y usados en Argentina."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué datos ofrece la API de autos?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "La API provee marcas, modelos, versiones y valuaciones por año-modelo del mercado automotor argentino. Los precios están en USD con conversión a ARS usando cotización dólar oficial en tiempo real vía Bluelytics."
                }
            }
        ]
    }
    </script>

    <!-- Favicon & PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
    <link rel="manifest" href="/manifest.json">

    {{-- #9 Preconnect y preload de fuentes criticas --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- #7 HTML semantico --}}
    <main id="app"></main>

    {{-- #1 Contenido estatico para bots/crawlers que no ejecutan JS --}}
    <noscript>
        <header>
            <h1>Arg Autos API — Precios de autos en Argentina</h1>
            <p>API REST pública y gratuita con precios actualizados del mercado automotor argentino. Consultá valuaciones de más de 60 marcas, 600 modelos y 5.800 versiones de autos 0km y usados.</p>
        </header>
        <section>
            <h2>Endpoints disponibles</h2>
            <ul>
                <li><code>GET /api/v1/brands</code> — Lista de marcas</li>
                <li><code>GET /api/v1/brands/{id}/models</code> — Modelos de una marca</li>
                <li><code>GET /api/v1/models/{id}/versions</code> — Versiones de un modelo</li>
                <li><code>GET /api/v1/versions/{id}/valuations</code> — Precios por año (USD por defecto)</li>
                <li><code>GET /api/v1/versions/{id}/valuations?currency=ars</code> — Precios en ARS (dólar oficial)</li>
                <li><code>GET /api/v1/search?q={term}</code> — Búsqueda general</li>
            </ul>
            <p>Base URL: <code>{{ config('app.url') }}/api/v1</code></p>
        </section>
        <section>
            <h2>Preguntas frecuentes</h2>
            <h3>¿Cómo consultar el precio de un auto en Argentina?</h3>
            <p>Podés consultar precios actualizados del mercado automotor argentino usando la API REST gratuita de Arg Autos API. Buscá por marca, modelo, versión y año-modelo con conversión USD/ARS en tiempo real.</p>
            <h3>¿La API de precios de autos es gratuita?</h3>
            <p>Sí, Arg Autos API es completamente gratuita. Ofrece acceso a valuaciones de más de 60 marcas, 600 modelos y 5.800 versiones de vehículos 0km y usados en Argentina.</p>
            <h3>¿Qué datos ofrece la API de autos?</h3>
            <p>La API provee marcas, modelos, versiones y valuaciones por año-modelo del mercado automotor argentino. Los precios están en USD con conversión a ARS usando cotización dólar oficial en tiempo real vía Bluelytics.</p>
        </section>
        <footer>
            <p>Desarrollado por <a href="https://github.com/SantiEvangelista">Santiago Evangelista</a></p>
            <p><a href="{{ config('app.url') }}/docs/api">Documentación de la API</a></p>
        </footer>
    </noscript>
</body>
</html>
