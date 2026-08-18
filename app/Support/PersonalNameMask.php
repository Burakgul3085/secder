<?php

namespace App\Support;

class PersonalNameMask
{
    public static function display(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '-';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '-';
        }

        return collect($parts)
            ->map(function (string $part): string {
                $first = mb_substr($part, 0, 1);
                $restLength = max(mb_strlen($part) - 1, 1);

                return $first . str_repeat('*', min($restLength, 6));
            })
            ->implode(' ');
    }
}
