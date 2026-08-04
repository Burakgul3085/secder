<?php

namespace App\Support\Crm;

use App\Models\Donation;
use App\Models\PosterTemplate;
use App\Models\Setting;

/**
 * Bir bağıştan afiş şablonlarında kullanılacak yer tutucu (placeholder) verisini üretir.
 */
class PosterDataResolver
{
    /**
     * Şablon tasarımcısında listelenecek kullanılabilir yer tutucular.
     *
     * @return array<string, string>
     */
    public static function availablePlaceholders(): array
    {
        return [
            'ad' => 'Bağışçı adı',
            'soyad' => 'Bağışçı soyadı',
            'ad_soyad' => 'Bağışçı ad soyad',
            'telefon' => 'Telefon',
            'tarih' => 'Bağış tarihi',
            'faaliyet' => 'Proje / Faaliyet',
            'bagis_turu' => 'Bağış türü',
            'odeme_turu' => 'Ödeme türü',
            'bagis_tutari' => 'Bağış tutarı',
            'para_birimi' => 'Para birimi',
            'tutar_birimli' => 'Tutar + para birimi',
            'bagis_no' => 'Bağış no',
            'makbuz_no' => 'Makbuz no',
            'not' => 'Bağış notu (açıklama)',
            'aciklama' => 'Açıklama (bağış notu)',
            'tesekkur_metni' => 'Teşekkür metni (kalıptan üretilir)',
            'dernek_adi' => 'Dernek adı',
        ];
    }

    /**
     * Form yardım metninde gösterilecek örnek kalıp.
     */
    public static function templateHelperHtml(): string
    {
        $keys = collect(self::availablePlaceholders())
            ->except('tesekkur_metni')
            ->map(fn (string $label, string $key): string => "<code>{{$key}}</code> = {$label}")
            ->implode('<br>');

        return 'Bu kalıp teşekkür afişindeki <strong>Teşekkür metni</strong> kutusuna yazılır. '
            . 'Süslü parantezli alanlar, bağış kaydındaki bilgilerle otomatik doldurulur.<br><br>'
            . $keys
            . '<br><br>Örnek: <code>{ad_soyad}</code>, <code>{tutar_birimli}</code>, <code>{faaliyet}</code>, <code>{tarih}</code>';
    }

    /**
     * Bağış verisinden tüm yer tutucu değerlerini döndürür.
     *
     * @return array<string, string>
     */
    public function resolve(Donation $donation, ?PosterTemplate $template = null): array
    {
        $donation->loadMissing(['donor', 'donationType', 'paymentMethod', 'project']);

        $settings = Setting::current();

        $amount = number_format((float) $donation->amount, 2, ',', '.');
        $currency = (string) ($donation->currency ?? 'TRY');
        $date = $donation->donated_at?->format('d.m.Y') ?? now()->format('d.m.Y');
        $faaliyet = trim((string) ($donation->project?->title ?? ''));
        $bagisTuru = trim((string) ($donation->donationType?->name ?? ''));
        $odemeTuru = trim((string) ($donation->paymentMethod?->name ?? ''));
        $description = trim((string) ($donation->description ?? ''));

        $data = [
            'ad' => trim((string) ($donation->donor?->first_name ?? '')),
            'soyad' => trim((string) ($donation->donor?->last_name ?? '')),
            'ad_soyad' => trim((string) ($donation->donor?->full_name ?? '')),
            'telefon' => trim((string) ($donation->donor?->phone ?? '')),
            'tarih' => $date,
            'faaliyet' => $faaliyet,
            'bagis_turu' => $bagisTuru,
            'odeme_turu' => $odemeTuru,
            'bagis_tutari' => $amount,
            'para_birimi' => $currency,
            'tutar_birimli' => trim($amount . ' ' . $currency),
            'bagis_no' => (string) ($donation->donation_number ?? ''),
            'makbuz_no' => (string) ($donation->receipt_number ?? $donation->donation_number ?? ''),
            'not' => $description,
            'aciklama' => $description,
            'dernek_adi' => (string) ($settings->site_title ?: 'SECDER'),
        ];

        $data['tesekkur_metni'] = $this->buildThanksText($template, $data);

        return $data;
    }

    /**
     * Teşekkür metnini şablondaki kalıptan veya varsayılandan üretir.
     *
     * @param  array<string, string>  $data
     */
    private function buildThanksText(?PosterTemplate $template, array $data): string
    {
        $tpl = $template?->thanks_text_template;

        if (! is_string($tpl) || trim($tpl) === '') {
            $tpl = $this->composeDefaultThanksTemplate($data);
        }

        return $this->fill($tpl, $data);
    }

    /**
     * Varsayılan kalıp (boş alanlara göre cümleleri uyarlar).
     *
     * @param  array<string, string>  $data
     */
    private function composeDefaultThanksTemplate(array $data): string
    {
        $lines = [];

        $amountPart = filled($data['tutar_birimli']) ? '{tutar_birimli} tutarındaki ' : '';
        $typePart = filled($data['bagis_turu']) ? '{bagis_turu} ' : '';
        $datePart = filled($data['tarih']) ? ' {tarih} tarihinde' : '';

        $lines[] = trim($amountPart . $typePart . 'bağışınız' . $datePart . ' derneğimize ulaşmış ve kayda alınmıştır.');

        if (filled($data['faaliyet'])) {
            $lines[] = '{faaliyet} kapsamında verdiğiniz destek, yürüttüğümüz hizmetlere güç katmaktadır.';
        } else {
            $lines[] = 'Verdiğiniz destek, caminin ve toplum hizmetlerimizin güçlenmesine katkı sağlamaktadır.';
        }

        if (filled($data['odeme_turu'])) {
            $lines[] = 'Ödeme türü: {odeme_turu}.';
        }

        $refParts = [];
        if (filled($data['bagis_no'])) {
            $refParts[] = 'Bağış No: {bagis_no}';
        }
        if (filled($data['makbuz_no'])) {
            $refParts[] = 'Makbuz No: {makbuz_no}';
        }
        if ($refParts !== []) {
            $lines[] = implode('  |  ', $refParts);
        }

        return implode("\n\n", $lines);
    }

    public function defaultThanksTemplate(): string
    {
        return "{tutar_birimli} tutarındaki {bagis_turu} bağışınız {tarih} tarihinde derneğimize ulaşmış ve kayda alınmıştır.\n\n"
            . "{faaliyet} kapsamında verdiğiniz destek, yürüttüğümüz hizmetlere güç katmaktadır.\n\n"
            . "Ödeme türü: {odeme_turu}.\n\n"
            . "Bağış No: {bagis_no}  |  Makbuz No: {makbuz_no}";
    }

    /**
     * Metindeki {anahtar} kalıplarını verilerle değiştirir.
     *
     * @param  array<string, string>  $data
     */
    public function fill(string $text, array $data): string
    {
        $result = preg_replace_callback('/\{(\w+)\}/', function (array $matches) use ($data): string {
            return $data[$matches[1]] ?? '';
        }, $text) ?? $text;

        // Boş yer tutuculardan kalan gereksiz boşluk / noktalama kırıntılarını sadeleştir.
        $result = preg_replace('/[ \t]+\n/', "\n", $result) ?? $result;
        $result = preg_replace('/\n{3,}/', "\n\n", $result) ?? $result;
        $result = preg_replace('/[ \t]{2,}/', ' ', $result) ?? $result;
        $result = preg_replace('/\s+\|/', ' |', $result) ?? $result;
        $result = preg_replace('/\|\s+/', '| ', $result) ?? $result;
        $result = preg_replace('/(?:\s*\|\s*){2,}/', ' | ', $result) ?? $result;
        $result = preg_replace('/^\s*\|\s*/m', '', $result) ?? $result;
        $result = preg_replace('/\s*\|\s*$/m', '', $result) ?? $result;

        return trim($result);
    }
}
