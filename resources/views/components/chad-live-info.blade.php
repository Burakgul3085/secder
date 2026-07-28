@php
    $chadLiveConfig = [
        'locale' => app()->getLocale(),
        'labels' => [
            'weather' => __('app.chad_live.weather'),
            'local_time' => __('app.chad_live.local_time'),
            'hijri' => __('app.chad_live.hijri'),
            'next_prayer' => __('app.chad_live.next_prayer'),
            'weather_error' => __('app.chad_live.weather_error'),
            'donate' => __('app.chad_live.donate'),
        ],
        'prayerNames' => __('app.chad_live.prayers'),
        'hijriMonths' => __('app.chad_live.hijri_months'),
        'donateUrl' => route('donations'),
        // Şehir bilgisi config/live_info.php dosyasından yönetilir.
        'location' => [
            'latitude' => (float) config('live_info.latitude'),
            'longitude' => (float) config('live_info.longitude'),
            'timezone' => config('live_info.timezone'),
            'prayerMethod' => (int) config('live_info.prayer_method'),
            'cachePrefix' => config('live_info.cache_prefix'),
        ],
    ];
@endphp

<section
    class="mx-auto mb-10 mt-8 max-w-7xl px-4 md:px-6"
    aria-label="{{ __('app.chad_live.title') }}"
>
    <div
        x-data="chadLiveInfo(@js($chadLiveConfig))"
        x-init="init()"
        class="chad-live-card chad-live-enter overflow-hidden rounded-[18px] border border-slate-100 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.06)]"
        :class="{ 'chad-live-enter--visible': ready || !loading }"
    >
        <div class="border-b border-slate-100 bg-gradient-to-r from-cyan-50/80 via-white to-white px-6 py-6 md:px-8 md:py-7">
            <div class="flex flex-col items-center gap-3 text-center md:flex-row md:items-start md:gap-4 md:text-start">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-hidden="true">
                    <svg class="h-full w-full" viewBox="0 0 36 24" role="img" aria-label="{{ __('app.chad_live.badge') }}">
                        <rect width="36" height="24" fill="#E30A17"/>
                        <circle cx="13.6" cy="12" r="5" fill="#ffffff"/>
                        <circle cx="15.3" cy="12" r="4" fill="#E30A17"/>
                        <polygon fill="#ffffff" points="21.6,9.6 22.16,11.23 23.88,11.26 22.5,12.29 23.01,13.94 21.6,12.95 20.19,13.94 20.7,12.29 19.32,11.26 21.04,11.23"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-700">{{ __('app.chad_live.badge') }}</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 md:text-[1.65rem] md:leading-tight">
                        {{ __('app.chad_live.title') }}
                    </h2>
                    <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 md:mx-0 md:text-[15px]">
                        {{ __('app.chad_live.subtitle') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 md:px-8 md:py-7">
            {{-- Skeleton --}}
            <div x-show="loading" x-cloak class="space-y-5" aria-hidden="true">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (range(1, 4) as $item)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <div class="h-11 w-11 animate-pulse rounded-xl bg-slate-200/70"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-2.5 w-16 animate-pulse rounded bg-slate-200/70"></div>
                                <div class="h-5 w-20 animate-pulse rounded bg-slate-300/70"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mx-auto h-12 w-full max-w-sm animate-pulse rounded-full bg-slate-200/70"></div>
            </div>

            {{-- İçerik --}}
            <div x-show="!loading" x-cloak class="space-y-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-slate-200">
                    {{-- Hava --}}
                    <div class="chad-live-stat group rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition duration-300 hover:border-cyan-100 hover:bg-cyan-50/40 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:px-6 lg:first:ps-0 lg:last:pe-0">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm shadow-cyan-900/15 transition duration-300 group-hover:scale-105 group-hover:bg-cyan-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ __('app.chad_live.weather') }}</p>
                                <p class="mt-1 text-xl font-bold tabular-nums tracking-tight text-slate-900" x-text="weatherDisplay"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Saat --}}
                    <div class="chad-live-stat group rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition duration-300 hover:border-cyan-100 hover:bg-cyan-50/40 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:px-6">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm shadow-cyan-900/15 transition duration-300 group-hover:scale-105 group-hover:bg-cyan-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ __('app.chad_live.local_time') }}</p>
                                <p class="mt-1 text-xl font-bold tabular-nums tracking-tight text-slate-900" x-text="localTime" aria-live="polite"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Hicri --}}
                    <div class="chad-live-stat group rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition duration-300 hover:border-cyan-100 hover:bg-cyan-50/40 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:px-6">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm shadow-cyan-900/15 transition duration-300 group-hover:scale-105 group-hover:bg-cyan-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M21 14.5A8.5 8.5 0 1 1 12 6v2.2"/>
                                    <path d="M12 3v4M9.5 5.5 12 8l2.5-2.5"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ __('app.chad_live.hijri') }}</p>
                                <p class="mt-1 text-base font-bold leading-snug tracking-tight text-slate-900 md:text-lg" x-text="hijri"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Namaz --}}
                    <div class="chad-live-stat group rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition duration-300 hover:border-cyan-100 hover:bg-cyan-50/40 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:px-6 lg:last:pe-0">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm shadow-cyan-900/15 transition duration-300 group-hover:scale-105 group-hover:bg-cyan-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M4 20h16"/>
                                    <path d="M6 20V11l6-4 6 4v9"/>
                                    <path d="M9 20v-5h6v5"/>
                                    <path d="M12 7V4"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ __('app.chad_live.next_prayer') }}</p>
                                <p class="mt-1 text-base font-bold leading-snug tracking-tight text-slate-900 md:text-lg">
                                    <span x-text="prayerName"></span>
                                    <span class="font-semibold text-slate-400" x-show="prayerTime !== '--'">·</span>
                                    <span class="tabular-nums" x-text="prayerTime"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center border-t border-slate-100 pt-6">
                    <a
                        href="{{ route('donations') }}"
                        class="btn-primary inline-flex w-full min-w-[240px] items-center justify-center gap-2 rounded-full px-8 py-3 text-sm font-semibold shadow-md shadow-cyan-900/10 transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-cyan-900/15 sm:w-auto"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        {{ __('app.chad_live.donate') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
