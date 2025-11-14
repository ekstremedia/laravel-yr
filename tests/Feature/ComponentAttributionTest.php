<?php

it('demo route includes proper licensing attribution', function () {
    config(['yr.enable_demo_route' => true]);

    $response = $this->get('/yr');

    $response->assertStatus(200)
        ->assertSee('The Norwegian Meteorological Institute (MET Norway)', false)
        ->assertSee('www.met.no', false)
        ->assertSee('CC BY 4.0', false)
        ->assertSee('NLOD 2.0', false);
});

it('demo route attribution links are properly formatted', function () {
    config(['yr.enable_demo_route' => true]);

    $response = $this->get('/yr');

    $response->assertStatus(200)
        ->assertSee('href="https://www.met.no/"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener"', false)
        ->assertSee('href="https://creativecommons.org/licenses/by/4.0/"', false)
        ->assertSee('href="https://data.norge.no/nlod/en/2.0"', false);
});
