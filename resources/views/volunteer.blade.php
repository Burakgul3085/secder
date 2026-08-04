<x-layouts.app>
    <x-page-hero :title="__('app.volunteer.page_title')" />

    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6" x-data="{ policyModal: null }">
        @php
            $kvkkText = __('app.legal.kvkk_content');
            $volunteerClarificationText = __('app.legal.clarification_content');

            $kvkkModalTitle = __('app.volunteer.kvkk_modal_title');
            $volModalTitle  = __('app.volunteer.vol_modal_title');
        @endphp

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl border border-emerald-200 bg-[#f2fbf7] p-6 md:p-8">
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $siteSettings->site_title }}
                </span>

                <h2 class="mt-4 text-3xl font-bold leading-tight text-slate-900">
                    {{ __('app.volunteer.heading_line1') }}
                    <br class="hidden md:block">
                    {{ __('app.volunteer.heading_line2') }}
                </h2>
                <p class="mt-3 text-slate-600">
                    {{ __('app.volunteer.desc') }}
                </p>
                <p class="mt-2 text-sm text-slate-500">{{ __('app.volunteer.soon') }}</p>

                @if (session('success'))
                    <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('volunteer.submit') }}" class="mt-6 space-y-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="first_name" type="text" value="{{ old('first_name') }}" placeholder="{{ __('app.volunteer.placeholder_name') }}" required class="w-full rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('app.volunteer.placeholder_email') }}" required class="w-full rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <input name="last_name" type="text" value="{{ old('last_name') }}" placeholder="{{ __('app.volunteer.placeholder_lastname') }}" required class="w-full rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <input name="phone" type="text" value="{{ old('phone') }}" placeholder="{{ __('app.volunteer.placeholder_phone') }}" required class="w-full rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <select name="preference" required class="w-full rounded-full border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">{{ __('app.volunteer.preference_default') }}</option>
                        @foreach(($volunteerPreferenceOptions ?? []) as $option)
                            <option value="{{ $option }}" @selected(old('preference') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>

                    <textarea name="about" rows="7" placeholder="{{ __('app.volunteer.placeholder_about') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">{{ old('about') }}</textarea>

                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" required class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>
                            <button type="button" @click="policyModal = 'kvkk'" class="font-semibold text-emerald-700 underline underline-offset-2 hover:text-emerald-800">
                                {{ __('app.volunteer.kvkk_link') }}
                            </button>
                            {{ __('app.volunteer.kvkk_read') }}
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" required class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>
                            <button type="button" @click="policyModal = 'volunteer'" class="font-semibold text-emerald-700 underline underline-offset-2 hover:text-emerald-800">
                                {{ __('app.volunteer.volunteer_link') }}
                            </button>
                            {{ __('app.volunteer.volunteer_read') }}
                        </span>
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        {{ __('app.volunteer.submit') }}
                    </button>
                </form>
            </div>

            <aside class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-6 text-slate-700">
                    <div class="border-b border-slate-100 pb-5 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 text-cyan-700">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-7 w-7"><path d="M2 3.75A1.75 1.75 0 0 1 3.75 2h2.31c.83 0 1.54.58 1.7 1.39l.39 1.98a1.75 1.75 0 0 1-.5 1.57l-1.1 1.1a13.13 13.13 0 0 0 5.4 5.4l1.1-1.1a1.75 1.75 0 0 1 1.57-.5l1.98.4A1.75 1.75 0 0 1 18 13.94v2.31A1.75 1.75 0 0 1 16.25 18h-.75C8.6 18 2 11.4 2 3.75Z"/></svg>
                        </div>
                        <p class="text-xl font-bold text-slate-900">{{ __('app.volunteer.phone_label') }}</p>
                        <p class="mt-1">{{ $siteSettings->phone ?: '-' }}</p>
                    </div>

                    <div class="border-b border-slate-100 pb-5 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 text-cyan-700">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-7 w-7"><path d="M2.94 5.5A2 2 0 0 1 4.8 4h10.4a2 2 0 0 1 1.86 1.5L10 9.88 2.94 5.5Z"/><path d="M2.8 7.25V14a2 2 0 0 0 2 2h10.4a2 2 0 0 0 2-2V7.25l-6.69 4.15a1 1 0 0 1-1.02 0L2.8 7.25Z"/></svg>
                        </div>
                        <p class="text-xl font-bold text-slate-900">{{ __('app.volunteer.email_label') }}</p>
                        <p class="mt-1 break-all">{{ $siteSettings->email ?: '-' }}</p>
                    </div>

                    <div class="text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50 text-cyan-700">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-7 w-7"><path fill-rule="evenodd" d="M10 2.5a5.5 5.5 0 0 0-5.5 5.5c0 4.3 4.65 8.76 5.03 9.12a.7.7 0 0 0 .94 0c.38-.36 5.03-4.82 5.03-9.12A5.5 5.5 0 0 0 10 2.5Zm0 7.25a1.75 1.75 0 1 1 0-3.5 1.75 1.75 0 0 1 0 3.5Z" clip-rule="evenodd"/></svg>
                        </div>
                        <p class="text-xl font-bold text-slate-900">{{ __('app.volunteer.address_label') }}</p>
                        <p class="mt-1">{{ $siteSettings->address ?: '-' }}</p>
                    </div>
                </div>
            </aside>
        </div>

        {{-- KVKK / Gönüllü Aydınlatma Metni Modalı --}}
        <div x-show="policyModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;">
            <div @click.outside="policyModal = null" class="max-h-[85vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <h3 class="text-xl font-bold text-slate-900"
                        x-text="policyModal === 'kvkk' ? '{{ $kvkkModalTitle }}' : '{{ $volModalTitle }}'">
                    </h3>
                    <button type="button" @click="policyModal = null" class="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        {{ __('app.volunteer.close') }}
                    </button>
                </div>

                <div x-show="policyModal === 'kvkk'" class="space-y-3 text-sm leading-6 text-slate-700">
                    {!! nl2br(e($kvkkText)) !!}
                </div>

                <div x-show="policyModal === 'volunteer'" class="space-y-3 text-sm leading-6 text-slate-700">
                    {!! nl2br(e($volunteerClarificationText)) !!}
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
