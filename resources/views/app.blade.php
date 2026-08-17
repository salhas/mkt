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
            
            // Default OG metadata (using lightweight < 150KB og_thumbnail.jpg for WhatsApp crawler compliance)
            $ogTitle = "Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana";
            $ogDesc = "Ekosistem penanggulangan bencana terpadu, relawan donor darah siaga 24/7, tim rescue SAR lapangan, dan transparansi donasi kemanusiaan.";
            $ogImage = asset('images/og_thumbnail.jpg');
            $ogType = "website";
            $currentUrl = url()->current();

            // Dynamic OG for News Article Detail Page
            $route = request()->route();
            if ($route && $route->getName() === 'public.news.show') {
                $slugOrId = $route->parameter('slug');
                if ($slugOrId) {
                    $newsItem = \App\Models\News::where('slug', $slugOrId)->orWhere('id', $slugOrId)->first();
                    if ($newsItem) {
                        $ogTitle = $newsItem->title . ' - Yayasan MKT Indonesia';
                        $ogDesc = \Illuminate\Support\Str::limit(strip_tags($newsItem->content), 160);
                        $ogType = "article";
                        if ($newsItem->image_url) {
                            $ogImage = str_starts_with($newsItem->image_url, 'http') 
                                ? $newsItem->image_url 
                                : url($newsItem->image_url);
                        }
                    }
                }
            }

            // Ensure absolute URLs with matching scheme
            if (!str_starts_with($ogImage, 'http')) {
                $ogImage = url($ogImage);
            }
            if (request()->isSecure() && str_starts_with($ogImage, 'http://')) {
                $ogImage = str_replace('http://', 'https://', $ogImage);
            }
            if (request()->isSecure() && str_starts_with($currentUrl, 'http://')) {
                $currentUrl = str_replace('http://', 'https://', $currentUrl);
            }
        @endphp
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        <link rel="image_src" href="{{ $ogImage }}">

        <!-- Open Graph / Facebook / WhatsApp / Telegram Sharelink -->
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:url" content="{{ $currentUrl }}">
        <meta property="og:site_name" content="Yayasan MKT Indonesia">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDesc }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:alt" content="{{ $ogTitle }}">
        <meta property="og:locale" content="id_ID">

        <!-- Twitter / X Sharelink Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $currentUrl }}">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDesc }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="{{ $ogTitle }}">

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
