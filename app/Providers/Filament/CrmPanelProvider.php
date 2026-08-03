<?php

namespace App\Providers\Filament;

use App\Filament\Crm\Auth\Login;
use App\Filament\Crm\Pages\CrmDashboard;
use App\Models\Setting;
use App\Support\BrandPalette;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CrmPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Bu kısıt yalnızca CRM paneli için geçerlidir; diğer panellerde
        // silme yetkisi ilgili kaynağın kendi kurallarına bırakılır.
        $canDelete = function (): bool {
            if (Filament::getCurrentPanel()?->getId() !== 'crm') {
                return true;
            }

            return auth('crm')->check()
                && (auth('crm')->user()?->canDeleteRecords() ?? false);
        };

        DeleteAction::configureUsing(
            fn (DeleteAction $action): DeleteAction => $action->visible($canDelete),
        );

        DeleteBulkAction::configureUsing(
            fn (DeleteBulkAction $action): DeleteBulkAction => $action->visible($canDelete),
        );

        return $panel
            ->id('crm')
            ->path('secder-crm')
            ->login(Login::class)
            ->authGuard('crm')
            ->brandName('SECDER Bağış Yönetimi')
            ->brandLogo(fn (): string => Setting::current()->logo
                ? asset('storage/' . Setting::current()->logo)
                : asset('images/default-logo.svg'))
            ->brandLogoHeight('3.25rem')
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
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->navigationGroups([
                NavigationGroup::make('Bağış Kayıtları'),
                NavigationGroup::make('Liste Tanımları'),
                NavigationGroup::make('Notlar'),
                NavigationGroup::make('Afiş Yönetimi'),
                NavigationGroup::make('Raporlar'),
                NavigationGroup::make('Sistem'),
            ])
            ->discoverResources(in: app_path('Filament/Crm/Resources'), for: 'App\Filament\Crm\Resources')
            ->discoverPages(in: app_path('Filament/Crm/Pages'), for: 'App\Filament\Crm\Pages')
            ->pages([
                CrmDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Crm/Widgets'), for: 'App\Filament\Crm\Widgets')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.crm.partials.login-style')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => self::shouldLoadPosterAssets()
                    ? view('filament.crm.partials.poster-assets')->render()
                    : '',
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => view('filament.crm.partials.login-hero')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.crm.partials.login-footer')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => request()->routeIs('filament.crm.auth.login')
                    ? view('filament.crm.partials.login-clock')->render()
                    : '',
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // AuthenticateSession kaldırıldı: admin (web) oturumu CRM girişini bozabiliyordu
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private static function shouldLoadPosterAssets(): bool
    {
        if (! auth('crm')->check()) {
            return false;
        }

        $path = request()->path();

        if (str_contains($path, 'poster-templates') && str_contains($path, 'design')) {
            return true;
        }

        if (preg_match('#^secder-crm/donations(?:/\d+)?/edit$#', $path) || $path === 'secder-crm/donations/create') {
            return true;
        }

        return false;
    }
}
