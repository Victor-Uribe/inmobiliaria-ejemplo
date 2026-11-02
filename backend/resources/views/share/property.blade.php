<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>

    <meta name="description" content="{{ $description }}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Inmobiliaria Real Estate">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $imageUrl }}">

    <link rel="canonical" href="{{ $frontUrl }}">
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <img src="{{ $imageUrl }}" alt="{{ $title }}" style="max-width:100%;height:auto;">

    <p>
        <a href="{{ $frontUrl }}">Ver propiedad</a>
    </p>

    <script>
        window.location.replace(@json($frontUrl));
    </script>
</body>
</html>
