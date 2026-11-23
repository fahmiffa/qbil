<!-- PWA Web App Manifest -->
<link rel="manifest" href="{{ asset('manifest.json') }}">

<!-- Theme Color for Chrome, Edge & Opera -->
<meta name="theme-color" content="#2563eb" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">

<!-- iOS Safari Support -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'QBIL') }}">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icons/icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">

<!-- Android & Other Mobile Browsers -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="{{ config('app.name', 'QBIL') }}">

<!-- Windows Tile Support -->
<meta name="msapplication-TileColor" content="#2563eb">
<meta name="msapplication-TileImage" content="{{ asset('icons/icon-144x144.png') }}">

<!-- High Quality Favicons -->
<link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
