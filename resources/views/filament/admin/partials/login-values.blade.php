<div class="bkd-login-back" wire:ignore>
    <form action="{{ url('/') }}" method="get" target="_top">
        <button type="submit" class="bkd-login-back__btn">{{ __('app.auth.admin.back_home') }}</button>
    </form>

    <form action="{{ url('/secder-crm/login') }}" method="get" target="_top" style="margin-top:.55rem;">
        <button type="submit" class="bkd-login-back__btn">{{ __('app.auth.admin.go_crm') }}</button>
    </form>

    <form action="{{ url('/secder-panel/sifremi-unuttum') }}" method="get" target="_top" style="margin-top:.35rem; text-align:center;">
        <button type="submit" class="bkd-login-forgot__btn" style="background:none;border:0;cursor:pointer;padding:0;">{{ __('app.auth.admin.forgot_password') }}</button>
    </form>
</div>
<div class="bkd-values" wire:ignore>
    <h4>{{ __('app.auth.admin.values_title') }}</h4>
    <ul>
        <li>{{ __('app.auth.admin.value_1') }}</li>
        <li>{{ __('app.auth.admin.value_2') }}</li>
        <li>{{ __('app.auth.admin.value_3') }}</li>
    </ul>
</div>
