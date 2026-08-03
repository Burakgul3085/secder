@php
    use Illuminate\Support\Carbon;

    $isTr = app()->getLocale() === 'tr';
    $kvkkText = $isTr
        ? (trim((string) ($siteSettings->kvkk_text ?? '')) ?: __('app.legal.kvkk_content'))
        : __('app.legal.kvkk_content');

    $carouselItems = $testimonials->map(function ($item) {
        $date = $item->approved_at ?? $item->created_at;

        return [
            'name' => $item->display_name,
            'city' => $item->city,
            'rating' => (int) $item->rating,
            'comment' => $item->comment,
            'date' => $date instanceof Carbon
                ? $date->locale(app()->getLocale())->translatedFormat('d F Y')
                : '',
            'is_volunteer' => (bool) $item->is_volunteer,
            'is_donor' => (bool) $item->is_donor,
        ];
    })->values()->all();

    $modalConfig = [
        'submitUrl' => route('testimonials.store'),
        'openOnLoad' => $errors->any(),
        'initialRating' => (int) old('rating', 0),
        'labels' => [
            'rating_aria' => __('app.home.testimonials_form_rating_aria'),
        ],
        'kvkkText' => $kvkkText,
    ];
@endphp

<section
    id="destekci-deneyimleri"
    class="pt-16 pb-12 md:pb-14"
    aria-labelledby="testimonials-heading"
    x-data="testimonialModal(@js($modalConfig))"
    @keydown.window="handleKeydown($event)"
>
    <div class="mx-auto max-w-7xl px-4 md:px-6">
        @if (session('testimonial_success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900" role="status">
                {{ __('app.home.testimonials_success') }}
            </div>
        @endif

        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-800">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                    {{ __('app.home.testimonials_badge') }}
                </span>
                <h2 id="testimonials-heading" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 md:text-5xl">
                    {{ __('app.home.testimonials_title') }}
                </h2>
                <p class="mt-3 text-base leading-relaxed text-slate-600 md:text-lg">
                    {{ __('app.home.testimonials_subtitle') }}
                </p>
            </div>

            @if ($testimonialStats['count'] > 0)
                <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50/80 to-white px-5 py-4 text-center shadow-sm lg:min-w-[220px]">
                    <div class="flex items-center justify-center gap-0.5 text-amber-400" aria-hidden="true">
                        @for ($star = 1; $star <= 5; $star++)
                            <svg class="h-4 w-4 {{ $star <= round($testimonialStats['average']) ? 'fill-current' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.31 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">
                        {{ number_format($testimonialStats['average'], 1, ',', '.') }} / 5
                    </p>
                    <p class="mt-1 text-sm font-medium text-slate-600">
                        {{ trans_choice('app.home.testimonials_count', $testimonialStats['count'], ['count' => $testimonialStats['count']]) }}
                    </p>
                </div>
            @endif
        </div>

        @if ($testimonials->isNotEmpty())
            @php
                $testimonialCount = $testimonials->count();
            @endphp

            <div
                class="relative mt-8"
                @if ($testimonialCount > 1)
                    x-data="testimonialsCarousel({ items: @js($carouselItems) })"
                    @mouseenter="pause()"
                    @mouseleave="resume()"
                    role="region"
                    aria-roledescription="carousel"
                    aria-label="{{ __('app.home.testimonials_title') }}"
                @endif
            >
                <div class="{{ $testimonialCount > 1 ? 'overflow-hidden' : '' }}">
                    @if ($testimonialCount === 1)
                        {{-- Tek yorum: her zaman yatay ortada --}}
                        <div style="display:flex;justify-content:center;width:100%;">
                            @php
                                $testimonial = $testimonials->first();
                                $date = $testimonial->approved_at ?? $testimonial->created_at;
                            @endphp
                            <div style="width:100%;max-width:36rem;padding-left:0.5rem;padding-right:0.5rem;">
                                <x-testimonial-card
                                    :name="$testimonial->display_name"
                                    :city="$testimonial->city"
                                    :rating="$testimonial->rating"
                                    :comment="$testimonial->comment"
                                    :date="$date->locale(app()->getLocale())->translatedFormat('d F Y')"
                                    :is-volunteer="$testimonial->is_volunteer"
                                    :is-donor="$testimonial->is_donor"
                                    class="testimonial-fade-up"
                                />
                            </div>
                        </div>
                    @else
                        <div class="flex transition-transform duration-500 ease-out" :style="trackStyle">
                            @foreach ($testimonials as $testimonial)
                                @php
                                    $date = $testimonial->approved_at ?? $testimonial->created_at;
                                @endphp
                                <div class="w-full flex-shrink-0 px-2 md:w-1/2 lg:w-1/3">
                                    <x-testimonial-card
                                        :name="$testimonial->display_name"
                                        :city="$testimonial->city"
                                        :rating="$testimonial->rating"
                                        :comment="$testimonial->comment"
                                        :date="$date->locale(app()->getLocale())->translatedFormat('d F Y')"
                                        :is-volunteer="$testimonial->is_volunteer"
                                        :is-donor="$testimonial->is_donor"
                                        class="testimonial-fade-up"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($testimonialCount > 1)
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700 disabled:opacity-40"
                            @click="prev()"
                            :disabled="!canPrev"
                            aria-label="{{ __('app.home.testimonials_prev') }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <div class="flex items-center gap-2">
                            <template x-for="index in Array.from({ length: maxIndex + 1 }, (_, i) => i)" :key="index">
                                <button
                                    type="button"
                                    class="h-2.5 rounded-full transition-all"
                                    :class="current === index ? 'w-6 bg-cyan-600' : 'w-2.5 bg-slate-300'"
                                    @click="goTo(index)"
                                    :aria-label="'{{ __('app.home.testimonials_go_to') }} ' + (index + 1)"
                                ></button>
                            </template>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700"
                            @click="next()"
                            aria-label="{{ __('app.home.testimonials_next') }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                @endif
            </div>

            @if ($testimonialStats['count'] > 0)
                <script type="application/ld+json">
                    {!! json_encode([
                        '@context' => 'https://schema.org',
                        '@type' => 'Organization',
                        'name' => $siteSettings->site_title ?? __('app.site.default_title'),
                        'url' => url('/'),
                        'aggregateRating' => [
                            '@type' => 'AggregateRating',
                            'ratingValue' => (string) $testimonialStats['average'],
                            'bestRating' => '5',
                            'worstRating' => '1',
                            'ratingCount' => (string) $testimonialStats['count'],
                        ],
                        'review' => $testimonials->take(10)->map(fn ($item) => [
                            '@type' => 'Review',
                            'author' => [
                                '@type' => 'Person',
                                'name' => $item->display_name,
                            ],
                            'reviewRating' => [
                                '@type' => 'Rating',
                                'ratingValue' => (string) $item->rating,
                                'bestRating' => '5',
                            ],
                            'reviewBody' => $item->comment,
                            'datePublished' => optional($item->approved_at ?? $item->created_at)->toDateString(),
                        ])->values()->all(),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                </script>
            @endif
        @else
            <div class="mt-8 rounded-[20px] border border-dashed border-cyan-200 bg-gradient-to-br from-cyan-50/40 to-white px-6 py-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 text-cyan-700" aria-hidden="true">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                </div>
                <p class="mt-4 text-lg font-semibold text-slate-800">{{ __('app.home.testimonials_empty_title') }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ __('app.home.testimonials_empty_desc') }}</p>
            </div>
        @endif

        {{-- Referanstaki davet şeridi: metin solda, kompakt buton sağda --}}
        <div class="mt-12 rounded-[24px] border border-cyan-100 bg-gradient-to-r from-cyan-50 via-white to-slate-50 shadow-sm md:mt-14">
            <div class="flex flex-col items-stretch gap-5 px-6 py-7 sm:px-8 md:flex-row md:items-center md:justify-between md:gap-8 md:px-10 md:py-8">
                <div class="flex min-w-0 flex-1 items-start gap-4">
                    <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-700" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold tracking-tight text-slate-900 md:text-xl">
                            {{ __('app.home.testimonials_share_cta') }}
                        </h3>
                        <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-slate-600">
                            {{ __('app.home.testimonials_share_desc') }}
                        </p>
                    </div>
                </div>

                <div class="shrink-0 md:ms-4" style="flex-shrink:0;">
                    <button
                        type="button"
                        @click="openModal()"
                        class="btn-primary inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full px-6 py-3 text-sm font-semibold shadow-md transition hover:-translate-y-0.5"
                        style="width:auto;max-width:100%;"
                    >
                        {{ __('app.home.testimonials_share_cta') }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm"
        @click="handleBackdrop($event)"
        role="dialog"
        aria-modal="true"
        aria-labelledby="testimonial-modal-title"
    >
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-[20px] border border-slate-100 bg-white p-6 shadow-2xl md:p-8" @click.stop>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">{{ __('app.home.testimonials_badge') }}</p>
                    <h3 id="testimonial-modal-title" class="mt-1 text-xl font-bold text-slate-900">{{ __('app.home.testimonials_form_title') }}</h3>
                </div>
                <button type="button" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" @click="closeModal()" aria-label="{{ __('app.footer.close') }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('testimonials.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="text" name="company_website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">{{ __('app.home.testimonials_form_name') }} *</span>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">{{ __('app.home.testimonials_form_city') }} *</span>
                        <input type="text" name="city" value="{{ old('city') }}" required maxlength="120" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </label>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('app.home.testimonials_form_email') }} *</span>
                    <span class="block text-xs text-slate-500">{{ __('app.home.testimonials_form_email_hint') }}</span>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="190" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                </label>

                <div>
                    <span class="text-sm font-medium text-slate-700">{{ __('app.home.testimonials_form_rating') }} *</span>
                    <div class="mt-2 flex items-center gap-1">
                        @for ($star = 1; $star <= 5; $star++)
                            <button
                                type="button"
                                class="rounded p-1 transition"
                                @mouseenter="hoverRating = {{ $star }}"
                                @mouseleave="hoverRating = 0"
                                @click="setRating({{ $star }})"
                                :aria-label="'{{ $star }}'"
                            >
                                <svg class="h-7 w-7" :class="(hoverRating || rating) >= {{ $star }} ? 'text-amber-400 fill-amber-400' : 'text-slate-300'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.31 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" :value="rating" required>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('app.home.testimonials_form_comment') }} *</span>
                    <textarea name="comment" rows="4" required minlength="20" maxlength="500" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">{{ old('comment') }}</textarea>
                </label>

                <div class="space-y-2 text-sm text-slate-700">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="is_anonymous" value="1" class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('is_anonymous'))>
                        <span>{{ __('app.home.testimonials_form_anonymous') }}</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="is_volunteer" value="1" class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('is_volunteer'))>
                        <span>{{ __('app.home.testimonials_form_volunteer') }}</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="is_donor" value="1" class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('is_donor'))>
                        <span>{{ __('app.home.testimonials_form_donor') }}</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="kvkk_consent" value="1" required class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('kvkk_consent'))>
                        <span>
                            <button type="button" class="font-semibold text-cyan-700 underline underline-offset-2" @click.prevent="showKvkk = !showKvkk">{{ __('app.legal.kvkk_label') }}</button>
                            {{ __('app.home.testimonials_form_kvkk_read') }}
                        </span>
                    </label>
                </div>

                <div x-show="showKvkk" x-cloak class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
                    {!! nl2br(e($kvkkText)) !!}
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit" class="btn-primary w-full rounded-xl px-6 py-3 text-sm font-semibold shadow-md" :disabled="rating < 1">
                    {{ __('app.home.testimonials_form_submit') }}
                </button>
            </form>
        </div>
    </div>

    <style>
        .testimonial-fade-up {
            animation: testimonialFadeUp 400ms ease both;
        }
        @keyframes testimonialFadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</section>
