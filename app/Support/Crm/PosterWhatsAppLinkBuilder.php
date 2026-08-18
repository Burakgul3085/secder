<?php

namespace App\Support\Crm;

use App\Models\PosterDocument;
use App\Models\PosterTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

class PosterWhatsAppLinkBuilder
{
    public function build(PosterDocument $poster): ?string
    {
        $poster->loadMissing('donation.donor', 'donation.project', 'donation.donationType');

        $donation = $poster->donation;
        $donor = $donation?->donor;

        if (! $donor) {
            return null;
        }

        $phone = PhoneNumberNormalizer::toWhatsApp($donor->phone);

        if ($phone === null) {
            return null;
        }

        $orgName = Setting::current()->site_title ?? 'Birlikte Kardeşlik Derneği';
        $typeLabel = PosterTemplate::TYPES[$poster->type] ?? 'Afiş';
        $date = $donation->donated_at?->format('d.m.Y') ?? '-';
        $faaliyet = $donation->project?->title ?? ($donation->donationType?->name ?? '');

        $publicUrl = URL::signedRoute('crm.posters.public', ['poster' => $poster->id]);
        $downloadUrl = URL::signedRoute('crm.posters.public.download', ['poster' => $poster->id]);

        if ($poster->type === PosterTemplate::TYPE_THANKS) {
            $lines = [
                "Sayın {$donor->full_name},",
                '',
                "{$orgName} olarak desteğiniz için teşekkür ederiz. Teşekkür afişiniz hazır:",
            ];
        } else {
            $lines = [
                "Sayın {$donor->full_name},",
                '',
                "{$orgName} bağışınız için hazırlanan afiş:",
            ];
        }

        if ($faaliyet !== '') {
            $lines[] = '';
            $lines[] = "Faaliyet: {$faaliyet}";
            $lines[] = "Tarih: {$date}";
        }

        $lines[] = '';
        $lines[] = "Afişi görüntüleyin: {$publicUrl}";
        $lines[] = "Afişi indirin: {$downloadUrl}";
        $lines[] = '';
        $lines[] = 'Teşekkür ederiz.';

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode(implode("\n", $lines));
    }

    public function donorHasPhone(PosterDocument $poster): bool
    {
        $poster->loadMissing('donation.donor');

        return PhoneNumberNormalizer::toWhatsApp($poster->donation?->donor?->phone) !== null;
    }
}
