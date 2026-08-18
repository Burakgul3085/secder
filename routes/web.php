<?php

use App\Http\Controllers\ZakatController;
use App\Http\Controllers\IslamicFinanceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\CrmDocumentController;
use App\Http\Controllers\Crm\PosterController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\AdminOtpController;
use App\Http\Controllers\AdminForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Dil değiştirme rotası
Route::get('/locale/{lang}', function (string $lang) {
    $allowed = ['tr', 'en', 'ar'];
    if (! in_array($lang, $allowed, true)) {
        $lang = 'tr';
    }

    $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
    $previous = url()->previous(route('home'));
    $previousHost = parse_url($previous, PHP_URL_HOST);
    $redirect = ($previousHost === $appHost || $previousHost === request()->getHost())
        ? $previous
        : route('home');

    $secure = request()->isSecure() || app()->environment('production');

    return redirect($redirect)
        ->withCookie(cookie()->forever('bkd_locale', $lang, '/', null, $secure, true, false, 'lax'))
        ->with('bkd_locale', $lang);
})->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/bagis-yap', [HomeController::class, 'donations'])->name('donations');
Route::get('/zekat-hesapla', [ZakatController::class, 'index'])->name('zakat.index');
Route::get('/islami-finans-araclari', [IslamicFinanceController::class, 'index'])->name('islamic-finance.index');
Route::get('/api/zekat/fiyatlar', [ZakatController::class, 'prices'])
    ->middleware('throttle:60,1')
    ->name('zakat.prices');
Route::get('/iletisim', [HomeController::class, 'contact'])->name('contact');
Route::post('/iletisim', [HomeController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');
Route::get('/faaliyetler', [HomeController::class, 'activities'])->name('activities.index');
Route::get('/faaliyetler/{slug}', [HomeController::class, 'activityShow'])->name('activities.show');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('gallery');
Route::get('/haberler', [HomeController::class, 'news'])->name('news.index');
Route::get('/haberler/{news}', [HomeController::class, 'newsShow'])->name('news.show');
Route::get('/gonullu-ol', [HomeController::class, 'volunteer'])->name('volunteer');
Route::post('/gonullu-ol', [HomeController::class, 'submitVolunteer'])
    ->middleware('throttle:5,1')
    ->name('volunteer.submit');
Route::post('/ebulten/kayit', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:30,1')
    ->name('newsletter.subscribe');
Route::post('/destekci-deneyimi', [TestimonialController::class, 'store'])
    ->middleware('throttle:3,60')
    ->name('testimonials.store');
Route::get('/ebulten/iptal/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/sayfa/{slug}', [HomeController::class, 'page'])->name('pages.show');

Route::get('/belge-dogrula/{code}', [CrmDocumentController::class, 'verify'])
    ->middleware('throttle:30,1')
    ->name('crm.document.verify');
Route::get('/makbuz-indir/{code}', [CrmDocumentController::class, 'downloadByCode'])
    ->middleware('throttle:20,1')
    ->name('crm.document.download.public');

Route::get('/afis-goster/{poster}', [PosterController::class, 'publicShow'])
    ->middleware('signed')
    ->name('crm.posters.public');
Route::get('/afis-indir/{poster}', [PosterController::class, 'publicDownload'])
    ->middleware('signed')
    ->name('crm.posters.public.download');

Route::middleware('auth:crm')->group(function (): void {
    Route::get('/belge-indir/{document}', [CrmDocumentController::class, 'download'])->name('crm.documents.download');

    Route::post('/crm/afis/yukle', [PosterController::class, 'store'])->name('crm.posters.store');
    Route::get('/crm/afis-duzenle/{poster}', [PosterController::class, 'edit'])->name('crm.posters.edit');
    Route::post('/crm/afis-duzenle/{poster}', [PosterController::class, 'update'])->name('crm.posters.update');
    Route::get('/crm/afis/{poster}/indir-png', [PosterController::class, 'downloadPng'])->name('crm.posters.download.png');
    Route::get('/crm/afis/{poster}/indir-pdf', [PosterController::class, 'downloadPdf'])->name('crm.posters.download.pdf');
});

Route::prefix('secder-panel')->name('admin.otp.')->group(function (): void {
    Route::get('/dogrulama', [AdminOtpController::class, 'show'])->name('form');
    Route::post('/dogrulama', [AdminOtpController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('verify');
});

Route::prefix('secder-panel')->name('admin.password.')->group(function (): void {
    Route::get('/sifremi-unuttum', [AdminForgotPasswordController::class, 'show'])->name('forgot');
    Route::post('/sifremi-unuttum', [AdminForgotPasswordController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('email');
    Route::get('/sifre-yenile/{token}', [AdminForgotPasswordController::class, 'showReset'])->name('confirm');
    Route::post('/sifre-yenile', [AdminForgotPasswordController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('update');
});

// Eski panel URL'lerinden yeni SECDER yollarına yönlendirme
Route::any('/bkd-panel/{path?}', function (?string $path = null) {
    $target = '/secder-panel' . ($path ? '/' . ltrim($path, '/') : '');

    return redirect($target, 301);
})->where('path', '.*');

Route::any('/crm/{path?}', function (?string $path = null) {
    $target = '/secder-crm' . ($path ? '/' . ltrim($path, '/') : '');

    return redirect($target, 301);
})->where('path', '.*');
