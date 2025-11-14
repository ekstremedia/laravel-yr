<?php

it('weather card component includes MET Norway attribution', function () {
    $html = (string) $this->blade(
        '<x-yr-weather-card :latitude="59.9139" :longitude="10.7522" location="Oslo, Norway" />'
    );

    expect($html)
        ->toBeString()
        ->toContain('The Norwegian Meteorological Institute (MET Norway)')
        ->toContain('www.met.no')
        ->toContain('CC BY 4.0');
});

it('forecast card component includes MET Norway attribution', function () {
    $html = (string) $this->blade(
        '<x-yr-forecast-card :latitude="59.9139" :longitude="10.7522" location="Oslo" :days="5" />'
    );

    expect($html)
        ->toBeString()
        ->toContain('The Norwegian Meteorological Institute (MET Norway)')
        ->toContain('www.met.no')
        ->toContain('CC BY 4.0')
        ->toContain('NLOD 2.0');
});

it('demo route includes proper licensing attribution', function () {
    config(['yr.enable_demo_route' => true]);

    $response = $this->get('/yr');

    $response->assertStatus(200)
        ->assertSee('The Norwegian Meteorological Institute (MET Norway)', false)
        ->assertSee('www.met.no', false)
        ->assertSee('CC BY 4.0', false)
        ->assertSee('NLOD 2.0', false);
});

it('weather card attribution links are properly formatted', function () {
    $html = (string) $this->blade(
        '<x-yr-weather-card :latitude="59.9139" :longitude="10.7522" location="Oslo" />'
    );

    // Check for proper link attributes
    expect($html)
        ->toBeString()
        ->toContain('href="https://www.met.no/"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener"')
        ->toContain('href="https://creativecommons.org/licenses/by/4.0/"');
});

it('forecast card attribution links are properly formatted', function () {
    $html = (string) $this->blade(
        '<x-yr-forecast-card :latitude="59.9139" :longitude="10.7522" location="Oslo" :days="5" />'
    );

    // Check for proper link attributes
    expect($html)
        ->toBeString()
        ->toContain('href="https://www.met.no/"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener"')
        ->toContain('href="https://creativecommons.org/licenses/by/4.0/"')
        ->toContain('href="https://data.norge.no/nlod/en/2.0"');
});
