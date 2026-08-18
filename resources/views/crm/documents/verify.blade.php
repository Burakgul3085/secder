@php
    $logoSrc = $siteSettings->logo ? asset('storage/' . $siteSettings->logo) : asset('images/default-logo.svg');
    $siteTitle = $siteSettings->site_title ?? __('app.site.default_title');
    $description = trim((string) ($donation->description ?? ''));
    $descriptionLong = strlen($description) > 180;
    $receiptNumber = filled($donation->receipt_number) ? $donation->receipt_number : $donation->donation_number;
@endphp

<x-layouts.app>
    <section class="bg-gradient-to-b from-slate-100 via-slate-50 to-white py-10 md:py-16">
        <div class="mx-auto max-w-4xl px-4">
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-300/30">
                {{-- Üst başlık --}}
                <div class="verify-hero relative overflow-hidden px-6 py-8 md:px-10 md:py-9">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-10 h-40 w-40 rounded-full bg-cyan-400/20 blur-2xl"></div>

                    <div class="relative flex flex-col items-center gap-5 text-center md:flex-row md:items-center md:text-left">
                        <div class="relative shrink-0">
                            <img
                                src="{{ $logoSrc }}"
                                alt="{{ $siteTitle }}"
                                class="h-20 w-20 rounded-full object-cover ring-4 ring-white/30 shadow-xl"
                            >
                            <span class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg ring-2 ring-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100/90">{{ $siteTitle }}</p>
                            <h1 class="mt-2 font-serif text-2xl font-semibold leading-tight text-white md:text-3xl">{{ __('app.verify.heading') }}</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-cyan-50/95">
                                {{ __('app.verify.intro') }}
                            </p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs text-white/90 backdrop-blur-sm">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('app.verify.verified_at') }} {{ now()->format('d.m.Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 p-6 md:p-8 lg:p-10">
                    {{-- Tutar --}}
                    <div class="verify-amount-box">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('app.verify.amount') }}</p>
                        <p class="mt-3 font-serif text-4xl font-bold tabular-nums text-cyan-800 md:text-5xl">
                            {{ number_format((float) $donation->amount, 2, ',', '.') }}
                            <span class="text-2xl font-semibold text-cyan-600 md:text-3xl">{{ $donation->currency }}</span>
                        </p>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-sm text-slate-600">
                            <span class="rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200">
                                {{ __('app.verify.donation_date') }} <strong class="text-slate-800">{{ $donation->donated_at?->format('d.m.Y') ?? '-' }}</strong>
                            </span>
                            <span class="rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200">
                                {{ __('app.verify.receipt_no_short') }} <strong class="text-slate-800">{{ $receiptNumber }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 md:items-stretch">
                        {{-- Bağış bilgileri --}}
                        <div class="verify-info-panel">
                            <h2 class="verify-section-title">
                                <span class="verify-section-icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                {{ __('app.verify.donation_section') }}
                            </h2>
                            <dl class="mt-3 flex-1">
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.donation_no') }}</dt>
                                    <dd class="verify-info-value">{{ $donation->donation_number }}</dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.receipt_no') }}</dt>
                                    <dd class="verify-info-value">{{ $receiptNumber }}</dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.donor') }}</dt>
                                    <dd class="verify-info-value">{{ $donation->donor?->full_name ?? '-' }}</dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.donation_type') }}</dt>
                                    <dd class="verify-info-value">{{ $donation->donationType?->name ?? '-' }}</dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.payment_type') }}</dt>
                                    <dd class="verify-info-value">{{ $donation->paymentMethod?->name ?? '-' }}</dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.project') }}</dt>
                                    <dd class="verify-info-value">{{ $donation->project?->title ?? '-' }}</dd>
                                </div>
                            </dl>

                            @if ($description !== '')
                                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-4" x-data="{ expanded: false }">
                                    <p class="verify-section-title !mb-2">{{ __('app.verify.description') }}</p>
                                    <p
                                        class="text-sm leading-relaxed text-slate-700 whitespace-pre-line"
                                        :class="expanded ? '' : 'line-clamp-4'"
                                    >{{ $description }}</p>
                                    @if ($descriptionLong)
                                        <button
                                            type="button"
                                            @click="expanded = !expanded"
                                            class="mt-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800 transition"
                                            x-text="expanded ? @js(__('app.verify.show_less')) : @js(__('app.verify.show_more'))"
                                        ></button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Belge bilgileri --}}
                        <div class="verify-info-panel">
                            <h2 class="verify-section-title">
                                <span class="verify-section-icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                {{ __('app.verify.document_section') }}
                            </h2>
                            <dl class="mt-3 flex-1">
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.document_type') }}</dt>
                                    <dd class="verify-info-value">{{ $document->type_label }}</dd>
                                </div>
                                <div class="verify-info-row !block">
                                    <dt class="verify-info-label mb-2">{{ __('app.verify.verification_code') }}</dt>
                                    <dd>
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                            <code class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-center font-mono text-sm font-bold tracking-wide text-slate-800 sm:text-left">{{ $document->verification_code }}</code>
                                            <button
                                                type="button"
                                                data-copy-label="{{ __('app.verify.copy') }}"
                                                data-copied-label="{{ __('app.verify.copied') }}"
                                                onclick="navigator.clipboard.writeText('{{ $document->verification_code }}'); const el = this.querySelector('[data-label]'); const copied = this.dataset.copiedLabel; const copy = this.dataset.copyLabel; el.textContent = copied; setTimeout(() => el.textContent = copy, 2000)"
                                                class="verify-btn-outline !px-4 !py-2.5 text-xs"
                                            >
                                                <span data-label>{{ __('app.verify.copy') }}</span>
                                            </button>
                                        </div>
                                    </dd>
                                </div>
                                <div class="verify-info-row">
                                    <dt class="verify-info-label">{{ __('app.verify.created_at') }}</dt>
                                    <dd class="verify-info-value">{{ $document->generated_at?->format('d.m.Y H:i') ?? '-' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                                <div class="flex gap-3">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <p class="text-xs leading-relaxed text-emerald-900">
                                        {{ __('app.verify.security_note') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksiyon çubuğu --}}
                <div class="border-t border-slate-100 bg-slate-50/90 px-6 py-6 md:px-10">
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <a href="{{ $document->public_download_url }}" class="verify-btn-download">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ __('app.verify.download_pdf') }}
                        </a>
                        <a href="{{ route('home') }}" class="verify-btn-outline">
                            {{ __('app.verify.back_home') }}
                        </a>
                    </div>

                    @if (filled($siteSettings->phone) || filled($siteSettings->email))
                        <p class="mt-5 text-center text-xs text-slate-500">{{ __('app.verify.page_note') }}</p>
                        <div class="mt-2 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-slate-600">
                            @if (filled($siteSettings->phone))
                                <a href="tel:{{ preg_replace('/\s+/', '', $siteSettings->phone) }}" class="inline-flex items-center gap-1.5 text-slate-600 no-underline transition hover:text-cyan-700">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $siteSettings->phone }}
                                </a>
                            @endif
                            @if (filled($siteSettings->email))
                                <a href="mailto:{{ $siteSettings->email }}" class="inline-flex items-center gap-1.5 text-slate-600 no-underline transition hover:text-cyan-700">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $siteSettings->email }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
