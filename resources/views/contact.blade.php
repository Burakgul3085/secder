<x-layouts.app>
    <x-page-hero :title="__('app.contact.page_title')" />

    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
        <p class="mt-2 text-slate-600">{{ __('app.contact.page_desc') }}</p>

        @if (session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- E-posta formu --}}
            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('app.contact.email_form_title') }}</h2>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700">{{ __('app.contact.official_channel') }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="first_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.first_name') }}</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label for="last_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.last_name') }}</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                </div>

                <div>
                    <label for="message" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.your_message') }}</label>
                    <textarea id="message" name="message" rows="7" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn-primary">{{ __('app.contact.send_message') }}</button>
            </form>

            {{-- WhatsApp formu --}}
            @php
                $waGreeting  = __('app.contact.wa_greeting');
                $waNameLabel = __('app.contact.wa_name_label');
                $waEmailLbl  = __('app.contact.wa_email_label');
                $waMsgLabel  = __('app.contact.wa_msg_label');
                $waClosing   = __('app.contact.wa_closing');
            @endphp
            <form
                x-data="{ first_name: '', last_name: '', email: '', message: '' }"
                @submit.prevent="
                    const text = `{{ $waGreeting }}%0A%0A` +
                        `{{ $waNameLabel }}: ${first_name} ${last_name}%0A` +
                        `{{ $waEmailLbl }}: ${email}%0A%0A` +
                        `{{ $waMsgLabel }}:%0A${message}%0A%0A` +
                        `{{ $waClosing }}`;
                    window.open(`https://wa.me/905331524219?text=${text}`, '_blank');
                "
                class="space-y-4 rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-5 w-5 text-emerald-600" fill="currentColor" aria-hidden="true">
                            <path d="M19.11 17.21c-.26-.13-1.53-.76-1.77-.84-.24-.09-.41-.13-.58.13-.17.25-.67.84-.82 1.01-.15.17-.31.19-.57.06-.26-.13-1.09-.4-2.08-1.27-.77-.68-1.28-1.52-1.43-1.78-.15-.25-.02-.39.11-.52.12-.12.26-.31.39-.46.13-.15.17-.25.26-.42.09-.17.04-.32-.02-.45-.06-.13-.58-1.4-.8-1.92-.21-.5-.43-.43-.58-.44h-.5c-.17 0-.45.06-.69.32-.24.25-.9.88-.9 2.14s.92 2.49 1.04 2.66c.13.17 1.8 2.74 4.36 3.84.61.26 1.09.42 1.46.54.61.19 1.16.17 1.59.1.49-.07 1.53-.63 1.75-1.24.22-.61.22-1.13.15-1.24-.06-.11-.24-.17-.5-.29z"/>
                            <path d="M16.01 3.2c-7.05 0-12.78 5.73-12.78 12.78 0 2.25.59 4.45 1.7 6.39L3.2 28.8l6.61-1.73c1.88 1.03 4 1.58 6.2 1.58h.01c7.05 0 12.78-5.73 12.78-12.78 0-3.41-1.33-6.61-3.75-9.02A12.67 12.67 0 0 0 16.01 3.2zm0 23.3h-.01a10.4 10.4 0 0 1-5.3-1.45l-.38-.22-3.92 1.03 1.05-3.82-.25-.39a10.36 10.36 0 0 1-1.6-5.52c0-5.74 4.67-10.41 10.41-10.41 2.78 0 5.39 1.08 7.35 3.05a10.33 10.33 0 0 1 3.05 7.35c0 5.74-4.67 10.41-10.4 10.41z"/>
                        </svg>
                        {{ __('app.contact.wa_form_title') }}
                    </h2>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">+90 533 152 42 19</span>
                </div>

                <p class="text-sm text-slate-600">
                    {{ __('app.contact.wa_desc') }}
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="wa_first_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.first_name') }}</label>
                        <input id="wa_first_name" x-model="first_name" type="text" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="wa_last_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.last_name') }}</label>
                        <input id="wa_last_name" x-model="last_name" type="text" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                </div>

                <div>
                    <label for="wa_email" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.email') }}</label>
                    <input id="wa_email" x-model="email" type="email" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>

                <div>
                    <label for="wa_message" class="mb-1 block text-sm font-medium text-slate-700">{{ __('app.contact.your_message') }}</label>
                    <textarea id="wa_message" x-model="message" rows="7" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>

                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                        <path d="M19.11 17.21c-.26-.13-1.53-.76-1.77-.84-.24-.09-.41-.13-.58.13-.17.25-.67.84-.82 1.01-.15.17-.31.19-.57.06-.26-.13-1.09-.4-2.08-1.27-.77-.68-1.28-1.52-1.43-1.78-.15-.25-.02-.39.11-.52.12-.12.26-.31.39-.46.13-.15.17-.25.26-.42.09-.17.04-.32-.02-.45-.06-.13-.58-1.4-.8-1.92-.21-.5-.43-.43-.58-.44h-.5c-.17 0-.45.06-.69.32-.24.25-.9.88-.9 2.14s.92 2.49 1.04 2.66c.13.17 1.8 2.74 4.36 3.84.61.26 1.09.42 1.46.54.61.19 1.16.17 1.59.1.49-.07 1.53-.63 1.75-1.24.22-.61.22-1.13.15-1.24-.06-.11-.24-.17-.5-.29z"/>
                        <path d="M16.01 3.2c-7.05 0-12.78 5.73-12.78 12.78 0 2.25.59 4.45 1.7 6.39L3.2 28.8l6.61-1.73c1.88 1.03 4 1.58 6.2 1.58h.01c7.05 0 12.78-5.73 12.78-12.78 0-3.41-1.33-6.61-3.75-9.02A12.67 12.67 0 0 0 16.01 3.2zm0 23.3h-.01a10.4 10.4 0 0 1-5.3-1.45l-.38-.22-3.92 1.03 1.05-3.82-.25-.39a10.36 10.36 0 0 1-1.6-5.52c0-5.74 4.67-10.41 10.41-10.41 2.78 0 5.39 1.08 7.35 3.05a10.33 10.33 0 0 1 3.05 7.35c0 5.74-4.67 10.41-10.4 10.41z"/>
                    </svg>
                    {{ __('app.contact.wa_send') }}
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
