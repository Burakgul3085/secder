<x-layouts.app>
    <x-page-hero :title="__('app.donations.page_title')" />

    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
        <p class="mt-2 text-slate-600">{{ __('app.donations.page_desc') }}</p>

        @if ($presetAmount || $presetNote)
            <div class="mt-6 rounded-2xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-white p-5 shadow-sm">
                <p class="text-sm font-bold text-cyan-900">{{ __('app.donations.zakat_preset_title') }}</p>
                <p class="mt-2 text-sm text-cyan-800">{{ __('app.donations.zakat_preset_desc') }}</p>
                <div class="mt-4 rounded-xl border border-cyan-100 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.donations.zakat_preset_example_label') }}</p>
                    <p class="mt-1 font-mono text-sm font-medium text-slate-900" dir="ltr">
                        @if ($presetAmount)
                            {{ number_format($presetAmount, 0, ',', '.') }} TL
                        @endif
                        @if ($presetAmount && $presetNote) – @endif
                        @if ($presetNote)
                            {{ $presetNote }}
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 200)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative mt-6 overflow-hidden rounded-2xl border-2 border-cyan-200 bg-gradient-to-br from-cyan-50 to-white p-5 shadow-sm"
        >
            {{-- Parlama animasyonu --}}
            <div class="pointer-events-none absolute inset-0 -translate-x-full animate-[shimmer_2.5s_ease-in-out_1.5s_1_forwards] bg-gradient-to-r from-transparent via-white/40 to-transparent"></div>

            {{-- Üst köşe vurgu çizgisi --}}
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-cyan-500 via-cyan-600 to-cyan-500"></div>

            <div class="flex items-start gap-4">
                {{-- Zil ikonu animasyonlu --}}
                <div class="flex-shrink-0 animate-bounce rounded-full bg-cyan-600 p-2.5 shadow-md shadow-cyan-900/15">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>

                <div class="flex-1">
                    <p class="text-base font-bold text-cyan-900">{{ __('app.donations.notice_title') }}</p>
                    <p class="mt-1 text-sm text-cyan-800">
                        {{ __('app.donations.notice_body') }} <strong class="underline decoration-cyan-500 decoration-2">{{ __('app.donations.notice_bold') }}</strong>
                        {{ __('app.donations.notice_suffix') }}
                    </p>
                    <ul class="mt-3 space-y-1.5 text-sm text-cyan-800">
                        <li class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">1</span>
                            <span><strong>{{ __('app.donations.item_name') }}</strong> — {{ __('app.donations.item_name_desc') }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">2</span>
                            <span><strong>{{ __('app.donations.item_phone') }}</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">3</span>
                            <span><strong>{{ __('app.donations.item_purpose') }}</strong> — {{ __('app.donations.item_purpose_desc') }}</span>
                        </li>
                    </ul>
                    <div class="mt-3 rounded-xl border border-cyan-100 bg-white px-4 py-2.5">
                        <p class="text-xs font-semibold text-cyan-700">{{ __('app.donations.example_label') }}</p>
                        <p class="mt-0.5 font-mono text-sm font-medium text-slate-800" dir="ltr">{{ __('app.donations.example_text') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes shimmer {
                0%   { transform: translateX(-100%); }
                100% { transform: translateX(200%); }
            }
        </style>

        <div class="mt-6 space-y-5">
            @forelse($bankAccounts as $account)
                <article class="rounded-3xl border border-cyan-100 bg-gradient-to-br from-cyan-50/50 to-white p-6 shadow-sm" x-data="{ copied: false }">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="space-y-3 lg:col-span-2">
                            <span class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-700">{{ $account->currency }}</span>

                            <p class="text-2xl font-bold text-slate-900">
                                {{ __('app.donations.account_name') }} <span class="font-semibold">{{ $account->recipient_name }}</span>
                            </p>

                            <p class="text-xl font-semibold text-slate-900">
                                {{ __('app.donations.branch_name') }}
                                <span class="font-medium">
                                    {{ $account->branch_name ?: __('app.donations.branch_suffix', ['bank' => $account->bank_name]) }}
                                </span>
                            </p>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-lg font-semibold text-slate-900">{{ __('app.donations.iban_label') }} <span class="font-mono">{{ $account->iban }}</span></p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ __('app.donations.account_no') }} <span class="font-medium">{{ $account->account_number ?: '-' }}</span></p>
                            </div>

                            @php $ibanCopied = __('app.donations.iban_copied'); @endphp
                            <button @click="navigator.clipboard.writeText('{{ $account->iban }}'); copied = true; setTimeout(() => copied = false, 2000)" class="btn-primary w-full max-w-sm">
                                {{ __('app.donations.iban_copy_btn') }}
                            </button>
                            <p x-show="copied" class="text-xs text-emerald-600">{{ __('app.donations.iban_copied') }}</p>
                        </div>

                        <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="mb-3 text-sm font-semibold text-slate-700">{{ __('app.donations.qr_scan') }}</p>
                            <img src="{{ asset('storage/' . $donationQrPath) }}" alt="{{ __('app.donations.page_title') }}" class="h-44 w-44 rounded-xl border border-slate-200 object-cover">
                            <p class="mt-3 text-center text-xs text-slate-500">{{ __('app.donations.qr_note') }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state :title="__('app.donations.empty_title')" :description="__('app.donations.empty_desc')" />
            @endforelse
        </div>
    </section>
</x-layouts.app>
