@php
    $settings = \App\Models\Setting::current();
    $siteTitle = $settings->site_title ?: 'Birlikte Kardeşlik Derneği';
    $sitePhone = $settings->phone ?: '-';
    $siteEmail = $settings->email ?: env('PHPMAILER_FROM_ADDRESS', '-');
    $websiteUrl = $settings->website_url ?: config('app.url');
    $logoUrl = $settings->logo ? asset('storage/' . $settings->logo) : asset('images/default-logo.svg');
    $logoSrc = $settings->logo ? 'cid:bkd-logo' : $logoUrl;
    $socialLinks = [
        'Instagram' => $settings->instagram_url,
        'YouTube' => $settings->youtube_url,
        'TikTok' => $settings->tiktok_url,
        'Facebook' => $settings->facebook_url,
    ];
@endphp
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailTitle ?? $siteTitle }}</title>
</head>
<body style="margin:0; padding:24px; background:#f1f5f9; font-family:Arial,Helvetica,sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
        <tr>
            <td style="padding:20px 24px; background:linear-gradient(135deg, #333c55, #5f6f9b);">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <img src="{{ $logoSrc }}" alt="Logo" width="40" height="40" style="display:block; border-radius:9999px; background:#ffffff; padding:4px;">
                        </td>
                        <td style="padding-left:12px; vertical-align:middle;">
                            <div style="font-size:16px; font-weight:700; color:#ffffff;">{{ $siteTitle }}</div>
                            <div style="font-size:12px; color:#d1dbec;">Kurumsal Bilgilendirme</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                @if(!empty($mailGreeting))
                    <p style="margin:0 0 12px; font-size:14px; line-height:1.6;">{{ $mailGreeting }}</p>
                @endif

                @if(!empty($mailIntro))
                    <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">{{ $mailIntro }}</p>
                @endif

                @if(!empty($mailContentHtml))
                    <div style="font-size:14px; line-height:1.65; border:1px solid #cbd5e1; background:#f8fafc; border-radius:10px; padding:12px 14px;">
                        {!! $mailContentHtml !!}
                    </div>
                @endif

                @if(!empty($mailFooterNote))
                    <p style="margin:16px 0 0; font-size:13px; line-height:1.6; color:#334155;">{{ $mailFooterNote }}</p>
                @endif

                @if(!empty($websiteUrl))
                    <div style="margin-top:18px;">
                        <a href="{{ $websiteUrl }}" target="_blank" style="display:inline-block; background:#4d5c83; color:#ffffff; text-decoration:none; font-size:13px; font-weight:700; padding:10px 16px; border-radius:10px;">
                            Web Sitemizi Ziyaret Edin
                        </a>
                    </div>
                @endif

                <p style="margin:14px 0 0; font-size:12px; color:#64748b;">
                    Bu e-posta otomatik olarak gönderilmiştir.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 24px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                <div style="font-size:12px; color:#475569;">
                    <strong>{{ $siteTitle }}</strong><br>
                    Telefon:
                    @if($sitePhone !== '-')
                        <a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" style="color:#3f4c6b; text-decoration:none;">{{ $sitePhone }}</a>
                    @else
                        {{ $sitePhone }}
                    @endif
                    <br>
                    E-posta:
                    @if($siteEmail !== '-')
                        <a href="mailto:{{ $siteEmail }}" style="color:#3f4c6b; text-decoration:none;">{{ $siteEmail }}</a>
                    @else
                        {{ $siteEmail }}
                    @endif
                    <br>
                    Web Site:
                    @if(!empty($websiteUrl))
                        <a href="{{ $websiteUrl }}" target="_blank" style="color:#3f4c6b; text-decoration:none;">{{ $websiteUrl }}</a>
                    @else
                        -
                    @endif
                </div>
                <div style="margin-top:10px; font-size:12px; color:#475569;">
                    @foreach($socialLinks as $label => $url)
                        @if(!empty($url))
                            <a href="{{ $url }}" target="_blank" style="color:#3f4c6b; text-decoration:none; margin-right:10px;">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

