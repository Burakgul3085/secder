import { configureCachePrefix, getCachedValue, isExpired, setCache } from './cache';
import { fetchAladhanData, localizeHijri, localizePrayer } from './prayer';
import { configureTimezone, formatLocalTime } from './time';
import { fetchWeather } from './weather';

const TTL = {
    weather: 30 * 60 * 1000,
    hijri: 24 * 60 * 60 * 1000,
    prayer: 12 * 60 * 60 * 1000,
};

// config/live_info.php gelmezse kullanılacak yedek şehir bilgisi.
const DEFAULT_LOCATION = {
    latitude: 37.0662,
    longitude: 37.3833,
    timezone: 'Europe/Istanbul',
    prayerMethod: 13,
    cachePrefix: 'bkd_live_gaziantep_',
};

document.addEventListener('alpine:init', () => {
    Alpine.data('chadLiveInfo', (config = {}) => ({
        loading: true,
        ready: false,
        locale: config.locale ?? 'tr',
        labels: config.labels ?? {},
        prayerNames: config.prayerNames ?? {},
        hijriMonths: config.hijriMonths ?? {},
        donateUrl: config.donateUrl ?? '/bagis-yap',
        location: { ...DEFAULT_LOCATION, ...(config.location ?? {}) },
        weather: null,
        weatherError: false,
        localTime: '--',
        hijri: '--',
        prayerName: '--',
        prayerTime: '--',
        timeTick: false,
        _clockTimer: null,
        _refreshTimer: null,
        _tickTimer: null,

        init() {
            // Önbellek ve saat dilimi, veri okumadan önce şehre göre ayarlanır.
            configureCachePrefix(this.location.cachePrefix);
            configureTimezone(this.location.timezone);

            this.bootstrap();
            this._clockTimer = window.setInterval(() => this.tickClock(), 1000);
            this._refreshTimer = window.setInterval(() => this.refreshStaleData(), 60_000);
        },

        destroy() {
            if (this._clockTimer) {
                window.clearInterval(this._clockTimer);
            }
            if (this._refreshTimer) {
                window.clearInterval(this._refreshTimer);
            }
            if (this._tickTimer) {
                window.clearTimeout(this._tickTimer);
            }
        },

        async bootstrap() {
            this.loading = true;
            this.applyWeatherFromCache();
            this.applyHijriFromCache();
            this.applyPrayerFromCache();
            this.tickClock();

            await Promise.allSettled([
                this.loadWeather(),
                this.loadAladhan(),
            ]);

            this.loading = false;
            this.ready = true;
        },

        async refreshStaleData() {
            this.tickClock();

            const tasks = [];
            if (isExpired('weather', TTL.weather)) {
                tasks.push(this.loadWeather());
            }
            if (isExpired('hijri', TTL.hijri) || isExpired('prayer', TTL.prayer)) {
                tasks.push(this.loadAladhan());
            }

            if (tasks.length) {
                await Promise.allSettled(tasks);
            }
        },

        tickClock() {
            const next = formatLocalTime(this.locale);
            if (next === this.localTime) {
                return;
            }

            this.localTime = next;
            this.timeTick = true;

            if (this._tickTimer) {
                window.clearTimeout(this._tickTimer);
            }

            this._tickTimer = window.setTimeout(() => {
                this.timeTick = false;
            }, 280);
        },

        applyWeatherFromCache() {
            const cached = getCachedValue('weather');
            if (cached?.temperature != null) {
                this.weather = `${cached.temperature}°C`;
                this.weatherError = false;
            }
        },

        applyHijriFromCache() {
            const cached = getCachedValue('hijri');
            if (cached) {
                this.hijri = localizeHijri(cached, this.hijriMonths);
            }
        },

        applyPrayerFromCache() {
            const cached = getCachedValue('prayer');
            if (cached) {
                const localized = localizePrayer(cached, this.prayerNames);
                this.prayerName = localized.name;
                this.prayerTime = localized.time;
            }
        },

        async loadWeather() {
            if (!isExpired('weather', TTL.weather)) {
                this.applyWeatherFromCache();
                return;
            }

            try {
                const data = await fetchWeather(this.location);
                setCache('weather', data);
                this.weather = `${data.temperature}°C`;
                this.weatherError = false;
            } catch {
                const cached = getCachedValue('weather');
                if (cached?.temperature != null) {
                    this.weather = `${cached.temperature}°C`;
                    this.weatherError = false;
                } else {
                    this.weather = null;
                    this.weatherError = true;
                }
            }
        },

        async loadAladhan() {
            const hijriFresh = !isExpired('hijri', TTL.hijri);
            const prayerFresh = !isExpired('prayer', TTL.prayer);

            if (hijriFresh && prayerFresh) {
                this.applyHijriFromCache();
                this.applyPrayerFromCache();
                return;
            }

            try {
                const data = await fetchAladhanData(this.location);

                if (!hijriFresh || !getCachedValue('hijri')) {
                    setCache('hijri', data.hijri);
                    this.hijri = localizeHijri(data.hijri, this.hijriMonths);
                }

                if (!prayerFresh || !getCachedValue('prayer')) {
                    setCache('prayer', data.nextPrayer);
                    const localized = localizePrayer(data.nextPrayer, this.prayerNames);
                    this.prayerName = localized.name;
                    this.prayerTime = localized.time;
                }
            } catch {
                this.applyHijriFromCache();
                this.applyPrayerFromCache();

                if (!getCachedValue('hijri')) {
                    this.hijri = '--';
                }
                if (!getCachedValue('prayer')) {
                    this.prayerName = '--';
                    this.prayerTime = '--';
                }
            }
        },

        get weatherDisplay() {
            if (this.weatherError) {
                return this.labels.weather_error ?? 'Veri Alınamadı';
            }

            return this.weather ?? '--';
        },
    }));
});
