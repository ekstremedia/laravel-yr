<div class="yr-forecast-card">
    <h3 class="forecast-title">{{ $days }}-Day Forecast • {{ $location }}</h3>

    @if($getDailyForecast())
        <div class="forecast-grid">
            @foreach($getDailyForecast() as $day)
                <div class="forecast-day">
                    <div class="day-header">
                        <div class="day-name">
                            {{ $day['date']->isToday() ? 'Today' : $day['date']->format('D') }}
                        </div>
                        <div class="day-date">
                            {{ $day['date']->format('M j') }}
                        </div>
                    </div>

                    @if($day['symbol_code'])
                        <img
                            src="{{ $weatherService->getSymbolUrl($day['symbol_code']) }}"
                            alt="{{ $day['symbol_code'] }}"
                            class="forecast-icon"
                            title="{{ $day['symbol_code'] }}"
                        >
                    @endif

                    <div class="temp-range">
                        <span class="temp-high">{{ $day['temp_high'] ?? 'N/A' }}°</span>
                        <span class="temp-divider">/</span>
                        <span class="temp-low">{{ $day['temp_low'] ?? 'N/A' }}°</span>
                    </div>

                    @if($day['precipitation'] > 0)
                        <div class="precip-info">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" style="opacity: 0.7">
                                <path d="M6 0C6 0 3 3 3 6C3 7.657 4.343 9 6 9C7.657 9 9 7.657 9 6C9 3 6 0 6 0Z"/>
                            </svg>
                            {{ $day['precipitation'] }}mm
                        </div>
                    @endif

                    @if($day['wind_speed_avg'])
                        <div class="wind-info">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.7">
                                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
                            </svg>
                            {{ $day['wind_speed_avg'] }}m/s
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="forecast-attribution">
            <small>
                Weather data from <a href="https://www.met.no/" target="_blank" rel="noopener">The Norwegian Meteorological Institute (MET Norway)</a><br>
                Licensed under <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">CC BY 4.0</a> and <a href="https://data.norge.no/nlod/en/2.0" target="_blank" rel="noopener">NLOD 2.0</a>
            </small>
        </div>
    @else
        <div class="forecast-error">
            <p>Unable to load forecast data</p>
        </div>
    @endif
</div>

<style>
    .yr-forecast-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 2rem;
        color: white;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        width: 100%;
    }

    .forecast-title {
        margin: 0 0 2rem 0;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .forecast-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .forecast-day {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        cursor: default;
    }

    .forecast-day:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .day-header {
        text-align: center;
        width: 100%;
    }

    .day-name {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .day-date {
        font-size: 0.875rem;
        opacity: 0.85;
    }

    .forecast-icon {
        width: 56px;
        height: 56px;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
        margin: 0.5rem 0;
    }

    .temp-range {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0.25rem 0;
    }

    .temp-high {
        color: white;
    }

    .temp-divider {
        opacity: 0.5;
        font-weight: 400;
    }

    .temp-low {
        opacity: 0.75;
        font-size: 1.25rem;
    }

    .precip-info,
    .wind-info {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.875rem;
        opacity: 0.9;
        color: rgba(255, 255, 255, 0.95);
    }

    .forecast-attribution {
        text-align: center;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        margin-top: 1rem;
        opacity: 0.8;
    }

    .forecast-attribution small {
        font-size: 0.8rem;
    }

    .forecast-attribution a {
        color: white;
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        transition: border-color 0.2s;
    }

    .forecast-attribution a:hover {
        border-bottom-color: white;
    }

    .forecast-error {
        text-align: center;
        padding: 2rem;
        opacity: 0.8;
    }

    @media (max-width: 640px) {
        .forecast-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
        }

        .forecast-day {
            padding: 1rem 0.75rem;
        }

        .forecast-icon {
            width: 48px;
            height: 48px;
        }

        .temp-range {
            font-size: 1.25rem;
        }
    }
</style>
