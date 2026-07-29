<?php

namespace App\Console\Commands;

use App\Models\HeroSlide;
use App\Support\HeroImageRenderer;
use Illuminate\Console\Command;
use Throwable;

class RenderHeroImages extends Command
{
    protected $signature = 'hero:render
        {--id=* : Yalnızca belirtilen slayt ID(ler)i için çalışır}
        {--force : İmza değişmemiş olsa da yeniden üretir}';

    protected $description = 'Hero slider görsellerini masaüstü, tablet ve telefon bandlarına göre yeniden üretir.';

    public function handle(HeroImageRenderer $renderer): int
    {
        $slides = HeroSlide::query()
            ->when($this->option('id'), fn ($query, $ids) => $query->whereIn('id', $ids))
            ->orderBy('sort_order')
            ->get();

        if ($slides->isEmpty()) {
            $this->components->warn('İşlenecek slayt bulunamadı.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $rendered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($slides as $slide) {
            try {
                $didRender = $renderer->render($slide, $force);
            } catch (Throwable $exception) {
                $failed++;
                $this->components->error("#{$slide->getKey()} — {$exception->getMessage()}");

                continue;
            }

            if (! $didRender) {
                $skipped++;
                $this->components->twoColumnDetail("#{$slide->getKey()}", 'güncel, atlandı');

                continue;
            }

            $rendered++;
            $variants = collect($slide->render_meta['variants'] ?? [])
                ->map(fn (array $variant): string => sprintf(
                    '%s %dx%d (%s)',
                    $variant['label'],
                    $variant['width'],
                    $variant['height'],
                    ($variant['fit'] ?? 'contain') === 'cover'
                        ? sprintf('kırparak doldurdu, %%%d kırpma', (int) round(($variant['crop_ratio'] ?? 0) * 100))
                        : sprintf('%s dolgu, %%%d', $variant['fill'] ?? 'zemin', (int) round(($variant['fill_ratio'] ?? 0) * 100))
                ))
                ->implode(' · ');

            $this->components->twoColumnDetail("#{$slide->getKey()}", $variants ?: 'üretildi');

            foreach ($slide->renderWarnings() as $warning) {
                $this->components->warn('  ' . $warning);
            }
        }

        $this->newLine();
        $this->components->info("Üretildi: {$rendered} · Atlandı: {$skipped} · Hata: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
