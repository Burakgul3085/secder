@props([
    'slides' => [],
])

@php
    $firstImage = $slides[0]['image'] ?? null;
@endphp

{{-- Statik fallback: Alpine.js başlamazsa (proxy, JS hata vb.) bu görünür --}}
@if($firstImage)
<div
    id="hero-static-fallback"
    class="relative w-full max-h-[760px] overflow-hidden bg-slate-100 aspect-[1/1] md:aspect-[2/1] lg:aspect-[4/1]"
>
    <picture>
        @if(filled($slides[0]['image_mobile'] ?? null))
            <source media="(max-width: 767px)" type="image/webp" srcset="{{ $slides[0]['image_mobile'] }}">
        @endif
        @if(filled($slides[0]['image_tablet'] ?? null))
            <source media="(max-width: 1023px)" type="image/webp" srcset="{{ $slides[0]['image_tablet'] }}">
        @endif
        @if(filled($slides[0]['desktop_srcset'] ?? null))
            <source type="image/webp" srcset="{{ $slides[0]['desktop_srcset'] }}" sizes="100vw">
        @endif
        <img
            src="{{ $firstImage }}"
            alt="Hero"
            class="absolute inset-0 h-full w-full object-cover object-center"
            loading="eager"
        >
    </picture>
</div>
@endif

<section
    class="relative z-10 w-full max-w-[100vw] overflow-x-hidden"
    aria-label="Ana tanıtım slider"
    translate="no"
    x-data="homeHeroSlider({ slides: @js($slides) })"
    x-init="$nextTick(function(){ var f=document.getElementById('hero-static-fallback'); if(f) f.remove(); })"
    @touchstart.passive="startTouch($event)"
    @touchend.passive="endTouch($event)"
>
    <template x-if="total === 0">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            <x-empty-state title="Hero alanı hazır" description="Yönetim panelinden «Hero Slider» bölümünden slayt ekleyin. Her slayta yalnızca bir görsel yükleyebilirsiniz; birden fazla slayt ekleyerek kaydırmalı alanı oluşturursunuz." />
        </div>
    </template>

    <div
        x-show="total > 0"
        x-cloak
        class="relative w-full overflow-hidden bg-slate-100"
        role="region"
        aria-roledescription="carousel"
    >
        {{-- Sabit oranlı geniş banner: telefon 1:1, tablet 2:1, masaüstü 4:1.
             Yönetim panelinde üretilen varyantlar bu oranlara birebir oturur. --}}
        <div class="relative w-full max-h-[760px] overflow-hidden aspect-[1/1] md:aspect-[2/1] lg:aspect-[4/1]">
            <picture>
                <source media="(max-width: 767px)" type="image/webp" :srcset="current.image_mobile">
                <source media="(max-width: 1023px)" type="image/webp" :srcset="current.image_tablet">
                <source type="image/webp" :srcset="current.desktop_srcset" sizes="100vw">
                <img
                    :src="current.image"
                    :alt="'Hero slayt ' + (idx + 1)"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                />
            </picture>

            {{-- Oklar (tam genişlik kenarları) --}}
            <template x-if="total > 1">
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 right-0 z-20 flex max-h-[100%] items-center justify-between px-1 sm:px-2 md:px-4"
                >
                    <button
                        type="button"
                        class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full border border-slate-200/90 bg-white text-slate-900 shadow-lg transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-400/70 md:h-12 md:w-12"
                        @click="prev()"
                        aria-label="Önceki slayt"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full border border-slate-200/90 bg-white text-slate-900 shadow-lg transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-400/70 md:h-12 md:w-12"
                        @click="next()"
                        aria-label="Sonraki slayt"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </template>

            {{-- Noktalar görselin alt kenarında yüzer; altta beyaz şerit oluşmaz --}}
            <div
                class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900/35 px-3 py-2 backdrop-blur-sm md:bottom-5"
                x-show="total > 1"
                role="tablist"
                aria-label="Slayt seçimi"
            >
                <template x-for="(slide, i) in slides" :key="'hero-dot-' + i">
                    <button
                        type="button"
                        class="h-2 rounded-full transition focus:outline-none focus:ring-2 focus:ring-white/70"
                        :class="idx === i ? 'w-6 bg-white' : 'w-2 bg-white/55 hover:bg-white/80'"
                        @click="go(i)"
                        :aria-label="'Slayt ' + (i + 1)"
                        :aria-current="idx === i ? 'true' : 'false'"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>
