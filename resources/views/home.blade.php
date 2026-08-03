<x-layouts.app>
    <x-home-hero-slider :slides="$heroSlidesPayload" />

    <x-chad-live-info />

    @php
        $isTr = app()->getLocale() === 'tr';
        $focusCards = [
            [
                'title' => $isTr ? ($siteSettings->home_focus_1_title ?: __('app.home.focus_1_title')) : __('app.home.focus_1_title'),
                'text'  => $isTr ? ($siteSettings->home_focus_1_text  ?: __('app.home.focus_1_text'))  : __('app.home.focus_1_text'),
                'icon'  => 'education',
            ],
            [
                'title' => $isTr ? ($siteSettings->home_focus_2_title ?: __('app.home.focus_2_title')) : __('app.home.focus_2_title'),
                'text'  => $isTr ? ($siteSettings->home_focus_2_text  ?: __('app.home.focus_2_text'))  : __('app.home.focus_2_text'),
                'icon'  => 'projects',
            ],
            [
                'title' => $isTr ? ($siteSettings->home_focus_3_title ?: __('app.home.focus_3_title')) : __('app.home.focus_3_title'),
                'text'  => $isTr ? ($siteSettings->home_focus_3_text  ?: __('app.home.focus_3_text'))  : __('app.home.focus_3_text'),
                'icon'  => 'solidarity',
            ],
        ];
        if ($isTr) {
            $aboutItems = collect(preg_split('/[\r\n,]+/', (string) ($siteSettings->home_about_items ?? '')))
                ->map(fn (string $item): string => trim($item))
                ->filter()
                ->values()
                ->all();
            if (empty($aboutItems)) {
                $aboutItems = __('app.home.about_items');
            }
        } else {
            $aboutItems = __('app.home.about_items');
        }
        $aboutImage = $siteSettings->home_about_image ? asset('storage/' . $siteSettings->home_about_image) : asset('images/default-logo.svg');
    @endphp
    <section class="mx-auto max-w-7xl px-4 pt-6 md:px-6">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($focusCards as $card)
                <article class="group flex items-start gap-4 rounded-2xl border border-slate-100 border-t-4 border-t-cyan-600 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-900/10">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-cyan-600 text-white transition duration-300 group-hover:scale-110 group-hover:bg-cyan-700">
                        @if($card['icon'] === 'education')
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 1.5 8.5a1 1 0 0 0 0 1.74L5 12.1V17a1 1 0 0 0 .55.89l6 3a1 1 0 0 0 .9 0l6-3A1 1 0 0 0 19 17v-4.9l1.55-.8V16a1 1 0 1 0 2 0V9.5a1 1 0 0 0-.5-.87L12 3Zm0 2.2 7.2 3.8L12 12.8 4.8 9 12 5.2ZM7 13.05l4.45 2.34a1 1 0 0 0 .9 0L17 13.05V16.5l-4.55 2.27a1 1 0 0 1-.9 0L7 16.5v-3.45Z"/></svg>
                        @elseif($card['icon'] === 'projects')
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4a2 2 0 0 0-2 2v3h8V4H4Zm10 0v5h8V6a2 2 0 0 0-2-2h-6ZM2 11v7a2 2 0 0 0 2 2h6v-9H2Zm10 0v9h6a2 2 0 0 0 2-2v-7h-8Z"/></svg>
                        @else
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.5 7a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0ZM4 9.5a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0Zm11 0a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0ZM2.5 16.2A4.5 4.5 0 0 1 7 13h1.1a5.45 5.45 0 0 0-.1 1c0 .7.13 1.37.37 2H7a2.5 2.5 0 0 0-2.5 2.2V19a1 1 0 1 1-2 0v-.8Zm17 2.8a1 1 0 1 1-2 0v-.8A2.5 2.5 0 0 0 15 16h-1.37c.24-.63.37-1.3.37-2 0-.34-.03-.67-.1-1H17a4.5 4.5 0 0 1 4.5 3.2V19a1 1 0 0 1-2 0v-.8ZM9 14a3 3 0 0 1 6 0v5a1 1 0 1 1-2 0v-5a1 1 0 1 0-2 0v5a1 1 0 1 1-2 0v-5Z"/></svg>
                        @endif
                    </span>
                    <div>
                        <h3 class="text-2xl font-semibold tracking-tight text-slate-900 transition duration-300 group-hover:text-cyan-700">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-lg leading-relaxed text-slate-600 transition duration-300 group-hover:text-slate-700">{{ $card['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pt-10 md:px-6 md:pt-12">
        <div class="grid items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
            <div class="relative mx-auto w-full max-w-md lg:max-w-lg">
                <div class="about-float-orb absolute -left-4 -top-4 h-16 w-16 rounded-full bg-cyan-500/20 blur-[1px]"></div>
                <div class="about-float-orb-delayed absolute -bottom-4 -right-4 h-20 w-20 rounded-full bg-cyan-300/30 blur-[1px]"></div>
                <div class="relative aspect-square overflow-hidden rounded-3xl border border-white/40 bg-white/10 p-2 shadow-xl backdrop-blur-md">
                    <div class="h-full w-full rounded-2xl bg-white/5 p-2 backdrop-blur-sm">
                        <img src="{{ $aboutImage }}" alt="{{ __('app.home.about_title') }}" class="h-full w-full object-contain" />
                    </div>
                </div>
            </div>

            <div>
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    • {{ $siteSettings->home_about_badge ?: 'SECDER' }}
                </span>
                <h2 class="mt-3 text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
                    {{ $isTr ? ($siteSettings->home_about_title ?: __('app.home.about_title')) : __('app.home.about_title') }}
                </h2>
                <p class="mt-4 text-lg font-medium leading-relaxed text-slate-700">
                    {{ $isTr ? ($siteSettings->home_about_intro ?: __('app.home.about_intro')) : __('app.home.about_intro') }}
                </p>
                <p class="mt-4 text-base leading-relaxed text-slate-600">
                    {{ $isTr ? ($siteSettings->home_about_body ?: __('app.home.about_body')) : __('app.home.about_body') }}
                </p>

                <div class="mt-6 grid gap-2 sm:grid-cols-2">
                    @foreach($aboutItems as $item)
                        <div class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">✓</span>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('pages.show', ['slug' => 'hikayemiz']) }}" class="btn-primary mt-7">
                    {{ $isTr ? ($siteSettings->home_about_button_text ?: __('app.home.about_button')) : __('app.home.about_button') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ===== FAAALİYETLER - SONSUZ KAYAN ŞERİT ===== --}}
    @php $marqueeSpeed = max(30, $projects->count() * 5); @endphp
    <section class="scroll-mt-24 pt-10 md:pt-12" id="projeler">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="rounded-[28px] bg-cyan-50/45 px-5 pb-5 pt-5 md:px-8 md:pb-6">
                <div class="grid items-center gap-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-10">
                    <div class="space-y-2">
                        <h2 class="max-w-[280px] text-[44px] font-bold leading-[1.08] tracking-tight text-slate-900">
                            {{ $isTr ? ($activitySection->title ?: __('app.home.activities_title')) : __('app.home.activities_title') }}
                        </h2>
                    </div>
                    <p class="max-w-3xl text-base leading-relaxed text-slate-600 md:text-lg">
                        {{ $isTr ? ($activitySection->description ?: __('app.home.activities_desc')) : __('app.home.activities_desc') }}
                    </p>
                </div>
            </div>
        </div>

        @if($projects->isNotEmpty())
        {{-- Kayan şerit alanı --}}
        <div class="relative mt-6 overflow-hidden">
            {{-- Sol soluk geçiş --}}
            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-gradient-to-r from-white to-transparent md:w-40"></div>
            {{-- Sağ soluk geçiş --}}
            <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-gradient-to-l from-white to-transparent md:w-40"></div>

            <div
                class="flex gap-5 pb-4 pt-2"
                style="animation: activityMarquee {{ $marqueeSpeed }}s linear infinite; width: max-content; will-change: transform;"
                onmouseenter="this.style.animationPlayState='paused'"
                onmouseleave="this.style.animationPlayState='running'"
            >
                {{-- Orijinal set --}}
                @foreach($projects as $project)
                    @php
                        $projectTitle = $project->getLocalized('title', $project->title);
                        $projectDescription = $project->getLocalized('description', $project->description);
                        $projectContent = $project->getLocalized('content', $project->content);
                        $statusLabel = $project->status === 'tamamlandi' ? __('app.home.status_completed') : __('app.home.status_ongoing');
                        $statusClass = $project->status === 'tamamlandi'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-amber-200 bg-amber-50 text-amber-700';
                    @endphp
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]" style="width:360px;flex-shrink:0;">
                        <a href="{{ route('activities.show', ['slug' => $project->slug]) }}" class="block p-4">
                            <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img
                                    src="{{ $project->cover_image ? asset('storage/' . $project->cover_image) : asset('images/default-logo.svg') }}"
                                    class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                                    alt="{{ $projectTitle }}"
                                    loading="lazy"
                                >
                            </div>
                        </a>
                        <div class="px-5 pb-5">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            <h3 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">{{ $projectTitle }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                                {{ \Illuminate\Support\Str::limit($projectDescription ?: strip_tags((string) $projectContent), 170) }}
                            </p>
                            @if (! is_null($project->donation_amount))
                                <p class="mt-4 text-lg font-extrabold text-cyan-800">
                                    {{ number_format((float) $project->donation_amount, 2, ',', '.') }} {{ $project->donation_currency ?: 'TL' }}
                                </p>
                            @endif
                            <div class="mt-5 flex items-center gap-3">
                                <a href="{{ route('donations') }}" class="inline-flex items-center rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">{{ __('app.home.donate') }}</a>
                                <a href="{{ route('activities.show', ['slug' => $project->slug]) }}" class="inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900">{{ __('app.home.activity_detail') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach

                {{-- Kopyalanmış set (sonsuz döngü için) --}}
                @foreach($projects as $project)
                    @php
                        $projectTitle = $project->getLocalized('title', $project->title);
                        $projectDescription = $project->getLocalized('description', $project->description);
                        $projectContent = $project->getLocalized('content', $project->content);
                        $statusLabel = $project->status === 'tamamlandi' ? __('app.home.status_completed') : __('app.home.status_ongoing');
                        $statusClass = $project->status === 'tamamlandi'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-amber-200 bg-amber-50 text-amber-700';
                    @endphp
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]" style="width:360px;flex-shrink:0;" aria-hidden="true">
                        <a href="{{ route('activities.show', ['slug' => $project->slug]) }}" class="block p-4" tabindex="-1">
                            <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img
                                    src="{{ $project->cover_image ? asset('storage/' . $project->cover_image) : asset('images/default-logo.svg') }}"
                                    class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                                    alt="{{ $projectTitle }}"
                                    loading="lazy"
                                >
                            </div>
                        </a>
                        <div class="px-5 pb-5">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            <h3 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">{{ $projectTitle }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                                {{ \Illuminate\Support\Str::limit($projectDescription ?: strip_tags((string) $projectContent), 170) }}
                            </p>
                            @if (! is_null($project->donation_amount))
                                <p class="mt-4 text-lg font-extrabold text-cyan-800">
                                    {{ number_format((float) $project->donation_amount, 2, ',', '.') }} {{ $project->donation_currency ?: 'TL' }}
                                </p>
                            @endif
                            <div class="mt-5 flex items-center gap-3">
                                <a href="{{ route('donations') }}" class="inline-flex items-center rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700" tabindex="-1">{{ __('app.home.donate') }}</a>
                                <a href="{{ route('activities.show', ['slug' => $project->slug]) }}" class="inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900" tabindex="-1">{{ __('app.home.activity_detail') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="mx-auto mt-5 flex max-w-7xl justify-center px-4 md:px-6">
            <a href="{{ route('pages.show', ['slug' => 'faaliyetler']) }}" class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-6 py-2.5 text-sm font-semibold text-cyan-700 transition hover:border-cyan-400 hover:bg-cyan-100 hover:text-cyan-900">
                {{ __('app.home.activities_view_all') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @endif
    </section>

    <section class="mx-auto max-w-7xl px-4 pt-12 md:px-6 md:pt-14">
        <div class="rounded-3xl border border-cyan-100 bg-white p-6 shadow-sm md:p-8">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <article class="group relative rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-transparent transition-all duration-500 ease-out hover:-translate-y-1.5 hover:bg-white hover:shadow-xl hover:shadow-cyan-900/10 hover:ring-cyan-100">
                    <span class="absolute left-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-white transition-transform duration-500 group-hover:scale-110">01</span>
                    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-cyan-600 text-white shadow-sm transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:bg-cyan-700 group-hover:shadow-lg group-hover:shadow-cyan-700/30">
                        <svg class="h-7 w-7 transition-transform duration-500 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21a1 1 0 0 1-.7-.3l-6-6a5 5 0 1 1 7.1-7.1l.6.6.6-.6a5 5 0 1 1 7.1 7.1l-6 6a1 1 0 0 1-.7.3Z"/></svg>
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-900 transition-colors duration-500 group-hover:text-cyan-700">{{ __('app.home.step_1_title') }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600 transition-colors duration-500 group-hover:text-slate-700">{{ __('app.home.step_1_text') }}</p>
                </article>
                <article class="group relative rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-transparent transition-all duration-500 ease-out hover:-translate-y-1.5 hover:bg-white hover:shadow-xl hover:shadow-cyan-900/10 hover:ring-cyan-100">
                    <span class="absolute left-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-white transition-transform duration-500 group-hover:scale-110">02</span>
                    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-cyan-600 text-white shadow-sm transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:bg-cyan-700 group-hover:shadow-lg group-hover:shadow-cyan-700/30">
                        <svg class="h-7 w-7 transition-transform duration-500 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4a2 2 0 0 0-2 2v13.5a.5.5 0 0 0 .8.4L8 17.5l3.2 2.4a.5.5 0 0 0 .6 0L15 17.5l3.2 2.4a.5.5 0 0 0 .8-.4V6a2 2 0 0 0-2-2H6Zm0 2h12v11.26l-2.7-2.02a1 1 0 0 0-1.2 0L12 17.26l-2.1-1.58a1 1 0 0 0-1.2 0L6 17.26V6Z"/></svg>
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-900 transition-colors duration-500 group-hover:text-cyan-700">{{ __('app.home.step_2_title') }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600 transition-colors duration-500 group-hover:text-slate-700">{{ __('app.home.step_2_text') }}</p>
                </article>
                <article class="group relative rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-transparent transition-all duration-500 ease-out hover:-translate-y-1.5 hover:bg-white hover:shadow-xl hover:shadow-cyan-900/10 hover:ring-cyan-100">
                    <span class="absolute left-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-white transition-transform duration-500 group-hover:scale-110">03</span>
                    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-cyan-600 text-white shadow-sm transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:bg-cyan-700 group-hover:shadow-lg group-hover:shadow-cyan-700/30">
                        <svg class="h-7 w-7 transition-transform duration-500 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3a1 1 0 0 1 .8.4l1.7 2.26A7.5 7.5 0 1 1 4.5 12a1 1 0 1 1 2 0 5.5 5.5 0 1 0 5.2-5.49L10.8 8.6A1 1 0 0 1 9.2 7.4l2-2.66A1 1 0 0 1 12 3Zm-3.5 9a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm3.5 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm3.5 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z"/></svg>
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-900 transition-colors duration-500 group-hover:text-cyan-700">{{ __('app.home.step_3_title') }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600 transition-colors duration-500 group-hover:text-slate-700">{{ __('app.home.step_3_text') }}</p>
                </article>
                <article class="group relative rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-transparent transition-all duration-500 ease-out hover:-translate-y-1.5 hover:bg-white hover:shadow-xl hover:shadow-cyan-900/10 hover:ring-cyan-100">
                    <span class="absolute left-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-white transition-transform duration-500 group-hover:scale-110">04</span>
                    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-cyan-600 text-white shadow-sm transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:bg-cyan-700 group-hover:shadow-lg group-hover:shadow-cyan-700/30">
                        <svg class="h-7 w-7 transition-transform duration-500 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12a1 1 0 0 1 1-1h3.28l1.36-4.08a1 1 0 0 1 1.91.03L12.72 11H16a1 1 0 0 1 .8.4l1.45 1.93 1.78-3.56a1 1 0 0 1 1.9.45V18a1 1 0 1 1-2 0v-3.76l-.9 1.8a1 1 0 0 1-1.7.12L15.5 13H12a1 1 0 0 1-.95-.68l-1.1-3.3-.95 2.86A1 1 0 0 1 8 13H4a1 1 0 0 1-1-1Z"/></svg>
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-900 transition-colors duration-500 group-hover:text-cyan-700">{{ __('app.home.step_4_title') }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600 transition-colors duration-500 group-hover:text-slate-700">{{ __('app.home.step_4_text') }}</p>
                </article>
            </div>

            <div class="mt-7 flex justify-center">
                <a href="{{ route('donations') }}" class="group inline-flex min-w-[220px] items-center justify-center gap-2 rounded-full bg-cyan-700 px-8 py-3 text-sm font-semibold text-white shadow-md transition-all duration-500 hover:-translate-y-1 hover:scale-[1.02] hover:bg-cyan-800 hover:shadow-xl hover:shadow-cyan-700/30">
                    {{ __('app.home.steps_donate') }}
                    <span class="text-base transition-transform duration-500 group-hover:translate-x-1 group-hover:scale-110">+</span>
                </a>
            </div>
        </div>
    </section>

    @php $newsMarqueeSpeed = max(30, $newsItems->count() * 5); @endphp
    <section class="pt-14" id="haberler">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                        • {{ __('app.home.news_badge') }}
                    </span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 md:text-5xl">{{ __('app.home.news_title') }}</h2>
                </div>
            </div>
        </div>

        @if($newsItems->isNotEmpty())
        <div class="relative mt-7 overflow-hidden">
            {{-- Sol soluk geçiş --}}
            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-gradient-to-r from-white to-transparent md:w-40"></div>
            {{-- Sağ soluk geçiş --}}
            <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-gradient-to-l from-white to-transparent md:w-40"></div>

            <div
                class="flex gap-5 pb-4 pt-2"
                style="animation: newsMarquee {{ $newsMarqueeSpeed }}s linear infinite; width: max-content; will-change: transform;"
                onmouseenter="this.style.animationPlayState='paused'"
                onmouseleave="this.style.animationPlayState='running'"
            >
                {{-- Orijinal set --}}
                @foreach($newsItems as $news)
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]" style="width:360px;flex-shrink:0;">
                        <a href="{{ route('news.show', ['news' => $news->id]) }}" class="block p-4">
                            <div class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img
                                    src="{{ $news->cover_image ? asset('storage/' . $news->cover_image) : asset('images/default-logo.svg') }}"
                                    alt="{{ $news->getLocalized('title') }}"
                                    class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                                    loading="lazy"
                                >
                                <span class="absolute left-3 top-3 inline-flex rounded-full bg-cyan-700 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    {{ optional($news->published_at)->format('d M') ?: __('app.page.news_new') }}
                                </span>
                            </div>
                        </a>
                        <div class="px-5 pb-5">
                            <h3 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">{{ $news->getLocalized('title') }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                                {{ \Illuminate\Support\Str::limit($news->getLocalized('summary') ?: strip_tags((string) $news->getLocalized('content')), 170) }}
                            </p>
                            <a href="{{ route('news.show', ['news' => $news->id]) }}" class="mt-5 inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900">
                                {{ __('app.home.news_detail') }}
                            </a>
                        </div>
                    </article>
                @endforeach

                {{-- Kopyalanmış set (sonsuz döngü için) --}}
                @foreach($newsItems as $news)
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]" style="width:360px;flex-shrink:0;" aria-hidden="true">
                        <a href="{{ route('news.show', ['news' => $news->id]) }}" class="block p-4" tabindex="-1">
                            <div class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img
                                    src="{{ $news->cover_image ? asset('storage/' . $news->cover_image) : asset('images/default-logo.svg') }}"
                                    alt="{{ $news->getLocalized('title') }}"
                                    class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                                    loading="lazy"
                                >
                                <span class="absolute left-3 top-3 inline-flex rounded-full bg-cyan-700 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    {{ optional($news->published_at)->format('d M') ?: __('app.page.news_new') }}
                                </span>
                            </div>
                        </a>
                        <div class="px-5 pb-5">
                            <h3 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">{{ $news->getLocalized('title') }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                                {{ \Illuminate\Support\Str::limit($news->getLocalized('summary') ?: strip_tags((string) $news->getLocalized('content')), 170) }}
                            </p>
                            <a href="{{ route('news.show', ['news' => $news->id]) }}" class="mt-5 inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900" tabindex="-1">
                                {{ __('app.home.news_detail') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="mx-auto mt-5 flex max-w-7xl justify-center px-4 md:px-6">
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-6 py-2.5 text-sm font-semibold text-cyan-700 transition hover:border-cyan-400 hover:bg-cyan-100 hover:text-cyan-900">
                {{ __('app.home.news_view_all') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @endif
    </section>

    <x-home-testimonials :testimonials="$testimonials" :testimonial-stats="$testimonialStats" />

    <style>
        @keyframes activityMarquee {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @keyframes newsMarquee {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .wa-float { animation: wa-float-in 0.6s ease-out forwards; }
        .wa-ring  { animation: wa-ring-pulse 2s ease-in-out infinite; }
        .wa-btn:hover { transform: scale(1.12); }
        @keyframes wa-float-in {
            0%   { opacity: 0; transform: translateY(40px) scale(0.7); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes wa-ring-pulse {
            0%   { transform: scale(1);   opacity: 0.6; }
            70%  { transform: scale(1.9); opacity: 0; }
            100% { transform: scale(1.9); opacity: 0; }
        }
    </style>

    <div class="wa-float" style="position:fixed; right:20px; bottom:24px; z-index:9999; filter: drop-shadow(0 4px 16px rgba(34,197,94,0.45));">
        <span class="wa-ring absolute inset-0 rounded-full bg-green-400"></span>
        <a
            href="https://wa.me/905425214040"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="WhatsApp ile iletişime geçin"
            class="wa-btn relative flex h-14 w-14 items-center justify-center rounded-full shadow-lg transition-transform duration-200"
            style="background-color:#25D366;"
        >
            <svg viewBox="0 0 32 32" class="h-8 w-8" fill="white" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.003 3C9.374 3 4 8.373 4 15c0 2.388.68 4.617 1.859 6.508L4 29l7.695-1.827A11.94 11.94 0 0016.003 28C22.63 28 28 22.627 28 16S22.63 3 16.003 3zm0 2c5.516 0 10 4.484 10 10s-4.484 10-10 10a9.96 9.96 0 01-5.145-1.432l-.37-.223-4.568 1.084 1.12-4.44-.242-.386A9.955 9.955 0 016 15c0-5.516 4.487-10 10.003-10zm-3.04 5.188c-.22 0-.576.082-.877.408-.302.326-1.152 1.126-1.152 2.747s1.18 3.188 1.344 3.408c.165.22 2.313 3.53 5.607 4.81.784.338 1.395.54 1.872.692.786.25 1.502.215 2.068.13.631-.093 1.944-.794 2.218-1.561.274-.768.274-1.427.192-1.565-.082-.138-.302-.22-.632-.385-.33-.165-1.944-.958-2.246-1.068-.302-.11-.521-.165-.74.165-.22.33-.852 1.068-.963 1.233-.11.166-.22.187-.55.022-.33-.165-1.392-.513-2.652-1.636-.98-.874-1.642-1.952-1.834-2.282-.192-.33-.02-.508.144-.672.148-.148.33-.385.495-.578.165-.192.22-.33.33-.55.11-.22.055-.412-.028-.578-.082-.165-.74-1.784-1.014-2.44-.267-.64-.54-.552-.74-.562l-.63-.01z"/>
            </svg>
        </a>
    </div>

</x-layouts.app>
