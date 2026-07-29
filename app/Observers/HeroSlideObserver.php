<?php

namespace App\Observers;

use App\Models\HeroSlide;
use App\Support\HeroImageRenderer;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Slayt kaydedildiğinde cihaz görsellerini üretir, silindiğinde temizler.
 *
 * Üretim servisi `saveQuietly()` kullandığı için gözlemci tekrar tetiklenmez.
 * İmza değişmediyse servis zaten hiçbir iş yapmaz, bu yüzden sıralama gibi
 * küçük güncellemeler ek maliyet çıkarmaz.
 */
class HeroSlideObserver
{
    public function __construct(private readonly HeroImageRenderer $renderer) {}

    public function saved(HeroSlide $slide): void
    {
        try {
            $this->renderer->render($slide);
        } catch (Throwable $exception) {
            Log::error('Hero görsel üretimi başarısız.', [
                'slide_id' => $slide->getKey(),
                'message' => $exception->getMessage(),
            ]);

            $this->notifyFailure($exception->getMessage());
        }
    }

    public function deleted(HeroSlide $slide): void
    {
        try {
            $this->renderer->purge($slide);
        } catch (Throwable $exception) {
            Log::warning('Hero görselleri temizlenemedi.', [
                'slide_id' => $slide->getKey(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyFailure(string $message): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        Notification::make()
            ->title('Hero görselleri üretilemedi')
            ->body($message)
            ->danger()
            ->send();
    }
}
