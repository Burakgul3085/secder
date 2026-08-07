@php
    /* Menü etiketini mevcut dile göre çeviren yardımcı */
    function navMenuLabel(string $label): string {
        $key = 'app.menu.' . $label;
        return __($key) !== $key ? __($key) : $label;
    }
    $currentLocale = app()->getLocale();
    $langList = [
        ['code' => 'tr', 'flag' => 'https://flagcdn.com/w40/tr.png', 'label' => 'Türkçe'],
        ['code' => 'en', 'flag' => 'https://flagcdn.com/w40/gb.png', 'label' => 'English'],
        ['code' => 'ar', 'flag' => 'https://flagcdn.com/w40/sa.png', 'label' => 'العربية'],
        ['code' => 'ru', 'flag' => 'https://flagcdn.com/w40/ru.png', 'label' => 'Русский'],
    ];
    $flagMap = [
        'tr' => 'https://flagcdn.com/w40/tr.png',
        'en' => 'https://flagcdn.com/w40/gb.png',
        'ar' => 'https://flagcdn.com/w40/sa.png',
        'ru' => 'https://flagcdn.com/w40/ru.png',
    ];
    $currentFlag = $flagMap[$currentLocale] ?? $flagMap['tr'];

    /* Logo yanındaki kurumsal slogan: Genel Ayarlar » Header Sloganı alanından gelir.
       Alan boşsa slogan satırı hiç basılmaz. */
    /* Admin'de yazıldığı gibi gösterilir (büyük harfe çevrilmez, sondaki ... silinmez). */
    $brandTagline = trim((string) ($siteSettings->header_tagline ?? ''));
    if (mb_strlen($brandTagline) > 80) {
        $brandTagline = mb_substr($brandTagline, 0, 80);
    }

    /* Menüde bulunduğumuz sayfayı işaretlemek için yol karşılaştırması */
    $currentPath = '/' . trim(request()->path(), '/');
    $navIsActive = function (?string $url) use ($currentPath): bool {
        if (blank($url)) {
            return false;
        }

        $path = '/' . trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '/') {
            return $currentPath === '/';
        }

        return $currentPath === $path || str_starts_with($currentPath, $path . '/');
    };

    /* Masaüstü menü: marka ile sağ aksiyonlar arası alana yayılır */
    $navLinkBase = 'whitespace-nowrap rounded-md px-3 py-2 font-semibold transition';
    $navLinkIdle = 'text-slate-800 hover:bg-slate-100 hover:text-cyan-700';
    $navLinkActive = 'bg-cyan-50 text-cyan-800';
@endphp

<header
    x-data="{
        contactOpen: false,
        scrolled: false,
        ticking: false,
        /* Histerezis: açılma ve kapanma eşikleri farklı olduğu için
           sınır noktasında ileri geri titreme oluşmaz. */
        syncScroll() {
            if (this.ticking) return;
            this.ticking = true;
            requestAnimationFrame(() => {
                const y = window.scrollY;
                if (! this.scrolled && y > 140) {
                    this.scrolled = true;
                } else if (this.scrolled && y < 70) {
                    this.scrolled = false;
                }
                this.ticking = false;
            });
        },
        init() {
            this.scrolled = window.scrollY > 140;
            this.$watch('contactOpen', (v) => {
                document.documentElement.classList.toggle('overflow-hidden', v);
                document.body.classList.toggle('secder-menu-open', v);
            });
        }
    }"
    {{-- Sayfa kaydırıldığında header incelir ve gölgesi belirginleşir --}}
    @scroll.window.passive="syncScroll()"
    class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 backdrop-blur transition-shadow duration-300"
    :class="scrolled ? 'shadow-lg shadow-slate-900/5' : 'shadow-sm'"
>
    <div
        class="overflow-hidden border-b border-cyan-800/60 bg-cyan-900 text-cyan-50 transition-all duration-300"
        :class="scrolled ? 'max-h-0 border-b-0 py-0 opacity-0 pointer-events-none' : 'max-h-14 opacity-100 lg:max-h-24'"
    >
        @php
            $topBarSocialLinks = $siteSettings->activeSocialLinks();
            $topBarAria = [
                'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'facebook' => 'Facebook',
                'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'website' => __('app.nav.website_aria'),
            ];
            // Inline stiller: Tailwind dinamik hex sınıflarını derlemediği için kullanılıyor.
            $topBarBrandStyle = [
                'instagram' => 'background:linear-gradient(135deg,#f58529,#dd2a7b,#8134af);color:#fff;',
                'youtube'   => 'background:#FF0000;color:#fff;',
                'tiktok'    => 'background:#010101;color:#fff;',
                'facebook'  => 'background:#1877F2;color:#fff;',
                'x'         => 'background:#ffffff;color:#0f172a;',
                'linkedin'  => 'background:#0A66C2;color:#fff;',
                'whatsapp'  => 'background:#25D366;color:#fff;',
                'telegram'  => 'background:#26A5E4;color:#fff;',
                'website'   => 'background:#ffffff;color:#155e75;',
            ];
        @endphp

        {{-- Telefon / tablet: telefon | sosyal | e-posta + adres --}}
        <div class="lg:hidden">
            <div class="mx-auto flex max-w-7xl items-center gap-2 px-3 py-1.5 text-[11px]">
                <div class="flex min-w-0 shrink-0 items-center">
                    @if(!empty($siteSettings->phone))
                        <a href="tel:{{ preg_replace('/\s+/', '', $siteSettings->phone) }}" class="inline-flex items-center gap-1 font-semibold text-cyan-50 transition hover:text-white">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 shrink-0"><path d="M2 3.75A1.75 1.75 0 0 1 3.75 2h2.31c.83 0 1.54.58 1.7 1.39l.39 1.98a1.75 1.75 0 0 1-.5 1.57l-1.1 1.1a13.13 13.13 0 0 0 5.4 5.4l1.1-1.1a1.75 1.75 0 0 1 1.57-.5l1.98.4A1.75 1.75 0 0 1 18 13.94v2.31A1.75 1.75 0 0 1 16.25 18h-.75C8.6 18 2 11.4 2 3.75Z" /></svg>
                            <span>{{ $siteSettings->phone }}</span>
                        </a>
                    @endif
                </div>

                <div class="no-scrollbar flex min-w-0 flex-1 items-center justify-center gap-1.5 overflow-x-auto px-1">
                    @foreach ($topBarSocialLinks as $social)
                        <a
                            href="{{ $social['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full shadow-sm transition hover:brightness-110"
                            style="{{ $topBarBrandStyle[$social['platform']] ?? 'background:rgba(255,255,255,.2);color:#fff;' }}"
                            title="{{ $topBarAria[$social['platform']] ?? $social['platform'] }}"
                            aria-label="{{ $topBarAria[$social['platform']] ?? $social['platform'] }}"
                        >
                            <x-social-brand-icon :platform="$social['platform']" icon-class="h-3.5 w-3.5" />
                        </a>
                    @endforeach
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    @if(!empty($siteSettings->email))
                        <a href="mailto:{{ $siteSettings->email }}" class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-cyan-50 transition hover:bg-white/20" aria-label="{{ $siteSettings->email }}">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path d="M2.94 5.5A2 2 0 0 1 4.8 4h10.4a2 2 0 0 1 1.86 1.5L10 9.88 2.94 5.5Z" /><path d="M2.8 7.25V14a2 2 0 0 0 2 2h10.4a2 2 0 0 0 2-2V7.25l-6.69 4.15a1 1 0 0 1-1.02 0L2.8 7.25Z" /></svg>
                        </a>
                    @endif
                    @if(!empty($siteSettings->address))
                        <button
                            type="button"
                            @click="contactOpen = true"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-cyan-50 transition hover:bg-white/20"
                            title="{{ $siteSettings->address }}"
                            aria-label="{{ __('app.nav.address_open') }}"
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M10 2.5a5.5 5.5 0 0 0-5.5 5.5c0 4.3 4.65 8.76 5.03 9.12a.7.7 0 0 0 .94 0c.38-.36 5.03-4.82 5.03-9.12A5.5 5.5 0 0 0 10 2.5Zm0 7.25a1.75 1.75 0 1 1 0-3.5 1.75 1.75 0 0 1 0 3.5Z" clip-rule="evenodd" /></svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mx-auto hidden max-w-7xl items-center justify-between gap-3 px-4 py-1.5 text-xs lg:flex lg:px-6">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                @if(!empty($siteSettings->email))
                    <span class="inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path d="M2.94 5.5A2 2 0 0 1 4.8 4h10.4a2 2 0 0 1 1.86 1.5L10 9.88 2.94 5.5Z" /><path d="M2.8 7.25V14a2 2 0 0 0 2 2h10.4a2 2 0 0 0 2-2V7.25l-6.69 4.15a1 1 0 0 1-1.02 0L2.8 7.25Z" /></svg>
                        <a href="mailto:{{ $siteSettings->email }}" class="hover:text-white">{{ $siteSettings->email }}</a>
                    </span>
                @endif
                @if(!empty($siteSettings->address))
                    <span class="inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M10 2.5a5.5 5.5 0 0 0-5.5 5.5c0 4.3 4.65 8.76 5.03 9.12a.7.7 0 0 0 .94 0c.38-.36 5.03-4.82 5.03-9.12A5.5 5.5 0 0 0 10 2.5Zm0 7.25a1.75 1.75 0 1 1 0-3.5 1.75 1.75 0 0 1 0 3.5Z" clip-rule="evenodd" /></svg>
                        <span>{{ $siteSettings->address }}</span>
                    </span>
                @endif
                @if(!empty($siteSettings->phone))
                    <span class="inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path d="M2 3.75A1.75 1.75 0 0 1 3.75 2h2.31c.83 0 1.54.58 1.7 1.39l.39 1.98a1.75 1.75 0 0 1-.5 1.57l-1.1 1.1a13.13 13.13 0 0 0 5.4 5.4l1.1-1.1a1.75 1.75 0 0 1 1.57-.5l1.98.4A1.75 1.75 0 0 1 18 13.94v2.31A1.75 1.75 0 0 1 16.25 18h-.75C8.6 18 2 11.4 2 3.75Z" /></svg>
                        <a href="tel:{{ preg_replace('/\s+/', '', $siteSettings->phone) }}" class="hover:text-white">{{ $siteSettings->phone }}</a>
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @foreach ($topBarSocialLinks as $social)
                    <a
                        href="{{ $social['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition duration-200 hover:-translate-y-0.5 hover:brightness-110"
                        style="{{ $topBarBrandStyle[$social['platform']] ?? 'background:rgba(255,255,255,.2);color:#fff;' }}"
                        title="{{ $topBarAria[$social['platform']] ?? $social['platform'] }}"
                        aria-label="{{ $topBarAria[$social['platform']] ?? $social['platform'] }}"
                    >
                        <x-social-brand-icon :platform="$social['platform']" icon-class="h-4 w-4" />
                    </a>
                @endforeach
                <a href="{{ route('donations') }}" class="ml-2 rounded-full bg-white/15 px-3 py-1.5 font-medium text-cyan-50 transition hover:bg-white/25">{{ __('app.nav.donate') }}</a>
                <a href="{{ route('volunteer') }}" class="rounded-full border border-cyan-100/50 px-3 py-1.5 font-medium text-cyan-50 transition hover:bg-white/10">{{ __('app.nav.volunteer') }}</a>
            </div>
        </div>
    </div>

    @php
        $excludedMainLabels = ['ana sayfa', 'anasayfa', 'iletişim', 'iletisim', 'bağış', 'bagis', 'bağış yap', 'bagis yap', 'bağış hesapları', 'bagis hesaplari', 'medyada biz'];
        $headerTopItems = $menuItems
            ->whereNull('parent_id')
            ->filter(function ($item) use ($excludedMainLabels) {
                $label = mb_strtolower(trim((string) $item->label));
                return ! in_array($label, $excludedMainLabels, true);
            })
            ->values();
        $headerChildren = $menuItems
            ->whereNotNull('parent_id')
            ->groupBy('parent_id');
    @endphp

    {{-- Kurumsal header: [Marka SOL] [Menü ORTA] [Aksiyonlar SAĞ] — lg ve üzeri masaüstü --}}
    <div
        class="mx-auto flex max-w-7xl items-center gap-2 px-3 py-2 transition-all duration-300 sm:gap-3 sm:px-4 lg:gap-4 lg:px-6"
        :class="scrolled ? 'lg:py-1.5' : 'lg:py-2'"
    >
        {{-- SOL: Logo + SECDER + slogan --}}
        <a href="{{ route('home') }}" class="group flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
            <span class="relative shrink-0">
                <span class="pointer-events-none absolute -inset-1 rounded-full bg-cyan-200/50 opacity-0 blur-[6px] transition duration-300 group-hover:opacity-100" aria-hidden="true"></span>
                <img
                    src="{{ $siteSettings->logo ? asset('storage/' . $siteSettings->logo) : asset('images/default-logo.svg') }}"
                    alt="{{ $siteSettings->site_title }}"
                    class="relative h-9 w-9 rounded-full bg-white object-cover shadow-sm ring-1 ring-slate-200/80 transition-all duration-300 group-hover:ring-cyan-300 sm:h-10 sm:w-10"
                    :class="scrolled ? 'lg:h-9 lg:w-9' : 'lg:h-11 lg:w-11'"
                >
            </span>

            <span class="hidden h-9 w-px shrink-0 bg-gradient-to-b from-transparent via-slate-200 to-transparent lg:block" aria-hidden="true"></span>

            <span class="min-w-0 leading-none">
                <span class="secder-brand-title block truncate whitespace-nowrap">{{ $siteSettings->site_title }}</span>
                @if($brandTagline !== '')
                    <span
                        class="secder-brand-tagline mt-0.5 hidden truncate whitespace-nowrap sm:block sm:mt-1"
                        :class="scrolled ? 'lg:hidden' : ''"
                        title="{{ $brandTagline }}"
                    >{{ $brandTagline }}</span>
                @endif
            </span>
        </a>

        {{-- Telefon / tablet: Dil · Galeri · Menü · Hesaplar (kısa etiket; tam ad menüde) --}}
        <div class="ml-auto flex shrink-0 items-center gap-1.5 lg:hidden">
            <div class="relative shrink-0" x-data="{ mobileLangOpen: false }" @click.outside="mobileLangOpen = false">
                <button
                    type="button"
                    @click="mobileLangOpen = !mobileLangOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white shadow-sm"
                    aria-label="{{ __('app.nav.lang_selector') }}"
                >
                    <img src="{{ $currentFlag }}" alt="{{ strtoupper($currentLocale) }}" class="h-4 w-5 rounded object-cover">
                </button>
                <div
                    x-show="mobileLangOpen"
                    x-cloak
                    class="absolute right-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                >
                    @foreach($langList as $lang)
                        <a
                            href="{{ route('locale.switch', $lang['code']) }}"
                            class="flex items-center gap-2 border-b border-slate-100 px-3 py-2.5 text-xs font-semibold text-slate-700 last:border-0 hover:bg-cyan-50 {{ $currentLocale === $lang['code'] ? 'bg-cyan-50' : '' }}"
                        >
                            <img src="{{ $lang['flag'] }}" alt="{{ strtoupper($lang['code']) }}" class="h-4 w-5 rounded object-cover">
                            <span class="flex-1">{{ $lang['label'] }}</span>
                            <span class="text-[10px] uppercase text-slate-400">{{ $lang['code'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a
                href="{{ route('gallery') }}"
                title="{{ __('app.nav.gallery_title') }}"
                aria-label="{{ __('app.nav.gallery_title') }}"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
            </a>
            <button
                type="button"
                @click="contactOpen = true"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50"
                :aria-expanded="contactOpen"
                aria-label="{{ __('app.nav.menu_open') }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            {{-- Kısa etiket sığar; uzun “Hesap Numaralarımız” menü panelinde --}}
            <a
                href="{{ route('donations') }}"
                class="inline-flex h-9 shrink-0 items-center gap-1 rounded-full bg-gradient-to-r from-cyan-600 to-cyan-800 px-2.5 text-[11px] font-bold text-white shadow-sm ring-1 ring-inset ring-white/15 transition hover:brightness-110 sm:gap-1.5 sm:px-3 sm:text-xs"
                title="{{ __('app.nav.donate') }}"
                aria-label="{{ __('app.nav.donate') }}"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 shrink-0" aria-hidden="true">
                    <path d="M10 17.5s-6.5-4.06-6.5-8.13A3.87 3.87 0 0 1 10 6.44a3.87 3.87 0 0 1 6.5 2.93c0 4.07-6.5 8.13-6.5 8.13Z" />
                </svg>
                <span class="whitespace-nowrap">{{ __('app.nav.donate_mobile') }}</span>
            </a>
        </div>

        {{-- ORTA: Menü — yalnızca büyük ekran --}}
        <nav
            class="hidden min-w-0 items-center lg:flex"
            style="flex:1 1 0%; min-width:0; justify-content:space-between; align-items:center; padding:0 0.15rem 0 0.5rem; font-size:15px;"
        >
            <a href="{{ route('home') }}" class="{{ $navLinkBase }} {{ request()->routeIs('home') ? $navLinkActive : $navLinkIdle }}">{{ __('app.nav.home') }}</a>

            @forelse($headerTopItems as $item)
                @php
                    $children = $headerChildren->get($item->id, collect());
                    $hasChildren = $children->isNotEmpty();
                    $itemLabel = navMenuLabel($item->label);
                    $isActiveItem = $navIsActive($item->url)
                        || $children->contains(fn ($child) => $navIsActive($child->url));
                @endphp
                @if ($hasChildren)
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button
                            type="button"
                            class="inline-flex items-center gap-0.5 {{ $navLinkBase }} {{ $isActiveItem ? $navLinkActive : $navLinkIdle }}"
                            :aria-expanded="open"
                        >
                            <span>{{ $itemLabel }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            class="absolute left-0 top-full z-50 min-w-[220px] pt-2"
                        >
                            <div class="rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                                @foreach($children as $child)
                                    <a
                                        href="{{ $child->url }}"
                                        target="{{ $child->open_in_new_tab ? '_blank' : '_self' }}"
                                        class="block border-b border-slate-100 px-4 py-2.5 text-sm font-medium transition last:border-b-0 hover:bg-slate-50 hover:text-cyan-700 {{ $navIsActive($child->url) ? 'bg-cyan-50/70 text-cyan-800' : 'text-slate-700' }}"
                                    >{{ navMenuLabel($child->label) }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $item->url }}"
                        target="{{ $item->open_in_new_tab ? '_blank' : '_self' }}"
                        class="{{ $navLinkBase }} {{ $isActiveItem ? $navLinkActive : $navLinkIdle }}"
                    >{{ $itemLabel }}</a>
                @endif
            @empty
                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs text-slate-500">{{ __('app.nav.menu_empty') }}</span>
            @endforelse

            <a href="{{ route('news.index') }}" class="{{ $navLinkBase }} {{ request()->routeIs('news.*') ? $navLinkActive : $navLinkIdle }}">{{ __('app.nav.news_short') }}</a>

            <a href="{{ route('contact') }}" class="{{ $navLinkBase }} {{ request()->routeIs('contact') ? $navLinkActive : $navLinkIdle }}">{{ __('app.nav.contact') }}</a>
        </nav>

        {{-- SAĞ: Kamera | panel | Hesaplarımız | dil | Zekât — yalnızca büyük ekran --}}
        <div class="hidden shrink-0 items-center lg:flex" style="margin-left:0.2rem; gap:0.35rem;">
            <a
                href="{{ route('gallery') }}"
                title="{{ __('app.nav.gallery_title') }}"
                aria-label="{{ __('app.nav.gallery_title') }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50/90 text-slate-600 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50/90 hover:text-cyan-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
            </a>
            <button
                type="button"
                @click="contactOpen = true"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50/90 text-slate-700 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50/90"
                :aria-expanded="contactOpen"
                aria-label="{{ __('app.nav.quick_contact') }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            <a
                href="{{ route('donations') }}"
                class="group/donate inline-flex h-9 items-center gap-1.5 rounded-full bg-gradient-to-r from-cyan-600 to-cyan-800 px-3 text-[11px] font-bold uppercase tracking-wide text-white shadow-sm ring-1 ring-inset ring-white/15 transition duration-300 hover:brightness-110 xl:px-3.5"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 transition-transform duration-300 group-hover/donate:scale-110" aria-hidden="true">
                    <path d="M10 17.5s-6.5-4.06-6.5-8.13A3.87 3.87 0 0 1 10 6.44a3.87 3.87 0 0 1 6.5 2.93c0 4.07-6.5 8.13-6.5 8.13Z" />
                </svg>
                {{ __('app.nav.donate_short') }}
            </a>

            <div class="relative" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                <button
                    type="button"
                    @click="langOpen = !langOpen"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50/90 px-2 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50/90"
                    aria-label="{{ __('app.nav.lang_selector') }}"
                >
                    <img
                        src="{{ $currentFlag }}"
                        alt="{{ strtoupper($currentLocale) }}"
                        class="h-4 w-6 rounded object-cover shadow-sm"
                    >
                    <span class="text-[11px] font-bold text-slate-600">{{ strtoupper($currentLocale) }}</span>
                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4"/></svg>
                </button>

                <div
                    x-show="langOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 top-full z-50 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                >
                    @foreach($langList as $lang)
                    <a
                        href="{{ route('locale.switch', $lang['code']) }}"
                        class="flex w-full items-center gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-cyan-50 {{ $currentLocale === $lang['code'] ? 'bg-cyan-50' : '' }}"
                    >
                        <img src="{{ $lang['flag'] }}" alt="{{ strtoupper($lang['code']) }}" class="h-5 w-7 rounded object-cover shadow-sm">
                        <span class="flex-1 text-sm font-semibold text-slate-700">{{ $lang['label'] }}</span>
                        <span class="text-xs font-bold text-slate-400">{{ strtoupper($lang['code']) }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            <a
                href="{{ route('zakat.index') }}"
                class="inline-flex h-9 items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 text-[11px] font-bold uppercase tracking-wide text-cyan-800 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-100 xl:px-3.5"
                title="{{ __('app.nav.zakat_calculate') }}"
            >
                {{ __('app.nav.zakat_short') }}
            </a>
        </div>
    </div>

    @include('components.header-contact-panel')
</header>
