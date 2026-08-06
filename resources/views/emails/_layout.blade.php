@php
    $settings = \App\Models\Setting::current();
    $siteTitle = $settings->site_title ?: 'SECDER';
    $sitePhone = $settings->phone ?: '-';
    $siteEmail = $settings->email ?: env('PHPMAILER_FROM_ADDRESS', '-');
    $websiteUrl = $settings->website_url ?: config('app.url');
    $appBase = rtrim((string) ($settings->website_url ?: config('app.url')), '/');
    if ($settings->logo) {
        $logoSrc = $appBase . '/storage/' . ltrim((string) $settings->logo, '/');
    } else {
        $logoSrc = $appBase . '/images/default-logo.svg';
    }

    $socialMeta = [
        'instagram' => ['label' => 'Instagram', 'icon' => 'instagram.png', 'url' => $settings->instagram_url],
        'youtube' => ['label' => 'YouTube', 'icon' => 'youtube.png', 'url' => $settings->youtube_url],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'tiktok.png', 'url' => $settings->tiktok_url],
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook.png', 'url' => $settings->facebook_url],
        'x' => ['label' => 'X', 'icon' => 'x.png', 'url' => $settings->x_url ?? null],
    ];
    $activeSocial = collect($socialMeta)->filter(fn ($item) => filled($item['url'] ?? null));

    $year = now()->year;
    $mailEyebrow = $mailEyebrow ?? 'Kurumsal Bilgilendirme';
    $mailPreheader = $mailPreheader ?? ($mailIntro ?? $mailTitle ?? $siteTitle);
    $mailContentBoxed = $mailContentBoxed ?? true;
    $mailShowSignature = $mailShowSignature ?? true;
    $mailSignatureTitle = $mailSignatureTitle ?? 'Dernek Yönetimi';
    $mailPrimaryLabel = $mailPrimaryLabel ?? 'Web Sitemizi Ziyaret Edin';
    $mailPrimaryUrl = $mailPrimaryUrl ?? $websiteUrl;
    $mailSecondaryLabel = $mailSecondaryLabel ?? null;
    $mailSecondaryUrl = $mailSecondaryUrl ?? null;
    $mailAutoNote = $mailAutoNote ?? 'Bu e-posta otomatik olarak gönderilmiştir.';
@endphp
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $mailTitle ?? $siteTitle }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0; padding:0; background:#e8edf3; font-family:Arial,Helvetica,sans-serif; color:#0b1220; -webkit-text-size-adjust:100%;">
    {{-- Preheader: gelen kutusunda konu altında görünür --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#e8edf3; opacity:0;">
        {{ \Illuminate\Support\Str::limit(strip_tags((string) $mailPreheader), 120) }}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#e8edf3;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px; width:100%; background:#ffffff; border-collapse:separate; border-spacing:0; border:1px solid #d5dde8;">
                    <tr>
                        <td style="height:4px; background:#c5a059; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:18px 20px 16px; background:#0c2340;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="52" style="vertical-align:middle;">
                                        <img src="{{ $logoSrc }}" alt="{{ $siteTitle }}" width="44" height="44" style="display:block; width:44px; height:44px; border-radius:50%; background:#ffffff; border:2px solid rgba(197,160,89,0.55);">
                                    </td>
                                    <td style="padding-left:12px; vertical-align:middle;">
                                        <div style="font-family:Georgia,'Times New Roman',Times,serif; font-size:20px; font-weight:700; letter-spacing:0.04em; color:#ffffff; line-height:1.15;">
                                            {{ $siteTitle }}
                                        </div>
                                        <div style="margin-top:4px; font-family:Arial,Helvetica,sans-serif; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#c5a059;">
                                            {{ $mailEyebrow }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 20px 10px; background:#ffffff;">
                            @if(!empty($mailTitle))
                                <h1 style="margin:0 0 14px; font-family:Georgia,'Times New Roman',Times,serif; font-size:19px; font-weight:700; line-height:1.35; color:#0c2340;">
                                    {{ $mailTitle }}
                                </h1>
                            @endif

                            @if(!empty($mailGreeting))
                                <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#1e293b;">
                                    {{ $mailGreeting }}
                                </p>
                            @endif

                            @if(!empty($mailIntro))
                                <p style="margin:0 0 16px; font-size:14px; line-height:1.7; color:#334155;">
                                    {{ $mailIntro }}
                                </p>
                            @endif

                            @if(!empty($mailContentHtml))
                                @if($mailContentBoxed)
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:4px 0 12px;">
                                        <tr>
                                            <td style="width:4px; background:#c5a059; font-size:0; line-height:0;">&nbsp;</td>
                                            <td style="padding:14px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-left:0; font-size:14px; line-height:1.7; color:#1e293b;">
                                                {!! $mailContentHtml !!}
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div style="margin:0 0 12px;">
                                        {!! $mailContentHtml !!}
                                    </div>
                                @endif
                            @endif

                            @if(!empty($mailFooterNote) && ! $mailShowSignature)
                                <p style="margin:14px 0 0; font-size:13px; line-height:1.65; color:#64748b;">
                                    {{ $mailFooterNote }}
                                </p>
                            @endif

                            @if($mailShowSignature)
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:20px 0 8px;">
                                    <tr>
                                        <td style="font-size:14px; line-height:1.6; color:#334155;">
                                            <div style="margin-bottom:2px;">Saygılarımızla,</div>
                                            <div style="font-family:Georgia,'Times New Roman',Times,serif; font-size:15px; font-weight:700; color:#0c2340;">{{ $siteTitle }}</div>
                                            <div style="font-size:12px; color:#94a3b8; letter-spacing:0.04em;">{{ $mailSignatureTitle }}</div>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if(!empty($mailPrimaryUrl) || !empty($mailSecondaryUrl))
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:22px 0 6px;">
                                    <tr>
                                        @if(!empty($mailPrimaryUrl))
                                            <td style="background:#0c2340; padding:0;">
                                                <a href="{{ $mailPrimaryUrl }}" target="_blank" style="display:inline-block; padding:11px 18px; font-size:13px; font-weight:700; letter-spacing:0.03em; text-decoration:none; color:#ffffff;">
                                                    {{ $mailPrimaryLabel }}
                                                </a>
                                            </td>
                                        @endif
                                        @if(!empty($mailPrimaryUrl) && !empty($mailSecondaryUrl))
                                            <td style="width:10px; font-size:0;">&nbsp;</td>
                                        @endif
                                        @if(!empty($mailSecondaryUrl))
                                            <td style="border:1px solid #c5a059; padding:0;">
                                                <a href="{{ $mailSecondaryUrl }}" target="_blank" style="display:inline-block; padding:10px 16px; font-size:13px; font-weight:700; letter-spacing:0.03em; text-decoration:none; color:#0c2340;">
                                                    {{ $mailSecondaryLabel }}
                                                </a>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:18px 0 0; font-size:12px; line-height:1.5; color:#94a3b8;">
                                {{ $mailAutoNote }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 20px;">
                            <div style="height:1px; background:#e8d5a8; font-size:0; line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 20px 20px; background:#f7f9fc;">
                            <div style="font-family:Georgia,'Times New Roman',Times,serif; font-size:14px; font-weight:700; color:#0c2340; margin-bottom:10px;">
                                {{ $siteTitle }}
                            </div>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:12px; line-height:1.7; color:#475569;">
                                @if($sitePhone !== '-')
                                    <tr>
                                        <td style="padding:2px 0; width:72px; color:#94a3b8; vertical-align:top;">Telefon</td>
                                        <td style="padding:2px 0;">
                                            <a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" style="color:#334155; text-decoration:none;">{{ $sitePhone }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if($siteEmail !== '-')
                                    <tr>
                                        <td style="padding:2px 0; width:72px; color:#94a3b8; vertical-align:top;">E-posta</td>
                                        <td style="padding:2px 0;">
                                            <a href="mailto:{{ $siteEmail }}" style="color:#334155; text-decoration:none;">{{ $siteEmail }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if(!empty($websiteUrl))
                                    <tr>
                                        <td style="padding:2px 0; width:72px; color:#94a3b8; vertical-align:top;">Web</td>
                                        <td style="padding:2px 0;">
                                            <a href="{{ $websiteUrl }}" target="_blank" style="color:#0c2340; text-decoration:none; font-weight:600;">{{ $websiteUrl }}</a>
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            @if($activeSocial->isNotEmpty())
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:14px;">
                                    <tr>
                                        @foreach($activeSocial as $item)
                                            <td style="padding:0 8px 0 0;">
                                                <a href="{{ $item['url'] }}" target="_blank" style="display:inline-block; text-decoration:none; border:0; outline:none;">
                                                    <img src="{{ $appBase }}/images/email/{{ $item['icon'] }}" width="30" height="30" alt="{{ $item['label'] }}" style="display:block; width:30px; height:30px; border:0; border-radius:2px;">
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:14px 0 0; font-size:11px; line-height:1.5; color:#94a3b8;">
                                © {{ $year }} {{ $siteTitle }}. Tüm hakları saklıdır.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
