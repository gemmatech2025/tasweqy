
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Customer\Auth\AuthController;
use App\Http\Controllers\Api\Customer\Auth\_2FAuthController;

use App\Http\Controllers\Api\Customer\Profile\CustomerController;
use App\Http\Controllers\Api\Customer\Payment\BankInfoController;
use App\Http\Controllers\Api\Customer\Payment\PayPalAccountController;






    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('send-forget-password-otp', [AuthController::class, 'sendForgetPasswordOtp']);
    Route::post('verify-forget-password-otp', [AuthController::class, 'verifyOtpForgetPassword']);


Route::middleware(['auth:sanctum'])->group(function () {




    Route::middleware(['role:customer'])->group(function () {

      


    Route::post('logout', [AuthController::class, 'logout']);
    Route::delete('delete-profile', [AuthController::class, 'deleteProfile']);
    Route::put('change-old-password', [AuthController::class, 'changeOldPassword']);

    Route::controller(_2FAuthController::class)->prefix('2fa')->group(function () {
            Route::get('/enalble', 'enable2FA');
            Route::get('/get-qr-code', 'getQrCode');
            Route::post('/verify-2fa-code', 'verify2FACode');
            Route::get('/regenerate-recovery-codes', 'regenerateRecoveryCodes')->middleware('2fa-confirmed');
            Route::post('/use-recovery-code', 'useRecoveryCode');
            Route::get('/disable-2fa', 'disable2FA');

            

            
    });



    

    Route::controller(CustomerController::class)->prefix('profile')->group(function () {
            Route::post('/update-profile', 'updateProfile');
            Route::post('/verify-phone', 'verifyPhoneOtp');
            Route::get('/get-profile', 'getMyData');
            Route::post('/request-approval', 'requestApproval');
            Route::get('/get-my-approval-requests', 'getMyApprovalRequests');
        });



    Route::controller(BankInfoController::class)->prefix('bank-info')->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
    });


    Route::controller(BankInfoController::class)->prefix('payment')->group(function () {
            Route::get('/get-all-accounts', 'index');
            Route::put('/set-default/{id}/{type}', 'setDefault');;

    });



    Route::controller(PayPalAccountController::class)->prefix('paypal-accounts')->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
        });



   

    });



        
});





//   Route::controller(WhatsappController::class)->prefix('whatsapp')->group(function () {
//             Route::post('/create-session', 'createSession');
//             Route::delete('/delete-session/{id}', 'deleteSession');
//             Route::get('/get-all-sessions', 'getSessions');
//             Route::get('/get-session/{id}', 'getSessionDetails');


// });



