<div class="yr-weather-card">
    @if($weather)
        <div class="weather-content">
            <h3 class="weather-location">{{ $location }}</h3>

            <div class="weather-main">
                @if($getSymbolUrl())
                    <img src="{{ $getSymbolUrl() }}" alt="Weather icon" class="weather-icon">
                @endif

                <div class="weather-temp-container">
                    <div class="weather-temp">
                        {{ $getTemperature() ?? 'N/A' }}
                    </div>
                    @if(($weather['feels_like'] ?? null) !== null && $weather['feels_like'] != $weather['temperature'])
                        <div class="feels-like">
                            Feels like {{ round($weather['feels_like'], 1) }}°
                        </div>
                    @endif
                </div>
            </div>

            <div class="weather-details">
                @if(($weather['wind_speed'] ?? null) !== null)
                    <div class="weather-detail">
                        <div class="detail-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Wind</span>
                            <span class="detail-value">{{ $getWindSpeed() }}</span>
                        </div>
                    </div>
                @endif

                @if(($weather['humidity'] ?? null) !== null)
                    <div class="weather-detail">
                        <div class="detail-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Humidity</span>
                            <span class="detail-value">{{ round($weather['humidity']) }}%</span>
                        </div>
                    </div>
                @endif

                @if(($weather['precipitation_amount'] ?? null) !== null && $weather['precipitation_amount'] > 0)
                    <div class="weather-detail">
                        <div class="detail-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C12 2 6 8 6 13C6 16.31 8.69 19 12 19C15.31 19 18 16.31 18 13C18 8 12 2 12 2Z"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Rain</span>
                            <span class="detail-value">{{ $weather['precipitation_amount'] }}mm</span>
                        </div>
                    </div>
                @endif

                @if(($weather['pressure'] ?? null) !== null)
                    <div class="weather-detail">
                        <div class="detail-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Pressure</span>
                            <span class="detail-value">{{ round($weather['pressure']) }}hPa</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="weather-footer">
                <div class="weather-time">
                    Updated {{ \Carbon\Carbon::parse($weather['time'])->diffForHumans() }}
                </div>
                <div class="weather-attribution">
                    <small>
                        Weather data from <a href="https://www.met.no/" target="_blank" rel="noopener">The Norwegian Meteorological Institute (MET Norway)</a><br>
                        Licensed under <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">CC BY 4.0</a>
                    </small>
                </div>
            </div>
        </div>
    @else
        <div class="weather-error">
            <p>Unable to load weather data</p>
        </div>
    @endif
</div>

<style>
    .yr-weather-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 2rem;
        color: white;
        max-width: 500px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .weather-location {
        margin: 0 0 1.5rem 0;
        font-size: 1.75rem;
        font-weight: 700;
        text-align: center;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .weather-main {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2rem 0;
        gap: 1.5rem;
    }

    .weather-icon {
        width: 96px;
        height: 96px;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.15));
    }

    .weather-temp-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .weather-temp {
        font-size: 4rem;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .feels-like {
        font-size: 0.95rem;
        opacity: 0.85;
        font-weight: 500;
    }

    .weather-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin: 2rem 0 1.5rem 0;
    }

    .weather-detail {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .weather-detail:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .detail-icon {
        flex-shrink: 0;
        opacity: 0.9;
    }

    .detail-content {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .detail-label {
        font-size: 0.8rem;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .detail-value {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .weather-footer {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .weather-time {
        text-align: center;
        font-size: 0.875rem;
        opacity: 0.85;
        margin-bottom: 0.75rem;
    }

    .weather-attribution {
        text-align: center;
        opacity: 0.8;
    }

    .weather-attribution small {
        font-size: 0.75rem;
    }

    .weather-attribution a {
        color: white;
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        transition: border-color 0.2s;
    }

    .weather-attribution a:hover {
        border-bottom-color: white;
    }

    .weather-error {
        text-align: center;
        padding: 2rem;
        opacity: 0.8;
    }

    @media (max-width: 640px) {
        .yr-weather-card {
            padding: 1.5rem;
        }

        .weather-temp {
            font-size: 3rem;
        }

        .weather-icon {
            width: 80px;
            height: 80px;
        }

        .weather-details {
            grid-template-columns: 1fr;
        }
    }
</style>
