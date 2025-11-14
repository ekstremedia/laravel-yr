<?php

use Illuminate\Support\Facades\Route;

Route::get('/yr', function () {
    return view('laravel-yr::demo');
})->name('yr.demo');
