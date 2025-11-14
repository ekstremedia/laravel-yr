@if($moonData)
<div class="moon-card">
    <div class="moon-header">
        <div class="moon-title">
            <div class="moon-phase-display">
                <span class="moon-emoji">{{ $moonData['phase_emoji'] }}</span>
            </div>
            <div>
                <h2>Moon Phase</h2>
                @if($location)
                    <p class="location-name">{{ $location }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="moon-body">
        <div class="moon-phase-info">
            <div class="phase-visual">
                <div class="moon-circle">
                    <div class="moon-shadow" style="--phase: {{ $moonData['moon_phase'] ?? 0 }}deg;"></div>
                </div>
                <div class="phase-details">
                    <span class="phase-name">{{ $moonData['phase_name'] }}</span>
                    <span class="phase-degree">{{ round($moonData['moon_phase'] ?? 0, 1) }}°</span>
                </div>
            </div>
        </div>

        <div class="moon-times">
            @if($moonData['moonrise']['time'])
            <div class="moon-time-item moonrise">
                <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <polyline points="16 5 12 9 8 5"/>
                </svg>
                <div class="time-info">
                    <span class="time-label">Moonrise</span>
                    <span class="time-value">{{ \Carbon\Carbon::parse($moonData['moonrise']['time'])->format('H:i') }}</span>
                    <span class="azimuth">{{ round($moonData['moonrise']['azimuth'], 1) }}°</span>
                </div>
            </div>
            @endif

            @if($moonData['moonset']['time'])
            <div class="moon-time-item moonset">
                <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <polyline points="8 14 12 10 16 14"/>
                </svg>
                <div class="time-info">
                    <span class="time-label">Moonset</span>
                    <span class="time-value">{{ \Carbon\Carbon::parse($moonData['moonset']['time'])->format('H:i') }}</span>
                    <span class="azimuth">{{ round($moonData['moonset']['azimuth'], 1) }}°</span>
                </div>
            </div>
            @endif
        </div>

        <div class="moon-details">
            @if($moonData['high_moon']['time'])
            <div class="detail-item">
                <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    <polyline points="12 8 12 12"/>
                </svg>
                <div class="detail-info">
                    <span class="detail-label">High Moon</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($moonData['high_moon']['time'])->format('H:i') }}</span>
                    <span class="detail-sub">{{ round($moonData['high_moon']['elevation'], 1) }}° elevation</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="moon-footer">
        <p class="attribution">
            Data from <a href="https://www.met.no/" target="_blank" rel="noopener">The Norwegian Meteorological Institute (MET Norway)</a>
        </p>
    </div>
</div>

<style scoped>
.moon-card {
    background: linear-gradient(135deg, rgba(30, 27, 75, 0.85) 0%, rgba(20, 30, 48, 0.85) 100%);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #ffffff;
    max-width: 500px;
    width: 100%;
}

.moon-header {
    margin-bottom: 2rem;
}

.moon-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.moon-phase-display {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.moon-emoji {
    font-size: 3rem;
    line-height: 1;
}

.moon-title h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.location-name {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0.25rem 0 0 0;
}

.moon-body {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.moon-phase-info {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.phase-visual {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.moon-circle {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f4f6 0%, #d1d5db 100%);
    box-shadow:
        0 0 20px rgba(167, 139, 250, 0.3),
        inset 0 0 10px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.moon-shadow {
    position: absolute;
    top: 0;
    right: 0;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, rgba(30, 27, 75, 0.9) 0%, rgba(30, 27, 75, 0.6) 100%);
    transform-origin: left center;
    transform: rotate(var(--phase));
    transition: transform 0.5s ease;
}

.phase-details {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.phase-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #a78bfa;
    text-align: center;
}

.phase-degree {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
}

.moon-times {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.moon-time-item {
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

.moon-time-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.moon-time-item.moonrise {
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.1) 0%, rgba(124, 58, 237, 0.05) 100%);
}

.moon-time-item.moonset {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(91, 33, 182, 0.05) 100%);
}

.time-icon {
    width: 40px;
    height: 40px;
    color: #a78bfa;
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

.moon-details {
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
    color: #a78bfa;
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

.moon-footer {
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
    color: #a78bfa;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .moon-card {
        padding: 1.5rem;
    }

    .moon-title h2 {
        font-size: 1.5rem;
    }

    .moon-emoji {
        font-size: 2.5rem;
    }

    .moon-circle {
        width: 100px;
        height: 100px;
    }

    .moon-times {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .moon-card {
        padding: 1.25rem;
    }

    .moon-title h2 {
        font-size: 1.25rem;
    }

    .moon-emoji {
        font-size: 2rem;
    }

    .moon-circle {
        width: 90px;
        height: 90px;
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
<div class="moon-card-error">
    <p>Unable to load moon data. Please try again later.</p>
</div>

<style scoped>
.moon-card-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px;
    padding: 1.5rem;
    color: #fecaca;
    text-align: center;
    max-width: 500px;
}

.moon-card-error p {
    margin: 0;
}
</style>
@endif
