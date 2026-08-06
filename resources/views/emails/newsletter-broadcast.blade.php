@php
    $siteSettings = $siteSettings ?? \App\Models\Setting::current();
    $mailTitle = $subject;
    $mailEyebrow = 'E-Bülten';
    $mailPreheader = \Illuminate\Support\Str::limit(strip_tags((string) $bodyHtml), 110);
    $mailGreeting = 'Merhaba,';
    $mailIntro = null;
    $mailContentHtml = $bodyHtml;
    $mailContentBoxed = true;
    $mailShowSignature = true;
    $mailAutoNote = 'Bu e-posta ' . ($siteSettings->site_title ?? 'SECDER') . ' e-bülten listesine abone olduğunuz için gönderilmiştir.';
@endphp

@include('emails._layout')
