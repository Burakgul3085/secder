<?php

namespace App\Filament\Crm\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function mount(): void
    {
        // Sadece CRM guard — admin (web) oturumu CRM girişini engellemesin
        if (Auth::guard('crm')->check()) {
            $this->redirect(Filament::getUrl());

            return;
        }

        $this->form->fill();
    }

    public function getTitle(): string | Htmlable
    {
        return 'SECDER Bağış Girişi';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'SECDER CRM';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return new HtmlString('Bağış yönetim paneli — crm@secder.org ile giriş yapın');
    }
}
