<?php

namespace App\Support\Crm;

use App\Models\DocumentTemplate;
use App\Models\Donation;
use App\Models\DonationDocument;
use App\Models\Setting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationDocumentGenerator
{
    public function generate(Donation $donation, bool $regenerate = false): DonationDocument
    {
        $donation->loadMissing(['donor', 'donationType', 'paymentMethod', 'project']);

        $template = DocumentTemplate::query()
            ->where('type', DocumentTemplate::TYPE_RECEIPT)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->first();

        if (! $template) {
            throw new \RuntimeException('Aktif makbuz şablonu bulunamadı.');
        }

        if (! $regenerate) {
            $existing = $donation->documents()
                ->where('type', DocumentTemplate::TYPE_RECEIPT)
                ->latest('generated_at')
                ->first();

            if ($existing && Storage::disk('public')->exists($existing->pdf_path)) {
                return $existing;
            }
        }

        $verificationCode = Str::upper(Str::random(12));
        $verifyUrl = route('crm.document.verify', $verificationCode);
        $pdfBinary = $this->renderReceiptPdf($donation, $verificationCode, $verifyUrl, $template);

        $relativePath = 'crm/documents/' . $donation->id . '/receipt-' . $verificationCode . '.pdf';
        Storage::disk('public')->put($relativePath, $pdfBinary);

        return DonationDocument::query()->create([
            'donation_id' => $donation->id,
            'document_template_id' => $template->id,
            'type' => DocumentTemplate::TYPE_RECEIPT,
            'verification_code' => $verificationCode,
            'pdf_path' => $relativePath,
            'generated_at' => now(),
            'meta' => [
                'donation_number' => $donation->donation_number,
                'donor_name' => $donation->donor?->full_name,
                'amount' => (string) $donation->amount,
                'currency' => $donation->currency,
                'template_id' => $template->id,
                'engine' => 'receipt_blade',
            ],
        ]);
    }

    private function renderReceiptPdf(Donation $donation, string $verificationCode, string $verifyUrl, DocumentTemplate $template): string
    {
        $viewData = $this->buildReceiptViewData($donation, $verificationCode, $verifyUrl, $template);
        $html = view($template->blade_view, $viewData)->render();

        return $this->renderDompdf($html, $template);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReceiptViewData(Donation $donation, string $verificationCode, string $verifyUrl, DocumentTemplate $template): array
    {
        $settings = Setting::current();
        $orientation = $template->resolvedOrientation();
        [$pageWidth, $pageHeight] = $this->pageDimensions($orientation);

        $logoPath = $settings->logo ? public_path('storage/' . $settings->logo) : public_path('images/default-logo.svg');

        return [
            'settings' => $settings,
            'donation' => $donation,
            'donor' => $donation->donor,
            'template' => $template,
            'verificationCode' => $verificationCode,
            'verifyUrl' => $verifyUrl,
            'pageWidth' => $pageWidth,
            'pageHeight' => $pageHeight,
            'logoDataUri' => $this->fileToDataUri($logoPath),
            'qrDataUri' => $this->qrDataUri($verifyUrl),
            'placeholders' => [
                'ad' => $donation->donor?->first_name ?? '',
                'soyad' => $donation->donor?->last_name ?? '',
                'ad_soyad' => $donation->donor?->full_name ?? '',
                'telefon' => $donation->donor?->phone ?? '',
                'bagis_no' => $donation->donation_number,
                'makbuz_no' => $donation->receipt_number ?? $donation->donation_number,
                'bagis_turu' => $donation->donationType?->name ?? '',
                'bagis_tutari' => number_format((float) $donation->amount, 2, ',', '.'),
                'para_birimi' => $donation->currency,
                'tarih' => $donation->donated_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
                'aciklama' => $donation->description ?? '',
            ],
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function pageDimensions(string $orientation): array
    {
        return $orientation === 'landscape' ? [842, 595] : [595, 842];
    }

    private function qrDataUri(string $url, int $size = 180): string
    {
        $writer = new PngWriter();
        $qrCode = new QrCode(data: $url, size: $size, margin: 4);
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    private function fileToDataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private function renderDompdf(string $html, DocumentTemplate $template): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $orientation = $template->resolvedOrientation();
        [$pageWidth, $pageHeight] = $this->pageDimensions($orientation);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, $pageWidth, $pageHeight], $orientation);
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
