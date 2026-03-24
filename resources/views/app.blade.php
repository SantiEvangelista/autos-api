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
    <link rel="canonical" href="{{ config('app.url') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:title" content="Arg Autos API — Precios de autos en Argentina">
    <meta property="og:description" content="API pública y gratuita para consultar precios del mercado automotor argentino. Más de 60 marcas, 600 modelos y 5.800 versiones con conversión USD/ARS en tiempo real. Para concesionarias, apps y desarrolladores.">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:site_name" content="Arg Autos API">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Arg Autos API — Precios de autos en Argentina">
    <meta name="twitter:description" content="API REST gratuita con precios actualizados de más de 5.800 versiones de autos en Argentina. Consulta por marca, modelo y año con conversión USD/ARS.">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebAPI",
        "name": "Arg Autos API",
        "description": "API REST pública para consultar precios del mercado automotor argentino por marca, modelo, versión y año-modelo.",
        "url": "{{ config('app.url') }}",
        "documentation": "{{ config('app.url') }}/docs/api",
        "provider": {
            "@@type": "Person",
            "name": "Santiago Evangelista",
            "url": "https://github.com/SantiEvangelista"
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

    <!-- Favicon (emoji-based SVG) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
