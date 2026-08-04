<x-layouts.app>
    @php
        $pageHeroTitleMap = [
            'hikayemiz' => __('app.page.story_page_title'),
            'dernek-tuzugu' => __('app.page.doc_charter_title'),
            'faaliyet-belgesi' => __('app.page.doc_activity_title'),
            'kurumsal-evrak-arsivi' => __('app.page.doc_archive_title'),
            'basin-kiti' => __('app.page.press_kit_title'),
            'hakkimizda' => __('app.page.about_us'),
            'yonetim' => __('app.page.management_title'),
            'resmi-bilgiler' => __('app.page.official_page_title'),
            'resmi-belgiler' => __('app.page.official_page_title'),
            'baskanin-mesaji' => __('app.page.president_message_title'),
            'vizyon-misyon' => __('app.page.vision_page_title'),
            'faaliyetler' => __('app.page.activities_page_title'),
        ];
        $pageHeroTitle = $page->getLocalized('title', $pageHeroTitleMap[$page->slug] ?? $page->title);
    @endphp
    <x-page-hero :title="$pageHeroTitle" />

    @if ($page->slug === 'hikayemiz')
        <section class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:py-16">
            @php
                $storyIntroHtml = $page->getLocalized('content')
                    ?: '<p>' . e(__('app.page.story_intro')) . '</p>';
            @endphp
            @if (! empty($storyIntroHtml))
                <div class="prose mx-auto mb-10 max-w-3xl text-center text-slate-600 prose-slate">{!! $storyIntroHtml !!}</div>
            @endif

            @php
                $storyItems = collect($page->story_items ?? [])
                    ->map(fn ($item) => [
                        'title' => \App\Models\Page::localizedFromItem($item, 'title'),
                        'description' => \App\Models\Page::localizedFromItem($item, 'description'),
                        'image' => $item['image'] ?? null,
                    ])
                    ->filter(fn ($item) => filled($item['title']) && filled($item['description']));
            @endphp

            @if ($storyItems->isNotEmpty())
                <div class="relative mx-auto max-w-6xl">
                    <div class="story-timeline-line z-0 hidden lg:block" aria-hidden="true"></div>

                    <div class="space-y-12 md:space-y-14">
                        @foreach ($storyItems as $index => $item)
                            @php
                                $isLeft = $index % 2 === 0; // çift: görsel sol, metin sağ; tek: metin sol, görsel sağ
                                $textOnRight = $isLeft;
                                $imagePath = ! empty($item['image']) ? \Illuminate\Support\Facades\Storage::url($item['image']) : null;
                                // Mobilde her zaman görsel üstte, metin altta olsun; masaüstünde zigzag devam etsin.
                                $imgOrder = $isLeft ? 'order-1 lg:order-1' : 'order-1 lg:order-3';
                                $textOrder = $isLeft ? 'order-2 lg:order-3' : 'order-2 lg:order-1';
                            @endphp

                            <article class="group/story relative z-10 flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_2.5rem_1fr] lg:items-start lg:gap-0 lg:px-0">
                                {{-- Görsel sütun --}}
                                <div class="{{ $imgOrder }}">
                                    <div
                                        class="h-full overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_10px_32px_rgba(15,23,42,0.07)] transition-all duration-500 ease-out group-hover/story:shadow-[0_18px_34px_rgba(14,116,144,0.14)]"
                                    >
                                        @if ($imagePath)
                                            <div class="relative h-[210px] w-full overflow-hidden bg-white/5 p-2 sm:h-[250px] lg:h-[280px]">
                                                <img
                                                    src="{{ $imagePath }}"
                                                    alt="{{ $item['title'] }}"
                                                    class="h-full w-full object-contain transition-transform duration-700 ease-out group-hover/story:scale-[1.02]"
                                                    loading="lazy"
                                                >
                                            </div>
                                        @else
                                            <div class="grid h-[240px] place-items-center bg-slate-100 text-sm text-slate-500 sm:h-[260px] lg:h-[280px]">
                                                {{ __('app.page.no_image') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Zaman noktası (masaüstü) --}}
                                <div class="pointer-events-none relative z-20 hidden w-8 shrink-0 items-start justify-center pt-8 lg:flex lg:order-2" aria-hidden="true">
                                    <span class="story-dot transition-all duration-500 group-hover/story:scale-110 group-hover/story:bg-cyan-400 group-hover/story:ring-cyan-400/35"></span>
                                </div>

                                {{-- Metin kartı: sadece bu kutu hover (arka plan + metin rengi) --}}
                                <div class="{{ $textOrder }} flex items-start">
                                    <div
                                        @class([
                                            'group/storytext relative w-full rounded-2xl border border-slate-200/95 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.10)]',
                                            'transition-all duration-500 ease-out md:p-7',
                                            'hover:-translate-y-1',
                                            'hover:border-cyan-300/80',
                                            'hover:shadow-[0_18px_38px_rgba(6,120,150,0.14)]',
                                        ])
                                    >
                                        @if ($textOnRight)
                                            <span class="story-link-line-left"></span>
                                        @else
                                            <span class="story-link-line-right"></span>
                                        @endif
                                        @if ($textOnRight)
                                            <span
                                                class="story-text-pointer--to-left hidden transition-colors duration-500 group-hover/storytext:border-r-cyan-100 lg:block"
                                                aria-hidden="true"
                                            ></span>
                                        @else
                                            <span
                                                class="story-text-pointer--to-right hidden transition-colors duration-500 group-hover/storytext:border-l-cyan-100 lg:block"
                                                aria-hidden="true"
                                            ></span>
                                        @endif
                                        <h2
                                            class="text-lg font-bold text-cyan-900 transition-colors duration-500 group-hover/storytext:text-cyan-700 md:text-xl"
                                        >
                                            {{ $item['title'] }}
                                        </h2>
                                        <p
                                            class="mt-3 text-sm leading-relaxed text-slate-600 transition-colors duration-500 group-hover/storytext:text-cyan-800 md:text-base"
                                        >
                                            {{ $item['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <article class="card-ui">
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('app.page.story_empty_title') }}</h2>
                    <p class="mt-4 text-slate-600">{{ __('app.page.story_empty_desc') }}</p>
                </article>
            @endif
        </section>
    @elseif ($page->slug === 'hakkimizda')
        @php
            $settings = \App\Models\Setting::current();
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $hasAboutImage = ! empty($meta['about_image']);
            $aboutImage = $hasAboutImage
                ? \Illuminate\Support\Facades\Storage::url($meta['about_image'])
                : null;
            $aboutContentHtml = $page->getLocalized('content', __('app.page.about_content_html'));
            $socialLinks = $settings->activeSocialLinks();
            $socialAria = [
                'instagram' => 'Instagram',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok',
                'facebook' => 'Facebook',
                'x' => 'X',
                'linkedin' => 'LinkedIn',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'website' => __('app.page.social_media_us'),
            ];
        @endphp

        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(95,111,155,0.10),_transparent_55%)]"></div>

            <div class="relative mx-auto max-w-5xl px-4 py-12 md:px-6 lg:py-16">
                <header class="mx-auto mb-8 max-w-3xl text-center md:mb-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">SECDER</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-cyan-950 md:text-4xl">{{ __('app.page.about_us') }}</h2>
                    <div class="mx-auto mt-5 h-1 w-16 rounded-full bg-cyan-600"></div>
                </header>

                <article class="overflow-hidden rounded-3xl border border-cyan-100 bg-white shadow-[0_18px_48px_rgba(15,23,42,0.10)]">
                    <div class="h-1.5 w-full bg-gradient-to-r from-cyan-500 via-cyan-600 to-cyan-700"></div>

                    <div class="grid items-stretch gap-0 md:grid-cols-2">
                        @if ($hasAboutImage && $aboutImage)
                            <div class="flex min-w-0 items-center justify-center bg-gradient-to-br from-cyan-50 via-white to-slate-50 p-6 md:p-8">
                                <img
                                    src="{{ $aboutImage }}"
                                    alt="{{ $page->title }}"
                                    class="h-44 w-44 rounded-full object-cover shadow-lg shadow-cyan-900/15 ring-4 ring-white sm:h-52 sm:w-52 md:h-56 md:w-56 lg:h-64 lg:w-64"
                                    loading="lazy"
                                >
                            </div>
                        @endif

                        <div @class([
                            'min-w-0 w-full px-5 py-7 md:px-8 md:py-9',
                            'md:col-span-2' => ! ($hasAboutImage && $aboutImage),
                        ])>
                            <div class="mb-5 flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-600 text-white shadow-md shadow-cyan-900/15">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.5-7-10a7 7 0 1 1 14 0c0 5.5-7 10-7 10Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold tracking-tight text-cyan-950 md:text-2xl">{{ __('app.page.about_us') }}</h3>
                            </div>

                            <div class="about-prose w-full max-w-none text-left text-[15px] leading-8 text-slate-700">
                                {!! $aboutContentHtml !!}
                            </div>
                        </div>
                    </div>

                    @if (! empty($socialLinks))
                        <div class="border-t border-cyan-50 bg-slate-50/60 px-5 py-5 md:px-8">
                            <p class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('app.page.social_media_us') }}</p>
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                @foreach ($socialLinks as $social)
                                    <a
                                        href="{{ $social['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-cyan-200 bg-white text-cyan-700 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-600 hover:text-white"
                                        title="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                        aria-label="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                    >
                                        <x-social-brand-icon :platform="$social['platform']" icon-class="h-4 w-4" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </div>
        </section>

        <style>
            .about-prose {
                width: 100%;
                max-width: 100%;
            }
            .about-prose > * {
                max-width: 100%;
            }
            .about-prose h2,
            .about-prose h3 {
                margin-top: 1.5rem;
                margin-bottom: 0.75rem;
                font-size: 1.125rem;
                font-weight: 800;
                letter-spacing: -0.01em;
                color: #2b3245;
            }
            .about-prose h2:first-child,
            .about-prose h3:first-child {
                margin-top: 0;
            }
            .about-prose p {
                margin: 0.75rem 0;
                line-height: 1.85;
                color: #334155;
            }
            .about-prose p:empty {
                display: none;
            }
            .about-prose ul,
            .about-prose ol {
                margin: 0.75rem 0;
                padding-left: 1.25rem;
            }
            .about-prose li {
                margin: 0.4rem 0;
                line-height: 1.7;
            }
            .about-prose a {
                color: #4d5c83;
                text-decoration: underline;
            }
            .about-prose strong {
                color: #2b3245;
                font-weight: 700;
            }
        </style>
    @elseif ($page->slug === 'vizyon-misyon')
        @php
            $settings = \App\Models\Setting::current();
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $visionHtml = $page->getMetaLocalized('vision_text')
                ?: '<p>' . e(__('app.page.vision_body')) . '</p>';
            $missionHtml = $page->getMetaLocalized('mission_text')
                ?: '<p>' . e(__('app.page.mission_body')) . '</p>';
            $socialLinks = $settings->activeSocialLinks();
            $socialAria = [
                'instagram' => 'Instagram',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok',
                'facebook' => 'Facebook',
                'x' => 'X',
                'linkedin' => 'LinkedIn',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'website' => __('app.page.social_media_us'),
            ];
        @endphp

        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(95,111,155,0.10),_transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-cyan-50/60 to-transparent"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 md:px-6 lg:py-16">
                <header class="mx-auto mb-10 max-w-3xl text-center md:mb-12">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">SECDER</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-cyan-950 md:text-4xl">{{ __('app.page.vision_title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600 md:text-base">{{ __('app.page.vision_subtitle') }}</p>
                    <div class="mx-auto mt-5 h-1 w-16 rounded-full bg-cyan-600"></div>
                </header>

                <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                    {{-- Vizyon --}}
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-cyan-100 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_48px_rgba(77,92,131,0.18)]">
                        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-cyan-500 via-cyan-600 to-cyan-700"></div>
                        <div class="flex flex-1 flex-col p-6 md:p-8">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-600 text-white shadow-md shadow-cyan-900/15">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold tracking-tight text-cyan-950 md:text-2xl">{{ __('app.page.vision_our') }}</h3>
                            </div>

                            <div class="vision-mission-prose prose max-w-none flex-1 text-left prose-slate prose-p:my-3 prose-p:text-[15px] prose-p:leading-8 prose-p:text-slate-700 prose-headings:text-cyan-950 prose-strong:text-cyan-900 prose-li:my-1.5 prose-li:text-[15px] prose-li:leading-7 prose-li:text-slate-700 prose-ul:my-4 prose-ol:my-4">
                                {!! $visionHtml !!}
                            </div>
                        </div>
                    </article>

                    {{-- Misyon --}}
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-cyan-100 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_48px_rgba(77,92,131,0.18)]">
                        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-cyan-700 via-cyan-600 to-cyan-500"></div>
                        <div class="flex flex-1 flex-col p-6 md:p-8">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-700 text-white shadow-md shadow-cyan-900/15">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 0 1 2-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 0 0-2 2Zm9-13.5V9"/>
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold tracking-tight text-cyan-950 md:text-2xl">{{ __('app.page.mission_our') }}</h3>
                            </div>

                            <div class="vision-mission-prose prose max-w-none flex-1 text-left prose-slate prose-p:my-3 prose-p:text-[15px] prose-p:leading-8 prose-p:text-slate-700 prose-headings:text-cyan-950 prose-strong:text-cyan-900 prose-li:my-1.5 prose-li:text-[15px] prose-li:leading-7 prose-li:text-slate-700 prose-ul:my-4 prose-ol:my-4">
                                {!! $missionHtml !!}
                            </div>
                        </div>
                    </article>
                </div>

                @if (! empty($socialLinks))
                    <div class="mt-8 rounded-2xl border border-cyan-100 bg-white/80 px-5 py-5 shadow-sm backdrop-blur-sm md:mt-10 md:px-8">
                        <p class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('app.page.social_media_us') }}</p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            @foreach ($socialLinks as $social)
                                <a
                                    href="{{ $social['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-cyan-200 bg-white text-cyan-700 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-600 hover:text-white"
                                    title="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                    aria-label="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                >
                                    <x-social-brand-icon :platform="$social['platform']" icon-class="h-4 w-4" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <style>
            .vision-mission-prose ul {
                list-style: none;
                padding-left: 0;
            }
            .vision-mission-prose ul > li {
                position: relative;
                padding-left: 1.65rem;
                margin-top: 0.65rem;
                margin-bottom: 0.65rem;
            }
            .vision-mission-prose ul > li::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0.55rem;
                width: 0.55rem;
                height: 0.55rem;
                border-radius: 9999px;
                background: #4d5c83;
                box-shadow: 0 0 0 3px rgba(77, 92, 131, 0.16);
            }
            .vision-mission-prose ol {
                padding-left: 1.15rem;
            }
            .vision-mission-prose ol > li::marker {
                color: #4d5c83;
                font-weight: 700;
            }
            .vision-mission-prose p:empty {
                display: none;
            }
        </style>
    @elseif (in_array($page->slug, ['dernek-tuzugu', 'faaliyet-belgesi', 'kurumsal-evrak-arsivi'], true))
        @php
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $documentConfig = [
                'dernek-tuzugu' => [
                    'legacy_file_key' => 'charter_file',
                    'legacy_title_key' => 'charter_title',
                    'default_title' => __('app.page.doc_charter_title'),
                    'empty_text' => __('app.page.doc_charter_empty'),
                ],
                'faaliyet-belgesi' => [
                    'legacy_file_key' => 'activity_doc_file',
                    'legacy_title_key' => 'activity_doc_title',
                    'default_title' => __('app.page.doc_activity_title'),
                    'empty_text' => __('app.page.doc_activity_empty'),
                ],
                'kurumsal-evrak-arsivi' => [
                    'legacy_file_key' => 'archive_doc_file',
                    'legacy_title_key' => 'archive_doc_title',
                    'default_title' => __('app.page.doc_archive_title'),
                    'empty_text' => __('app.page.doc_archive_empty'),
                ],
            ];
            $currentDoc = $documentConfig[$page->slug] ?? $documentConfig['dernek-tuzugu'];
            $documentFile = $meta['document_file'] ?? ($meta[$currentDoc['legacy_file_key']] ?? null);
            $documentUrl = filled($documentFile) ? \Illuminate\Support\Facades\Storage::url($documentFile) : null;
            $documentTitle = $page->getMetaLocalized(
                'document_title',
                trim((string) ($meta[$currentDoc['legacy_title_key']] ?? '')) ?: $currentDoc['default_title']
            );
            $documentExt = $documentFile ? strtolower(pathinfo((string) $documentFile, PATHINFO_EXTENSION)) : null;
            $isImagePreview = in_array($documentExt, ['jpg', 'jpeg', 'png'], true);
            $isPdfPreview = $documentExt === 'pdf';
        @endphp
        <section class="mx-auto max-w-5xl px-4 py-12 md:px-6 lg:py-16">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_14px_34px_rgba(15,23,42,0.08)] md:p-8">
                @if (filled($page->getLocalized('content')))
                    <div class="prose mx-auto mb-6 max-w-none text-center prose-slate prose-p:leading-8">
                        {!! $page->getLocalized('content') !!}
                    </div>
                @endif

                @if ($documentUrl)
                    <div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-slate-50/60 p-4 md:p-6">
                        <h2 class="text-center text-xl font-bold text-cyan-900 md:text-2xl">{{ $documentTitle }}</h2>

                        @if ($isPdfPreview)
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <iframe
                                    src="{{ $documentUrl }}"
                                    class="h-[560px] w-full"
                                    style="border:0;"
                                    loading="lazy"
                                    title="{{ $documentTitle }}"
                                ></iframe>
                            </div>
                        @elseif ($isImagePreview)
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                <img
                                    src="{{ $documentUrl }}"
                                    alt="{{ $documentTitle }}"
                                    class="mx-auto block h-auto max-h-[620px] w-full rounded-lg object-contain"
                                >
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                                <p class="text-sm leading-7 text-slate-600">
                                    {{ __('app.page.doc_no_preview') }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                            <a
                                href="{{ $documentUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                            >
                                {{ __('app.page.doc_open_tab') }}
                            </a>
                            <a
                                href="{{ $documentUrl }}"
                                download
                                class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-5 py-2.5 text-sm font-semibold text-cyan-800 transition hover:border-cyan-300 hover:bg-cyan-100"
                            >
                                {{ __('app.page.doc_download') }}
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-center text-sm text-slate-600">
                        {{ $currentDoc['empty_text'] }}
                    </p>
                @endif
            </article>
        </section>
    @elseif ($page->slug === 'faaliyetler')
        @php
            $fq     = request('q', '');
            $fstat  = request('status', '');
            $fsort  = request('sort', 'default');
            $activitiesIntroHtml = $page->getLocalized('content')
                ?: '<p>' . e(__('app.page.activities_intro')) . '</p>';

            $actQuery = \App\Models\Project::query()->active();

            if (filled($fq)) {
                $actQuery->where('title', 'like', '%' . $fq . '%');
            }
            if (in_array($fstat, ['devam_ediyor', 'tamamlandi'], true)) {
                $fstat === 'devam_ediyor'
                    ? $actQuery->where('status', '!=', 'tamamlandi')
                    : $actQuery->where('status', 'tamamlandi');
            }
            match ($fsort) {
                'amount_asc'  => $actQuery->orderByRaw('CAST(donation_amount AS DECIMAL(15,2)) ASC'),
                'amount_desc' => $actQuery->orderByRaw('CAST(donation_amount AS DECIMAL(15,2)) DESC'),
                default       => $actQuery->orderBy('sort_order'),
            };

            $activities = $actQuery->get();
            $pageUrl    = request()->url();
        @endphp
        <section class="mx-auto max-w-7xl px-4 py-10 md:px-6 lg:py-14">
            @if (! empty($activitiesIntroHtml))
                <div class="mx-auto mb-8 max-w-4xl rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center text-sm leading-7 text-slate-600 shadow-sm md:text-base">
                    {!! $activitiesIntroHtml !!}
                </div>
            @endif

            {{-- Filtre Alanı --}}
            <form method="GET" action="{{ $pageUrl }}" class="mb-8">
                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:gap-4">

                    {{-- Arama --}}
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="q"
                            value="{{ $fq }}"
                            placeholder="{{ __('app.page.activities_search') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100"
                        >
                    </div>

                    {{-- Durum --}}
                    <div class="flex gap-2">
                        @foreach (['' => __('app.page.activities_all'), 'devam_ediyor' => __('app.page.activities_ongoing'), 'tamamlandi' => __('app.page.activities_done')] as $val => $label)
                            <button
                                type="submit"
                                name="status"
                                value="{{ $val }}"
                                class="rounded-full border px-4 py-2 text-xs font-semibold transition
                                    {{ $fstat === $val
                                        ? 'border-cyan-500 bg-cyan-600 text-white shadow-sm'
                                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>

                    {{-- Sıralama --}}
                    <select
                        name="sort"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100"
                    >
                        <option value="default"     {{ $fsort === 'default'     ? 'selected' : '' }}>{{ __('app.page.activities_sort_default') }}</option>
                        <option value="amount_asc"  {{ $fsort === 'amount_asc'  ? 'selected' : '' }}>{{ __('app.page.activities_sort_asc') }}</option>
                        <option value="amount_desc" {{ $fsort === 'amount_desc' ? 'selected' : '' }}>{{ __('app.page.activities_sort_desc') }}</option>
                    </select>

                    {{-- Filtreyi Temizle --}}
                    @if($fq || $fstat || $fsort !== 'default')
                        <a
                            href="{{ $pageUrl }}"
                            class="flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('app.page.activities_clear') }}
                        </a>
                    @endif
                </div>

                <p class="mt-3 text-sm text-slate-500">
                    <span class="font-semibold text-cyan-700">{{ $activities->count() }}</span> {{ __('app.page.activities_count') }}
                </p>
            </form>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse($activities as $activity)
                    @php
                        /** @var \App\Models\Project $activity */
                        $activityTitle = $activity->getLocalized('title', $activity->title);
                        $activityDescription = $activity->getLocalized('description', $activity->description);
                        $activityContent = $activity->getLocalized('content', $activity->content);
                        $statusLabel = $activity->status === 'tamamlandi' ? __('app.page.activities_done') : __('app.page.activities_ongoing');
                        $statusClass = $activity->status === 'tamamlandi'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-amber-200 bg-amber-50 text-amber-700';
                    @endphp
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]">
                        <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}" class="block p-4">
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <img
                                    src="{{ $activity->cover_image ? asset('storage/' . $activity->cover_image) : asset('images/default-logo.svg') }}"
                                    alt="{{ $activityTitle }}"
                                    class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                                >
                            </div>
                        </a>

                        <div class="px-5 pb-5">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            <h2 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">
                                <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}">{{ $activityTitle }}</a>
                            </h2>
                            <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                                {{ \Illuminate\Support\Str::limit($activityDescription ?: strip_tags((string) $activityContent), 170) }}
                            </p>

                            @if (! is_null($activity->donation_amount))
                                <p class="mt-4 text-lg font-extrabold text-cyan-800">
                                    {{ number_format((float) $activity->donation_amount, 2, ',', '.') }} {{ $activity->donation_currency ?: 'TL' }}
                                </p>
                            @endif

                            <div class="mt-5 flex items-center gap-3">
                                <a
                                    href="{{ route('donations') }}"
                                    class="inline-flex items-center rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
                                >
                                    {{ __('app.page.donate_btn') }}
                                </a>
                                <a
                                    href="{{ route('activities.show', ['slug' => $activity->slug]) }}"
                                    class="inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900"
                                >
                                    {{ __('app.page.activity_detail') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-3 py-16 text-center">
                        <svg class="mx-auto mb-4 h-14 w-14 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-semibold text-slate-500">{{ __('app.page.activities_empty') }}</p>
                        <a href="{{ $pageUrl }}" class="mt-4 inline-flex items-center rounded-full bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">{{ __('app.page.activities_view_all') }}</a>
                    </div>
                @endforelse
            </div>
        </section>
    @elseif ($page->slug === 'baskanin-mesaji')
        @php
            $settings = \App\Models\Setting::current();
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $presidentImage = ! empty($meta['president_image']) ? \Illuminate\Support\Facades\Storage::url($meta['president_image']) : asset('images/default-logo.svg');
            $signatureTitle = trim((string) ($page->getMetaLocalized('signature_title') ?? '')) ?: __('app.page.president_signature_title');
            $signatureName = trim((string) ($meta['signature_name'] ?? ''));
            $presidentMessageHtml = $page->getLocalized('content')
                ?: nl2br(e(__('app.page.president_message_body')));
            $socialLinks = $settings->activeSocialLinks();
            $socialAria = [
                'instagram' => 'Instagram',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok',
                'facebook' => 'Facebook',
                'x' => 'X',
                'linkedin' => 'LinkedIn',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'website' => __('app.page.social_media_us'),
            ];
        @endphp
        <section class="mx-auto max-w-6xl px-4 py-12 md:px-6 lg:py-16">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_14px_34px_rgba(15,23,42,0.08)] md:p-8">
                <div class="items-start gap-8 md:grid md:grid-cols-[300px_1fr] lg:grid-cols-[380px_1fr]">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white/10 p-2 shadow-sm backdrop-blur-sm">
                        <div class="h-[300px] w-full rounded-xl bg-white/5 p-2 sm:h-[360px] lg:h-[420px]">
                            <img src="{{ $presidentImage }}" alt="{{ $page->title }}" class="h-full w-full object-contain">
                        </div>
                    </div>

                    <div>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-cyan-800 md:text-base">{{ $signatureTitle }}</p>
                            @if ($signatureName !== '')
                                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-950 md:text-3xl">{{ $signatureName }}</p>
                            @endif
                        </div>

                        <div class="prose mt-6 max-w-none prose-slate prose-p:leading-8">
                            {!! $presidentMessageHtml !!}
                        </div>

                        @if (! empty($socialLinks))
                            <div class="mt-6 flex flex-wrap items-center gap-2">
                                @foreach ($socialLinks as $social)
                                    <a
                                        href="{{ $social['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-cyan-200 bg-cyan-50 text-cyan-700 transition duration-300 hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-600 hover:text-white"
                                        title="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                        aria-label="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                    >
                                        <x-social-brand-icon :platform="$social['platform']" icon-class="h-4 w-4" />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        </section>
    @elseif ($page->slug === 'resmi-bilgiler' || $page->slug === 'resmi-belgiler')
        @php
            $settings = $siteSettings ?? \App\Models\Setting::current();
            $accounts = collect($bankAccounts ?? []);
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $mapsEmbedUrl = $meta['maps_embed_url'] ?? null;
            $donationPageUrl = $meta['donation_page_url'] ?? route('donations');
            $normalizedMapsUrl = null;
            $mapsDirectUrl = null;
            $mapsNeedsExternalOpen = false;
            if (filled($mapsEmbedUrl)) {
                $mapsEmbedUrl = trim((string) $mapsEmbedUrl);
                $mapsDirectUrl = $mapsEmbedUrl;
                if (\Illuminate\Support\Str::contains($mapsEmbedUrl, ['maps.app.goo.gl', 'goo.gl/maps'])) {
                    if (filled($settings->address)) {
                        $normalizedMapsUrl = 'https://www.google.com/maps?q=' . urlencode((string) $settings->address) . '&output=embed';
                    } else {
                        $mapsNeedsExternalOpen = true;
                    }
                } else {
                    $normalizedMapsUrl = \Illuminate\Support\Str::contains($mapsEmbedUrl, ['/maps/embed', 'output=embed'])
                        ? $mapsEmbedUrl
                        : 'https://www.google.com/maps?q=' . urlencode($mapsEmbedUrl) . '&output=embed';
                }
            }
            $officialAssocDesc = trim(html_entity_decode(strip_tags(
                (string) ($page->getLocalized('content')
                    ?: ($settings->site_description ?: __('app.page.official_assoc_desc')))
            )));
            $socialLinks = $settings->activeSocialLinks();
            $socialAria = [
                'instagram' => 'Instagram',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok',
                'facebook' => 'Facebook',
                'x' => 'X',
                'linkedin' => 'LinkedIn',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'website' => __('app.page.social_media_us'),
            ];
        @endphp

        <section class="mx-auto max-w-7xl px-4 py-10 md:px-6 lg:py-14">
            <h2 class="mb-8 text-center text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                {{ $settings->site_title ?? __('app.site.default_title') }}
            </h2>

            <div class="mx-auto grid max-w-6xl gap-6 md:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-7">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-cyan-800">{{ __('app.page.official_assoc_id') }}</h3>
                        <span class="text-2xl font-bold text-slate-200">01</span>
                    </div>
                    <p class="text-base leading-8 text-slate-600">
                        {{ $officialAssocDesc }}
                    </p>
                </article>

                @foreach ($accounts as $index => $account)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md md:p-7">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-cyan-800">{{ $account->currency }} {{ __('app.page.official_donation') }}</h3>
                            <span class="text-2xl font-bold text-slate-200">{{ str_pad((string) ($index + 2), 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="space-y-1.5 text-base text-slate-700">
                            <p><span class="font-semibold">{{ __('app.page.official_bank') }}</span> {{ $account->bank_name }}</p>
                            <p><span class="font-semibold">{{ __('app.page.official_account_name') }}</span> {{ $account->recipient_name }}</p>
                            @if (filled($account->account_number))
                                <p><span class="font-semibold">{{ __('app.page.official_account_no') }}</span> {{ $account->account_number }}</p>
                            @endif
                            <p class="break-all"><span class="font-semibold">{{ __('app.page.official_iban') }}</span> {{ $account->iban }}</p>
                        </div>
                        @if (filled($account->qr_image))
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                                <img src="{{ asset('storage/' . $account->qr_image) }}" alt="{{ $account->bank_name }} QR" class="mx-auto h-28 w-28 object-contain">
                            </div>
                        @endif
                        @if (filled($donationPageUrl))
                            <div class="mt-4">
                                <a href="{{ $donationPageUrl }}" target="_blank" class="inline-flex items-center rounded-full bg-cyan-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-800">
                                    {{ __('app.page.official_donate_btn') }}
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach

                @if (! empty($socialLinks))
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-7">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-cyan-800">{{ __('app.page.official_social') }}</h3>
                            <span class="text-2xl font-bold text-slate-200">{{ str_pad((string) ($accounts->count() + 2), 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($socialLinks as $social)
                                <a
                                    href="{{ $social['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-cyan-200 bg-cyan-50 text-cyan-700 transition hover:-translate-y-0.5 hover:bg-cyan-600 hover:text-white"
                                    title="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                    aria-label="{{ $socialAria[$social['platform']] ?? $social['platform'] }}"
                                >
                                    <x-social-brand-icon :platform="$social['platform']" icon-class="h-4 w-4" />
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endif

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-7">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-cyan-800">{{ __('app.page.official_visit') }}</h3>
                        <span class="text-2xl font-bold text-slate-200">{{ str_pad((string) ($accounts->count() + 3), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    @if (filled($normalizedMapsUrl))
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <iframe
                                src="{{ $normalizedMapsUrl }}"
                                class="h-64 w-full"
                                style="border:0;"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @elseif ($mapsNeedsExternalOpen && filled($mapsDirectUrl))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm leading-7 text-slate-600">
                                {{ __('app.page.official_map_hint') }}
                            </p>
                            <a
                                href="{{ $mapsDirectUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center rounded-full bg-cyan-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-cyan-800"
                            >
                                {{ __('app.page.official_map_open') }}
                            </a>
                        </div>
                    @else
                        <p class="text-sm leading-7 text-slate-600">{{ __('app.page.official_map_empty') }}</p>
                    @endif
                </article>
            </div>
        </section>
    @elseif ($page->slug === 'basin-kiti')
        @php
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $items = collect($meta['press_kit_items'] ?? [])
                ->filter(fn ($item) => filled(\App\Models\Page::localizedFromItem($item, 'title')) && filled($item['file'] ?? null))
                ->values();
            $siteSettings = \App\Models\Setting::current();
            $defaultLogo = $siteSettings->logo ? asset('storage/' . $siteSettings->logo) : asset('images/default-logo.svg');
            $pressIntro = $page->getLocalized('content', __('app.page.press_intro'));
        @endphp

        <section class="mx-auto max-w-7xl px-4 py-10 md:px-6 lg:py-14">
            @if (! empty($pressIntro))
                <div class="mx-auto mb-8 max-w-5xl rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center text-sm leading-7 text-slate-600 shadow-sm md:text-base">
                    {!! $pressIntro !!}
                </div>
            @endif

            @if ($items->isNotEmpty())
                <div class="mx-auto grid max-w-6xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        @php
                            $filePath = (string) $item['file'];
                            $fileUrl = asset('storage/' . ltrim($filePath, '/'));
                            $fileExt = strtoupper(pathinfo($filePath, PATHINFO_EXTENSION) ?: 'DOSYA');
                            $formatLabel = filled($item['format_label'] ?? null) ? strtoupper((string) $item['format_label']) : $fileExt;
                            $logo = ! empty($item['logo']) ? asset('storage/' . ltrim((string) $item['logo'], '/')) : $defaultLogo;
                            $itemTitle = \App\Models\Page::localizedFromItem($item, 'title');
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <img src="{{ $logo }}" alt="{{ $itemTitle }}" class="mx-auto h-16 w-auto object-contain">
                            <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $itemTitle }}</h3>
                            <p class="mt-3 text-sm font-medium text-slate-500">{{ __('app.page.press_format') }}</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">"{{ $formatLabel }}"</p>
                            <div class="my-5 h-px bg-slate-200"></div>
                            <a
                                href="{{ $fileUrl }}"
                                download
                                class="group inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-cyan-500 hover:bg-cyan-500 hover:text-white hover:shadow-[0_10px_20px_rgba(77,92,131,0.32)]"
                            >
                                {{ __('app.page.press_download') }}
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-y-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2.5a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.25A.75.75 0 0 1 10 2.5Z"/>
                                    <path d="M3.5 14.25a.75.75 0 0 1 .75.75v.5a1.25 1.25 0 0 0 1.25 1.25h9a1.25 1.25 0 0 0 1.25-1.25V15a.75.75 0 0 1 1.5 0v.5A2.75 2.75 0 0 1 14.5 18.25h-9A2.75 2.75 0 0 1 2.75 15.5V15a.75.75 0 0 1 .75-.75Z"/>
                                </svg>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <article class="card-ui">
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('app.page.press_empty_title') }}</h2>
                    <p class="mt-4 text-slate-600">{{ __('app.page.press_empty_desc') }}</p>
                </article>
            @endif
        </section>
    @elseif ($page->slug === 'yonetim')
        @php
            $meta = is_array($page->page_meta ?? null) ? $page->page_meta : [];
            $managementIntro = $page->getLocalized('content', __('app.page.management_intro'));
            $sections = collect($meta['management_sections'] ?? [])
                ->filter(fn ($section) => filled(\App\Models\Page::localizedFromItem($section, 'section_title') ?: ($section['section_title'] ?? null)))
                ->map(function ($section) {
                    $members = collect($section['members'] ?? [])
                        ->filter(fn ($member) => filled($member['name'] ?? null) && filled(
                            \App\Models\Page::localizedFromItem($member, 'role') ?: ($member['role'] ?? null)
                        ))
                        ->values();

                    $sectionTitle = \App\Models\Page::localizedFromItem($section, 'section_title', $section['section_title'] ?? '');

                    return [
                        'title' => \App\Models\Page::localizeManagementRole($sectionTitle),
                        'members' => $members,
                    ];
                })
                ->filter(fn ($section) => $section['members']->isNotEmpty())
                ->values();

            // Tek kişilik bölümleri yan yana grid'de topla; çoklu bölümleri ayrı tut.
            $blocks = [];
            $soloBuffer = [];
            foreach ($sections as $section) {
                if ($section['members']->count() === 1) {
                    $soloBuffer[] = $section;
                    continue;
                }
                if ($soloBuffer !== []) {
                    $blocks[] = ['type' => 'solo', 'sections' => $soloBuffer];
                    $soloBuffer = [];
                }
                $blocks[] = ['type' => 'group', 'section' => $section];
            }
            if ($soloBuffer !== []) {
                $blocks[] = ['type' => 'solo', 'sections' => $soloBuffer];
            }
        @endphp

        <section class="mx-auto max-w-7xl px-4 py-10 md:px-6 lg:py-14">
            @if (! empty($managementIntro))
                <div class="prose mx-auto mb-10 max-w-3xl text-center prose-slate md:mb-12">
                    {!! $managementIntro !!}
                </div>
            @endif

            @if ($sections->isNotEmpty())
                <div class="space-y-12 md:space-y-14">
                    @foreach ($blocks as $block)
                        @if ($block['type'] === 'solo')
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4">
                                @foreach ($block['sections'] as $section)
                                    @php
                                        $member = $section['members']->first();
                                        $memberPhoto = ! empty($member['photo']) ? \Illuminate\Support\Facades\Storage::url($member['photo']) : null;
                                        $displayPhoto = $memberPhoto ?: asset('images/default-logo.svg');
                                        $roleLabel = \App\Models\Page::localizeManagementRole(
                                            \App\Models\Page::localizedFromItem($member, 'role')
                                        ) ?: $section['title'];
                                    @endphp
                                    <article class="group relative overflow-hidden rounded-2xl border border-cyan-100 bg-gradient-to-b from-white to-cyan-50/40 p-5 text-center shadow-[0_10px_24px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-[0_18px_34px_rgba(77,92,131,0.18)]">
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 via-cyan-600 to-cyan-500"></div>
                                        <div class="mx-auto mt-1 flex h-28 w-28 items-center justify-center overflow-hidden rounded-full bg-cyan-50 ring-4 ring-white shadow-md sm:h-32 sm:w-32">
                                            <img
                                                src="{{ $displayPhoto }}"
                                                alt="{{ $member['name'] }}"
                                                class="h-full w-full {{ $memberPhoto ? 'object-cover' : 'object-contain p-4' }} transition duration-500 group-hover:scale-105"
                                                loading="lazy"
                                            >
                                        </div>
                                        <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">{{ $roleLabel }}</p>
                                        <h3 class="mt-1.5 text-lg font-bold tracking-tight text-slate-900">{{ $member['name'] }}</h3>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            @php $section = $block['section']; @endphp
                            <div>
                                <div class="mb-6 text-center md:mb-8">
                                    <h2 class="text-2xl font-extrabold uppercase tracking-wide text-cyan-950 md:text-3xl">
                                        {{ $section['title'] }}
                                    </h2>
                                    <div class="mx-auto mt-3 h-1 w-16 rounded-full bg-cyan-500/80"></div>
                                </div>

                                <div class="mx-auto grid max-w-6xl grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4">
                                    @foreach ($section['members'] as $member)
                                        @php
                                            $memberPhoto = ! empty($member['photo']) ? \Illuminate\Support\Facades\Storage::url($member['photo']) : null;
                                            $displayPhoto = $memberPhoto ?: asset('images/default-logo.svg');
                                            $roleLabel = \App\Models\Page::localizeManagementRole(
                                                \App\Models\Page::localizedFromItem($member, 'role', $member['role'] ?? '')
                                            );
                                        @endphp
                                        <article class="group relative overflow-hidden rounded-2xl border border-cyan-100 bg-gradient-to-b from-white to-cyan-50/40 p-5 text-center shadow-[0_10px_24px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-[0_18px_34px_rgba(77,92,131,0.18)]">
                                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 via-cyan-600 to-cyan-500"></div>
                                            <div class="mx-auto mt-1 flex h-28 w-28 items-center justify-center overflow-hidden rounded-full bg-cyan-50 ring-4 ring-white shadow-md sm:h-32 sm:w-32">
                                                <img
                                                    src="{{ $displayPhoto }}"
                                                    alt="{{ $member['name'] }}"
                                                    class="h-full w-full {{ $memberPhoto ? 'object-cover' : 'object-contain p-4' }} transition duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                >
                                            </div>
                                            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">{{ $roleLabel }}</p>
                                            <h3 class="mt-1.5 text-lg font-bold tracking-tight text-slate-900">{{ $member['name'] }}</h3>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <article class="card-ui">
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('app.page.management_empty_title') }}</h2>
                    <p class="mt-4 text-slate-600">{{ __('app.page.management_empty_desc') }}</p>
                </article>
            @endif
        </section>
    @else
        <section class="mx-auto max-w-4xl px-4 py-12 md:px-6">
            <article class="card-ui">
                <div class="prose mt-6 max-w-none prose-slate">{!! $page->getLocalized('content') !!}</div>
            </article>
        </section>
    @endif
</x-layouts.app>
