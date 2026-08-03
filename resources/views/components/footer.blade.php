@props(['siteSettings' => \App\Models\Setting::current()])
@php
    $topBarSocialMap = [
        'instagram_url' => 'instagram',
        'youtube_url'   => 'youtube',
        'tiktok_url'    => 'tiktok',
        'facebook_url'  => 'facebook',
        'x_url'         => 'x',
        'linkedin_url'  => 'linkedin',
        'whatsapp_url'  => 'whatsapp',
        'telegram_url'  => 'telegram',
        'website_url'   => 'website',
    ];
    $topBarAria = [
        'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'facebook' => 'Facebook',
        'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'website' => 'Web sitesi',
    ];
    $logoSrc = $siteSettings->logo ? asset('storage/' . $siteSettings->logo) : asset('images/default-logo.svg');
    $isTr = app()->getLocale() === 'tr';
    $legalTextItems = [
        [
            'key'     => 'kvkk',
            'label'   => __('app.legal.kvkk_label'),
            'title'   => __('app.legal.kvkk_title'),
            'content' => $isTr
                ? (trim((string) ($siteSettings->kvkk_text ?? '')) ?: __('app.legal.kvkk_content'))
                : __('app.legal.kvkk_content'),
        ],
        [
            'key'     => 'clarification',
            'label'   => __('app.legal.clarification_label'),
            'title'   => __('app.legal.clarification_title'),
            'content' => $isTr
                ? (trim((string) ($siteSettings->volunteer_clarification_text ?? '')) ?: __('app.legal.clarification_content'))
                : __('app.legal.clarification_content'),
        ],
        [
            'key'     => 'privacy',
            'label'   => __('app.legal.privacy_label'),
            'title'   => __('app.legal.privacy_title'),
            'content' => $isTr
                ? (trim((string) ($siteSettings->privacy_policy_text ?? '')) ?: __('app.legal.privacy_content'))
                : __('app.legal.privacy_content'),
        ],
        [
            'key'     => 'cookie',
            'label'   => __('app.legal.cookie_label'),
            'title'   => __('app.legal.cookie_title'),
            'content' => $isTr
                ? (trim((string) ($siteSettings->cookie_policy_text ?? '')) ?: __('app.legal.cookie_content'))
                : __('app.legal.cookie_content'),
        ],
    ];
    $legalTextItems = array_values($legalTextItems);

    $excludedMainLabels = ['ana sayfa', 'anasayfa', 'iletişim', 'iletisim', 'bağış', 'bagis', 'bağış yap', 'bagis yap', 'bağış hesapları', 'bagis hesaplari', 'medyada biz'];
    $footerTopItems = $menuItems
        ->whereNull('parent_id')
        ->filter(function ($item) use ($excludedMainLabels) {
            $label = mb_strtolower(trim((string) $item->label));

            return ! in_array($label, $excludedMainLabels, true);
        })
        ->values();
    $footerChildren = $menuItems
        ->whereNotNull('parent_id')
        ->groupBy('parent_id');

    $hasContact = filled($siteSettings->email) || filled($siteSettings->phone) || filled($siteSettings->address);
    $socialLinks = collect($topBarSocialMap)
        ->filter(fn ($platform, $field): bool => filled($siteSettings->{$field} ?? null));

    if (! function_exists('footerMenuLabel')) {
        function footerMenuLabel(string $label): string
        {
            $key = 'app.menu.' . $label;

            return __($key) !== $key ? __($key) : $label;
        }
    }
@endphp

<footer
    class="bg-slate-950 text-slate-300"
    x-data='{
        openLegal: false,
        legalTitle: "",
        legalContent: "",
        showLegal(title, content) {
            this.legalTitle = title;
            this.legalContent = content;
            this.openLegal = true;
        }
    }'
    @keydown.escape.window="openLegal = false"
>
    <div class="h-1 w-full bg-cyan-700"></div>

    <div class="mx-auto max-w-7xl px-4 py-14 md:px-6">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-3 lg:gap-10">

            {{-- Marka + bülten --}}
            <div class="space-y-5">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img
                        src="{{ $logoSrc }}"
                        alt="{{ $siteSettings->site_title }}"
                        class="h-14 w-14 rounded-full object-cover ring-2 ring-white/10"
                    >
                    <span class="text-left font-serif text-xl font-semibold leading-tight text-white">
                        {{ $siteSettings->site_title }}
                    </span>
                </a>

                <p class="max-w-sm text-sm leading-relaxed text-slate-400">
                    {{ $siteSettings->site_description ?: 'Birlikte iyiliği büyütüyoruz. E-bültene kayıt olarak duyurulardan haberdar olabilirsiniz.' }}
                </p>

                <div class="rounded-2xl border border-slate-700 bg-slate-900/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">
                        {{ __('app.footer.newsletter') }}
                    </p>
                    <form action="{{ route('newsletter.subscribe') }}" method="post" class="mt-3 flex gap-2">
                        @csrf
                        <label class="sr-only" for="footer-newsletter-email">{{ __('app.footer.email') }}</label>
                        <input
                            id="footer-newsletter-email"
                            type="email"
                            name="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="{{ __('app.footer.newsletter_ph') }}"
                            class="min-h-[2.75rem] flex-1 rounded-xl border border-slate-600 bg-slate-950 px-3.5 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                        >
                        <button
                            type="submit"
                            class="inline-flex min-h-[2.75rem] min-w-[2.75rem] items-center justify-center rounded-xl bg-cyan-600 text-white transition hover:bg-cyan-500"
                            aria-label="{{ __('app.footer.subscribe') }}"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                    <p class="mt-2.5 text-xs leading-relaxed text-slate-500">
                        {{ __('app.footer.newsletter_note') }}
                    </p>
                </div>
            </div>

            {{-- Hızlı linkler --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-white">
                    {{ __('app.footer.quick_links') }}
                </h3>
                <div class="mt-5 space-y-6">
                    @forelse($footerTopItems as $item)
                        @php
                            $children = $footerChildren->get($item->id, collect());
                        @endphp
                        <div>
                            <a
                                href="{{ $item->url }}"
                                target="{{ $item->open_in_new_tab ? '_blank' : '_self' }}"
                                class="text-sm font-semibold text-cyan-200 transition hover:text-cyan-100"
                            >
                                {{ footerMenuLabel($item->label) }}
                            </a>

                            @if ($children->isNotEmpty())
                                <ul class="mt-3 space-y-2">
                                    @foreach ($children as $child)
                                        <li>
                                            <a
                                                href="{{ $child->url }}"
                                                target="{{ $child->open_in_new_tab ? '_blank' : '_self' }}"
                                                class="inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-white"
                                            >
                                                <span class="h-px w-3 bg-slate-600" aria-hidden="true"></span>
                                                <span>{{ footerMenuLabel($child->label) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('app.footer.menu_auto') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- İletişim + sosyal --}}
            <div class="space-y-6 md:col-span-2 lg:col-span-1">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-white">
                        {{ __('app.footer.contact_us') }}
                    </h3>

                    <ul class="mt-5 space-y-4">
                        @if (filled($siteSettings->phone))
                            <li>
                                <a
                                    href="tel:{{ preg_replace('/\s+/', '', $siteSettings->phone) }}"
                                    class="group flex items-start gap-3"
                                >
                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-cyan-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 3.5A1.5 1.5 0 013.5 2h1.76a1.5 1.5 0 011.47 1.2l.5 2.5a1.5 1.5 0 01-.84 1.7l-1.1.5a11.05 11.05 0 005.01 5.01l.5-1.1a1.5 1.5 0 011.7-.84l2.5.5A1.5 1.5 0 0118 14.74V16.5A1.5 1.5 0 0116.5 18C8.94 18 2 11.06 2 3.5z"/></svg>
                                    </span>
                                    <span>
                                        <span class="block text-xs uppercase tracking-wider text-slate-500">{{ __('app.footer.phone') }}</span>
                                        <span class="text-sm text-slate-200 transition group-hover:text-cyan-200">{{ $siteSettings->phone }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif

                        @if (filled($siteSettings->email))
                            <li>
                                <a href="mailto:{{ $siteSettings->email }}" class="group flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-cyan-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4a2 2 0 00-2 2v.28l9 5.4 9-5.4V6a2 2 0 00-2-2H3zm16 4.08l-8.37 5.02a1.25 1.25 0 01-1.26 0L1 8.08V14a2 2 0 002 2h14a2 2 0 002-2V8.08z"/></svg>
                                    </span>
                                    <span>
                                        <span class="block text-xs uppercase tracking-wider text-slate-500">{{ __('app.footer.email') }}</span>
                                        <span class="break-all text-sm text-slate-200 transition group-hover:text-cyan-200">{{ $siteSettings->email }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif

                        @if (filled($siteSettings->address))
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-cyan-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                                </span>
                                <span>
                                    <span class="block text-xs uppercase tracking-wider text-slate-500">{{ __('app.footer.address') }}</span>
                                    <span class="text-sm leading-relaxed text-slate-200">{{ $siteSettings->address }}</span>
                                </span>
                            </li>
                        @endif

                        @unless ($hasContact)
                            <li>
                                <a
                                    href="#site-map-heading"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2.5 text-sm text-slate-300 transition hover:border-cyan-500 hover:text-cyan-200"
                                >
                                    <svg class="h-3.5 w-3.5 text-cyan-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                                    {{ __('app.footer.location_title') }}
                                </a>
                            </li>
                        @endunless
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-white">
                        {{ __('app.footer.follow_us') }}
                    </p>
                    @if ($socialLinks->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($socialLinks as $field => $platform)
                                <a
                                    href="{{ $siteSettings->$field }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-slate-300 transition hover:border-cyan-500 hover:text-white"
                                    title="{{ $topBarAria[$platform] ?? $platform }}"
                                    aria-label="{{ $topBarAria[$platform] ?? $platform }}"
                                >
                                    <x-social-brand-icon :platform="$platform" icon-class="h-3.5 w-3.5" />
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">
                            {{ __('app.footer.location_subtitle') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-5 border-t border-slate-800 pt-7 text-xs text-slate-500 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1 text-center lg:text-left">
                <p>
                    © {{ date('Y') }}
                    <span class="text-slate-400">·</span>
                    {{ __('app.footer.all_rights') }}
                    <span class="text-slate-400">·</span>
                    <span class="text-slate-300">{{ $siteSettings->site_title }}</span>
                </p>
                <p class="flex items-center justify-center gap-1.5 lg:justify-start">
                    <span>{{ __('app.footer.developer') }}:</span>
                    <a
                        href="https://www.linkedin.com/in/burakgul1006/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-cyan-400 transition hover:text-cyan-300"
                        title="LinkedIn Profilim"
                    >
                        <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        burakgul3085@gmail.com
                    </a>
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 lg:justify-end">
                <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1" aria-label="Yasal">
                    @foreach ($legalTextItems as $item)
                        <button
                            type="button"
                            class="cursor-pointer text-slate-400 transition hover:text-cyan-300"
                            data-legal-title="{{ e($item['title']) }}"
                            data-legal-content="{{ e(str_replace(["\r\n", "\r"], "\n", $item['content'])) }}"
                            @click="showLegal($el.dataset.legalTitle, $el.dataset.legalContent)"
                        >
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                </nav>
                <a
                    href="{{ route('filament.crm.auth.login') }}"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-700 text-slate-500 transition hover:border-teal-500 hover:text-teal-300"
                    title="{{ __('app.footer.crm_login') }}"
                    aria-label="{{ __('app.footer.crm_login') }}"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2 4.75A2.75 2.75 0 0 1 4.75 2h10.5A2.75 2.75 0 0 1 18 4.75v2.5A2.75 2.75 0 0 1 15.25 10H4.75A2.75 2.75 0 0 1 2 7.25v-2.5ZM4.75 11.5A2.75 2.75 0 0 0 2 14.25v1.5A2.75 2.75 0 0 0 4.75 18.5h10.5A2.75 2.75 0 0 0 18 15.75v-1.5A2.75 2.75 0 0 0 15.25 11.5H4.75Z" />
                    </svg>
                </a>
                <a
                    href="{{ route('filament.admin.auth.login') }}"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-700 text-slate-500 transition hover:border-cyan-500 hover:text-cyan-300"
                    title="{{ __('app.footer.admin_login') }}"
                    aria-label="{{ __('app.footer.admin_login') }}"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 1.75a4.25 4.25 0 0 0-4.25 4.25v1.11a2.25 2.25 0 0 0-1.75 2.19v6.2a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 16 15.5V9.3a2.25 2.25 0 0 0-1.75-2.19V6A4.25 4.25 0 0 0 10 1.75Zm2.75 5.5V6a2.75 2.75 0 1 0-5.5 0v1.25h5.5Zm-2 4.45a.75.75 0 0 0-1.5 0v1.6a.75.75 0 0 0 1.5 0v-1.6Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div
        x-show="openLegal"
        x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="footer-legal-title"
        @click.self="openLegal = false"
    >
        <div class="max-h-[85vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h3 id="footer-legal-title" class="text-lg font-semibold text-slate-900" x-text="legalTitle"></h3>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 text-slate-600 transition hover:bg-slate-100"
                    @click="openLegal = false"
                    aria-label="{{ __('app.footer.close') }}"
                >
                    ×
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                <p class="whitespace-pre-line text-sm leading-7 text-slate-700" x-text="legalContent"></p>
            </div>
        </div>
    </div>
</footer>
