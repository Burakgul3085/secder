@php
    $isTr = app()->getLocale() === 'tr';
    $volunteerText = $isTr
        ? ($siteSettings->header_panel_volunteer_text ?: __('app.panel.volunteer_text'))
        : __('app.panel.volunteer_text');
    $socialTitle = $isTr
        ? ($siteSettings->social_section_title ?: __('app.panel.social_title'))
        : __('app.panel.social_title');
    $socials = collect($siteSettings->activeSocialLinks())
        ->mapWithKeys(fn (array $item): array => [$item['platform'] => $item['url']])
        ->all();
    $socialBrandStyle = [
        'instagram' => 'background:linear-gradient(135deg,#f58529,#dd2a7b,#8134af);color:#fff;border-color:transparent;',
        'youtube'   => 'background:#FF0000;color:#fff;border-color:transparent;',
        'tiktok'    => 'background:#010101;color:#fff;border-color:rgba(37,244,238,.45);',
        'facebook'  => 'background:#1877F2;color:#fff;border-color:transparent;',
        'x'         => 'background:#ffffff;color:#0f172a;border-color:transparent;',
        'linkedin'  => 'background:#0A66C2;color:#fff;border-color:transparent;',
        'whatsapp'  => 'background:#25D366;color:#fff;border-color:transparent;',
        'telegram'  => 'background:#26A5E4;color:#fff;border-color:transparent;',
        'website'   => 'background:#ffffff;color:#155e75;border-color:transparent;',
    ];
    $panelNavLink = 'flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10';
@endphp

<template x-teleport="body">
    <div
        x-show="contactOpen"
        x-cloak
        @keydown.escape.window="contactOpen = false"
        class="fixed inset-0 z-[10050]"
    >
        <div
            x-show="contactOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
            @click="contactOpen = false"
        ></div>
        <div
            x-show="contactOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="-translate-y-full opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-full opacity-0"
            class="absolute inset-x-0 top-0 z-10 flex h-[100dvh] max-h-[100dvh] flex-col border-b border-cyan-800/30 bg-gradient-to-b from-cyan-950 via-cyan-900 to-slate-900 text-white shadow-2xl lg:h-auto lg:max-h-[min(100dvh,920px)]"
        >
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-4 py-3 md:px-6">
                <div class="lg:hidden">
                    <p class="text-lg font-semibold text-white">{{ __('app.panel.menu_heading') }}</p>
                    <p class="text-xs text-cyan-100/80">{{ __('app.panel.menu_subtitle') }}</p>
                </div>
                <button
                    type="button"
                    @click="contactOpen = false"
                    class="ml-auto inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition hover:bg-white/20"
                    aria-label="{{ __('app.panel.close') }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mx-auto w-full max-w-7xl flex-1 overflow-y-auto overscroll-contain px-4 pb-28 pt-4 md:px-6 md:pb-12 md:pt-6" style="-webkit-overflow-scrolling:touch;">

                {{-- Telefon / tablet: sayfa menüsü --}}
                <div class="mb-8 space-y-2 lg:hidden">
                    <a href="{{ route('home') }}" @click="contactOpen = false" class="{{ $panelNavLink }} {{ request()->routeIs('home') ? 'border-cyan-300/40 bg-white/10' : '' }}">
                        <span>{{ __('app.nav.home') }}</span>
                        <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                    </a>

                    @foreach($headerTopItems as $item)
                        @php
                            $children = $headerChildren->get($item->id, collect());
                            $itemLabel = navMenuLabel($item->label);
                        @endphp
                        @if ($children->isNotEmpty())
                            <details class="group overflow-hidden rounded-xl border border-white/10 bg-white/5">
                                <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-white">
                                    <span class="inline-flex w-full items-center justify-between gap-2">
                                        {{ $itemLabel }}
                                        <svg class="h-4 w-4 text-cyan-200/70 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" />
                                        </svg>
                                    </span>
                                </summary>
                                <div class="border-t border-white/10 bg-black/10 py-1">
                                    @foreach($children as $child)
                                        <a
                                            href="{{ $child->url }}"
                                            target="{{ $child->open_in_new_tab ? '_blank' : '_self' }}"
                                            @click="contactOpen = false"
                                            class="block px-4 py-2.5 text-sm font-medium text-cyan-50/95 transition hover:bg-white/10 hover:text-white"
                                        >{{ navMenuLabel($child->label) }}</a>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <a
                                href="{{ $item->url }}"
                                target="{{ $item->open_in_new_tab ? '_blank' : '_self' }}"
                                @click="contactOpen = false"
                                class="{{ $panelNavLink }}"
                            >
                                <span>{{ $itemLabel }}</span>
                                <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                            </a>
                        @endif
                    @endforeach

                    <a href="{{ route('news.index') }}" @click="contactOpen = false" class="{{ $panelNavLink }} {{ request()->routeIs('news.*') ? 'border-cyan-300/40 bg-white/10' : '' }}">
                        <span>{{ __('app.nav.news_short') }}</span>
                        <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('gallery') }}" @click="contactOpen = false" class="{{ $panelNavLink }} {{ request()->routeIs('gallery') ? 'border-cyan-300/40 bg-white/10' : '' }}">
                        <span>{{ __('app.nav.gallery') }}</span>
                        <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" @click="contactOpen = false" class="{{ $panelNavLink }} {{ request()->routeIs('contact') ? 'border-cyan-300/40 bg-white/10' : '' }}">
                        <span>{{ __('app.nav.contact') }}</span>
                        <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('zakat.index') }}" @click="contactOpen = false" class="{{ $panelNavLink }} {{ request()->routeIs('zakat.*') ? 'border-cyan-300/40 bg-white/10' : '' }}">
                        <span>{{ __('app.nav.zakat_short') }}</span>
                        <svg class="h-4 w-4 text-cyan-200/70" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l6 6-6 6"/></svg>
                    </a>

                    <div class="pt-2">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-cyan-100/70">{{ __('app.panel.actions_heading') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <a
                                href="{{ route('donations') }}"
                                @click="contactOpen = false"
                                class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-3 text-center text-xs font-bold uppercase tracking-wide text-cyan-900 shadow-sm"
                            >{{ __('app.nav.donate') }}</a>
                            <a
                                href="{{ route('volunteer') }}"
                                @click="contactOpen = false"
                                class="inline-flex items-center justify-center rounded-xl border border-white/25 bg-white/10 px-3 py-3 text-center text-xs font-bold uppercase tracking-wide text-white"
                            >{{ __('app.nav.volunteer') }}</a>
                        </div>
                    </div>
                </div>

                <div class="grid gap-10 md:grid-cols-2 md:gap-12">
                    <div>
                        <h2 class="text-xl font-semibold text-white md:text-2xl">{{ __('app.panel.contact_heading') }}</h2>
                        <p class="mt-1 text-sm text-cyan-100/90">{{ __('app.panel.contact_subtitle') }}</p>
                        <ul class="mt-6 space-y-5">
                            @if(filled($siteSettings->email))
                                <li class="flex gap-4">
                                    <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-emerald-400/40 bg-emerald-500/10 text-emerald-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 6.5A2.5 2.5 0 0 1 4.5 4h15A2.5 2.5 0 0 1 22 6.5v11A2.5 2.5 0 0 1 19.5 20h-15A2.5 2.5 0 0 1 2 17.5v-11Zm1.8-.24 7.28 5.1a1.2 1.2 0 0 0 1.38 0l7.3-5.1H3.8ZM4 18.5h16V8.2l-6.2 4.3a2.5 2.5 0 0 1-2.86 0L4 8.2v10.3Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-cyan-200/80">{{ __('app.panel.label_email') }}</p>
                                        <a href="mailto:{{ $siteSettings->email }}" class="text-base font-medium text-white transition hover:text-cyan-200">{{ $siteSettings->email }}</a>
                                    </div>
                                </li>
                            @endif
                            @if(filled($siteSettings->address))
                                <li class="flex gap-4">
                                    <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-emerald-400/40 bg-emerald-500/10 text-emerald-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25a6.75 6.75 0 0 0-6.75 6.75c0 5.4 5.8 10.2 6.1 10.4a.9.9 0 0 0 1.3 0c.3-.2 6.1-5 6.1-10.4A6.75 6.75 0 0 0 12 2.25Zm0 8.75A2.25 2.25 0 1 1 12 6.5a2.25 2.25 0 0 1 0 4.5Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-cyan-200/80">{{ __('app.panel.label_address') }}</p>
                                        <p class="text-base font-medium leading-relaxed text-white">{{ $siteSettings->address }}</p>
                                    </div>
                                </li>
                            @endif
                            @if(filled($siteSettings->phone))
                                <li class="flex gap-4">
                                    <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-emerald-400/40 bg-emerald-500/10 text-emerald-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M2.5 4.5c0-1.1.9-2 2-2h1.5c.9 0 1.6.6 1.8 1.4l.5 2.4a1.5 1.5 0 0 1-.4 1.3L6 8.1a10.1 10.1 0 0 0 4.1 4.1l.7-.4a1.5 1.5 0 0 1 1.3-.3l2.3.4c.7.1 1.2.7 1.2 1.4V18c0 1.1-.9 2-2 2h-1A14.5 14.5 0 0 1 2.5 6.5v-2Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-cyan-200/80">{{ __('app.panel.label_phone') }}</p>
                                        <a href="tel:{{ preg_replace('/\s+/', '', $siteSettings->phone) }}" class="text-base font-medium text-white transition hover:text-cyan-200">{{ $siteSettings->phone }}</a>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-xl font-semibold text-white md:text-2xl">{{ __('app.panel.volunteer_heading') }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-cyan-50/90">{!! nl2br(e($volunteerText)) !!}</p>
                        <a
                            href="{{ route('volunteer') }}"
                            @click="contactOpen = false"
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20"
                        >
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75H19.5a.75.75 0 0 1 .75.75v16.5a.75.75 0 0 1-.75.75H6a2.25 2.25 0 0 1-2.25-2.25V7.5c0-.41.2-.8.5-1.05L9 1.2a.75.75 0 0 1 .5-.2h.25Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75V7.5a.75.75 0 0 0 .75.75h1.5" />
                                <path stroke-linecap="round" d="M8.25 12.75h7.5M8.25 15.75h5.5" />
                            </svg>
                            {{ __('app.panel.volunteer_btn') }}
                        </a>
                        @if (! empty($socials))
                            <div class="mt-auto border-t border-white/10 pt-8">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-cyan-100/90">{{ $socialTitle }}</h3>
                                <div class="mt-4 flex flex-wrap gap-2.5">
                                    @foreach ($socials as $key => $url)
                                        @php
                                            $label = match ($key) {
                                                'instagram' => 'Instagram',
                                                'youtube' => 'YouTube',
                                                'x' => 'X (Twitter)',
                                                'facebook' => 'Facebook',
                                                'linkedin' => 'LinkedIn',
                                                'whatsapp' => 'WhatsApp',
                                                'telegram' => 'Telegram',
                                                'tiktok' => 'TikTok',
                                                'website' => __('app.panel.website_label'),
                                                default => ucfirst($key),
                                            };
                                        @endphp
                                        <a
                                            href="{{ $url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex h-12 w-12 items-center justify-center rounded-full border shadow-sm transition hover:-translate-y-0.5 hover:brightness-110"
                                            style="{{ $socialBrandStyle[$key] ?? 'background:rgba(255,255,255,.08);color:#fff;border-color:rgba(255,255,255,.2);' }}"
                                            title="{{ $label }}"
                                            aria-label="{{ $label }}"
                                        >
                                            <x-social-brand-icon :platform="$key" />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
