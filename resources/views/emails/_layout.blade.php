@php
    $settings = \App\Models\Setting::current();
    $siteTitle = $settings->site_title ?: 'SECDER';
    $sitePhone = $settings->phone ?: '-';
    $siteEmail = $settings->email ?: env('PHPMAILER_FROM_ADDRESS', '-');
    $websiteUrl = $settings->website_url ?: config('app.url');
    /* E-posta istemcilerinde CID bazen gömülmez / kırılır.
       Bu yüzden her zaman site üzerinden mutlak HTTPS logo URL kullanılır. */
    $appBase = rtrim((string) ($settings->website_url ?: config('app.url')), '/');
    if ($settings->logo) {
        $logoSrc = $appBase . '/storage/' . ltrim((string) $settings->logo, '/');
    } else {
        $logoSrc = $appBase . '/images/default-logo.svg';
    }
    $socialLinks = [
        'Instagram' => $settings->instagram_url,
        'YouTube' => $settings->youtube_url,
        'TikTok' => $settings->tiktok_url,
        'Facebook' => $settings->facebook_url,
        'X' => $settings->x_url ?? null,
    ];
    $year = now()->year;
@endphp
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $mailTitle ?? $siteTitle }}</title>
</head>
<body style="margin:0; padding:0; background:#e8edf3; font-family:Georgia,'Times New Roman',Times,serif; color:#0b1220; -webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#e8edf3; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px; background:#ffffff; border-collapse:separate; border-spacing:0; border:1px solid #d5dde8; border-radius:4px; overflow:hidden;">
                    {{-- Üst altın şerit --}}
                    <tr>
                        <td style="height:4px; background:#c5a059; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- Kurumsal başlık --}}
                    <tr>
                        <td style="padding:22px 28px 20px; background:#0c2340;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="56" style="vertical-align:middle;">
                                        <img src="{{ $logoSrc }}" alt="{{ $siteTitle }}" width="48" height="48" style="display:block; width:48px; height:48px; border-radius:50%; background:#ffffff; border:2px solid rgba(197,160,89,0.55);">
                                    </td>
                                    <td style="padding-left:14px; vertical-align:middle;">
                                        <div style="font-family:Georgia,'Times New Roman',Times,serif; font-size:22px; font-weight:700; letter-spacing:0.04em; color:#ffffff; line-height:1.15;">
                                            {{ $siteTitle }}
                                        </div>
                                        <div style="margin-top:4px; font-family:Arial,Helvetica,sans-serif; font-size:11px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:#c5a059;">
                                            {{ $mailEyebrow ?? 'Kurumsal Bilgilendirme' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- İnce ayırıcı --}}
                    <tr>
                        <td style="height:1px; background:#e2e8f0; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- İçerik --}}
                    <tr>
                        <td style="padding:28px 28px 8px; background:#ffffff; font-family:Arial,Helvetica,sans-serif;">
                            @if(!empty($mailTitle))
                                <h1 style="margin:0 0 16px; font-family:Georgia,'Times New Roman',Times,serif; font-size:20px; font-weight:700; line-height:1.3; color:#0c2340;">
                                    {{ $mailTitle }}
                                </h1>
                            @endif

                            @if(!empty($mailGreeting))
                                <p style="margin:0 0 12px; font-size:15px; line-height:1.65; color:#1e293b;">
                                    {{ $mailGreeting }}
                                </p>
                            @endif

                            @if(!empty($mailIntro))
                                <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#334155;">
                                    {{ $mailIntro }}
                                </p>
                            @endif

                            @if(!empty($mailContentHtml))
                                <div style="margin:0 0 8px;">
                                    {!! $mailContentHtml !!}
                                </div>
                            @endif

                            @if(!empty($mailFooterNote))
                                <p style="margin:18px 0 0; font-size:13px; line-height:1.65; color:#64748b;">
                                    {{ $mailFooterNote }}
                                </p>
                            @endif

                            @if(!empty($websiteUrl))
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:24px 0 8px;">
                                    <tr>
                                        <td style="background:#0c2340; border-radius:2px;">
                                            <a href="{{ $websiteUrl }}" target="_blank" style="display:inline-block; padding:12px 22px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:700; letter-spacing:0.04em; text-decoration:none; color:#ffffff;">
                                                Web Sitemizi Ziyaret Edin
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:20px 0 0; font-size:12px; line-height:1.5; color:#94a3b8;">
                                Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayınız.
                            </p>
                        </td>
                    </tr>

                    {{-- Altın çizgi --}}
                    <tr>
                        <td style="padding:0 28px;">
                            <div style="height:1px; background:#e8d5a8; font-size:0; line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    {{-- Kurumsal alt bilgi --}}
                    <tr>
                        <td style="padding:20px 28px 24px; background:#f7f9fc; font-family:Arial,Helvetica,sans-serif;">
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

                            @php
                                $activeSocial = collect($socialLinks)->filter(fn ($url) => filled($url));
                            @endphp
                            @if($activeSocial->isNotEmpty())
                                <div style="margin-top:14px; font-size:12px;">
                                    @foreach($activeSocial as $label => $url)
                                        <a href="{{ $url }}" target="_blank" style="display:inline-block; margin:0 10px 6px 0; color:#0c2340; text-decoration:none; font-weight:600; border-bottom:1px solid #c5a059;">{{ $label }}</a>
                                    @endforeach
                                </div>
                            @endif

                            <p style="margin:16px 0 0; font-size:11px; line-height:1.5; color:#94a3b8;">
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
