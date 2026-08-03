@props([
    'siteSettings' => null,
])

@php
    $siteSettings = $siteSettings ?? \App\Models\Setting::current();
    $resolved = \App\Support\GoogleMapsEmbed::resolve(
        $siteSettings->google_maps_embed_url ?? null,
        $siteSettings->address ?? null
    );
    $embedUrl = $resolved['embed'];
    $externalUrl = $resolved['external'];
    $needsExternal = $resolved['needs_external'];
    $mapTitle = filled($siteSettings->address ?? null)
        ? $siteSettings->address
        : ($siteSettings->site_title ?? __('app.footer.our_location'));
@endphp

@if (filled($embedUrl) || ($needsExternal && filled($externalUrl)))
<section
    class="relative w-full overflow-hidden"
    aria-labelledby="site-map-heading"
>
    <div class="border-t border-slate-200 bg-gradient-to-b from-slate-100 via-slate-50 to-cyan-50/40">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-end sm:justify-between sm:px-6 md:py-10">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-800">
                    {{ __('app.footer.location_badge') }}
                </p>
                <h2
                    id="site-map-heading"
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl"
                >
                    {{ __('app.footer.location_title') }}
                </h2>
                <p class="mt-2 text-base leading-relaxed text-slate-700 md:text-lg">
                    {{ filled($siteSettings->address ?? null) ? $siteSettings->address : __('app.footer.location_subtitle') }}
                </p>
            </div>

            @if (filled($externalUrl))
                <a
                    href="{{ $externalUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex shrink-0 items-center gap-2 self-start rounded-full border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-900 shadow-sm transition hover:border-slate-500 hover:bg-white sm:self-auto"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                    </svg>
                    {{ __('app.footer.open_map') }}
                </a>
            @endif
        </div>
    </div>

    <div class="relative w-full bg-slate-100">
        @if (filled($embedUrl))
            <iframe
                src="{{ $embedUrl }}"
                width="100%"
                height="450"
                class="block w-full border-0"
                style="border:0; height:450px; width:100%;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
                title="{{ $mapTitle }}"
            ></iframe>
        @else
            <div class="flex h-[450px] w-full items-center justify-center px-6 text-center">
                <div class="max-w-md space-y-3">
                    <p class="text-sm leading-relaxed text-slate-600">
                        {{ __('app.footer.map_restriction') }}
                    </p>
                    <a
                        href="{{ $externalUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-slate-800"
                    >
                        {{ __('app.footer.open_map') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>
@endif
