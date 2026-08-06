@php
    use App\Support\MailTemplate;

    $mailTitle = $subject;
    $mailEyebrow = 'Gönüllü Başvurusu';
    $mailPreheader = 'Gönüllülük başvurunuza SECDER yanıtı hazır.';
    $mailGreeting = MailTemplate::greeting($application->first_name, $application->last_name);
    $mailIntro = 'Gönüllülük başvurunuza yanıtımız aşağıdadır.';
    $mailContentHtml = nl2br(e($body));
    $mailContentBoxed = true;
    $mailShowSignature = true;
@endphp

@include('emails._layout')
