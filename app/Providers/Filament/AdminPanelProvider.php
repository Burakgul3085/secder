<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Widgets\OrganizationIdentity;
use App\Filament\Widgets\OrganizationOverview;
use App\Filament\Widgets\SuspiciousActivityAlert;
use App\Http\Middleware\EnforceAdminOtpVerification;
use App\Http\Middleware\LogAdminNavigation;
use App\Models\Setting;
use App\Support\BrandPalette;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('bkd-panel')
            ->login(Login::class)
            ->brandName('Birlikte Kardeşlik Derneği')
            ->brandLogo(fn (): string => Setting::current()->logo
                ? asset('storage/' . Setting::current()->logo)
                : asset('images/default-logo.svg'))
            ->favicon(fn (): string => Setting::current()->favicon
                ? asset('storage/' . Setting::current()->favicon)
                : asset('images/default-logo.svg'))
            ->colors([
                'primary' => BrandPalette::shades(),
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                SuspiciousActivityAlert::class,
                AccountWidget::class,
                OrganizationIdentity::class,
                OrganizationOverview::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.admin.partials.login-style')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => view('filament.admin.partials.login-hero')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.admin.partials.login-values')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => request()->routeIs('filament.admin.auth.login')
                    ? view('filament.admin.partials.login-clock')->render()
                    : '',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => request()->routeIs('filament.admin.auth.login')
                    ? ''
                    : view('filament.admin.partials.file-upload-cancel-fix')->render(),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DispatchServingFilamentEvent::class,
                LogAdminNavigation::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnforceAdminOtpVerification::class,
            ]);
    }
}
