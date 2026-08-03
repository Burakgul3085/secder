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
    class="mx-auto mb-10 mt-8 max-w-7xl px-4 md:px-6"
    aria-labelledby="chad-live-heading"
>
    <div
        x-data="chadLiveInfo(@js($chadLiveConfig))"
        x-init="init()"
        class="chad-live-enter overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-b from-slate-100 via-slate-50 to-cyan-50/50 px-5 py-8 shadow-sm md:px-8 md:py-10"
        :class="{ 'chad-live-enter--visible': ready || !loading }"
    >
        <div class="mx-auto max-w-3xl text-center">
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-cyan-800">
                <span class="chad-live-pulse" aria-hidden="true"></span>
                <span class="inline-flex h-5 w-7 overflow-hidden rounded-sm border border-slate-200 bg-white" aria-hidden="true">
                    <svg class="h-full w-full" viewBox="0 0 36 24">
                        <rect width="36" height="24" fill="#E30A17"/>
                        <circle cx="13.6" cy="12" r="5" fill="#ffffff"/>
                        <circle cx="15.3" cy="12" r="4" fill="#E30A17"/>
                        <polygon fill="#ffffff" points="21.6,9.6 22.16,11.23 23.88,11.26 22.5,12.29 23.01,13.94 21.6,12.95 20.19,13.94 20.7,12.29 19.32,11.26 21.04,11.23"/>
                    </svg>
                </span>
                {{ __('app.chad_live.badge') }}
            </p>
            <h2
                id="chad-live-heading"
                class="mt-2 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl"
            >
                {{ __('app.chad_live.title') }}
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-base leading-relaxed text-slate-700 md:text-lg">
                {{ __('app.chad_live.subtitle') }}
            </p>
        </div>

        {{-- Skeleton --}}
        <div x-show="loading" x-cloak class="mt-8" aria-hidden="true">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (range(1, 4) as $item)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mx-auto h-10 w-10 animate-pulse rounded-full bg-slate-200"></div>
                        <div class="mx-auto mt-4 h-2.5 w-20 animate-pulse rounded bg-slate-200"></div>
                        <div class="mx-auto mt-3 h-6 w-24 animate-pulse rounded bg-slate-300"></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Metrik kartları --}}
        <div x-show="!loading" x-cloak class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="chad-live-stat rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <span class="chad-live-icon chad-live-icon--weather mx-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-cyan-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                    </svg>
                </span>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.chad_live.weather') }}</p>
                <p class="chad-live-value mt-1.5 text-2xl font-bold tabular-nums tracking-tight text-slate-900" x-text="weatherDisplay"></p>
            </article>

            <article class="chad-live-stat rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <span class="chad-live-icon chad-live-icon--time mx-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-cyan-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </span>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.chad_live.local_time') }}</p>
                <p
                    class="chad-live-value mt-1.5 text-2xl font-bold tabular-nums tracking-tight text-slate-900"
                    :class="{ 'chad-live-value--tick': timeTick }"
                    x-text="localTime"
                    aria-live="polite"
                ></p>
            </article>

            <article class="chad-live-stat rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <span class="chad-live-icon chad-live-icon--hijri mx-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-cyan-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M21 14.5A8.5 8.5 0 1 1 12 6v2.2"/>
                        <path d="M12 3v4M9.5 5.5 12 8l2.5-2.5"/>
                    </svg>
                </span>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.chad_live.hijri') }}</p>
                <p class="chad-live-value mt-1.5 text-lg font-bold leading-snug tracking-tight text-slate-900 md:text-xl" x-text="hijri"></p>
            </article>

            <article class="chad-live-stat rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                <span class="chad-live-icon chad-live-icon--prayer mx-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-cyan-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 20h16"/>
                        <path d="M6 20V11l6-4 6 4v9"/>
                        <path d="M9 20v-5h6v5"/>
                        <path d="M12 7V4"/>
                    </svg>
                </span>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.chad_live.next_prayer') }}</p>
                <p class="chad-live-value mt-1.5 text-lg font-bold leading-snug tracking-tight text-slate-900 md:text-xl">
                    <span x-text="prayerName"></span>
                    <span class="font-semibold text-slate-400" x-show="prayerTime !== '--'">·</span>
                    <span class="tabular-nums" x-text="prayerTime"></span>
                </p>
            </article>
        </div>
    </div>
</section>
