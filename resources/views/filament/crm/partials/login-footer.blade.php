<div class="bkd-login-back" wire:ignore>
    <form action="{{ url('/') }}" method="get" target="_top">
        <button type="submit" class="bkd-login-back__btn">{{ __('app.auth.crm.back_home') }}</button>
    </form>

    <form action="{{ url('/secder-panel/login') }}" method="get" target="_top" style="margin-top:.55rem;">
        <button type="submit" class="bkd-login-back__btn">{{ __('app.auth.crm.go_admin') }}</button>
    </form>
</div>
<div class="bkd-values" wire:ignore>
    <h4>{{ __('app.auth.crm.values_title') }}</h4>
    <ul>
        <li>{{ __('app.auth.crm.value_1') }}</li>
        <li>{{ __('app.auth.crm.value_2') }}</li>
        <li>{{ __('app.auth.crm.value_3') }}</li>
    </ul>
</div>
