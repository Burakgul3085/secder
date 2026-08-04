import { configureCachePrefix, getCachedValue, isExpired, setCache } from './cache';
import { fetchAladhanData, localizeHijri, localizePrayer, resolveNextPrayer } from './prayer';
import { configureTimezone, formatLocalTime, getLocalDateKey } from './time';
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
        _lastPrayerMinute: null,

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
            if (this.isPrayerScheduleStale() || isExpired('hijri', TTL.hijri)) {
                tasks.push(this.loadAladhan());
            }

            if (tasks.length) {
                await Promise.allSettled(tasks);
            } else {
                this.updateNextPrayer();
            }
        },

        tickClock() {
            const next = formatLocalTime(this.locale);
            if (next !== this.localTime) {
                this.localTime = next;
                this.timeTick = true;

                if (this._tickTimer) {
                    window.clearTimeout(this._tickTimer);
                }

                this._tickTimer = window.setTimeout(() => {
                    this.timeTick = false;
                }, 280);
            }

            // Dakika değişince sıradaki namazı canlı çizelgeden yeniden hesapla.
            const minuteKey = Math.floor(Date.now() / 60_000);
            if (minuteKey !== this._lastPrayerMinute) {
                this._lastPrayerMinute = minuteKey;
                this.updateNextPrayer();
            }
        },

        isPrayerScheduleStale() {
            if (isExpired('prayer', TTL.prayer)) {
                return true;
            }

            const cached = getCachedValue('prayer');
            if (!cached?.timings) {
                return true;
            }

            if (cached.date && cached.date !== getLocalDateKey()) {
                return true;
            }

            return false;
        },

        updateNextPrayer() {
            const cached = getCachedValue('prayer');
            const timings = cached?.timings;

            if (!timings) {
                return;
            }

            if (cached.date && cached.date !== getLocalDateKey()) {
                return;
            }

            const nextPrayer = resolveNextPrayer(timings);
            const localized = localizePrayer(nextPrayer, this.prayerNames);
            this.prayerName = localized.name;
            this.prayerTime = localized.time;
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
            if (cached?.timings) {
                this.updateNextPrayer();
                return;
            }

            // Eski önbellek formatı (yalnızca tek vakit) — bir kez göster, sonra yenile.
            if (cached?.key) {
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
            const prayerFresh = !this.isPrayerScheduleStale();

            if (hijriFresh && prayerFresh) {
                this.applyHijriFromCache();
                this.updateNextPrayer();
                return;
            }

            try {
                const data = await fetchAladhanData(this.location);

                if (!hijriFresh || !getCachedValue('hijri')) {
                    setCache('hijri', data.hijri);
                    this.hijri = localizeHijri(data.hijri, this.hijriMonths);
                }

                if (!prayerFresh || !getCachedValue('prayer')?.timings) {
                    setCache('prayer', {
                        timings: data.timings,
                        date: getLocalDateKey(),
                    });
                    this.updateNextPrayer();
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
