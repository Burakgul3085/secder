import { getMinutesSinceMidnight, parsePrayerTime } from './time';

const ALADHAN_ENDPOINT = 'https://api.aladhan.com/v1/timings';

const PRAYER_KEYS = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

/**
 * Seçili şehir için hicri tarih ve sıradaki namaz vaktini getirir.
 *
 * @param {{latitude: number, longitude: number, prayerMethod: number}} location
 */
export async function fetchAladhanData(location) {
    const params = new URLSearchParams({
        latitude: String(location.latitude),
        longitude: String(location.longitude),
        method: String(location.prayerMethod),
    });

    const response = await fetch(`${ALADHAN_ENDPOINT}?${params.toString()}`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Aladhan responded with ${response.status}`);
    }

    const payload = await response.json();
    const data = payload?.data;

    if (!data?.timings || !data?.date?.hijri) {
        throw new Error('Aladhan returned invalid payload');
    }

    return {
        hijri: formatHijri(data.date.hijri),
        hijriRaw: data.date.hijri,
        nextPrayer: resolveNextPrayer(data.timings),
    };
}

function formatHijri(hijri) {
    const day = hijri?.day ?? '';
    const monthNumber = Number(hijri?.month?.number ?? 0);
    const year = hijri?.year ?? '';

    return {
        day,
        monthNumber,
        year,
        display: `${day} ${hijri?.month?.en ?? ''} ${year}`.trim(),
    };
}

export function localizeHijri(hijriRaw, hijriMonths = {}) {
    if (!hijriRaw) {
        return '--';
    }

    const monthName = hijriMonths[String(hijriRaw.monthNumber)] ?? hijriRaw.display;

    return `${hijriRaw.day} ${monthName} ${hijriRaw.year}`.trim();
}

function resolveNextPrayer(timings) {
    const now = getMinutesSinceMidnight();

    for (const key of PRAYER_KEYS) {
        const time = timings[key];
        if (!time) {
            continue;
        }

        const minutes = parsePrayerTime(time);
        if (minutes > now) {
            return {
                key,
                time: time.slice(0, 5),
            };
        }
    }

    const fajr = timings.Fajr?.slice(0, 5) ?? '--';

    return {
        key: 'Fajr',
        time: fajr,
        isTomorrow: true,
    };
}

export function localizePrayer(prayer, prayerNames = {}) {
    if (!prayer?.key) {
        return { name: '--', time: '--' };
    }

    return {
        name: prayerNames[prayer.key] ?? prayer.key,
        time: prayer.time ?? '--',
    };
}
