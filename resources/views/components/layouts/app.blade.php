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
            gap: 1.35rem;
            overflow: hidden;
            background:
                radial-gradient(ellipse 70% 55% at 50% 42%, rgba(34, 211, 238, 0.22), transparent 62%),
                radial-gradient(ellipse 50% 40% at 20% 80%, rgba(14, 116, 144, 0.35), transparent 55%),
                radial-gradient(ellipse 45% 35% at 85% 15%, rgba(8, 145, 178, 0.2), transparent 50%),
                linear-gradient(165deg, #0b1220 0%, #0f3a4d 48%, #0e7490 100%);
            transition: opacity 0.45s ease, visibility 0.45s ease;
        }
        #site-boot.is-done {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        #site-boot__mark {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 9.5rem;
            height: 9.5rem;
            animation: site-boot-enter 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @media (min-width: 768px) {
            #site-boot__mark {
                width: 11.5rem;
                height: 11.5rem;
            }
        }
        #site-boot__glow {
            position: absolute;
            inset: -18%;
            border-radius: 9999px;
            background: radial-gradient(circle, rgba(103, 232, 249, 0.45) 0%, rgba(14, 116, 144, 0.12) 48%, transparent 70%);
            animation: site-boot-glow 1.8s ease-in-out infinite;
            pointer-events: none;
        }
        #site-boot__ring {
            position: absolute;
            inset: -6%;
            border-radius: 9999px;
            border: 2px solid rgba(165, 243, 252, 0.35);
            box-shadow:
                0 0 0 6px rgba(34, 211, 238, 0.08),
                0 0 40px rgba(34, 211, 238, 0.28);
            animation: site-boot-ring 1.6s ease-in-out infinite;
            pointer-events: none;
        }
        #site-boot__logo {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            border-radius: 9999px;
            object-fit: cover;
            background: #fff;
            box-shadow:
                0 18px 48px rgba(8, 47, 73, 0.45),
                0 0 0 4px rgba(255, 255, 255, 0.92),
                0 0 0 8px rgba(103, 232, 249, 0.22);
            animation: site-boot-pulse 1.4s ease-in-out infinite;
        }
        #site-boot__label {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.34em;
            text-indent: 0.34em;
            text-transform: uppercase;
            color: #ecfeff;
            text-shadow: 0 2px 18px rgba(34, 211, 238, 0.45);
            animation: site-boot-label 0.85s ease 0.15s both;
        }
        #site-boot__dots {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.15rem;
        }
        #site-boot__dots span {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 9999px;
            background: #67e8f9;
            opacity: 0.35;
            animation: site-boot-dot 1s ease-in-out infinite;
        }
        #site-boot__dots span:nth-child(2) { animation-delay: 0.15s; }
        #site-boot__dots span:nth-child(3) { animation-delay: 0.3s; }
        @keyframes site-boot-enter {
            from { transform: scale(0.86); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes site-boot-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.035); }
        }
        @keyframes site-boot-glow {
            0%, 100% { opacity: 0.65; transform: scale(0.96); }
            50% { opacity: 1; transform: scale(1.08); }
        }
        @keyframes site-boot-ring {
            0%, 100% { transform: scale(1); opacity: 0.85; }
            50% { transform: scale(1.04); opacity: 1; }
        }
        @keyframes site-boot-label {
            from { opacity: 0; transform: translateY(8px); letter-spacing: 0.5em; }
            to { opacity: 1; transform: translateY(0); letter-spacing: 0.34em; }
        }
        @keyframes site-boot-dot {
            0%, 100% { opacity: 0.3; transform: translateY(0); }
            50% { opacity: 1; transform: translateY(-3px); }
        }
        @media (prefers-reduced-motion: reduce) {
            #site-boot__logo,
            #site-boot__glow,
            #site-boot__ring,
            #site-boot__mark,
            #site-boot__label,
            #site-boot__dots span { animation: none; }
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
        <div id="site-boot__mark">
            <span id="site-boot__glow" aria-hidden="true"></span>
            <span id="site-boot__ring" aria-hidden="true"></span>
            <img
                id="site-boot__logo"
                src="{{ $bootLogo }}"
                alt="{{ $bootTitle }}"
                width="184"
                height="184"
                decoding="async"
                fetchpriority="high"
            >
        </div>
        <p id="site-boot__label">SECDER</p>
        <div id="site-boot__dots" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>
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
