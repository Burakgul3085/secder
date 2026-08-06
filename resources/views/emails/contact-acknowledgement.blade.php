@php
    use App\Support\MailTemplate;

    $mailTitle = 'Mesajınız Alındı';
    $mailEyebrow = 'İletişim';
    $mailPreheader = 'Mesajınız SECDER ekibine başarıyla iletildi.';
    $mailGreeting = MailTemplate::greeting($contactMessage->first_name, $contactMessage->last_name);
    $mailIntro = 'Mesajınız ' . ($siteTitle ?? 'SECDER') . ' ekibine başarıyla iletilmiştir.';
    $mailContentHtml = 'En kısa sürede size dönüş yapacağız. İlginiz için teşekkür ederiz.';
    $mailContentBoxed = true;
    $mailShowSignature = true;
@endphp

@include('emails._layout')
