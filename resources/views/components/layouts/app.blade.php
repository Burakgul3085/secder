<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSettings->site_title ?? __('app.site.default_title') }}</title>
    <meta name="description" content="{{ $siteSettings->site_description }}">
    @if($siteSettings->favicon)
        <link rel="icon" href="{{ asset('storage/' . $siteSettings->favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $bootLogo = $siteSettings->logo
            ? asset('storage/' . $siteSettings->logo)
            : asset('images/default-logo.svg');
        $bootTitle = $siteSettings->site_title ?? 'SECDER';
    @endphp
    {{-- İlk boyamada logo flash'ını önlemek için Vite'dan bağımsız kritik CSS --}}
    <style>
        html.site-booting { overflow: hidden; }
        html.site-boot-skip #site-boot { display: none !important; }
        #site-boot {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
            background: linear-gradient(160deg, #0f172a 0%, #164e63 55%, #0e7490 100%);
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        #site-boot.is-done {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        #site-boot__logo {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 9999px;
            object-fit: cover;
            background: #fff;
            box-shadow: 0 12px 36px rgba(8, 47, 73, 0.35);
            border: 2px solid rgba(255, 255, 255, 0.85);
            animation: site-boot-pulse 1.15s ease-in-out infinite;
        }
        #site-boot__label {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }
        @keyframes site-boot-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }
        @media (prefers-reduced-motion: reduce) {
            #site-boot__logo { animation: none; }
            #site-boot { transition: none; }
        }
    </style>
    <script>
        (function () {
            try {
                if (sessionStorage.getItem('secder-soft-nav') === '1') {
                    sessionStorage.removeItem('secder-soft-nav');
                    document.documentElement.classList.add('site-boot-skip');
                    return;
                }
            } catch (e) {}
            document.documentElement.classList.add('site-booting');
        })();
    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-R2X10WDTGW"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-R2X10WDTGW');
    </script>
</head>
<body>
    <div id="site-boot" role="status" aria-live="polite" aria-label="{{ __('app.site.loading') }}">
        <img
            id="site-boot__logo"
            src="{{ $bootLogo }}"
            alt="{{ $bootTitle }}"
            width="72"
            height="72"
            decoding="async"
            fetchpriority="high"
        >
        <p id="site-boot__label">SECDER</p>
    </div>
    <script>
        (function () {
            var boot = document.getElementById('site-boot');
            if (!boot || document.documentElement.classList.contains('site-boot-skip')) {
                document.documentElement.classList.remove('site-booting');
                if (boot && boot.parentNode) {
                    boot.parentNode.removeChild(boot);
                }
                return;
            }

            var done = false;
            var minUntil = Date.now() + 320;

            function hideBoot() {
                if (done) {
                    return;
                }
                done = true;
                boot.classList.add('is-done');
                document.documentElement.classList.remove('site-booting');
                window.setTimeout(function () {
                    if (boot && boot.parentNode) {
                        boot.parentNode.removeChild(boot);
                    }
                }, 420);
            }

            function tryHide() {
                var wait = Math.max(0, minUntil - Date.now());
                window.setTimeout(hideBoot, wait);
            }

            if (document.readyState === 'complete') {
                tryHide();
            } else {
                window.addEventListener('load', tryHide, { once: true });
            }
            // JS / ağır görsel takılırsa sayfa kilitlenmesin
            window.setTimeout(hideBoot, 1100);
        })();
    </script>
    <div
        id="page-transition"
        class="pointer-events-none fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/88 opacity-0 transition-opacity duration-300"
        aria-hidden="true"
    >
        <div class="flex items-center gap-3">
            <span class="page-transition-dot page-transition-dot--1"></span>
            <span class="page-transition-dot page-transition-dot--2"></span>
            <span class="page-transition-dot page-transition-dot--3"></span>
        </div>
    </div>
    <x-navbar :menu-items="$menuItems" :site-settings="$siteSettings" />
    @if (session('newsletter_success'))
        <div class="border-b border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-900" role="status">
            {{ session('newsletter_success') }}
        </div>
    @endif
    @if (session('newsletter_info'))
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900" role="status">
            {{ session('newsletter_info') }}
        </div>
    @endif
    @if ($errors->has('email'))
        <div class="border-b border-rose-200 bg-rose-50 px-4 py-3 text-center text-sm text-rose-800" role="alert">
            {{ $errors->first('email') }}
        </div>
    @endif
    <main class="min-h-[70vh]">{{ $slot }}</main>
    <x-site-map :site-settings="$siteSettings" />
    <x-footer :site-settings="$siteSettings" />
    <x-back-to-top />
</body>
</html>
