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
        background: linear-gradient(135deg,
                    rgba(30, 27, 75, 0.85),
                    rgba(45, 27, 78, 0.8));
        backdrop-filter: blur(40px) saturate(150%);
        -webkit-backdrop-filter: blur(40px) saturate(150%);
        border-radius: 32px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 2.5rem;
        color: white;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),
                    0 0 0 1px rgba(255, 255, 255, 0.08) inset;
        position: relative;
        overflow: hidden;
    }

    .yr-weather-card::before {
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

    .weather-location {
        margin: 0 0 2rem 0;
        font-size: 1.875rem;
        font-weight: 600;
        text-align: center;
        background: linear-gradient(135deg, #ffffff, #e8d8ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.02em;
    }

    .weather-main {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2.5rem 0;
        gap: 2rem;
    }

    .weather-icon {
        width: 100px;
        height: 100px;
        filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.15));
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .weather-temp-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.625rem;
    }

    .weather-temp {
        font-size: 4.5rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -0.04em;
        background: linear-gradient(135deg, #fff, rgba(255, 255, 255, 0.9));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .weather-temp.temp-warm {
        background: linear-gradient(135deg, #ff9a9e, #fad0c4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .weather-temp.temp-cold {
        background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feels-like {
        font-size: 0.9rem;
        font-weight: 500;
        opacity: 0.9;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .feels-like.temp-warm {
        color: #ffd3d4;
    }

    .feels-like.temp-cold {
        color: #d3e9ff;
    }

    .weather-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin: 2rem 0 1.5rem 0;
    }

    .weather-detail {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.125rem;
        background: linear-gradient(135deg,
                    rgba(255, 255, 255, 0.05),
                    rgba(255, 255, 255, 0.02));
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }

    .weather-detail:hover {
        background: linear-gradient(135deg,
                    rgba(255, 255, 255, 0.08),
                    rgba(255, 255, 255, 0.04));
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .detail-icon {
        flex-shrink: 0;
        color: rgba(255, 255, 255, 0.9);
        width: 22px;
        height: 22px;
    }

    .detail-icon svg {
        width: 100%;
        height: 100%;
    }

    .detail-content {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        min-width: 0;
    }

    .detail-label {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 500;
    }

    .detail-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.95);
        letter-spacing: -0.02em;
    }

    .weather-footer {
        margin-top: 2rem;
        padding-top: 1.75rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .weather-time {
        text-align: center;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 500;
    }

    .weather-attribution {
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
    }

    .weather-attribution small {
        font-size: 0.7rem;
        letter-spacing: 0.02em;
    }

    .weather-attribution a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.2s ease;
    }

    .weather-attribution a:hover {
        color: white;
        border-bottom-color: rgba(255, 255, 255, 0.6);
    }

    .weather-error {
        text-align: center;
        padding: 3rem 2rem;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
    }

    .weather-error p {
        margin: 0;
        background: linear-gradient(135deg, #ff9a9e, #fad0c4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    @media (max-width: 768px) {
        .yr-weather-card {
            padding: 1.75rem;
            border-radius: 24px;
        }

        .weather-location {
            font-size: 1.375rem;
            margin-bottom: 1.5rem;
        }

        .weather-main {
            flex-direction: column;
            gap: 1.25rem;
            margin: 1.5rem 0;
        }

        .weather-temp {
            font-size: 3.25rem;
        }

        .weather-icon {
            width: 72px;
            height: 72px;
        }

        .weather-details {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin: 1.5rem 0 1rem 0;
        }

        .weather-detail {
            padding: 0.875rem;
            gap: 0.625rem;
        }

        .detail-label {
            font-size: 0.65rem;
        }

        .detail-value {
            font-size: 1rem;
        }

        .weather-footer {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
        }

        .weather-time {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .yr-weather-card {
            padding: 1.5rem;
            border-radius: 20px;
        }

        .weather-location {
            font-size: 1.25rem;
        }

        .weather-temp {
            font-size: 2.75rem;
        }

        .weather-icon {
            width: 64px;
            height: 64px;
        }

        .feels-like {
            font-size: 0.7rem;
        }

        .weather-details {
            grid-template-columns: 1fr;
            gap: 0.625rem;
        }

        .detail-icon {
            width: 18px;
            height: 18px;
        }
    }
</style>
