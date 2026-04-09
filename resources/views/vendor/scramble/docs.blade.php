<!doctype html>
<html lang="es-AR" data-theme="{{ $config->get('ui.theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="{{ $config->get('ui.theme', 'light') }}">
    <title>{{ $config->get('ui.title') ?? config('app.name') . ' - API Docs' }}</title>

    {{-- SEO Meta Tags - Developer Intent --}}
    <meta name="description" content="API REST gratuita para precios de autos en Argentina. Endpoints para consultar valuaciones por marca, modelo y versión con conversión USD/ARS.">
    <meta name="author" content="Santiago Evangelista">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="theme-color" content="#0a0f1c">

    <link rel="canonical" href="{{ config('app.url') }}/docs/api">
    <link rel="alternate" hreflang="es-AR" href="{{ config('app.url') }}/docs/api">
    <link rel="alternate" hreflang="x-default" href="{{ config('app.url') }}/docs/api">

    {{-- Open Graph - Developer focused --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:title" content="Documentación API — Arg Autos">
    <meta property="og:description" content="API REST gratuita para consultar precios del mercado automotor argentino. Documentación interactiva con endpoints, ejemplos y guía de integración para desarrolladores.">
    <meta property="og:url" content="{{ config('app.url') }}/docs/api">
    <meta property="og:site_name" content="Arg Autos API">
    <meta property="og:image" content="{{ config('app.url') }}/logo-api.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Arg Autos API — documentación de la API de precios automotor">

    {{-- Twitter Card - Developer focused --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Documentación API — Arg Autos">
    <meta name="twitter:description" content="API REST gratuita con documentación interactiva. Consultá precios de más de 5.800 versiones de autos en Argentina por marca, modelo y año.">
    <meta name="twitter:image" content="{{ config('app.url') }}/logo-api.jpg">
    <meta name="twitter:image:alt" content="Arg Autos API — documentación de la API de precios automotor">

    {{-- JSON-LD: SoftwareApplication --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "Arg Autos API",
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "All",
        "description": "API REST pública y gratuita para consultar precios del mercado automotor argentino. Más de 60 marcas, 600 modelos y 5.800 versiones con conversión USD/ARS en tiempo real.",
        "url": "{{ config('app.url') }}/docs/api",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Acceso gratuito a la API"
        },
        "author": {
            "@@type": "Person",
            "name": "Santiago Evangelista",
            "url": "https://github.com/SantiEvangelista"
        }
    }
    </script>

    {{-- JSON-LD: TechArticle --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "TechArticle",
        "headline": "Documentación API — Arg Autos",
        "description": "Guía completa de la API REST para consultar precios de autos en Argentina. Endpoints, parámetros, respuestas y ejemplos de integración.",
        "url": "{{ config('app.url') }}/docs/api",
        "inLanguage": "es-AR",
        "proficiencyLevel": "Beginner",
        "dependencies": "HTTP client (curl, fetch, axios)",
        "author": {
            "@@type": "Person",
            "name": "Santiago Evangelista",
            "url": "https://github.com/SantiEvangelista"
        },
        "publisher": {
            "@@type": "Organization",
            "name": "Arg Autos API",
            "logo": {
                "@@type": "ImageObject",
                "url": "{{ config('app.url') }}/logo-api.jpg"
            }
        }
    }
    </script>

    {{-- JSON-LD: BreadcrumbList --}}
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

    {{-- JSON-LD: HowTo - Pasos para integrar la API --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "HowTo",
        "name": "Cómo integrar la API de precios de autos argentinos",
        "description": "Guía paso a paso para consultar precios de autos en Argentina usando la API REST gratuita de Arg Autos.",
        "step": [
            {
                "@@type": "HowToStep",
                "position": 1,
                "name": "Obtener marcas disponibles",
                "text": "Realizá un GET a {{ config('app.url') }}/api/v1/brands para listar las 60+ marcas disponibles."
            },
            {
                "@@type": "HowToStep",
                "position": 2,
                "name": "Consultar modelos de una marca",
                "text": "Realizá un GET a /api/v1/brands/{id}/models para ver los modelos disponibles de una marca específica."
            },
            {
                "@@type": "HowToStep",
                "position": 3,
                "name": "Ver versiones de un modelo",
                "text": "Realizá un GET a /api/v1/models/{id}/versions para ver las versiones y variantes de un modelo."
            },
            {
                "@@type": "HowToStep",
                "position": 4,
                "name": "Consultar precios por año-modelo",
                "text": "Realizá un GET a /api/v1/versions/{id}/valuations para obtener precios por año-modelo en USD. Agregá ?currency=ars para convertir a pesos argentinos con cotización en tiempo real."
            }
        ]
    }
    </script>

    <!-- Favicon & PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
    <link rel="manifest" href="/manifest.json">

    <script src="https://unpkg.com/@stoplight/elements@8.4.2/web-components.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.4.2/styles.min.css">

    <script>
        const originalFetch = window.fetch;

        // intercept TryIt requests and add the XSRF-TOKEN header,
        // which is necessary for Sanctum cookie-based authentication to work correctly
        window.fetch = (url, options) => {
            const CSRF_TOKEN_COOKIE_KEY = "XSRF-TOKEN";
            const CSRF_TOKEN_HEADER_KEY = "X-XSRF-TOKEN";
            const getCookieValue = (key) => {
                const cookie = document.cookie.split(';').find((cookie) => cookie.trim().startsWith(key));
                return cookie?.split("=")[1];
            };

            const updateFetchHeaders = (
                headers,
                headerKey,
                headerValue,
            ) => {
                if (headers instanceof Headers) {
                    headers.set(headerKey, headerValue);
                } else if (Array.isArray(headers)) {
                    headers.push([headerKey, headerValue]);
                } else if (headers) {
                    headers[headerKey] = headerValue;
                }
            };
            const csrfToken = getCookieValue(CSRF_TOKEN_COOKIE_KEY);
            if (csrfToken) {
                const { headers = new Headers() } = options || {};
                updateFetchHeaders(headers, CSRF_TOKEN_HEADER_KEY, decodeURIComponent(csrfToken));
                return originalFetch(url, {
                    ...options,
                    headers,
                });
            }

            return originalFetch(url, options);
        };
    </script>

    <style>
        html, body { margin:0; height:100%; }
        body { background-color: var(--color-canvas); }
        /* issues about the dark theme of stoplight/mosaic-code-viewer using web component:
         * https://github.com/stoplightio/elements/issues/2188#issuecomment-1485461965
         */
        [data-theme="dark"] .token.property {
            color: rgb(128, 203, 196) !important;
        }
        [data-theme="dark"] .token.operator {
            color: rgb(255, 123, 114) !important;
        }
        [data-theme="dark"] .token.number {
            color: rgb(247, 140, 108) !important;
        }
        [data-theme="dark"] .token.string {
            color: rgb(165, 214, 255) !important;
        }
        [data-theme="dark"] .token.boolean {
            color: rgb(121, 192, 255) !important;
        }
        [data-theme="dark"] .token.punctuation {
            color: #dbdbdb !important;
        }
    </style>
</head>
<body style="height: 100vh; overflow-y: hidden; display: flex; flex-direction: column;">
<div style="padding: 10px 16px; background: #0a0f1c; border-bottom: 1px solid rgba(212, 175, 55, 0.15); flex-shrink: 0;">
    <a href="{{ config('app.url') }}" style="font-size: 13px; color: #d4af37; text-decoration: none; font-family: system-ui, sans-serif; font-weight: 500;">← Volver a la página principal</a>
</div>
<elements-api
    style="flex: 1; overflow-y: auto;"
    id="docs"
    tryItCredentialsPolicy="{{ $config->get('ui.try_it_credentials_policy', 'include') }}"
    router="hash"
    @if($config->get('ui.hide_try_it')) hideTryIt="true" @endif
    @if($config->get('ui.hide_schemas')) hideSchemas="true" @endif
    @if($config->get('ui.logo')) logo="{{ $config->get('ui.logo') }}" @endif
    @if($config->get('ui.layout')) layout="{{ $config->get('ui.layout') }}" @endif
/>
<script>
    (async () => {
        const docs = document.getElementById('docs');
        docs.apiDescriptionDocument = @json($spec);
    })();
</script>

@if($config->get('ui.theme', 'light') === 'system')
    <script>
        var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        function updateTheme(e) {
            if (e.matches) {
                window.document.documentElement.setAttribute('data-theme', 'dark');
                window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'dark');
            } else {
                window.document.documentElement.setAttribute('data-theme', 'light');
                window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'light');
            }
        }

        mediaQuery.addEventListener('change', updateTheme);
        updateTheme(mediaQuery);
    </script>
@endif

{{-- Contenido estático para bots/crawlers que no ejecutan JS --}}
<noscript>
    <header>
        <h1>Documentación API — Arg Autos</h1>
        <p>API REST pública y gratuita para consultar precios de autos en Argentina. Documentación interactiva con endpoints, ejemplos y guía de integración para desarrolladores.</p>
    </header>
    <section>
        <h2>Endpoints disponibles</h2>
        <ul>
            <li><code>GET /api/v1/brands</code> — Lista de las 60+ marcas disponibles</li>
            <li><code>GET /api/v1/brands/{id}/models</code> — Modelos de una marca específica</li>
            <li><code>GET /api/v1/models/{id}/versions</code> — Versiones y variantes de un modelo</li>
            <li><code>GET /api/v1/versions/{id}/valuations</code> — Precios por año-modelo en USD</li>
            <li><code>GET /api/v1/versions/{id}/valuations?currency=ars</code> — Precios en pesos argentinos</li>
            <li><code>GET /api/v1/search?q={término}</code> — Búsqueda general de vehículos</li>
            <li><code>GET /api/v1/price-explorer?min_price=X&max_price=Y</code> — Explorador de precios por presupuesto</li>
            <li><code>GET /api/v1/stats</code> — Estadísticas del mercado automotor</li>
        </ul>
        <p>Base URL: <code>{{ config('app.url') }}/api/v1</code></p>
    </section>
    <section>
        <h2>Cómo integrar la API</h2>
        <ol>
            <li>Consultá las marcas disponibles con <code>GET /api/v1/brands</code></li>
            <li>Seleccioná una marca y consultá sus modelos con <code>GET /api/v1/brands/{id}/models</code></li>
            <li>Elegí un modelo y consultá las versiones con <code>GET /api/v1/models/{id}/versions</code></li>
            <li>Obtené los precios por año-modelo con <code>GET /api/v1/versions/{id}/valuations</code>. Agregá <code>?currency=ars</code> para convertir a pesos argentinos con cotización en tiempo real.</li>
        </ol>
    </section>
    <footer>
        <p>Desarrollado por <a href="https://github.com/SantiEvangelista">Santiago Evangelista</a></p>
        <p><a href="{{ config('app.url') }}">Volver a la página principal</a></p>
    </footer>
</noscript>
</body>
</html>
