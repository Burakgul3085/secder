@php
    use App\Support\MailTemplate;

    $mailTitle = 'Gönüllülük Başvurunuz Alındı';
    $mailEyebrow = 'Gönüllü Başvurusu';
    $mailPreheader = 'Gönüllülük başvurunuz SECDER ekibine ulaştı.';
    $mailGreeting = MailTemplate::greeting($application->first_name, $application->last_name);
    $mailIntro = 'Gönüllülük başvurunuz ' . ($siteTitle ?? 'SECDER') . ' ekibine başarıyla iletilmiştir.';
    $mailContentHtml = 'Başvurunuz incelenecek ve en kısa sürede sizinle iletişime geçilecektir.';
    $mailContentBoxed = true;
    $mailShowSignature = true;
@endphp

@include('emails._layout')
