<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    public function mount(): void
    {
        session()->forget([
            'admin_otp_code_hash',
            'admin_otp_expires_at',
            'admin_otp_user_id',
            'admin_otp_pending_nonce',
            'admin_otp_verified_nonce',
            'admin_otp_intended_url',
            'admin_otp_email_hint',
        ]);
        session()->put('admin_otp_login_nonce', Str::uuid()->toString());

        parent::mount();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Yönetim Girişi';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'SECDER';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return new HtmlString('Selahaddin Eyyubi Cami Derneği yönetim paneli');
    }
}
