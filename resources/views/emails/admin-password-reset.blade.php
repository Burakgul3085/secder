@php
    use App\Support\MailTemplate;

    $mailContentHtml = '
        <p style="margin:0 0 8px; font-size:13px; color:#334155;">Hesap E-postası</p>
        <p style="margin:0 0 14px; font-size:14px; font-weight:700; color:#0c2340;">' . e($requestedEmail) . '</p>
        <p style="margin:0 0 8px; font-size:13px; color:#334155;">Yeni Şifre</p>
        <p style="margin:0; font-size:26px; letter-spacing:3px; font-weight:800; color:#0c2340;">' . e($newPassword) . '</p>
    ';
@endphp

@include('emails._layout', [
    'mailTitle' => 'Admin Şifre Sıfırlama',
    'mailEyebrow' => 'Güvenli Giriş',
    'mailPreheader' => 'Yeni yönetim paneli şifreniz oluşturuldu.',
    'mailGreeting' => MailTemplate::greeting($user->name ?: 'Yönetici'),
    'mailIntro' => 'Şifre sıfırlama talebi alındı. Yeni giriş şifresi aşağıdadır.',
    'mailContentHtml' => $mailContentHtml,
    'mailContentBoxed' => true,
    'mailShowSignature' => true,
    'mailAutoNote' => 'Giriş yaptıktan sonra güvenlik için şifrenizi değiştirmeniz önerilir.',
    'mailSecondaryLabel' => 'Panele Git',
    'mailSecondaryUrl' => url('/secder-panel'),
])
