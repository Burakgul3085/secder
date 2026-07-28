// Önek config/live_info.php üzerinden gelir; şehir değişince eski veri okunmaz.
let prefix = 'bkd_live_';

export function configureCachePrefix(value) {
    if (typeof value === 'string' && value.trim() !== '') {
        prefix = value;
    }
}

export function getCacheEntry(key) {
    try {
        const raw = localStorage.getItem(prefix + key);
        if (!raw) {
            return null;
        }

        return JSON.parse(raw);
    } catch {
        return null;
    }
}

export function getCachedValue(key) {
    return getCacheEntry(key)?.value ?? null;
}

export function setCache(key, value) {
    try {
        localStorage.setItem(prefix + key, JSON.stringify({
            value,
            fetchedAt: Date.now(),
        }));
    } catch {
        // localStorage unavailable — silently ignore
    }
}

export function isExpired(key, ttlMs) {
    const entry = getCacheEntry(key);
    if (!entry?.fetchedAt) {
        return true;
    }

    return Date.now() - entry.fetchedAt > ttlMs;
}
