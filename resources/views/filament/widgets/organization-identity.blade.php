@php
    $settings = \App\Models\Setting::current();
    $logoUrl = $settings->logo ? asset('storage/' . $settings->logo) : asset('images/default-logo.svg');
    $title = $settings->site_title ?: __('app.site.default_title');
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex; align-items:center; gap:16px; border-radius:16px; border:1px solid #d1dbec; background:linear-gradient(135deg, #f7f9fd 0%, #e8edf6 100%); padding:16px; box-shadow:0 12px 28px rgba(15, 23, 42, .08);">
            <img src="{{ $logoUrl }}" alt="Logo" style="width:68px; height:68px; border-radius:9999px; object-fit:cover; box-shadow:0 8px 18px rgba(77,92,131,.26); background:#fff; border:2px solid #e8edf6;">
            <div>
                <div style="font-weight:800; font-size:20px; color:#0f172a; line-height:1.2;">{{ $title }}</div>
                <div style="margin-top:4px; font-size:13px; color:#3f4c6b; font-weight:600;">{{ __('app.auth.admin.values_title') }}</div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

