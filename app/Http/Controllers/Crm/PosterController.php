<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\PosterDocument;
use App\Models\PosterTemplate;
use App\Support\Crm\PosterDataResolver;
use App\Support\Crm\PosterPdfBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PosterController extends Controller
{
    private function canWrite(): bool
    {
        return auth('crm')->user()?->canWriteDonations() ?? false;
    }

    /**
     * Sessiz üretimden (offscreen) gelen yeni afiş PNG'sini kaydeder.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless($this->canWrite(), 403);

        $data = $request->validate([
            'donation_id' => ['required', 'integer', 'exists:donations,id'],
            'type' => ['required', 'string', 'in:donation_poster,thanks_poster'],
            'template_id' => ['nullable', 'integer', 'exists:poster_templates,id'],
            'image' => ['required', 'image', 'max:10240'],
            'layout_snapshot' => ['nullable', 'string'],
        ]);

        $path = 'crm/posters/' . $data['donation_id'] . '/' . $data['type'] . '-' . Str::lower(Str::random(10)) . '.png';
        Storage::disk('public')->put($path, file_get_contents($request->file('image')->getRealPath()));

        $poster = PosterDocument::query()->create([
            'donation_id' => $data['donation_id'],
            'poster_template_id' => $data['template_id'] ?? null,
            'type' => $data['type'],
            'image_path' => $path,
            'layout_snapshot' => $this->decodeLayout($data['layout_snapshot'] ?? null),
            'generated_at' => now(),
        ]);

        return response()->json([
            'id' => $poster->id,
            'image_url' => $poster->image_url,
        ]);
    }

    /**
     * Afiş stüdyosu (elle düzenleme) ekranı.
     */
    public function edit(PosterDocument $poster)
    {
        abort_unless($this->canWrite(), 403);

        $poster->loadMissing(['donation.donor', 'template']);
        $template = $poster->template;
        $data = app(PosterDataResolver::class)->resolve($poster->donation, $template);

        $layout = $poster->layout_snapshot;
        if (empty($layout) && $template) {
            $layout = $template->layout ?? [];
        }

        $config = [
            'mode' => 'studio',
            'backgroundUrl' => $template?->background_url,
            'canvasWidth' => $template?->canvas_width ?: null,
            'canvasHeight' => $template?->canvas_height ?: null,
            'layout' => $layout ?? [],
            'data' => $data,
            'fonts' => \App\Filament\Crm\Resources\PosterTemplates\Pages\DesignPosterTemplate::availableFonts(),
            'placeholders' => PosterDataResolver::availablePlaceholders(),
            'saveUrl' => route('crm.posters.update', $poster),
            // Not: Filament panel bağlamı dışında olduğumuz için getUrl() yerine
            // bağış düzenleme yolunu manuel kuruyoruz.
            'returnUrl' => url('/secder-crm/donations/' . $poster->donation_id . '/edit'),
        ];

        return view('crm.poster.studio', [
            'poster' => $poster,
            'config' => $config,
        ]);
    }

    /**
     * Stüdyodan gelen güncellenmiş afiş PNG'sini mevcut kayda yazar.
     */
    public function update(Request $request, PosterDocument $poster): JsonResponse
    {
        abort_unless($this->canWrite(), 403);

        $data = $request->validate([
            'image' => ['required', 'image', 'max:10240'],
            'layout_snapshot' => ['nullable', 'string'],
        ]);

        // Eski dosyaları temizle
        foreach ([$poster->image_path, $poster->pdf_path] as $old) {
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        $path = 'crm/posters/' . $poster->donation_id . '/' . $poster->type . '-' . Str::lower(Str::random(10)) . '.png';
        Storage::disk('public')->put($path, file_get_contents($request->file('image')->getRealPath()));

        $poster->update([
            'image_path' => $path,
            'pdf_path' => null,
            'layout_snapshot' => $this->decodeLayout($data['layout_snapshot'] ?? null),
            'generated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'image_url' => $poster->image_url]);
    }

    /**
     * WhatsApp ile paylaşılan imzalı genel görüntüleme linki (kimlik doğrulama gerektirmez).
     */
    public function publicShow(PosterDocument $poster)
    {
        abort_unless(
            $poster->image_path && Storage::disk('public')->exists($poster->image_path),
            404,
        );

        return response()->file(Storage::disk('public')->path($poster->image_path));
    }

    /**
     * WhatsApp ile paylaşılan imzalı genel indirme linki (kimlik doğrulama gerektirmez).
     */
    public function publicDownload(PosterDocument $poster): StreamedResponse
    {
        abort_unless(
            $poster->image_path && Storage::disk('public')->exists($poster->image_path),
            404,
        );

        return Storage::disk('public')->download(
            $poster->image_path,
            $this->fileName($poster) . '.png',
        );
    }

    public function downloadPng(PosterDocument $poster): StreamedResponse
    {
        abort_unless(auth('crm')->check(), 403);

        abort_unless(
            $poster->image_path && Storage::disk('public')->exists($poster->image_path),
            404,
        );

        return Storage::disk('public')->download(
            $poster->image_path,
            $this->fileName($poster) . '.png',
        );
    }

    public function downloadPdf(PosterDocument $poster, PosterPdfBuilder $builder): StreamedResponse
    {
        abort_unless(auth('crm')->check(), 403);

        $pdf = $builder->build($poster);
        $fileName = $this->fileName($poster) . '.pdf';

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $fileName, ['Content-Type' => 'application/pdf']);
    }

    private function fileName(PosterDocument $poster): string
    {
        $name = Str::slug($poster->donation?->donor?->full_name ?? 'afis');
        $typeSlug = $poster->type === PosterTemplate::TYPE_THANKS ? 'tesekkur-afisi' : 'bagis-afisi';

        return $typeSlug . '-' . ($name ?: $poster->id);
    }

    private function decodeLayout(?string $raw): ?array
    {
        if (! $raw) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
