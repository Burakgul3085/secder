@php
    use App\Support\MailTemplate;

    $mailTitle = $subject;
    $mailEyebrow = 'İletişim Yanıtı';
    $mailPreheader = 'İletişim formu mesajınıza SECDER yanıtı hazır.';
    $mailGreeting = MailTemplate::greeting($contactMessage->first_name, $contactMessage->last_name);
    $mailIntro = 'İletişim formundan ilettiğiniz mesajınıza yanıtımız aşağıdadır.';
    $mailContentHtml = nl2br(e($body));
    $mailContentBoxed = true;
    $mailShowSignature = true;
    $mailFooterNote = null;
@endphp

@include('emails._layout')
