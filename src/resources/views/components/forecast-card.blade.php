<div class="yr-forecast-card-purple">
    <div class="forecast-header-purple">
        <h3 class="forecast-title-purple">{{ $days }}-Day Forecast • {{ $location }}</h3>
        <p class="forecast-coordinates">{{ $latitude }}°N, {{ $longitude }}°E</p>
    </div>

    @if($getDailyForecast())
        <div class="forecast-days-container">
            @foreach($getDailyForecast() as $index => $day)
                <div class="day-row" x-data="{ expanded: {{ $index === 0 ? 'true' : 'false' }} }">
                    <div class="day-summary" @click="expanded = !expanded">
                        <div class="day-info">
                            <div class="day-name-large">
                                {{ $day['date']->isToday() ? 'Today' : $day['date']->format('l') }}
                            </div>
                            <div class="day-date-small">
                                {{ $day['date']->format('F j, Y') }}
                            </div>
                        </div>

                        @if($day['symbol_code'])
                            <div class="day-icon-col">
                                <img
                                    src="{{ $weatherService->getSymbolUrl($day['symbol_code']) }}"
                                    alt="{{ $day['symbol_code'] }}"
                                    class="day-icon"
                                >
                            </div>
                        @endif

                        <div class="temp-range-large">
                            <span class="temp-high-large {{ ($day['temp_high'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">{{ $day['temp_high'] ?? 'N/A' }}°</span>
                            <span class="temp-divider-large">/</span>
                            <span class="temp-low-large {{ ($day['temp_low'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">{{ $day['temp_low'] ?? 'N/A' }}°</span>
                        </div>

                        <div class="day-stats">
                            @if($day['precipitation'] > 0)
                                <div class="stat-item">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                        <path d="M6 0C6 0 3 3 3 6C3 7.657 4.343 9 6 9C7.657 9 9 7.657 9 6C9 3 6 0 6 0Z"/>
                                    </svg>
                                    <span>{{ $day['precipitation'] }}mm</span>
                                </div>
                            @endif
                            @if($day['wind_speed_avg'])
                                <div class="stat-item">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
                                    </svg>
                                    <span>{{ $day['wind_speed_avg'] }}m/s</span>
                                </div>
                            @endif
                        </div>

                        <div class="expand-icon" :class="{ 'rotated': expanded }">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                    </div>

                    <div class="timeseries-container" x-show="expanded" x-collapse>
                        <div class="timeseries-list">
                            @foreach($day['timeseries'] as $hour)
                                <div class="hour-row">
                                    <div class="hour-time-col">{{ $hour['time']->format('H:i') }}</div>

                                    <div class="hour-icon-col">
                                        @if($hour['symbol_code'])
                                            <img
                                                src="{{ $weatherService->getSymbolUrl($hour['symbol_code']) }}"
                                                alt="{{ $hour['symbol_code'] }}"
                                                class="hour-icon-small"
                                            >
                                        @endif
                                    </div>

                                    <div class="hour-temp-col">
                                        <span class="temp-value {{ ($hour['temperature'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">{{ $hour['temperature'] !== null ? round($hour['temperature'], 1) . '°' : 'N/A' }}</span>
                                    </div>

                                    @if($hour['feels_like'] !== null && $hour['feels_like'] != $hour['temperature'])
                                        <div class="hour-feels-col">
                                            <span class="feels-value {{ ($hour['feels_like'] ?? 0) > 0 ? 'temp-warm' : 'temp-cold' }}">feels {{ round($hour['feels_like'], 1) }}°</span>
                                        </div>
                                    @else
                                        <div class="hour-feels-col"></div>
                                    @endif

                                    @if($hour['precipitation_amount'] > 0)
                                        <div class="hour-rain-col">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                                <path d="M6 0C6 0 3 3 3 6C3 7.657 4.343 9 6 9C7.657 9 9 7.657 9 6C9 3 6 0 6 0Z"/>
                                            </svg>
                                            <span>{{ $hour['precipitation_amount'] }}mm</span>
                                        </div>
                                    @else
                                        <div class="hour-rain-col"></div>
                                    @endif

                                    @if($hour['wind_speed'] !== null)
                                        <div class="hour-wind-col">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2"/>
                                            </svg>
                                            <span>{{ round($hour['wind_speed'], 1) }}m/s</span>
                                        </div>
                                    @else
                                        <div class="hour-wind-col"></div>
                                    @endif

                                    @if($hour['humidity'] !== null)
                                        <div class="hour-humidity-col">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                            </svg>
                                            <span>{{ round($hour['humidity']) }}%</span>
                                        </div>
                                    @else
                                        <div class="hour-humidity-col"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="forecast-error-purple">
            <p>Unable to load forecast data</p>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    .yr-forecast-card-purple {
        background: linear-gradient(135deg,
                    rgba(88, 86, 214, 0.42),
                    rgba(175, 108, 233, 0.38));
        backdrop-filter: blur(40px) saturate(150%);
        -webkit-backdrop-filter: blur(40px) saturate(150%);
        border-radius: 32px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 2.5rem;
        color: white;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15),
                    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .yr-forecast-card-purple::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg,
            transparent,
            rgba(255, 255, 255, 0.3),
            transparent);
    }

    .forecast-header-purple {
        text-align: center;
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(180deg,
                    rgba(255, 255, 255, 0.02),
                    transparent);
    }

    .forecast-title-purple {
        margin: 0 0 0.625rem 0;
        font-size: 1.625rem;
        font-weight: 600;
        background: linear-gradient(135deg, #ffffff, #e8d8ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.02em;
    }

    .forecast-coordinates {
        margin: 0;
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 500;
    }

    .forecast-days-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .day-row {
        background: linear-gradient(135deg,
                    rgba(255, 255, 255, 0.06),
                    rgba(255, 255, 255, 0.02));
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }

    .day-row:hover {
        border-color: rgba(255, 255, 255, 0.2);
        background: linear-gradient(135deg,
                    rgba(255, 255, 255, 0.08),
                    rgba(255, 255, 255, 0.03));
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .day-summary {
        display: grid;
        grid-template-columns: 180px 70px 140px 1fr 40px;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .day-summary:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .day-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .day-name-large {
        font-size: 1.125rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.95);
        letter-spacing: -0.02em;
    }

    .day-date-small {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 500;
    }

    .day-icon-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-icon {
        width: 56px;
        height: 56px;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
    }

    .temp-range-large {
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .temp-high-large {
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .temp-high-large.temp-warm {
        color: #ff6b6b;
        text-shadow: 0 2px 8px rgba(255, 107, 107, 0.5);
    }

    .temp-high-large.temp-cold {
        color: #4dabf7;
        text-shadow: 0 2px 8px rgba(77, 171, 247, 0.5);
    }

    .temp-divider-large {
        opacity: 0.6;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
    }

    .temp-low-large {
        font-size: 1.375rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .temp-low-large.temp-warm {
        color: #ffa8a8;
        text-shadow: 0 2px 8px rgba(255, 168, 168, 0.5);
    }

    .temp-low-large.temp-cold {
        color: #74c0fc;
        text-shadow: 0 2px 8px rgba(116, 192, 252, 0.5);
    }

    .day-stats {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .stat-item svg {
        flex-shrink: 0;
        opacity: 0.6;
    }

    .expand-icon {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.7;
    }

    .expand-icon.rotated {
        transform: rotate(180deg);
    }

    .timeseries-container {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
    }

    .timeseries-list {
        display: flex;
        flex-direction: column;
        padding: 0.75rem;
    }

    .hour-row {
        display: grid;
        grid-template-columns: 60px 50px 80px 100px 90px 90px 90px;
        align-items: center;
        gap: 1rem;
        padding: 0.625rem 1.25rem;
        background: transparent;
        border-radius: 10px;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
    }

    .hour-row:last-child {
        margin-bottom: 0;
    }

    .hour-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .hour-time-col {
        font-size: 0.875rem;
        font-weight: 600;
        color: #ffffff;
        letter-spacing: 0.3px;
    }

    .hour-icon-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hour-icon-small {
        width: 36px;
        height: 36px;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
    }

    .hour-temp-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .temp-value {
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1;
        letter-spacing: -0.02em;
        color: rgba(255, 255, 255, 0.9);
    }

    .temp-value.temp-warm {
        background: linear-gradient(135deg, #ff9a9e, #fad0c4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .temp-value.temp-cold {
        background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hour-feels-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .feels-value {
        font-size: 0.75rem;
        font-weight: 500;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        line-height: 1;
        opacity: 0.8;
    }

    .feels-value.temp-warm {
        color: #ffa8a8;
    }

    .feels-value.temp-cold {
        color: #74c0fc;
    }

    .hour-rain-col,
    .hour-wind-col,
    .hour-humidity-col {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .hour-rain-col svg,
    .hour-wind-col svg,
    .hour-humidity-col svg {
        flex-shrink: 0;
        opacity: 0.6;
    }

    .forecast-attribution-purple {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        margin-top: 1.5rem;
    }

    .forecast-attribution-purple small {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .forecast-attribution-purple a {
        color: #ffffff;
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.2s;
    }

    .forecast-attribution-purple a:hover {
        border-bottom-color: #ffffff;
        color: #ff6b6b;
    }

    .forecast-error-purple {
        text-align: center;
        padding: 2rem;
        color: rgba(255, 255, 255, 0.9);
    }

    @media (max-width: 768px) {
        .day-summary {
            grid-template-columns: 1fr 60px 120px 30px;
            gap: 1rem;
            padding: 1rem 1.25rem;
        }

        .day-stats {
            grid-column: 1 / -1;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .temp-range-large {
            font-size: 1.5rem;
        }

        .day-icon {
            width: 48px;
            height: 48px;
        }

        .hour-row {
            grid-template-columns: 55px 45px 75px 90px 80px 80px 80px;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
        }

        .temp-value {
            font-size: 1.125rem;
        }

        .feels-value {
            font-size: 0.6875rem;
        }

        .hour-rain-col,
        .hour-wind-col,
        .hour-humidity-col {
            font-size: 0.6875rem;
        }
    }

    @media (max-width: 640px) {
        .yr-forecast-card-purple {
            padding: 1.5rem;
        }

        .day-summary {
            grid-template-columns: 1fr 50px 30px;
            gap: 0.75rem;
            padding: 1rem;
        }

        .temp-range-large {
            grid-column: 1 / -1;
            font-size: 1.375rem;
            margin-top: 0.5rem;
        }

        .day-stats {
            order: 5;
        }

        .expand-icon {
            order: 4;
        }

        .day-icon {
            width: 44px;
            height: 44px;
        }

        .hour-row {
            grid-template-columns: 50px 38px 1fr;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
        }

        .hour-icon-small {
            width: 32px;
            height: 32px;
        }

        .temp-value {
            font-size: 1rem;
        }

        .feels-value {
            font-size: 0.625rem;
        }

        .hour-feels-col,
        .hour-rain-col,
        .hour-wind-col,
        .hour-humidity-col {
            grid-column: 1 / -1;
            justify-content: flex-start;
            font-size: 0.6875rem;
            padding-left: 0.5rem;
        }

        .hour-feels-col:empty,
        .hour-rain-col:empty,
        .hour-wind-col:empty,
        .hour-humidity-col:empty {
            display: none;
        }
    }
</style>
