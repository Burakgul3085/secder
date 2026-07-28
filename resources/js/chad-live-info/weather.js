const OPEN_METEO_ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

/**
 * Seçili şehir için anlık sıcaklığı getirir.
 *
 * @param {{latitude: number, longitude: number, timezone: string}} location
 */
export async function fetchWeather(location) {
    const params = new URLSearchParams({
        latitude: String(location.latitude),
        longitude: String(location.longitude),
        current: 'temperature_2m',
        timezone: location.timezone,
    });

    const response = await fetch(`${OPEN_METEO_ENDPOINT}?${params.toString()}`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Open-Meteo responded with ${response.status}`);
    }

    const payload = await response.json();
    const temperature = payload?.current?.temperature_2m;

    if (temperature == null || Number.isNaN(Number(temperature))) {
        throw new Error('Open-Meteo returned invalid temperature');
    }

    return {
        temperature: Math.round(Number(temperature)),
    };
}
