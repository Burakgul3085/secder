<x-layouts.app>
    @php
        $activityTitle = $activity->getLocalized('title', $activity->title);
        $activityDescription = $activity->getLocalized('description', $activity->description);
        $activityContent = $activity->getLocalized('content', $activity->content);
        $activityDetailItems = $activity->getLocalizedDetailItems();
    @endphp
    <x-page-hero :title="$activityTitle" />

    @php
        $statusLabel = $activity->status === 'tamamlandi'
            ? __('app.page.activities_done')
            : __('app.page.activities_ongoing');
        $statusClass = $activity->status === 'tamamlandi'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-amber-200 bg-amber-50 text-amber-700';
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <img
                        src="{{ $activity->cover_image ? asset('storage/' . $activity->cover_image) : asset('images/default-logo.svg') }}"
                        alt="{{ $activityTitle }}"
                        class="mx-auto block h-auto max-h-[460px] w-full object-contain"
                    >
                </div>
                <h2 class="mt-6 text-3xl font-bold text-slate-900">{{ $activityTitle }}</h2>
                <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                @if (! is_null($activity->donation_amount))
                    <p class="mt-3 text-2xl font-extrabold text-cyan-800">
                        {{ number_format((float) $activity->donation_amount, 2, ',', '.') }} {{ $activity->donation_currency ?: 'TL' }}
                    </p>
                @endif
                <p class="mt-3 text-lg leading-relaxed text-slate-700">{{ $activityDescription }}</p>
                <div class="prose prose-slate mt-6 max-w-none text-base leading-relaxed">
                    {!! $activityContent ?: nl2br(e((string) $activityDescription)) !!}
                </div>

                <div class="mt-8">
                    <a
                        href="{{ route('donations') }}"
                        class="inline-flex items-center rounded-full bg-cyan-600 px-6 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-cyan-700"
                    >
                        {{ __('app.page.donate_btn') }}
                    </a>
                </div>

                @php
                    $galleryImages = collect($activity->galleryImagePaths())->filter()->values();
                    $galleryVideos = collect($activity->galleryVideoPaths())->filter()->values();
                @endphp
                @if ($galleryImages->isNotEmpty() || $galleryVideos->isNotEmpty())
                    <div class="mt-10" x-data="{ previewOpen: false, previewType: null, previewSrc: '' }">
                        <h3 class="text-xl font-bold text-slate-900">{{ __('app.page.activity_gallery') }}</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($galleryImages as $image)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 text-left transition hover:border-cyan-300 hover:shadow-md"
                                    @click="previewOpen = true; previewType = 'image'; previewSrc = '{{ asset('storage/' . ltrim((string) $image, '/')) }}'"
                                >
                                    <img
                                        src="{{ asset('storage/' . ltrim((string) $image, '/')) }}"
                                        alt="{{ $activityTitle }} {{ __('app.page.activity_image_alt') }}"
                                        class="mx-auto block h-auto max-h-[280px] w-full object-contain"
                                    >
                                </button>
                            @endforeach
                            @foreach ($galleryVideos as $video)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 text-left transition hover:border-cyan-300 hover:shadow-md"
                                    @click="previewOpen = true; previewType = 'video'; previewSrc = '{{ asset('storage/' . ltrim((string) $video, '/')) }}'"
                                >
                                    <video controls class="h-auto max-h-[280px] w-full rounded-lg bg-black/90 object-contain">
                                        <source src="{{ asset('storage/' . ltrim((string) $video, '/')) }}">
                                        {{ __('app.page.video_not_supported') }}
                                    </video>
                                </button>
                            @endforeach
                        </div>

                        <div
                            x-show="previewOpen"
                            x-cloak
                            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/75 p-4"
                            @click.self="previewOpen = false; previewSrc = ''; previewType = null"
                            @keydown.escape.window="previewOpen = false; previewSrc = ''; previewType = null"
                        >
                            <div class="relative w-full max-w-5xl rounded-2xl border border-slate-700 bg-slate-900 p-3 shadow-2xl">
                                <button
                                    type="button"
                                    class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25"
                                    @click="previewOpen = false; previewSrc = ''; previewType = null"
                                    aria-label="{{ __('app.page.close_preview') }}"
                                >✕</button>

                                <template x-if="previewType === 'image' && previewSrc">
                                    <img :src="previewSrc" alt="{{ __('app.page.activity_gallery_image') }}" class="mx-auto block h-auto max-h-[78vh] w-full rounded-xl object-contain" />
                                </template>
                                <template x-if="previewType === 'video' && previewSrc">
                                    <video controls autoplay class="h-auto max-h-[78vh] w-full rounded-xl bg-black object-contain">
                                        <source :src="previewSrc">
                                    </video>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-10 space-y-3" x-data="{ openIdx: 1 }">
                    @foreach($activityDetailItems as $idx => $acc)
                        <div class="overflow-hidden rounded-2xl border border-cyan-200/80 bg-white">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between px-5 py-4 text-left"
                                @click="openIdx = openIdx === {{ $idx }} ? -1 : {{ $idx }}"
                            >
                                <span class="text-xl font-semibold text-slate-900">{{ $acc['title'] }}</span>
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-cyan-500 text-lg text-white" x-text="openIdx === {{ $idx }} ? '−' : '+'"></span>
                            </button>
                            <div x-show="openIdx === {{ $idx }}" x-collapse class="border-t border-cyan-100 px-5 py-4">
                                <p class="text-base leading-relaxed text-slate-700">{{ $acc['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <aside class="space-y-4">
                @foreach($bankAccounts as $account)
                    <a href="{{ route('donations') }}" class="block rounded-2xl border border-cyan-100 bg-cyan-50/40 p-4 shadow-sm transition hover:border-cyan-300 hover:shadow-md">
                        <p class="text-xl font-bold uppercase text-slate-900">{{ __('app.page.donate_btn') }} | IBAN ({{ $account->currency }})</p>
                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white p-3">
                            <img src="{{ asset('storage/' . $donationQrPath) }}" alt="{{ __('app.page.donation_qr_alt') }}" class="h-56 w-full rounded-lg object-contain">
                        </div>
                    </a>
                @endforeach

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('app.page.other_activities') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($relatedActivities as $related)
                            <a href="{{ route('activities.show', ['slug' => $related->slug]) }}" class="block rounded-xl border border-slate-100 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50/40 hover:text-cyan-800">
                                {{ $related->title }}
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('app.page.no_other_activities') }}</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
