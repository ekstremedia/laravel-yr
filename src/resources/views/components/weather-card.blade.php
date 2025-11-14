<div class="yr-weather-card">
    @if($weather)
        <div class="weather-content">
            <h3 class="weather-location">{{ $location }}</h3>

            <div class="weather-main">
                @if($getSymbolUrl())
                    <img src="{{ $getSymbolUrl() }}" alt="Weather icon" class="weather-icon">
                @endif

                <div class="weather-temp-container">
                    <div class="weather-temp {{ ($weather['temperature'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">
                        {{ $getTemperature() ?? 'N/A' }}
                    </div>
                    @if(($weather['feels_like'] ?? null) !== null && $weather['feels_like'] != $weather['temperature'])
                        <div class="feels-like {{ ($weather['feels_like'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">
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
        background: rgba(46, 16, 101, 0.7);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border-radius: 28px;
        border: 1px solid rgba(88, 28, 135, 0.6);
        padding: 2rem;
        color: white;
        width: 100%;
        box-shadow: 0 12px 40px rgba(46, 16, 101, 0.5),
                    inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    .weather-location {
        margin: 0 0 1.5rem 0;
        font-size: 1.75rem;
        font-weight: 700;
        text-align: center;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.5);
        color: #ffffff;
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
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
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
        text-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    }

    .weather-temp.temp-warm {
        color: #ff6b6b;
        text-shadow: 0 4px 16px rgba(255, 107, 107, 0.5);
    }

    .weather-temp.temp-cold {
        color: #4dabf7;
        text-shadow: 0 4px 16px rgba(77, 171, 247, 0.5);
    }

    .feels-like {
        font-size: 0.95rem;
        font-weight: 500;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .feels-like.temp-warm {
        color: #ffa8a8;
    }

    .feels-like.temp-cold {
        color: #74c0fc;
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
        background: rgba(46, 16, 101, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 14px;
        border: 1px solid rgba(88, 28, 135, 0.6);
        transition: all 0.3s ease;
    }

    .weather-detail:hover {
        background: rgba(46, 16, 101, 0.7);
        border-color: rgba(109, 40, 217, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(46, 16, 101, 0.5);
    }

    .detail-icon {
        flex-shrink: 0;
        color: #ffffff;
    }

    .detail-content {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .detail-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .detail-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #ffffff;
    }

    .weather-footer {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.25);
    }

    .weather-time {
        text-align: center;
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.75rem;
    }

    .weather-attribution {
        text-align: center;
        color: rgba(255, 255, 255, 0.85);
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
        color: rgba(255, 255, 255, 0.85);
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
