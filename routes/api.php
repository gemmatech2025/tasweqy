<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tracking\TrakingController;
use App\Http\Controllers\Api\Admin\Referral\ReferralLinkController;

use App\Http\Controllers\Api\Customer\Auth\SocialAuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(ReferralLinkController::class)->prefix('refferral')->group(function () {
            Route::get('/export-template', 'exportReferralLinksTemplate');

});
Route::post('/handel-callback', [WhatsappController::class, 'handelWhatsappCallback']);



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





Route::controller(TrakingController::class)->prefix('event-tracker')->group(function () {
    Route::get('/track', 'trackPixel');

});


Route::controller(SocialAuthController::class)->prefix('auth')->group(function () {
    Route::post('/social-login', 'socialLogin');
    Route::get('/google/callback', 'googleCallback');
    Route::get('/google/generat-url', 'getGoogleAuthUrl');

    


});


