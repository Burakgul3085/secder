@props([
    'slides' => [],
])

@php
    use App\Support\HeroImageSpec;

    $firstImage = $slides[0]['image'] ?? null;

    /*
     | Çerçeve oranı = admin yükleme ölçüleri (HeroImageSpec).
     | Sınıflar burada sabit yazılır (Tailwind tarasın).
     | Görsel aynı oranda yüklendiyse object-cover kırpmaz / boşluk bırakmaz.
     */
    $heroFrame = 'relative w-full overflow-hidden bg-slate-100 aspect-[1080/1350] md:aspect-[1536/1024] lg:aspect-[1920/480]';
    $heroImg = 'absolute inset-0 h-full w-full object-cover object-center';
@endphp

{{-- Statik fallback: Alpine.js başlamazsa (proxy, JS hata vb.) bu görünür --}}
@if($firstImage)
<div id="hero-static-fallback" class="{{ $heroFrame }}">
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
            alt="{{ __('app.home.hero_alt') }}"
            class="{{ $heroImg }}"
            loading="eager"
        >
    </picture>
</div>
@endif

<section
    class="relative z-10 w-full max-w-[100vw] overflow-x-hidden"
    aria-label="{{ __('app.home.hero_carousel_aria') }}"
    translate="no"
    x-data="homeHeroSlider({ slides: @js($slides) })"
    x-init="$nextTick(function(){ var f=document.getElementById('hero-static-fallback'); if(f) f.remove(); })"
    @touchstart.passive="startTouch($event)"
    @touchend.passive="endTouch($event)"
>
    <template x-if="total === 0">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            <x-empty-state
                :title="__('app.home.hero_empty_title')"
                :description="__('app.home.hero_empty_desc')"
            />
        </div>
    </template>

    <div
        x-show="total > 0"
        x-cloak
        class="relative w-full overflow-hidden"
        role="region"
        aria-roledescription="carousel"
    >
        <div class="{{ $heroFrame }}">
            <picture>
                <source media="(max-width: 767px)" type="image/webp" :srcset="current.image_mobile">
                <source media="(max-width: 1023px)" type="image/webp" :srcset="current.image_tablet">
                <source type="image/webp" :srcset="current.desktop_srcset" sizes="100vw">
                <img
                    :src="current.image"
                    :alt="'{{ __('app.home.hero_slide_alt') }} ' + (idx + 1)"
                    class="{{ $heroImg }}"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                />
            </picture>

            <template x-if="total > 1">
                <div class="pointer-events-none absolute inset-y-0 left-0 right-0 z-20 flex items-center justify-between px-1 sm:px-2 md:px-3">
                    <button
                        type="button"
                        class="pointer-events-auto flex h-10 w-10 items-center justify-center text-white drop-shadow-[0_1px_3px_rgba(0,0,0,0.55)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 md:h-12 md:w-12"
                        @click="prev()"
                        aria-label="{{ __('app.home.hero_prev') }}"
                    >
                        <svg class="h-7 w-7 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="pointer-events-auto flex h-10 w-10 items-center justify-center text-white drop-shadow-[0_1px_3px_rgba(0,0,0,0.55)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 md:h-12 md:w-12"
                        @click="next()"
                        aria-label="{{ __('app.home.hero_next') }}"
                    >
                        <svg class="h-7 w-7 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </template>

            <div
                class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 md:bottom-4"
                x-show="total > 1"
                role="tablist"
                aria-label="{{ __('app.home.hero_dots_aria') }}"
            >
                <template x-for="(slide, i) in slides" :key="'hero-dot-' + i">
                    <button
                        type="button"
                        class="h-2 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                        :class="idx === i ? 'w-6 bg-white shadow' : 'w-2 bg-white/60 hover:bg-white/85'"
                        @click="go(i)"
                        :aria-label="'{{ __('app.home.hero_slide_n') }} ' + (i + 1)"
                        :aria-current="idx === i ? 'true' : 'false'"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>
