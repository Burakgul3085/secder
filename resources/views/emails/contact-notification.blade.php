@php
    use App\Support\MailTemplate;

    $fullName = MailTemplate::personName($contactMessage->first_name, $contactMessage->last_name);
    $mailTitle = 'Yeni İletişim Mesajı';
    $mailEyebrow = 'İletişim Bildirimi';
    $mailPreheader = 'Siteden yeni bir iletişim formu mesajı geldi.';
    $mailGreeting = 'Merhaba,';
    $mailIntro = 'Site üzerinden yeni bir iletişim formu mesajı alındı.';
    $mailContentHtml = '
        <p style="margin:0 0 8px;"><strong>Ad Soyad:</strong> ' . e($fullName) . '</p>
        <p style="margin:0 0 8px;"><strong>E-posta:</strong> ' . e($contactMessage->email) . '</p>
        <p style="margin:0 0 6px;"><strong>Mesaj:</strong></p>
        <div style="white-space:pre-line;">' . e($contactMessage->message) . '</div>
    ';
    $mailContentBoxed = true;
    $mailShowSignature = false;
    $mailFooterNote = 'Mesaj admin panelde İletişim Mesajları bölümüne de kaydedilmiştir.';
    $mailPrimaryLabel = 'Web Sitemizi Ziyaret Edin';
    $mailSecondaryLabel = 'Mesajı Panelde Aç';
    $mailSecondaryUrl = url('/secder-panel/contact-messages');
@endphp

@include('emails._layout')
