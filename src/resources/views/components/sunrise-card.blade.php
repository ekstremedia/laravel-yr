@if($sunData)
<div class="sunrise-card">
    <div class="sunrise-header">
        <div class="sunrise-title">
            <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <div>
                <h2>Sunrise & Sunset</h2>
                @if($location)
                    <p class="location-name">{{ $location }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="sunrise-body">
        <div class="sun-times">
            @if($sunData['sunrise']['time'])
            <div class="sun-time-item sunrise">
                <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 18a5 5 0 0 0-10 0"/>
                    <line x1="12" y1="2" x2="12" y2="9"/>
                    <line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/>
                    <line x1="1" y1="18" x2="3" y2="18"/>
                    <line x1="21" y1="18" x2="23" y2="18"/>
                    <line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/>
                    <line x1="23" y1="22" x2="1" y2="22"/>
                    <polyline points="8 6 12 2 16 6"/>
                </svg>
                <div class="time-info">
                    <span class="time-label">Sunrise</span>
                    <span class="time-value">{{ \Carbon\Carbon::parse($sunData['sunrise']['time'])->format('H:i') }}</span>
                    <span class="azimuth">{{ round($sunData['sunrise']['azimuth'], 1) }}°</span>
                </div>
            </div>
            @endif

            @if($sunData['sunset']['time'])
            <div class="sun-time-item sunset">
                <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 18a5 5 0 0 0-10 0"/>
                    <line x1="12" y1="9" x2="12" y2="2"/>
                    <line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/>
                    <line x1="1" y1="18" x2="3" y2="18"/>
                    <line x1="21" y1="18" x2="23" y2="18"/>
                    <line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/>
                    <line x1="23" y1="22" x2="1" y2="22"/>
                    <polyline points="16 5 12 9 8 5"/>
                </svg>
                <div class="time-info">
                    <span class="time-label">Sunset</span>
                    <span class="time-value">{{ \Carbon\Carbon::parse($sunData['sunset']['time'])->format('H:i') }}</span>
                    <span class="azimuth">{{ round($sunData['sunset']['azimuth'], 1) }}°</span>
                </div>
            </div>
            @endif
        </div>

        <div class="sun-details">
            @if($sunData['daylight_duration'])
            <div class="detail-item">
                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <div class="detail-info">
                    <span class="detail-label">Daylight</span>
                    <span class="detail-value">{{ $sunData['daylight_duration']['hours'] }}h {{ $sunData['daylight_duration']['minutes'] }}m</span>
                </div>
            </div>
            @endif

            @if($sunData['solar_noon']['time'])
            <div class="detail-item">
                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                </svg>
                <div class="detail-info">
                    <span class="detail-label">Solar Noon</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($sunData['solar_noon']['time'])->format('H:i') }}</span>
                    <span class="detail-sub">{{ round($sunData['solar_noon']['elevation'], 1) }}° elevation</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="sunrise-footer">
        <p class="attribution">
            Data from <a href="https://www.met.no/" target="_blank" rel="noopener">The Norwegian Meteorological Institute (MET Norway)</a>
        </p>
    </div>
</div>

<style scoped>
.sunrise-card {
    background: linear-gradient(135deg, rgba(30, 27, 75, 0.85) 0%, rgba(46, 20, 55, 0.85) 100%);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #ffffff;
    max-width: 500px;
    width: 100%;
}

.sunrise-header {
    margin-bottom: 2rem;
}

.sunrise-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sun-icon {
    width: 48px;
    height: 48px;
    color: #fbbf24;
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.sunrise-title h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.location-name {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0.25rem 0 0 0;
}

.sunrise-body {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sun-times {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.sun-time-item {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.sun-time-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.sun-time-item.sunrise {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
}

.sun-time-item.sunset {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.05) 100%);
}

.time-icon {
    width: 40px;
    height: 40px;
    color: #fbbf24;
}

.sun-time-item.sunset .time-icon {
    color: #f97316;
}

.time-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.time-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.time-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
}

.azimuth {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
}

.sun-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
}

.detail-icon {
    width: 32px;
    height: 32px;
    color: #fbbf24;
    flex-shrink: 0;
}

.detail-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
    font-weight: 500;
}

.detail-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
}

.detail-sub {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
}

.sunrise-footer {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.attribution {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
    text-align: center;
}

.attribution a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.3s ease;
}

.attribution a:hover {
    color: #fbbf24;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sunrise-card {
        padding: 1.5rem;
    }

    .sunrise-title h2 {
        font-size: 1.5rem;
    }

    .sun-icon {
        width: 40px;
        height: 40px;
    }

    .sun-times {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .sunrise-card {
        padding: 1.25rem;
    }

    .sunrise-title h2 {
        font-size: 1.25rem;
    }

    .time-value {
        font-size: 1.5rem;
    }

    .detail-value {
        font-size: 1.1rem;
    }
}
</style>
@else
<div class="sunrise-card-error">
    <p>Unable to load sunrise data. Please try again later.</p>
</div>

<style scoped>
.sunrise-card-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px;
    padding: 1.5rem;
    color: #fecaca;
    text-align: center;
    max-width: 500px;
}

.sunrise-card-error p {
    margin: 0;
}
</style>
@endif
