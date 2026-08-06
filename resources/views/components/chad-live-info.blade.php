@php
    $chadLiveConfig = [
        'locale' => app()->getLocale(),
        'labels' => [
            'weather' => __('app.chad_live.weather'),
            'local_time' => __('app.chad_live.local_time'),
            'hijri' => __('app.chad_live.hijri'),
            'next_prayer' => __('app.chad_live.next_prayer'),
            'weather_error' => __('app.chad_live.weather_error'),
        ],
        'prayerNames' => __('app.chad_live.prayers'),
        'hijriMonths' => __('app.chad_live.hijri_months'),
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
    class="relative z-10 mx-auto -mt-1 mb-8 max-w-7xl px-4 md:mb-10 md:px-6"
    aria-labelledby="chad-live-heading"
>
    <h2 id="chad-live-heading" class="sr-only">{{ __('app.chad_live.title') }}</h2>

    <div
        x-data="chadLiveInfo(@js($chadLiveConfig))"
        x-init="init()"
        class="chad-live-enter chad-live-strip overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-900 via-cyan-800 to-cyan-700 px-4 py-3.5 text-white shadow-[0_12px_28px_rgba(43,50,69,0.28)] md:px-5 md:py-4"
        :class="{ 'chad-live-enter--visible': ready || !loading }"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-5">
            <div class="flex shrink-0 items-center gap-2.5 lg:min-w-[11.5rem] lg:border-r lg:border-white/15 lg:pr-5">
                <span class="chad-live-pulse chad-live-pulse--light" aria-hidden="true"></span>
                <span class="inline-flex h-5 w-7 overflow-hidden rounded-sm border border-white/25 bg-white shadow-sm" aria-hidden="true">
                    <svg class="h-full w-full" viewBox="0 0 36 24">
                        <rect width="36" height="24" fill="#E30A17"/>
                        <circle cx="13.6" cy="12" r="5" fill="#ffffff"/>
                        <circle cx="15.3" cy="12" r="4" fill="#E30A17"/>
                        <polygon fill="#ffffff" points="21.6,9.6 22.16,11.23 23.88,11.26 22.5,12.29 23.01,13.94 21.6,12.95 20.19,13.94 20.7,12.29 19.32,11.26 21.04,11.23"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-100/90">{{ __('app.chad_live.badge') }}</p>
                    <p class="text-sm font-semibold text-white">{{ __('app.chad_live.live_label') }}</p>
                </div>
            </div>

            {{-- Skeleton --}}
            <div x-show="loading" x-cloak class="grid flex-1 grid-cols-2 gap-2 sm:grid-cols-4" aria-hidden="true">
                @foreach (range(1, 4) as $item)
                    <div class="rounded-xl bg-white/10 px-3 py-2.5">
                        <div class="h-2 w-14 animate-pulse rounded bg-white/25"></div>
                        <div class="mt-2 h-4 w-20 animate-pulse rounded bg-white/35"></div>
                    </div>
                @endforeach
            </div>

            {{-- Metrikler --}}
            <div x-show="!loading" x-cloak class="grid flex-1 grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-0">
                <article class="chad-live-stat rounded-xl bg-white/10 px-3 py-2.5 sm:rounded-none sm:bg-transparent sm:px-3 sm:py-0 md:px-4">
                    <div class="flex items-start gap-2.5">
                        <span class="chad-live-icon chad-live-icon--weather mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-100/80">{{ __('app.chad_live.weather') }}</p>
                            <p class="chad-live-value mt-0.5 truncate text-base font-bold tabular-nums tracking-tight text-white md:text-lg" x-text="weatherDisplay"></p>
                        </div>
                    </div>
                </article>

                <article class="chad-live-stat rounded-xl bg-white/10 px-3 py-2.5 sm:rounded-none sm:border-l sm:border-white/15 sm:bg-transparent sm:px-3 sm:py-0 md:px-4">
                    <div class="flex items-start gap-2.5">
                        <span class="chad-live-icon chad-live-icon--time mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-100/80">{{ __('app.chad_live.local_time') }}</p>
                            <p
                                class="chad-live-value mt-0.5 truncate text-base font-bold tabular-nums tracking-tight text-white md:text-lg"
                                :class="{ 'chad-live-value--tick': timeTick }"
                                x-text="localTime"
                                aria-live="polite"
                            ></p>
                        </div>
                    </div>
                </article>

                <article class="chad-live-stat rounded-xl bg-white/10 px-3 py-2.5 sm:rounded-none sm:border-l sm:border-white/15 sm:bg-transparent sm:px-3 sm:py-0 md:px-4">
                    <div class="flex items-start gap-2.5">
                        <span class="chad-live-icon chad-live-icon--hijri mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M21 14.5A8.5 8.5 0 1 1 12 6v2.2"/>
                                <path d="M12 3v4M9.5 5.5 12 8l2.5-2.5"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-100/80">{{ __('app.chad_live.hijri') }}</p>
                            <p class="chad-live-value mt-0.5 text-sm font-bold leading-snug tracking-tight text-white md:text-base" x-text="hijri"></p>
                            <p class="mt-0.5 truncate text-sm font-semibold leading-snug tracking-tight text-white/95 md:text-[15px]" x-text="gregorian" x-show="gregorian && gregorian !== '--'"></p>
                        </div>
                    </div>
                </article>

                <article class="chad-live-stat rounded-xl bg-white/10 px-3 py-2.5 sm:rounded-none sm:border-l sm:border-white/15 sm:bg-transparent sm:px-3 sm:py-0 md:px-4">
                    <div class="flex items-start gap-2.5">
                        <span class="chad-live-icon chad-live-icon--prayer mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M4 20h16"/>
                                <path d="M6 20V11l6-4 6 4v9"/>
                                <path d="M9 20v-5h6v5"/>
                                <path d="M12 7V4"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-100/80">{{ __('app.chad_live.next_prayer') }}</p>
                            <p class="chad-live-value mt-0.5 truncate text-sm font-bold leading-snug tracking-tight text-white md:text-base">
                                <span x-text="prayerName"></span>
                                <span class="font-semibold text-cyan-200/70" x-show="prayerTime !== '--'">·</span>
                                <span class="tabular-nums" x-text="prayerTime"></span>
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
