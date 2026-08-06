@php
    use App\Support\MailTemplate;

    $spacedCode = implode(' ', str_split((string) $code));
    $mailContentHtml = '
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:4px 0;">
            <tr>
                <td style="padding:18px 16px; text-align:center; background:#f8fafc; border:1px solid #e2e8f0;">
                    <div style="font-family:Arial,Helvetica,sans-serif; font-size:11px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#94a3b8; margin-bottom:12px;">
                        Doğrulama Kodunuz
                    </div>
                    <div style="display:inline-block; background:#0c2340; border-top:3px solid #c5a059; padding:14px 28px;">
                        <span style="font-family:Georgia,\'Times New Roman\',Times,serif; font-size:34px; font-weight:700; letter-spacing:0.28em; color:#ffffff; line-height:1;">
                            ' . e($spacedCode) . '
                        </span>
                    </div>
                    <div style="margin-top:12px; font-family:Arial,Helvetica,sans-serif; font-size:12px; color:#64748b;">
                        Geçerlilik süresi: <strong style="color:#0c2340;">10 dakika</strong>
                    </div>
                </td>
            </tr>
        </table>
    ';
@endphp

@include('emails._layout', [
    'mailTitle' => 'Yönetim Paneli Giriş Doğrulama',
    'mailEyebrow' => 'Güvenli Giriş',
    'mailPreheader' => 'Yönetim paneli giriş doğrulama kodunuz hazır.',
    'mailGreeting' => MailTemplate::greeting($user->name ?: 'Yönetici'),
    'mailIntro' => 'Yönetim paneli girişinizi tamamlamak için aşağıdaki 4 haneli doğrulama kodunu kullanın.',
    'mailContentHtml' => $mailContentHtml,
    'mailContentBoxed' => false,
    'mailShowSignature' => true,
    'mailFooterNote' => null,
    'mailAutoNote' => 'Bu işlemi siz başlatmadıysanız bu e-postayı dikkate almayın. Kodunuzu kimseyle paylaşmayın.',
    'mailPrimaryUrl' => null,
    'mailSecondaryUrl' => null,
])
