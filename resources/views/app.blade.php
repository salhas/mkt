<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="theme-color" content="#EA580C">

        <title inertia>{{ config('app.name', 'Yayasan MKT Indonesia') }}</title>

        <!-- Primary SEO Meta Tags -->
        <meta name="title" content="Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana Terpadu">
        <meta name="description" content="Ekosistem penanggulangan bencana terpadu, relawan donor darah siaga 24/7, tim rescue SAR lapangan, dan penghimpunan donasi kemanusiaan yang transparan.">
        <meta name="keywords" content="Yayasan MKT, Mitra Kemanusiaan Terpadu, Tanggap Bencana, Relawan Donor Darah, Tim Rescue SAR, Basarnas BPBD, Donasi Kemanusiaan, Filantropi">
        <meta name="author" content="Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia">

        <!-- Dynamic Favicon & Share Thumbnail -->
        @php
            $mktProfile = \App\Models\MktProfile::first();
            $faviconUrl = ($mktProfile && $mktProfile->logo) ? asset($mktProfile->logo) : asset('images/mkt_logo.png');
            $shareThumbnail = asset('images/hero_rescue.jpg');
        @endphp
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

        <!-- Open Graph / Facebook / WhatsApp / Telegram Sharelink -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Yayasan MKT Indonesia">
        <meta property="og:title" content="Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana Terpadu">
        <meta property="og:description" content="Ekosistem penanggulangan bencana terpadu, relawan donor darah siaga 24/7, tim rescue SAR lapangan, dan penghimpunan donasi kemanusiaan yang transparan.">
        <meta property="og:image" content="{{ $shareThumbnail }}">
        <meta property="og:image:secure_url" content="{{ $shareThumbnail }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:alt" content="Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia">
        <meta property="og:locale" content="id_ID">

        <!-- Twitter / X Sharelink Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana Terpadu">
        <meta name="twitter:description" content="Ekosistem penanggulangan bencana terpadu, relawan donor darah siaga 24/7, tim rescue SAR lapangan, dan transparansi donasi kemanusiaan MKT.">
        <meta name="twitter:image" content="{{ $shareThumbnail }}">
        <meta name="twitter:image:alt" content="Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950">
        @inertia
    </body>
</html>
