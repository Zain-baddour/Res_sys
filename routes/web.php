<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/image/{filename}', function ($filename) {
    $path = public_path($filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
        'Content-Type' => mime_content_type($path)
    ]);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stripe/return', function () {
    return '✅ تم الرجوع من Stripe بنجاح!';
});

Route::get('/stripe/refresh', function () {
    return '🔁 تم إعادة المحاولة من Stripe!';
});


