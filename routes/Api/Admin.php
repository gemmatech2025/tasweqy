

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\WhatsappController;
use App\Http\Controllers\Api\Admin\General\CountryController;
use App\Http\Controllers\Api\Admin\Auth\AuthController;
use App\Http\Controllers\Api\Admin\Customer\ApprovalRequestController;

    Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {




    Route::middleware(['role:admin'])->group(function () {

      

  Route::controller(CountryController::class)->prefix('countries')->group(function () {
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
        });


  Route::controller(WhatsappController::class)->prefix('whatsapp')->group(function () {
            Route::post('/create-session', 'createSession');
            Route::delete('/delete-session/{id}', 'deleteSession');
            Route::get('/get-all-sessions', 'getSessions');
            Route::get('/get-session/{id}', 'getSessionDetails');


        });

  Route::controller(ApprovalRequestController::class)->prefix('approval-requests')->group(function () {
            Route::get('/', 'getRequests');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
            Route::put('/update-approval/{id}', 'updateApproval');

            



        });


        


   

    });



        
});





//   Route::controller(WhatsappController::class)->prefix('whatsapp')->group(function () {
//             Route::post('/create-session', 'createSession');
//             Route::delete('/delete-session/{id}', 'deleteSession');
//             Route::get('/get-all-sessions', 'getSessions');
//             Route::get('/get-session/{id}', 'getSessionDetails');


// });






