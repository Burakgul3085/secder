@php
    $siteSettings = \App\Models\Setting::current();
    $siteTitle = $siteSettings->site_title ?? 'Birlikte Kardeşlik Derneği';
    if (!isset($mailTitle)) {
        $mailTitle = 'E-bülten kaydınız alındı';
    }
    $mailGreeting = 'Merhaba,';
    $mailIntro = $siteTitle . ' e-bülten listesine e-posta adresinizle kaydoldunuz. Düzenli duyuru ve haberlerimizi bu kanaldan alacaksınız.';
    $mailContentHtml = 'İstemediğiniz zaman aşağıdaki bağlantıdan aboneliğinizi sonlandırabilirsiniz.<br><br><a href="' . e($unsubscribeUrl) . '" style="color:#3f4c6b;">E-bülten aboneliğini iptal et</a>';
    $mailFooterNote = 'Bilgileriniz yalnızca bilgilendirme amaçlı kullanılır.';
@endphp

@include('emails._layout')
