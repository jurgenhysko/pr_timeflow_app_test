<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return redirect('http://localhost:3000/');
})->where('any', '.*');
