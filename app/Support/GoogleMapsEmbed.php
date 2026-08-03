<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Google Maps paylaşım / embed linklerini resmi iframe src'ye çevirir.
 *
 * Aile ve Nesil tarzı adres kartı için `maps/embed?pb=` formatı gerekir.
 * Eski `?q=...&output=embed` çoğu tarayıcıda boş / hatalı harita gösterir.
 */
final class GoogleMapsEmbed
{
    private const EXPAND_CACHE_PREFIX = 'maps_expand_v3:';

    /**
     * @return array{embed: ?string, external: ?string, needs_external: bool}
     */
    public static function resolve(?string $input, ?string $fallbackAddress = null): array
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return ['embed' => null, 'external' => null, 'needs_external' => false];
        }

        $external = self::extractUrl($raw) ?? $raw;
        $candidate = $external;

        if (self::isShortMapsUrl($candidate)) {
            $expanded = self::expandShortUrl($candidate);
            if (filled($expanded)) {
                $candidate = $expanded;
            }
        }

        $embed = self::toEmbedUrl($candidate, $fallbackAddress);

        if ($embed !== null) {
            return ['embed' => $embed, 'external' => $external, 'needs_external' => false];
        }

        if (filled($fallbackAddress)) {
            return [
                'embed' => self::buildPbEmbed(
                    lat: null,
                    lng: null,
                    placeId: null,
                    placeName: (string) $fallbackAddress,
                ),
                'external' => $external,
                'needs_external' => false,
            ];
        }

        return [
            'embed' => null,
            'external' => $external,
            'needs_external' => true,
        ];
    }

    private static function extractUrl(string $raw): ?string
    {
        if (preg_match('/src=["\']([^"\']+)["\']/i', $raw, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return $raw;
        }

        if (preg_match('#https?://[^\s"\'<>]+#i', $raw, $matches) === 1) {
            return rtrim($matches[0], '.,);]');
        }

        return null;
    }

    private static function expandShortUrl(string $url): ?string
    {
        $cacheKey = self::EXPAND_CACHE_PREFIX . sha1($url);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $expanded = self::expandWithCurl($url) ?? self::expandWithHttp($url);

        if (is_string($expanded) && $expanded !== '' && ! self::isShortMapsUrl($expanded)) {
            Cache::put($cacheKey, $expanded, now()->addDays(14));

            return $expanded;
        }

        return null;
    }

    private static function expandWithCurl(string $url): ?string
    {
        if (! function_exists('curl_init')) {
            return null;
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                CURLOPT_NOBODY => true,
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            $effective = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            return $effective !== '' ? $effective : null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function expandWithHttp(string $url): ?string
    {
        try {
            $response = Http::withOptions([
                'allow_redirects' => [
                    'max' => 10,
                    'track_redirects' => true,
                ],
                'timeout' => 10,
                'connect_timeout' => 5,
                'http_errors' => false,
                'verify' => false,
            ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                ])
                ->get($url);

            $candidates = [];
            $effective = (string) ($response->effectiveUri() ?? '');
            if ($effective !== '') {
                $candidates[] = $effective;
            }

            $redirectChain = $response->headers()['X-Guzzle-Redirect-History'] ?? [];
            if (is_array($redirectChain)) {
                foreach ($redirectChain as $item) {
                    if (is_string($item) && $item !== '') {
                        $candidates[] = $item;
                    }
                }
            }

            foreach (array_reverse($candidates) as $candidate) {
                if (! self::isShortMapsUrl($candidate) && Str::contains($candidate, ['google.com/maps', 'maps.google.'])) {
                    return $candidate;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private static function toEmbedUrl(string $url, ?string $fallbackAddress): ?string
    {
        // Zaten resmi embed
        if (Str::contains($url, '/maps/embed')) {
            return $url;
        }

        if (self::isShortMapsUrl($url)) {
            return null;
        }

        $placeName = null;
        if (preg_match('#/maps/place/([^/]+)#', $url, $m) === 1) {
            $decoded = trim(urldecode(str_replace('+', ' ', $m[1])));
            if ($decoded !== '' && preg_match('/^-?\d/', $decoded) !== 1) {
                $placeName = $decoded;
            }
        }

        $placeId = null;
        if (preg_match('/!1s(0x[0-9a-fA-F]+:0x[0-9a-fA-F]+)/', $url, $m) === 1) {
            $placeId = $m[1];
        } elseif (preg_match('/1s(0x[0-9a-fA-F]+:0x[0-9a-fA-F]+)/', $url, $m) === 1) {
            $placeId = $m[1];
        }

        $lat = null;
        $lng = null;
        if (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $url, $m) === 1) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
        } elseif (preg_match('/@(-?\d+\.?\d*),\s*(-?\d+\.?\d*)(?:,(\d+\.?\d*)z)?/', $url, $m) === 1) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
        }

        $zoom = 17;
        if (preg_match('/@-?\d+\.?\d*,\s*-?\d+\.?\d*,(\d+\.?\d*)z/', $url, $m) === 1) {
            $zoom = max(1, min(21, (int) round((float) $m[1])));
        }

        if ($placeId !== null || ($lat !== null && $lng !== null) || filled($placeName)) {
            return self::buildPbEmbed($lat, $lng, $placeId, $placeName, $zoom);
        }

        $parts = parse_url($url);
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
            foreach (['q', 'query'] as $key) {
                if (! empty($query[$key]) && is_string($query[$key])) {
                    return self::buildPbEmbed(null, null, null, $query[$key], $zoom);
                }
            }
        }

        if (preg_match('#/maps/search/([^/?]+)#', $url, $m) === 1) {
            return self::buildPbEmbed(null, null, null, urldecode(str_replace('+', ' ', $m[1])), $zoom);
        }

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return self::buildPbEmbed(null, null, null, $url, $zoom);
        }

        if (filled($fallbackAddress)) {
            return self::buildPbEmbed(null, null, null, (string) $fallbackAddress, $zoom);
        }

        return null;
    }

    /**
     * Aile ve Nesil ile aynı resmi embed formatı (adres kartı + pin).
     */
    private static function buildPbEmbed(
        ?float $lat,
        ?float $lng,
        ?string $placeId,
        ?string $placeName,
        int $zoom = 17,
    ): string {
        $lat ??= 37.0951589;
        $lng ??= 37.4274178;
        $zoom = max(1, min(21, $zoom));
        $span = self::spanForZoom($lat, $zoom);
        $ts = (string) (int) (microtime(true) * 1000);

        if (filled($placeId)) {
            $name = filled($placeName) ? rawurlencode($placeName) : rawurlencode('Konum');
            $pb = sprintf(
                '!1m18!1m12!1m3!1d%.10F!2d%.8F!3d%.8F!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s%s!2s%s!5e0!3m2!1str!2str!4v%s!5m2!1str!2str',
                $span,
                $lng,
                $lat,
                rawurlencode($placeId),
                $name,
                $ts
            );
        } elseif (filled($placeName)) {
            $pb = sprintf(
                '!1m18!1m12!1m3!1d%.10F!2d%.8F!3d%.8F!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0:0x0!2s%s!5e0!3m2!1str!2str!4v%s!5m2!1str!2str',
                $span,
                $lng,
                $lat,
                rawurlencode($placeName),
                $ts
            );
        } else {
            $pb = sprintf(
                '!1m14!1m12!1m3!1d%.10F!2d%.8F!3d%.8F!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1str!2str!4v%s!5m2!1str!2str',
                $span,
                $lng,
                $lat,
                $ts
            );
        }

        return 'https://www.google.com/maps/embed?pb=' . $pb;
    }

    private static function spanForZoom(float $lat, int $zoom): float
    {
        // Aile ve Nesil ~3000–3200 span kullanıyor (mahalle seviyesi).
        $targetZoom = min($zoom, 16);
        $metersPerPx = 156543.03392 * cos(deg2rad($lat)) / (2 ** $targetZoom);

        return max(3100.0, $metersPerPx * 1000);
    }

    private static function isShortMapsUrl(string $url): bool
    {
        return Str::contains($url, ['maps.app.goo.gl', 'goo.gl/maps', 'g.page/']);
    }
}
