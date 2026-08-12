@props([
    'slides' => [],
])

@php
    use App\Support\HeroImageSpec;

    $hasSlides = is_array($slides) && count($slides) > 0;
    $first = $hasSlides ? $slides[0] : null;
    $firstImage = $first['image'] ?? null;
    $firstMobile = $first['image_mobile'] ?? $firstImage;
    $firstTablet = $first['image_tablet'] ?? $firstImage;
    $firstDesktopSrcset = $first['desktop_srcset'] ?? '';

    $mobileW = HeroImageSpec::width('mobile');
    $mobileH = HeroImageSpec::height('mobile');
@endphp

{{--
  Tek çerçeve: SSR ilk slayt + Alpine aynı kutuda.
  Ayrı fallback kaldırıldığı için CLS (çift hero / yükseklik zıplaması) azalır.
--}}
<style>
    [x-cloak] { display: none !important; }
    .home-hero {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 100vw;
        overflow-x: hidden;
    }
    .home-hero-frame {
        position: relative;
        width: 100%;
        overflow: hidden;
        background-color: #f1f5f9;
        /* Mobil: oran rezerve + uzun dikey afişin alt içeriğe baskı yapmasını sınırla */
        height: min(70svh, calc(100vw * {{ $mobileH }} / {{ $mobileW }}));
    }
    @media (min-width: 768px) {
        .home-hero-frame {
            height: auto;
            aspect-ratio: {{ HeroImageSpec::width('tablet') }} / {{ HeroImageSpec::height('tablet') }};
        }
    }
    @media (min-width: 1024px) {
        .home-hero-frame {
            height: auto;
            aspect-ratio: {{ HeroImageSpec::width('desktop') }} / {{ HeroImageSpec::height('desktop') }};
        }
    }
    .home-hero-img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
    }
</style>

<section
    class="home-hero"
    aria-label="{{ __('app.home.hero_carousel_aria') }}"
    translate="no"
    @if($hasSlides)
        x-data="homeHeroSlider({ slides: @js($slides) })"
        @touchstart.passive="startTouch($event)"
        @touchend.passive="endTouch($event)"
    @endif
>
    @unless($hasSlides)
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            <x-empty-state
                :title="__('app.home.hero_empty_title')"
                :description="__('app.home.hero_empty_desc')"
            />
        </div>
    @else
        <div
            class="home-hero-frame"
            role="region"
            aria-roledescription="carousel"
        >
            <picture>
                @if(filled($firstMobile))
                    <source
                        media="(max-width: 767px)"
                        type="image/webp"
                        srcset="{{ $firstMobile }}"
                        @if($hasSlides) :srcset="current.image_mobile" @endif
                    >
                @endif
                @if(filled($firstTablet))
                    <source
                        media="(max-width: 1023px)"
                        type="image/webp"
                        srcset="{{ $firstTablet }}"
                        @if($hasSlides) :srcset="current.image_tablet" @endif
                    >
                @endif
                @if(filled($firstDesktopSrcset))
                    <source
                        type="image/webp"
                        srcset="{{ $firstDesktopSrcset }}"
                        sizes="100vw"
                        @if($hasSlides) :srcset="current.desktop_srcset" @endif
                    >
                @endif
                <img
                    src="{{ $firstImage }}"
                    @if($hasSlides)
                        :src="current.image"
                        :alt="'{{ __('app.home.hero_slide_alt') }} ' + (idx + 1)"
                    @endif
                    alt="{{ __('app.home.hero_alt') }}"
                    class="home-hero-img"
                    width="{{ $mobileW }}"
                    height="{{ $mobileH }}"
                    sizes="100vw"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
            </picture>

            <div
                class="pointer-events-none absolute inset-y-0 left-0 right-0 z-20 flex items-center justify-between px-1 sm:px-2 md:px-3"
                x-show="total > 1"
                x-cloak
            >
                <button
                    type="button"
                    class="pointer-events-auto flex h-10 w-10 items-center justify-center text-white drop-shadow-[0_1px_3px_rgba(0,0,0,0.55)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 md:h-12 md:w-12"
                    @click="prev(); restartAuto()"
                    aria-label="{{ __('app.home.hero_prev') }}"
                >
                    <svg class="h-7 w-7 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="pointer-events-auto flex h-10 w-10 items-center justify-center text-white drop-shadow-[0_1px_3px_rgba(0,0,0,0.55)] transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 md:h-12 md:w-12"
                    @click="next(); restartAuto()"
                    aria-label="{{ __('app.home.hero_next') }}"
                >
                    <svg class="h-7 w-7 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div
                class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 md:bottom-4"
                x-show="total > 1"
                x-cloak
                role="tablist"
                aria-label="{{ __('app.home.hero_dots_aria') }}"
            >
                <template x-for="(slide, i) in slides" :key="'hero-dot-' + i">
                    <button
                        type="button"
                        class="h-2 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                        :class="idx === i ? 'w-6 bg-white shadow' : 'w-2 bg-white/60 hover:bg-white/85'"
                        @click="go(i); restartAuto()"
                        :aria-label="'{{ __('app.home.hero_slide_n') }} ' + (i + 1)"
                        :aria-current="idx === i ? 'true' : 'false'"
                    ></button>
                </template>
            </div>
        </div>
    @endunless
</section>
