@php
    $zakatConfig = [
        'locale' => app()->getLocale(),
        'settings' => $settings,
        'faqItems' => $faqItems,
        'donateUrl' => route('donations'),
        'activitiesUrl' => route('activities.index'),
        'labels' => [
            'live_prices' => __('app.zakat.live_prices'),
            'prices_loading' => __('app.zakat.prices_loading'),
            'source_label' => __('app.zakat.source_label'),
            'source_genelpara' => __('app.zakat.source_genelpara'),
            'source_note' => __('app.zakat.source_note'),
            'data_via_client' => __('app.zakat.data_via_client'),
            'last_update' => __('app.zakat.last_update'),
            'nisap_label' => __('app.zakat.nisap_label'),
            'nisap_progress' => __('app.zakat.nisap_progress'),
            'forex_section' => __('app.zakat.forex_section'),
            'coins_live' => __('app.zakat.coins_live'),
            'gold_section' => __('app.zakat.gold_section'),
            'coins_section' => __('app.zakat.coins_section'),
            'money_section' => __('app.zakat.money_section'),
            'other_section' => __('app.zakat.other_section'),
            'debts_section' => __('app.zakat.debts_section'),
            'gram' => __('app.zakat.gram'),
            'piece' => __('app.zakat.piece'),
            'gold_24' => __('app.zakat.gold_24'),
            'silver' => __('app.zakat.silver'),
            'coin_quarter' => __('app.zakat.coin_quarter'),
            'coin_half' => __('app.zakat.coin_half'),
            'coin_full' => __('app.zakat.coin_full'),
            'summary_title' => __('app.zakat.summary_title'),
            'breakdown_title' => __('app.zakat.breakdown_title'),
            'total_assets' => __('app.zakat.total_assets'),
            'net_wealth' => __('app.zakat.net_wealth'),
            'zakat_amount' => __('app.zakat.zakat_amount'),
            'below_nisap' => __('app.zakat.below_nisap'),
            'hawl_label' => __('app.zakat.hawl_label'),
            'donate' => __('app.zakat.donate'),
            'activities' => __('app.zakat.activities'),
            'reset' => __('app.zakat.reset'),
            'print' => __('app.zakat.print'),
            'legal_title' => __('app.zakat.legal_title'),
            'legal_text' => $settings['legal_text'] ?? __('app.zakat.legal_text'),
            'faq_title' => __('app.zakat.faq_title'),
            'activities_title' => __('app.zakat.activities_title'),
            'activities_desc' => __('app.zakat.activities_desc'),
            'sticky_estimate' => __('app.zakat.sticky_estimate'),
            'live_rate' => __('app.zakat.live_rate'),
            'breakdown_gold24' => __('app.zakat.breakdown_gold24'),
            'breakdown_gold22' => __('app.zakat.breakdown_gold22'),
            'breakdown_gold18' => __('app.zakat.breakdown_gold18'),
            'breakdown_gold14' => __('app.zakat.breakdown_gold14'),
            'breakdown_silver' => __('app.zakat.breakdown_silver'),
            'breakdown_coin_quarter' => __('app.zakat.breakdown_coin_quarter'),
            'breakdown_coin_half' => __('app.zakat.breakdown_coin_half'),
            'breakdown_coin_full' => __('app.zakat.breakdown_coin_full'),
            'breakdown_coin_ata' => __('app.zakat.breakdown_coin_ata'),
            'breakdown_coin_cmr' => __('app.zakat.breakdown_coin_cmr'),
            'breakdown_cash' => __('app.zakat.breakdown_cash'),
            'breakdown_bank' => __('app.zakat.breakdown_bank'),
            'breakdown_usd' => __('app.zakat.breakdown_usd'),
            'breakdown_eur' => __('app.zakat.breakdown_eur'),
            'breakdown_gbp' => __('app.zakat.breakdown_gbp'),
            'breakdown_chf' => __('app.zakat.breakdown_chf'),
            'breakdown_sar' => __('app.zakat.breakdown_sar'),
            'breakdown_aed' => __('app.zakat.breakdown_aed'),
            'breakdown_trade' => __('app.zakat.breakdown_trade'),
            'breakdown_receivables' => __('app.zakat.breakdown_receivables'),
        ],
    ];
@endphp

<x-layouts.app>
    <x-page-hero :title="__('app.zakat.page_title')" />

    <section class="overflow-x-hidden">
        <div class="mx-auto max-w-7xl px-4 py-10 md:px-6 md:py-12">
            <p class="mx-auto mb-8 max-w-3xl text-center text-base leading-relaxed text-slate-600 md:text-lg print:hidden">
                {{ $settings['intro'] ?? __('app.zakat.intro') }}
            </p>

            <div
                x-data="zakatCalculator(@js($zakatConfig))"
                x-init="init()"
                class="w-full min-w-0"
            >
                <div class="grid w-full min-w-0 gap-8 lg:grid-cols-12">
                    {{-- Canlı piyasa paneli --}}
                    <aside class="min-w-0 print:hidden lg:col-span-4 lg:sticky lg:top-28 lg:self-start">
                        <div class="overflow-hidden rounded-[18px] border border-slate-100 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.06)]">
                            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-cyan-50/90 to-white px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 7h7v7"/></svg>
                                    </span>
                                    <h2 class="text-sm font-bold uppercase tracking-[0.12em] text-cyan-800">{{ __('app.zakat.live_prices') }}</h2>
                                </div>
                                <span class="relative flex h-2.5 w-2.5" x-show="!pricesLoading && !metalsError && !forexError" x-cloak>
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                </span>
                            </div>

                            <div class="space-y-3 p-5" x-show="pricesLoading" x-cloak>
                                @foreach (range(1, 8) as $line)
                                    <div class="h-12 animate-pulse rounded-xl bg-slate-100"></div>
                                @endforeach
                            </div>

                            <div class="space-y-3 p-5" x-show="!pricesLoading" x-cloak>
                                <template x-if="metalsError || forexError">
                                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900">
                                        <span x-show="metalsError">{{ __('app.zakat.metals_prices_error') }}</span>
                                        <span x-show="metalsError && forexError" class="mx-1">·</span>
                                        <span x-show="forexError">{{ __('app.zakat.forex_prices_error') }}</span>
                                    </p>
                                </template>

                                {{-- 24 Ayar Altın --}}
                                <div class="rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50/80 to-white p-3.5 transition-colors duration-300" :class="flashClass('gold_24_per_gram')">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z"/></svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ __('app.zakat.gold_24') }}</p>
                                            <p class="mt-0.5 text-lg font-bold text-slate-900" x-text="prices.gold_24_per_gram ? money(prices.gold_24_per_gram) : '—'"></p>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold" :class="trendClass('gold_24')">
                                                <template x-if="trend('gold_24').direction === 'up'">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4l8 8h-5v8H9v-8H4l8-8z"/></svg>
                                                </template>
                                                <template x-if="trend('gold_24').direction === 'down'">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20l-8-8h5V4h6v8h5l-8 8z"/></svg>
                                                </template>
                                                <span x-text="trend('gold_24').direction !== 'flat' ? number(trend('gold_24').change) + ' (' + percent(trend('gold_24').rate) + ')' : '—'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gümüş --}}
                                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5 transition-colors duration-300" :class="flashClass('silver_per_gram')">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="9" opacity="0.3"/><circle cx="12" cy="12" r="5"/></svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('app.zakat.silver') }}</p>
                                            <p class="mt-0.5 text-base font-bold text-slate-900" x-text="prices.silver_per_gram ? money(prices.silver_per_gram) : '—'"></p>
                                            <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold" :class="trendClass('silver')">
                                                <template x-if="trend('silver').direction === 'up'">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4l8 8h-5v8H9v-8H4l8-8z"/></svg>
                                                </template>
                                                <template x-if="trend('silver').direction === 'down'">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 20l-8-8h5V4h6v8h5l-8 8z"/></svg>
                                                </template>
                                                <span x-text="trend('silver').direction !== 'flat' ? number(trend('silver').change) + ' (' + percent(trend('silver').rate) + ')' : '—'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Adetli altın --}}
                                <div class="rounded-xl border border-amber-50 bg-white p-3.5">
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ __('app.zakat.coins_live') }}</p>
                                    <div class="space-y-2">
                                        <template x-for="coin in coinRows" :key="coin.key">
                                            <div class="flex items-center justify-between gap-2 text-xs transition-colors duration-300" :class="flashClass(coin.key)">
                                                <span class="truncate text-slate-600" x-text="coin.label"></span>
                                                <div class="flex shrink-0 items-center gap-1.5">
                                                    <span class="font-semibold text-slate-900" x-text="prices[coin.key] ? money(prices[coin.key]) : '—'"></span>
                                                    <span :class="trendClass(coin.trendKey)">
                                                        <template x-if="trend(coin.trendKey).direction === 'up'">▲</template>
                                                        <template x-if="trend(coin.trendKey).direction === 'down'">▼</template>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Dövizler --}}
                                <div class="rounded-xl border border-slate-100 bg-white p-3.5">
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('app.zakat.forex_section') }}</p>
                                    <div class="space-y-2">
                                        <template x-for="fx in forexRows" :key="fx.code">
                                            <div class="flex items-center justify-between gap-2 rounded-lg px-1 py-0.5 text-xs transition-colors duration-300" :class="flashClass(fx.key)">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <img :src="'https://flagcdn.com/w20/' + fx.flag + '.png'" :alt="fx.code" class="h-3.5 w-5 shrink-0 rounded-sm object-cover shadow-sm" loading="lazy" width="20" height="14">
                                                    <span class="font-semibold text-slate-700" x-text="fx.code"></span>
                                                </div>
                                                <div class="flex shrink-0 items-center gap-1.5">
                                                    <span class="font-semibold text-slate-900" x-text="prices[fx.key] ? money(prices[fx.key]) : '—'"></span>
                                                    <span class="font-bold" :class="trendClass(fx.trendKey)">
                                                        <template x-if="trend(fx.trendKey).direction === 'up'">▲</template>
                                                        <template x-if="trend(fx.trendKey).direction === 'down'">▼</template>
                                                        <template x-if="trend(fx.trendKey).direction === 'flat'">—</template>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Nisap --}}
                                <div class="rounded-xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-white p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        </span>
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700">{{ __('app.zakat.nisap_label') }}</p>
                                    </div>
                                    <p class="mt-2 text-xl font-extrabold text-cyan-900" x-text="prices.nisap_threshold_try ? money(prices.nisap_threshold_try) : '—'"></p>
                                    <p class="mt-0.5 text-[11px] text-cyan-800/80">{{ $settings['nisap_grams'] }} gr ({{ $settings['nisap_karat'] }} ayar)</p>
                                </div>

                                <p class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-[11px] leading-relaxed text-slate-500">
                                    <span class="font-medium text-slate-600">{{ __('app.zakat.source_label') }}:</span>
                                    {{ __('app.zakat.source_genelpara') }}
                                    <br>
                                    {{ __('app.zakat.last_update') }}:
                                    <span x-text="formatFetchedAt(sources?.genelpara?.fetched_at)"></span>
                                </p>

                                <p
                                    x-show="prices.metals_via_client || prices.forex_via_client"
                                    x-cloak
                                    class="rounded-xl border border-cyan-100 bg-cyan-50/70 px-3 py-2 text-[11px] leading-relaxed text-cyan-900"
                                >
                                    {{ __('app.zakat.data_via_client') }}
                                </p>
                            </div>
                        </div>
                    </aside>

                    {{-- Hesaplama formu --}}
                    <div class="min-w-0 space-y-6 lg:col-span-8">
                        <form @input="saveForm()" class="space-y-6 print:hidden">
                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <div class="flex items-center gap-3 border-b border-amber-100 pb-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z"/></svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.gold_section') }}</h2>
                                </div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    @foreach ([24, 22, 18, 14] as $karat)
                                        <label class="block min-w-0">
                                            <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.gold_karat', ['karat' => $karat]) }}</span>
                                            <div class="relative mt-1.5">
                                                <input type="number" min="0" step="0.01" x-model="form.gold{{ $karat }}" class="w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 text-slate-900 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                                <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-slate-400">{{ __('app.zakat.gram') }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                    <label class="block min-w-0 sm:col-span-2">
                                        <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.silver') }}</span>
                                        <div class="relative mt-1.5">
                                            <input type="number" min="0" step="0.01" x-model="form.silver" class="w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 text-slate-900 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                            <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-slate-400">{{ __('app.zakat.gram') }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <div class="flex items-center gap-3 border-b border-amber-100 pb-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v10M8 10h8M8 14h8"/></svg>
                                    </span>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.coins_section') }}</h2>
                                        <p class="text-xs text-slate-500">{{ __('app.zakat.coins_hint') }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    @foreach ([
                                        ['coinQuarter', 'coin_quarter', 'coin_quarter_try'],
                                        ['coinHalf', 'coin_half', 'coin_half_try'],
                                        ['coinFull', 'coin_full', 'coin_full_try'],
                                        ['coinAta', 'coin_ata', 'coin_ata_try'],
                                        ['coinCmr', 'coin_cmr', 'coin_cmr_try'],
                                    ] as [$field, $label, $priceKey])
                                        <label class="block min-w-0">
                                            <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.' . $label) }}</span>
                                            <p class="mt-0.5 text-[11px] text-cyan-700" x-show="prices.{{ $priceKey }}" x-cloak>
                                                {{ __('app.zakat.unit_price') }}: <span class="font-semibold" x-text="prices.{{ $priceKey }} ? money(prices.{{ $priceKey }}) : '—'"></span>
                                            </p>
                                            <div class="relative mt-1.5">
                                                <input type="number" min="0" step="1" x-model="form.{{ $field }}" class="w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 text-slate-900 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                                <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-slate-400">{{ __('app.zakat.piece') }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <div class="flex items-center gap-3 border-b border-cyan-100 pb-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.172-.879-1.172-2.303 0-3.182.553-.44 1.278-.659 2.003-.659.725 0 1.45.22 2.003.659"/></svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.money_section') }}</h2>
                                </div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <label class="block min-w-0">
                                        <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.cash') }}</span>
                                        <input type="number" min="0" step="0.01" x-model="form.cash" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                    </label>
                                    <label class="block min-w-0">
                                        <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.bank') }}</span>
                                        <input type="number" min="0" step="0.01" x-model="form.bank" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                    </label>
                                    @foreach ([
                                        ['usd', 'USD', 'usd_try', 'us'],
                                        ['eur', 'EUR', 'eur_try', 'eu'],
                                        ['gbp', 'GBP', 'gbp_try', 'gb'],
                                        ['chf', 'CHF', 'chf_try', 'ch'],
                                        ['sar', 'SAR', 'sar_try', 'sa'],
                                        ['aed', 'AED', 'aed_try', 'ae'],
                                    ] as [$field, $code, $priceKey, $flag])
                                        <label class="block min-w-0">
                                            <div class="flex items-center gap-2">
                                                <img src="https://flagcdn.com/w20/{{ $flag }}.png" alt="{{ $code }}" class="h-3.5 w-5 rounded-sm object-cover shadow-sm" loading="lazy" width="20" height="14">
                                                <span class="text-sm font-medium text-slate-700">{{ $code }}</span>
                                            </div>
                                            <p class="mt-0.5 text-[11px] text-cyan-700" x-show="prices.{{ $priceKey }}" x-cloak>
                                                {{ __('app.zakat.live_rate') }}: <span class="font-semibold" x-text="prices.{{ $priceKey }} ? money(prices.{{ $priceKey }}) : '—'"></span>
                                            </p>
                                            <input type="number" min="0" step="0.01" x-model="form.{{ $field }}" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.other_section') }}</h2>
                                </div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <label class="block min-w-0">
                                        <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.trade') }}</span>
                                        <input type="number" min="0" step="0.01" x-model="form.trade" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                    </label>
                                    <label class="block min-w-0">
                                        <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.receivables') }}</span>
                                        <input type="number" min="0" step="0.01" x-model="form.receivables" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                                <div class="flex items-center gap-3 border-b border-rose-100 pb-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.debts_section') }}</h2>
                                </div>
                                <label class="mt-4 block min-w-0">
                                    <span class="text-sm font-medium text-slate-700">{{ __('app.zakat.debts') }}</span>
                                    <input type="number" min="0" step="0.01" x-model="form.debts" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" placeholder="0">
                                </label>
                            </div>

                            <label class="flex items-start gap-3 rounded-[18px] border border-slate-200 bg-slate-50/80 p-4">
                                <input type="checkbox" x-model="form.hawl" class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span class="text-sm leading-relaxed text-slate-700">{{ __('app.zakat.hawl_label') }}</span>
                            </label>
                        </form>

                        {{-- Özet (yazdırma hedefi) --}}
                        <div id="zakat-print-summary" class="rounded-[18px] border border-cyan-100 bg-gradient-to-br from-cyan-50/80 to-white p-6 shadow-sm md:p-8">
                            <div class="mb-6 hidden border-b border-slate-200 pb-4 print:block">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.site.default_title') }}</p>
                                <h2 class="mt-1 text-xl font-bold text-slate-900">{{ __('app.zakat.print_title') }}</h2>
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ __('app.zakat.last_update') }}:
                                    <span x-text="formatFetchedAt(sources?.genelpara?.fetched_at)"></span>
                                </p>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.summary_title') }}</h2>

                            <div class="mt-4 print:hidden" x-show="prices.nisap_threshold_try > 0" x-cloak>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ __('app.zakat.nisap_progress') }}</span>
                                    <span class="font-semibold text-cyan-700" x-text="nisapProgress + '%'"></span>
                                </div>
                                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-cyan-600 transition-all duration-500" :style="'width:' + nisapProgress + '%'"></div>
                                </div>
                            </div>

                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                                    <dt class="text-slate-600">{{ __('app.zakat.total_assets') }}</dt>
                                    <dd class="font-bold text-slate-900" x-text="money(result.totalAssets)"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                                    <dt class="text-slate-600">{{ __('app.zakat.net_wealth') }}</dt>
                                    <dd class="font-bold text-slate-900" x-text="money(result.netWealth)"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-600">{{ __('app.zakat.zakat_amount') }}</dt>
                                    <dd class="text-2xl font-extrabold text-cyan-700" x-text="money(result.zakatAmount)"></dd>
                                </div>
                            </dl>
                            <p class="mt-3 text-xs text-slate-500" x-show="!result.meetsNisap && result.totalAssets > 0" x-cloak>{{ __('app.zakat.below_nisap') }}</p>

                            <div class="mt-5" x-show="breakdownRows.length" x-cloak>
                                <h3 class="text-sm font-bold text-slate-800">{{ __('app.zakat.breakdown_title') }}</h3>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <template x-for="row in breakdownRows" :key="row.key">
                                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-2">
                                            <dt class="text-slate-600" x-text="row.label"></dt>
                                            <dd class="font-semibold text-slate-900" x-text="money(row.value)"></dd>
                                        </div>
                                    </template>
                                </dl>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center print:hidden">
                                <a :href="donateLink" class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-full px-8 py-3 text-sm font-semibold shadow-md sm:w-auto">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    {{ __('app.zakat.donate') }}
                                </a>
                                <a href="{{ route('activities.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-cyan-200 bg-white px-8 py-3 text-sm font-semibold text-cyan-800 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50 sm:w-auto">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5M10.5 13.5 21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    {{ __('app.zakat.activities') }}
                                </a>
                                <button type="button" @click="resetForm()" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:w-auto">
                                    {{ __('app.zakat.reset') }}
                                </button>
                                <button type="button" @click="printSummary()" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto print:hidden">
                                    {{ __('app.zakat.print') }}
                                </button>
                            </div>

                            <div class="mt-6 rounded-[18px] border border-amber-100 bg-amber-50/50 p-5 text-sm leading-relaxed text-amber-950">
                                <p class="font-semibold">{{ __('app.zakat.legal_title') }}</p>
                                <p class="mt-2">{{ $settings['legal_text'] ?? __('app.zakat.legal_text') }}</p>
                            </div>
                        </div>

                        @if (count($faqItems))
                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8 print:hidden">
                                <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.faq_title') }}</h2>
                                <div class="mt-4 space-y-3">
                                    @foreach ($faqItems as $index => $item)
                                        <div class="overflow-hidden rounded-2xl border border-cyan-100 bg-slate-50/50">
                                            <button type="button" @click="toggleFaq({{ $index }})" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start text-sm font-semibold text-slate-900">
                                                <span>{{ $item['question'] }}</span>
                                                <span class="text-cyan-600" x-text="faqOpen === {{ $index }} ? '−' : '+'"></span>
                                            </button>
                                            <div x-show="faqOpen === {{ $index }}" x-cloak class="border-t border-cyan-100 px-5 py-4 text-sm leading-relaxed text-slate-600">
                                                {{ $item['answer'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($featuredActivities->isNotEmpty())
                            <div class="rounded-[18px] border border-slate-100 bg-white p-6 shadow-sm md:p-8 print:hidden">
                                <h2 class="text-lg font-bold text-slate-900">{{ __('app.zakat.activities_title') }}</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ __('app.zakat.activities_desc') }}</p>
                                <div class="mt-5 grid gap-4 md:grid-cols-3">
                                    @foreach ($featuredActivities as $activity)
                                        <a href="{{ route('activities.show', $activity->slug) }}" class="group rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-cyan-50/40 p-4 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-cyan-800">{{ $activity->getLocalized('title') }}</p>
                                            <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-slate-600">{{ $activity->getLocalized('description') }}</p>
                                            <span class="mt-3 inline-flex text-xs font-semibold text-cyan-700">{{ __('app.zakat.donate') }} →</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Mobil sticky özet (x-data kapsamı içinde) --}}
                <div
                    x-show="showStickySummary"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-full opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    class="fixed inset-x-0 bottom-0 z-40 w-full max-w-full border-t border-cyan-200 bg-white/95 px-4 py-3 shadow-[0_-8px_30px_rgba(15,23,42,0.08)] backdrop-blur md:hidden print:hidden"
                >
                    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('app.zakat.sticky_estimate') }}</p>
                            <p class="truncate text-lg font-extrabold text-cyan-700" x-text="money(result.zakatAmount)"></p>
                        </div>
                        <a :href="donateLink" class="btn-primary inline-flex shrink-0 items-center rounded-full px-5 py-2.5 text-sm font-semibold shadow-md">
                            {{ __('app.zakat.donate') }}
                        </a>
                    </div>
                </div>

                <div class="h-20 md:hidden" x-show="showStickySummary" x-cloak aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <style>
        .zakat-flash-up { animation: zakatFlashUp 0.6s ease; }
        .zakat-flash-down { animation: zakatFlashDown 0.6s ease; }
        @keyframes zakatFlashUp {
            0% { background-color: rgba(16, 185, 129, 0.25); }
            100% { background-color: transparent; }
        }
        @keyframes zakatFlashDown {
            0% { background-color: rgba(244, 63, 94, 0.2); }
            100% { background-color: transparent; }
        }
        @media print {
            @page { margin: 1.2cm; size: A4 portrait; }
            body { background: #fff !important; }
            nav, footer, #page-transition { display: none !important; }
            main > section.relative.isolate { display: none !important; }
            body * { visibility: hidden; }
            #zakat-print-summary, #zakat-print-summary * { visibility: visible; }
            #zakat-print-summary {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                border: none !important;
                box-shadow: none !important;
                background: #fff !important;
                padding: 0 !important;
            }
            .print\\:hidden { display: none !important; visibility: hidden !important; }
        }
    </style>
</x-layouts.app>
