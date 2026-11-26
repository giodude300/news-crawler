<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $indexPath = public_path('frontend/index.html');

    abort_unless(file_exists($indexPath), 503, 'Frontend build is not available.');

    return response()->file($indexPath);
});
