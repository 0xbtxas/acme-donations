<?php

use Illuminate\Support\Facades\Route;

// SPA entrypoint
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
