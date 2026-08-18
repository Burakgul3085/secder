<?php

namespace App\Providers;

use App\Models\BankAccount;
use App\Models\ContactMessage;
use App\Models\HeroSlide;
use App\Models\MenuItem;
use App\Models\NewsletterSubscriber;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Models\VolunteerApplication;
use App\Observers\AuditableObserver;
use App\Support\AdminActivity;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Export::polymorphicUserRelationship(true);

        Project::observe(AuditableObserver::class);
        News::observe(AuditableObserver::class);
        BankAccount::observe(AuditableObserver::class);
        ContactMessage::observe(AuditableObserver::class);
        VolunteerApplication::observe(AuditableObserver::class);
        Page::observe(AuditableObserver::class);
        HeroSlide::observe(AuditableObserver::class);
        MenuItem::observe(AuditableObserver::class);
        Setting::observe(AuditableObserver::class);
        User::observe(AuditableObserver::class);
        NewsletterSubscriber::observe(AuditableObserver::class);

        Event::listen(Login::class, function (Login $event): void {
            $userId = $event->user instanceof User ? $event->user->id : null;

            AdminActivity::log(
                'login',
                'Admin panele giriş yaptı',
                $userId,
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $userId = $event->user instanceof User ? $event->user->id : null;

            AdminActivity::log(
                'logout',
                'Admin panelden Çıkış yaptı',
                $userId,
            );
        });

        View::composer('*', function ($view) {
            $view->with('siteSettings', Setting::current());
            $view->with('menuItems', MenuItem::query()->active()->orderBy('sort_order')->get());
            $view->with('footerMenuQuick', collect());
            $view->with('footerMenuActivities', collect());
        });
    }
}
