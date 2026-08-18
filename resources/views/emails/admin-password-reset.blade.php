@php
    use App\Support\MailTemplate;

    $mailContentHtml = '
        <p style="margin:0 0 14px; font-size:14px; color:#334155; line-height:1.6;">
            Yönetim paneli şifrenizi yenilemek için aşağıdaki bağlantıya tıklayın. Bağlantı 60 dakika geçerlidir.
        </p>
        <p style="margin:0; font-size:13px; color:#64748b;">Hesap: <strong style="color:#0c2340;">' . e($user->email) . '</strong></p>
    ';
@endphp

@include('emails._layout', [
    'mailTitle' => 'Admin Şifre Yenileme',
    'mailEyebrow' => 'Güvenli Giriş',
    'mailPreheader' => 'Yönetim paneli şifre yenileme bağlantınız hazır.',
    'mailGreeting' => MailTemplate::greeting($user->name ?: 'Yönetici'),
    'mailIntro' => 'Şifre yenileme talebi aldık. Şifreniz henüz değiştirilmedi; yalnızca bağlantıya tıklarsanız güncellenir.',
    'mailContentHtml' => $mailContentHtml,
    'mailContentBoxed' => true,
    'mailShowSignature' => true,
    'mailAutoNote' => 'Bu talebi siz oluşturmadıysanız bu e-postayı yok sayın. Şifreniz değişmez.',
    'mailPrimaryLabel' => 'Şifreyi Yenile',
    'mailPrimaryUrl' => $resetUrl,
    'mailSecondaryLabel' => 'Panele Git',
    'mailSecondaryUrl' => url('/secder-panel'),
])
