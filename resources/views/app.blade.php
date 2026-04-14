<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arg Autos — Precios de autos en Argentina</title>
    <meta name="description" content="Consultá gratis el precio de tu auto en Argentina. Valuaciones de más de 60 marcas y 5.800 versiones de 0km y usados con conversión USD/ARS en tiempo real.">
    <meta name="keywords" content="precios autos argentina, valuación automotor, API autos argentina, precios 0km, autos usados argentina, cotización autos, mercado automotor, consulta precios vehículos, API REST automotriz, precios por marca modelo año">
    <meta name="author" content="Santiago Evangelista">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="theme-color" content="#0a0f1c">

    {{-- #6 Canonical dinamico basado en la URL actual --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="es-AR" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

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

    {{-- Schema.org JSON-LD: WebApplication (usuario casual) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebApplication",
        "name": "Arg Autos — Consulta de precios de autos en Argentina",
        "url": "{{ config('app.url') }}",
        "applicationCategory": "FinanceApplication",
        "operatingSystem": "All",
        "browserRequirements": "Requires JavaScript",
        "description": "Consultá el precio de tu auto en Argentina. Buscador de precios por marca, modelo y versión con más de 60 marcas y 5.800 versiones.",
        "inLanguage": "es-AR",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "featureList": [
            "Búsqueda de precios por marca, modelo y versión",
            "Explorador de precios por presupuesto",
            "Estadísticas del mercado automotor argentino",
            "Conversión USD/ARS en tiempo real",
            "Precios de autos 0km y usados"
        ]
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
            },
            {
                "@@type": "Question",
                "name": "¿Cuánto vale mi auto en Argentina hoy?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Podés consultar el valor de tu auto en Argentina buscando por marca, modelo y versión en Arg Autos. Los precios se actualizan mensualmente con datos de la CCA (ex InfoAuto) y ACARA, con conversión a pesos argentinos en tiempo real usando la cotización del dólar oficial."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Dónde puedo ver los precios de autos 0km en Argentina?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "En Arg Autos podés ver los precios de más de 5.800 versiones de autos 0km en Argentina. Los precios están en dólares con conversión a pesos usando la cotización del dólar oficial. También podés explorar autos por presupuesto usando el buscador de precios."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cómo se calculan los precios de los autos usados?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Los precios de referencia provienen de la CCA (Cámara del Comercio Automotor, ex InfoAuto) y ACARA. Cada versión tiene valuaciones por año-modelo, permitiendo comparar la depreciación entre 0km y autos usados de distintos años."
                }
            },
            {
                "@@type": "Question",
                "name": "¿De dónde salen los datos de marcas, modelos y versiones?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Los datos provienen de la CCA (Cámara del Comercio Automotor, ex InfoAuto) y ACARA (Asociación de Concesionarios de Automotores de la República Argentina). Estas son las fuentes oficiales del mercado automotor argentino que recopilan información de precios de referencia de vehículos 0km y usados."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué significa el precio que se muestra en Arg Autos?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Es el precio de referencia del mercado automotor argentino. No es un precio de venta ni una oferta comercial, sino una valuación de referencia utilizada por concesionarias, aseguradoras y registros automotores para estimar el valor de un vehículo según su marca, modelo, versión y año-modelo."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué cotización de dólar usa Arg Autos y cada cuánto se actualiza?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Las cotizaciones se obtienen en tiempo real a través de Bluelytics. Están disponibles tanto el dólar oficial (venta) como el dólar blue. Cada vez que se consulta un precio en pesos argentinos (ARS), se puede elegir con cuál cotización convertir. La cotización se actualiza automáticamente con cada consulta."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cada cuánto se actualizan los precios de los autos?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Los precios de referencia de la CCA y ACARA se actualizan mensualmente. Cada mes se procesan las nuevas valuaciones publicadas por estas entidades para reflejar los cambios del mercado automotor."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué diferencia hay entre el precio CCA y ACARA?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "CCA (Cámara del Comercio Automotor, ex InfoAuto) y ACARA (Asociación de Concesionarios) son dos entidades distintas que publican sus propias valuaciones. Los precios pueden diferir porque cada una usa metodologías y fuentes de mercado diferentes. En Arg Autos se pueden comparar ambas fuentes."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Puedo usar la API de Arg Autos en mi aplicación o proyecto?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Sí, la API REST es pública y gratuita. Se puede integrar en cualquier aplicación, sitio web o proyecto sin necesidad de registro ni API key."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué significa 0 km en la valuación?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "La categoría 0 km se refiere al año-modelo más reciente disponible para esa versión. Es el precio de referencia para un vehículo nuevo sin uso. Los demás años representan valuaciones de vehículos usados según su antigüedad."
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

    {{-- #1 Contenido estatico AEO para bots/crawlers que no ejecutan JS --}}
    <noscript>
        <header>
            <h1>Precios de autos en Argentina — Arg Autos</h1>
            <p><strong>Consultá gratis el precio de tu auto.</strong> Arg Autos tiene valuaciones actualizadas de más de 60 marcas, 600 modelos y 5.800 versiones de autos 0km y usados en Argentina, con conversión de USD a pesos en tiempo real.</p>
        </header>
        <section>
            <h2>¿Cuánto vale mi auto en Argentina?</h2>
            <p>Podés conocer el valor de tu auto buscando por marca, modelo y versión. Los precios se actualizan mensualmente con datos de la CCA (ex InfoAuto) y ACARA. Los valores están en dólares con conversión automática a pesos argentinos usando la cotización del dólar oficial.</p>
        </section>
        <section>
            <h2>Endpoints de la API</h2>
            <ul>
                <li><code>GET /api/v1/brands</code> — Lista de las 60+ marcas disponibles</li>
                <li><code>GET /api/v1/brands/{id}/models</code> — Modelos de una marca</li>
                <li><code>GET /api/v1/models/{id}/versions</code> — Versiones de un modelo</li>
                <li><code>GET /api/v1/versions/{id}/valuations</code> — Precios por año (USD por defecto)</li>
                <li><code>GET /api/v1/versions/{id}/valuations?currency=ars</code> — Precios en ARS (dólar oficial)</li>
                <li><code>GET /api/v1/search?q={término}</code> — Búsqueda general</li>
                <li><code>GET /api/v1/price-explorer?min_price=X&max_price=Y</code> — Explorador por presupuesto</li>
            </ul>
            <p>Base URL: <code>{{ config('app.url') }}/api/v1</code></p>
        </section>
        <section>
            <h2>Preguntas frecuentes</h2>
            <h3>¿Cómo consultar el precio de un auto en Argentina?</h3>
            <p>Podés consultar precios actualizados del mercado automotor argentino usando Arg Autos. Buscá por marca, modelo, versión y año-modelo con conversión USD/ARS en tiempo real.</p>
            <h3>¿La API de precios de autos es gratuita?</h3>
            <p>Sí, Arg Autos es completamente gratuita. Ofrece acceso a valuaciones de más de 60 marcas, 600 modelos y 5.800 versiones de vehículos 0km y usados en Argentina.</p>
            <h3>¿Qué datos ofrece la API de autos?</h3>
            <p>La API provee marcas, modelos, versiones y valuaciones por año-modelo del mercado automotor argentino. Los precios están en USD con conversión a ARS usando cotización dólar oficial en tiempo real vía Bluelytics.</p>
            <h3>¿Cuánto vale mi auto en Argentina hoy?</h3>
            <p>Podés consultar el valor de tu auto buscando por marca, modelo y versión en Arg Autos. Los precios se actualizan mensualmente con datos de la CCA (ex InfoAuto) y ACARA, con conversión a pesos argentinos en tiempo real.</p>
            <h3>¿Dónde puedo ver los precios de autos 0km en Argentina?</h3>
            <p>En Arg Autos podés ver los precios de más de 5.800 versiones de autos 0km en Argentina. Los precios están en dólares con conversión a pesos usando la cotización del dólar oficial.</p>
            <h3>¿Cómo se calculan los precios de los autos usados?</h3>
            <p>Los precios de referencia provienen de la CCA (Cámara del Comercio Automotor, ex InfoAuto) y ACARA. Cada versión tiene valuaciones por año-modelo, permitiendo comparar la depreciación entre 0km y usados.</p>
        </section>
        <section>
            <h2>Sobre los datos y precios</h2>
            <h3>¿De dónde salen los datos de marcas, modelos y versiones?</h3>
            <p>Los datos provienen de la CCA (Cámara del Comercio Automotor, ex InfoAuto) y ACARA. Son las fuentes oficiales del mercado automotor argentino.</p>
            <h3>¿Qué significa el precio que se muestra?</h3>
            <p>Es el precio de referencia del mercado automotor argentino utilizado por concesionarias, aseguradoras y registros automotores.</p>
            <h3>¿Qué cotización de dólar se usa?</h3>
            <p>Las cotizaciones se obtienen en tiempo real a través de Bluelytics. Están disponibles tanto el dólar oficial (venta) como el dólar blue para convertir precios a pesos argentinos.</p>
            <h3>¿Cada cuánto se actualizan los precios?</h3>
            <p>Los precios de referencia se actualizan mensualmente con datos de la CCA y ACARA.</p>
            <h3>¿Es gratis consultar los precios?</h3>
            <p>Sí, Arg Autos es 100% gratuito para consultar precios de más de 60 marcas, 600 modelos y 5.800 versiones.</p>
            <h3>¿Qué diferencia hay entre el precio CCA y ACARA?</h3>
            <p>Son dos entidades distintas con metodologías diferentes. En Arg Autos podés comparar ambas fuentes.</p>
            <h3>¿Puedo usar la API en mi aplicación o proyecto?</h3>
            <p>Sí, la API REST es pública y gratuita. Se puede integrar sin registro ni API key.</p>
            <h3>¿Qué significa "0 km" en la valuación?</h3>
            <p>Se refiere al año-modelo más reciente disponible, el precio de referencia para un vehículo nuevo sin uso.</p>
        </section>
        <footer>
            <p>Desarrollado por <a href="https://github.com/SantiEvangelista">Santiago Evangelista</a></p>
            <p><a href="{{ config('app.url') }}/docs/api">Documentación de la API</a></p>
        </footer>
    </noscript>

    <script>
        window.__RANKINGS__ = @json($rankings);
        window.__BODY_TYPES__ = @json($bodyTypes);
        window.__STATS__ = @json($stats);
        window.__TURNSTILE_SITE_KEY__ = @json(config('app.turnstile_site_key'));
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
</body>
</html>
