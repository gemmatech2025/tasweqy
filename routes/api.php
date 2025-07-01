<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::get('/artisan/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');

    return response()->json(['message' => 'All caches cleared successfully']);
});

Route::get('/artisan/run-migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return response()->json(['message' => 'Migration executed successfully']);
});




Route::get('/artisan/run-migrate-refresh', function () {
    Artisan::call('migrate:refresh', ['--force' => true]);
    return response()->json(['message' => 'Migration refresh executed successfully']);
});

Route::get('/run-seeder/{name}', function ($name) {
    Artisan::call('db:seed', [
        '--class' => $name,
        '--force' => true
    ]);

    return response()->json(['message' => 'Seeder executed successfully']);
});

