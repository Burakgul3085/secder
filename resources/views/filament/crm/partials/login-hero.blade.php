@php
    $settings = \App\Models\Setting::current();
    $logoUrl = $settings->logo ? asset('storage/' . $settings->logo) : asset('images/default-logo.svg');
@endphp
<div class="bkd-login-hero">
    <div class="bkd-login-hero__row">
        <div class="bkd-login-hero__logo-wrap">
            <img src="{{ $logoUrl }}" alt="{{ __('app.auth.crm.logo_alt') }}" class="bkd-login-hero__logo">
        </div>
        <div class="bkd-login-hero__copy">
            <p class="bkd-login-hero__eyebrow">{{ __('app.auth.crm.logo_alt') }}</p>
            <p class="bkd-login-hero__title">{{ __('app.auth.crm.welcome') }}</p>
            <p class="bkd-login-hero__subtitle">{{ __('app.auth.crm.welcome_sub') }}</p>
        </div>
    </div>
</div>
