@php
    $siteSettings = \App\Models\Setting::current();
    $siteTitle = $siteSettings->site_title ?? 'SECDER';
    $mailTitle = $mailTitle ?? 'E-bülten kaydınız alındı';
    $mailEyebrow = 'E-Bülten';
    $mailPreheader = 'E-bülten aboneliğiniz başarıyla oluşturuldu.';
    $mailGreeting = 'Merhaba,';
    $mailIntro = $siteTitle . ' e-bülten listesine e-posta adresinizle kaydoldunuz. Düzenli duyuru ve haberlerimizi bu kanaldan alacaksınız.';
    $mailContentHtml = 'İstemediğiniz zaman aşağıdaki bağlantıdan aboneliğinizi sonlandırabilirsiniz.';
    $mailContentBoxed = true;
    $mailShowSignature = true;
    $mailSecondaryLabel = 'Aboneliği İptal Et';
    $mailSecondaryUrl = $unsubscribeUrl;
    $mailAutoNote = 'Bilgileriniz yalnızca bilgilendirme amaçlı kullanılır.';
@endphp

@include('emails._layout')
