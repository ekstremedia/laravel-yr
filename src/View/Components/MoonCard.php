<?php

namespace Ekstremedia\LaravelYr\View\Components;

use Ekstremedia\LaravelYr\Services\MoonService;
use Illuminate\View\Component;

class MoonCard extends Component
{
    public ?array $moonData = null;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?string $location = null,
        public ?string $date = null,
        public int $offset = 0
    ) {
        $moonService = app(MoonService::class);
        $this->moonData = $moonService->getMoonData($latitude, $longitude, $date, $offset);
    }

    public function render()
    {
        return view('laravel-yr::components.moon-card');
    }
}
