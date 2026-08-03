<x-layouts.app>
    <x-page-hero :title="__('app.home.activities_title')" />

    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
        <p class="mx-auto mb-8 max-w-4xl text-center text-base leading-relaxed text-slate-600 md:text-lg">
            {{ __('app.page.activities_intro') }}
        </p>

        {{-- Filtre Alanı --}}
        <form method="GET" action="{{ route('activities.index') }}" class="mb-8">
            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:gap-4">

                {{-- Arama --}}
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="{{ __('app.page.activities_search') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100"
                    >
                </div>

                {{-- Durum --}}
                <div class="flex gap-2">
                    @foreach ([
                        '' => __('app.page.activities_all'),
                        'devam_ediyor' => __('app.page.activities_ongoing'),
                        'tamamlandi' => __('app.page.activities_done'),
                    ] as $val => $label)
                        <button
                            type="submit"
                            name="status"
                            value="{{ $val }}"
                            class="rounded-full border px-4 py-2 text-xs font-semibold transition
                                {{ $filters['status'] === $val
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
                    <option value="default"     {{ $filters['sort'] === 'default'     ? 'selected' : '' }}>{{ __('app.page.activities_sort_default') }}</option>
                    <option value="amount_asc"  {{ $filters['sort'] === 'amount_asc'  ? 'selected' : '' }}>{{ __('app.page.activities_sort_asc') }}</option>
                    <option value="amount_desc" {{ $filters['sort'] === 'amount_desc' ? 'selected' : '' }}>{{ __('app.page.activities_sort_desc') }}</option>
                </select>

                {{-- Filtreyi Temizle --}}
                @if($filters['q'] || $filters['status'] || $filters['sort'] !== 'default')
                    <a
                        href="{{ route('activities.index') }}"
                        class="flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('app.page.activities_clear') }}
                    </a>
                @endif
            </div>

            {{-- Sonuç sayısı --}}
            <p class="mt-3 text-sm text-slate-500">
                <span class="font-semibold text-cyan-700">{{ $activities->count() }}</span> {{ __('app.page.activities_count') }}
            </p>
        </form>

        {{-- Kart Grid --}}
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($activities as $activity)
                @php
                    $statusLabel = $activity->status === 'tamamlandi'
                        ? __('app.page.activities_done')
                        : __('app.page.activities_ongoing');
                    $statusClass = $activity->status === 'tamamlandi'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-amber-200 bg-amber-50 text-amber-700';
                @endphp
                <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-cyan-300 hover:shadow-[0_18px_34px_rgba(14,116,144,0.18)]">
                    <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}" class="block p-4">
                        <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <img
                                src="{{ $activity->cover_image ? asset('storage/' . $activity->cover_image) : asset('images/default-logo.svg') }}"
                                alt="{{ $activity->title }}"
                                class="mx-auto block h-auto max-h-[250px] w-full object-contain transition-transform duration-500 group-hover:scale-[1.02]"
                            >
                        </div>
                    </a>
                    <div class="px-5 pb-5">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        <h2 class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-cyan-700">{{ $activity->title }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600 transition-colors duration-300 group-hover:text-cyan-900">
                            {{ \Illuminate\Support\Str::limit($activity->description ?: strip_tags((string) $activity->content), 180) }}
                        </p>
                        @if (! is_null($activity->donation_amount))
                            <p class="mt-4 text-lg font-extrabold text-cyan-800">
                                {{ number_format((float) $activity->donation_amount, 2, ',', '.') }} {{ $activity->donation_currency ?: 'TL' }}
                            </p>
                        @endif
                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('donations') }}" class="inline-flex items-center rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">{{ __('app.page.donate_btn') }}</a>
                            <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}" class="inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-900">{{ __('app.page.activity_detail') }}</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-3 py-16 text-center">
                    <svg class="mx-auto mb-4 h-14 w-14 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-semibold text-slate-500">{{ __('app.page.activities_empty') }}</p>
                    <a href="{{ route('activities.index') }}" class="mt-4 inline-flex items-center rounded-full bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">{{ __('app.page.activities_view_all') }}</a>
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
