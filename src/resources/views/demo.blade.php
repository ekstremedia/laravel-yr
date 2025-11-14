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
            background: linear-gradient(135deg, #1e0342 0%, #2e1065 25%, #4c1d95 50%, #581c87 75%, #6d28d9 100%);
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
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: drift 20s linear infinite;
            pointer-events: none;
        }

        @keyframes drift {
            from { transform: translate(0, 0); }
            to { transform: translate(50px, 50px); }
        }

        .container {
            max-width: 900px;
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

        .disable-note {
            background: rgba(76, 29, 149, 0.35);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-radius: 20px;
            border: 1px solid rgba(109, 40, 217, 0.5);
            padding: 1.25rem;
            color: white;
            margin-top: 3rem;
            font-size: 0.875rem;
            text-align: center;
            box-shadow: 0 12px 40px rgba(76, 29, 149, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.12);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="weather-section">
            <x-yr-weather-card
                :latitude="68.7044"
                :longitude="15.4140"
                location="Sortland, Norway"
            />
        </div>

        <div class="weather-section">
            <x-yr-forecast-card
                :latitude="68.7044"
                :longitude="15.4140"
                location="Sortland"
                :days="5"
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
</body>
</html>
