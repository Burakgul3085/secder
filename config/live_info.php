<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ana sayfa canlı bilgi kartı
    |--------------------------------------------------------------------------
    |
    | Ana sayfadaki hava durumu, yerel saat, hicri tarih ve namaz vakti
    | kartının hangi şehir için çalıştığını belirler. Şehir değişeceğinde
    | yalnızca bu dosyayı (veya .env değerlerini) güncellemek yeterlidir.
    |
    */

    'city' => env('LIVE_INFO_CITY', 'Gaziantep'),

    'latitude' => (float) env('LIVE_INFO_LATITUDE', 37.0662),

    'longitude' => (float) env('LIVE_INFO_LONGITUDE', 37.3833),

    'timezone' => env('LIVE_INFO_TIMEZONE', 'Europe/Istanbul'),

    // Aladhan namaz vakti hesaplama yöntemi (13 = Diyanet İşleri Başkanlığı).
    'prayer_method' => (int) env('LIVE_INFO_PRAYER_METHOD', 13),

    // Tarayıcı önbellek anahtarı öneki; şehir değişince eski veriler okunmaz.
    'cache_prefix' => env('LIVE_INFO_CACHE_PREFIX', 'bkd_live_gaziantep_'),
];
