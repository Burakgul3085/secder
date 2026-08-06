<?php

namespace App\Support;

class MailTemplate
{
    /**
     * Ad-soyadı e-posta selamlaması için düzgün biçimler (burak gül → Burak Gül).
     */
    public static function personName(?string ...$parts): string
    {
        $joined = collect($parts)
            ->filter(fn ($part) => filled($part))
            ->map(fn ($part) => trim((string) $part))
            ->implode(' ');

        $raw = trim((string) preg_replace('/\s+/u', ' ', $joined));

        if ($raw === '') {
            return '';
        }

        return mb_convert_case(mb_strtolower($raw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    public static function greeting(?string ...$parts): string
    {
        $name = self::personName(...$parts);

        return $name !== '' ? 'Merhaba '.$name.',' : 'Merhaba,';
    }
}
