<div class="yr-weather-card">
    @if($weather)
        <div class="weather-content">
            <h3 class="weather-location">{{ $location }}</h3>

            <div class="weather-main">
                @if($getSymbolUrl())
                    <img src="{{ $getSymbolUrl() }}" alt="Weather icon" class="weather-icon">
                @endif

                <div class="weather-temp">
                    {{ $getTemperature() ?? 'N/A' }}
                </div>
            </div>

            <div class="weather-details">
                @if($weather['wind_speed'] !== null)
                    <div class="weather-detail">
                        <span class="detail-label">Wind:</span>
                        <span class="detail-value">{{ $getWindSpeed() }}</span>
                    </div>
                @endif

                @if($weather['humidity'] !== null)
                    <div class="weather-detail">
                        <span class="detail-label">Humidity:</span>
                        <span class="detail-value">{{ round($weather['humidity']) }}%</span>
                    </div>
                @endif

                @if($weather['precipitation'] !== null)
                    <div class="weather-detail">
                        <span class="detail-label">Precipitation:</span>
                        <span class="detail-value">{{ $weather['precipitation'] }} mm</span>
                    </div>
                @endif
            </div>

            <div class="weather-time">
                Updated: {{ \Carbon\Carbon::parse($weather['time'])->diffForHumans() }}
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .weather-location {
        margin: 0 0 1rem 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .weather-main {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1.5rem 0;
        gap: 1rem;
    }

    .weather-icon {
        width: 80px;
        height: 80px;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    }

    .weather-temp {
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
    }

    .weather-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin: 1.5rem 0;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
    }

    .weather-detail {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .detail-value {
        font-size: 1.125rem;
        font-weight: 600;
    }

    .weather-time {
        text-align: center;
        font-size: 0.875rem;
        opacity: 0.8;
        margin-top: 1rem;
    }

    .weather-error {
        text-align: center;
        padding: 2rem;
    }
</style>
