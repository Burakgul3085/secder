// Saat dilimi config/live_info.php üzerinden gelir; varsayılan Gaziantep.
let timezone = 'Europe/Istanbul';

const localeMap = {
    tr: 'tr-TR',
    en: 'en-GB',
    ar: 'ar',
    ru: 'ru-RU',
};

export function configureTimezone(value) {
    if (typeof value === 'string' && value.trim() !== '') {
        timezone = value;
    }
}

export function resolveLocale(locale) {
    return localeMap[locale] ?? 'tr-TR';
}

export function formatLocalTime(locale = 'tr') {
    return new Intl.DateTimeFormat(resolveLocale(locale), {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(new Date());
}

export function getMinutesSinceMidnight() {
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).formatToParts(new Date());

    const hour = Number(parts.find((part) => part.type === 'hour')?.value ?? 0);
    const minute = Number(parts.find((part) => part.type === 'minute')?.value ?? 0);

    return hour * 60 + minute;
}

/** Yerel takvim günü (YYYY-MM-DD); namaz çizelgesi gün değişince yenilenir. */
export function getLocalDateKey() {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date());
}

/** Örn. tr: "6 Ağustos Perşembe" */
export function formatGregorianDate(locale = 'tr') {
    const parts = new Intl.DateTimeFormat(resolveLocale(locale), {
        timeZone: timezone,
        day: 'numeric',
        month: 'long',
        weekday: 'long',
    }).formatToParts(new Date());

    const day = parts.find((part) => part.type === 'day')?.value ?? '';
    const month = parts.find((part) => part.type === 'month')?.value ?? '';
    const weekday = parts.find((part) => part.type === 'weekday')?.value ?? '';

    return [day, month, weekday].filter(Boolean).join(' ').trim() || '--';
}

export function parsePrayerTime(time) {
    const [hour, minute] = String(time).split(':').map(Number);

    return (hour * 60) + (minute || 0);
}
