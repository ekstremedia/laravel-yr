<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yr Weather Demo - Sortland, Norway</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #1a0b2e 0%, #2d1b4e 25%, #3d2463 50%, #4a2c6d 75%, #2d1b4e 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(167, 139, 250, 0.08) 0%, transparent 50%);
            pointer-events: none;
            opacity: 0.6;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            margin-bottom: 3rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .info {
            background: rgba(139, 92, 246, 0.15);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-radius: 24px;
            border: 1px solid rgba(167, 139, 250, 0.3);
            padding: 1.5rem;
            color: white;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 12px 40px rgba(139, 92, 246, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .info p {
            margin: 0.5rem 0;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .info code {
            background: rgba(109, 40, 217, 0.3);
            padding: 0.35rem 0.75rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-family: 'Monaco', 'Menlo', monospace;
            border: 1px solid rgba(167, 139, 250, 0.3);
            color: #fde047;
        }

        .weather-section {
            margin-bottom: 2rem;
        }

        .sun-moon-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .disable-note {
            background: linear-gradient(135deg,
                        rgba(30, 27, 75, 0.7),
                        rgba(45, 27, 78, 0.65));
            backdrop-filter: blur(30px) saturate(150%);
            -webkit-backdrop-filter: blur(30px) saturate(150%);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 1.5rem;
            color: white;
            margin-top: 3rem;
            font-size: 0.875rem;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2),
                        0 0 0 1px rgba(255, 255, 255, 0.08) inset;
            transition: all 0.3s ease;
        }

        .disable-note:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25),
                        0 0 0 1px rgba(255, 255, 255, 0.12) inset;
        }

        .disable-note strong {
            display: block;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .attribution {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .attribution a {
            color: #ffffff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.2s;
        }

        .attribution a:hover {
            border-bottom-color: white;
            color: #ff6b6b;
        }

        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.15));
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 24px;
            padding: 1.25rem 1.5rem;
            color: #fecaca;
            margin-bottom: 2rem;
            text-align: center;
            backdrop-filter: blur(20px);
        }

        .error-message strong {
            color: #fca5a5;
        }

        .location-search {
            background: linear-gradient(135deg,
                        rgba(139, 92, 246, 0.15),
                        rgba(124, 58, 237, 0.1));
            backdrop-filter: blur(30px) saturate(150%);
            -webkit-backdrop-filter: blur(30px) saturate(150%);
            border-radius: 28px;
            border: 1px solid rgba(167, 139, 250, 0.25);
            padding: 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 16px 48px rgba(139, 92, 246, 0.15),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .search-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 1.5rem 0;
            text-align: center;
            background: linear-gradient(135deg, #ffffff, #e8d8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-form {
            margin-bottom: 1.25rem;
        }

        .search-mode-toggle {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background: rgba(30, 27, 75, 0.4);
            padding: 0.375rem;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mode-btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mode-btn:hover {
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.05);
        }

        .mode-btn.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.4), rgba(124, 58, 237, 0.3));
            color: white;
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
        }

        .search-inputs {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .coordinate-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.875rem;
        }

        .coordinate-inputs .search-input {
            grid-column: 1 / -1;
        }

        .search-input,
        .coord-input {
            padding: 0.875rem 1.25rem;
            background: rgba(30, 27, 75, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            color: white;
            font-size: 0.9375rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .search-input::placeholder,
        .coord-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-input:focus,
        .coord-input:focus {
            border-color: rgba(167, 139, 250, 0.5);
            background: rgba(30, 27, 75, 0.7);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .search-button {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: 1px solid rgba(167, 139, 250, 0.3);
            border-radius: 16px;
            color: white;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
            background: linear-gradient(135deg, #9333ea, #8b5cf6);
        }

        .search-button:active {
            transform: translateY(0);
        }

        .current-location {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9375rem;
            padding: 1rem;
            background: rgba(30, 27, 75, 0.3);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .current-location strong {
            color: white;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            body {
                padding: 1.5rem 1rem;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 0.875rem;
            }

            .subtitle {
                font-size: 0.9375rem;
                margin-bottom: 2.5rem;
            }

            .info {
                padding: 1.25rem;
                margin-bottom: 1.75rem;
                border-radius: 20px;
            }

            .info p {
                font-size: 0.875rem;
                margin: 0.375rem 0;
            }

            .info code {
                font-size: 0.8125rem;
                padding: 0.3rem 0.625rem;
            }

            .location-search {
                padding: 1.5rem;
                margin-bottom: 2rem;
                border-radius: 24px;
            }

            .search-title {
                font-size: 1.25rem;
                margin-bottom: 1.25rem;
            }

            .mode-btn {
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
            }

            .coordinate-inputs {
                grid-template-columns: 1fr;
            }

            .current-location {
                font-size: 0.875rem;
            }

            .weather-section {
                margin-bottom: 1.75rem;
            }

            .sun-moon-grid {
                grid-template-columns: 1fr;
                gap: 1.75rem;
            }

            .disable-note {
                padding: 1.25rem;
                margin-top: 2.5rem;
                border-radius: 20px;
                font-size: 0.8125rem;
            }

            .disable-note strong {
                font-size: 0.9375rem;
            }

            .attribution {
                font-size: 0.8125rem;
                margin-top: 1.75rem;
                padding: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem 0.75rem;
            }

            h1 {
                font-size: 1.75rem;
                margin-bottom: 0.75rem;
            }

            .subtitle {
                font-size: 0.875rem;
                margin-bottom: 2rem;
            }

            .info {
                padding: 1rem;
                margin-bottom: 1.5rem;
                border-radius: 18px;
            }

            .info p {
                font-size: 0.8125rem;
                line-height: 1.6;
            }

            .info code {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
                display: inline-block;
                margin: 0.125rem 0;
            }

            .location-search {
                padding: 1.25rem;
                margin-bottom: 1.75rem;
                border-radius: 20px;
            }

            .search-title {
                font-size: 1.125rem;
                margin-bottom: 1rem;
            }

            .search-mode-toggle {
                flex-direction: column;
                gap: 0.375rem;
                padding: 0.25rem;
            }

            .mode-btn {
                padding: 0.625rem 0.875rem;
                font-size: 0.8125rem;
            }

            .search-input,
            .coord-input {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .search-button {
                padding: 0.75rem 1.25rem;
                font-size: 0.875rem;
            }

            .current-location {
                font-size: 0.8125rem;
                padding: 0.875rem;
            }

            .weather-section {
                margin-bottom: 1.5rem;
            }

            .sun-moon-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .disable-note {
                padding: 1rem;
                margin-top: 2rem;
                border-radius: 18px;
                font-size: 0.75rem;
            }

            .disable-note strong {
                font-size: 0.875rem;
                margin-bottom: 0.625rem;
            }

            .attribution {
                font-size: 0.75rem;
                margin-top: 1.5rem;
                padding: 0.75rem;
                line-height: 1.6;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($error)
            <div class="error-message">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif

        <div class="location-search">
            <h2 class="search-title">Change Location</h2>

            <form method="GET" action="{{ route('yr.demo') }}" class="search-form" x-data="{ mode: 'location' }">
                <div class="search-mode-toggle">
                    <button type="button" @click="mode = 'location'" :class="{ 'active': mode === 'location' }" class="mode-btn">
                        Search by Location
                    </button>
                    <button type="button" @click="mode = 'coordinates'" :class="{ 'active': mode === 'coordinates' }" class="mode-btn">
                        Manual Coordinates
                    </button>
                </div>

                <div x-show="mode === 'location'" class="search-inputs">
                    <input type="text" name="location" placeholder="e.g., Oslo, Norway or Paris, France" class="search-input" value="{{ request('location') }}">
                    <button type="submit" class="search-button">Search Location</button>
                </div>

                <div x-show="mode === 'coordinates'" class="search-inputs">
                    <div class="coordinate-inputs">
                        <input type="number" step="0.0001" name="latitude" placeholder="Latitude" class="coord-input" value="{{ request('latitude') }}">
                        <input type="number" step="0.0001" name="longitude" placeholder="Longitude" class="coord-input" value="{{ request('longitude') }}">
                        <input type="text" name="location_name" placeholder="Location name (optional)" class="search-input" value="{{ request('location_name') }}">
                    </div>
                    <button type="submit" class="search-button">Update Location</button>
                </div>
            </form>

            <div class="current-location">
                Currently showing: <strong>{{ $location }}</strong> ({{ round($latitude, 4) }}°N, {{ round($longitude, 4) }}°E)
            </div>
        </div>

        <div class="weather-section">
            <x-yr-weather-card
                :latitude="$latitude"
                :longitude="$longitude"
                :location="$location"
            />
        </div>

        <div class="weather-section">
            <x-yr-forecast-card
                :latitude="$latitude"
                :longitude="$longitude"
                :location="$location"
                :days="5"
            />
        </div>

        <div class="sun-moon-grid">
            <x-yr-sunrise-card
                :latitude="$latitude"
                :longitude="$longitude"
                :location="$location"
            />

            <x-yr-moon-card
                :latitude="$latitude"
                :longitude="$longitude"
                :location="$location"
            />
        </div>

        <div class="attribution">
            Weather data from <a href="https://www.met.no/" target="_blank" rel="noopener">The Norwegian Meteorological Institute (MET Norway)</a><br>
            Licensed under <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">CC BY 4.0</a> and <a href="https://data.norge.no/nlod/en/2.0" target="_blank" rel="noopener">NLOD 2.0</a>
        </div>

        <div class="disable-note">
            <strong>Want to disable this demo route?</strong>
            <p>Add <code>YR_DEMO_ROUTE=false</code> to your .env file</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
