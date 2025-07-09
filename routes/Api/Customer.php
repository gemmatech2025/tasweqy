
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Customer\Auth\AuthController;
use App\Http\Controllers\Api\Customer\Auth\_2FAuthController;

use App\Http\Controllers\Api\Customer\Profile\CustomerController;
use App\Http\Controllers\Api\Customer\Payment\BankInfoController;
use App\Http\Controllers\Api\Customer\Payment\PayPalAccountController;
use App\Http\Controllers\Api\Customer\Community\PostController;
use App\Http\Controllers\Api\Customer\Community\PostCommentController;
use App\Http\Controllers\Api\Customer\Wallet\WithdrawRequestController;

use App\Http\Controllers\Api\Admin\General\CountryController;
use App\Http\Controllers\Api\Customer\Referral\ReferralRequestController;
use App\Http\Controllers\Api\Customer\Brand\BrandController;

use App\Http\Controllers\Api\Customer\Wallet\WalletController;
use App\Http\Controllers\Api\Customer\Notification\NotificationController;

use App\Http\Controllers\Api\Admin\General\SocialMediaPlatformController;





use App\Http\Controllers\Api\Customer\Chat\ChatController;


Route::middleware(['set-locale'])->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('send-forget-password-otp', [AuthController::class, 'sendForgetPasswordOtp']);
    Route::post('verify-forget-password-otp', [AuthController::class, 'verifyOtpForgetPassword']);
    Route::post('add-new-password-forgot-password', [AuthController::class, 'addNewPasswordForgetPassword']);


Route::middleware(['auth:sanctum'])->group(function () {




    Route::middleware(['role:customer'])->group(function () {


    Route::post('/user/locale', [CustomerController::class, 'updateLocale']);
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
            Route::get('/home-info', 'homeInfo');


            
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




          Route::controller(PostController::class)->prefix('posts')->group(function () {
            Route::post('/', 'store');
            Route::get('/', 'index');

            Route::put('/{id}', 'update');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
            Route::get('/toggle-like/{id}', 'toggleLikePost');
            Route::get('/share/{id}', 'sharePost');



            
        });



          Route::controller(PostCommentController::class)->prefix('post-comments')->group(function () {
            Route::post('/', 'store');

            Route::put('/{id}', 'update');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
            Route::get('/by-post/{id}', 'getCommentsByPost');



            
        });

          Route::controller(WithdrawRequestController::class)->prefix('withdraw-requests')->group(function () {
            Route::get('/my-requests', 'getMyRequests');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');            
        });


        


        Route::controller(CountryController::class)->prefix('countries')->group(function () {
          Route::get('/get-all', 'getAllForSellect');
        });


        Route::controller(ReferralRequestController::class)->prefix('referral-request')->group(function () {
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete'); 
            Route::get('/', 'index');
        
        });


        Route::controller(BrandController::class)->prefix('brands')->group(function () {
            Route::get('/get-new-brands', 'getNewBrands');
            Route::get('/get-my-brands', 'getMyBrands');
            Route::get('/get-brand-by-id/{brand_id}', 'getBrandById');
            Route::put('/update-social-platform/{earning_id}/{platform_id}', 'addSocialMediaPlatform');


        });
 


     Route::controller(WalletController::class)->prefix('wallet')->group(function () {
      Route::get('/get-wallet-details', 'getWalletDetails');
      Route::get('/get-earnings', 'getEarnings');
      Route::get('/get-wallet-transactions', 'getTransactions');



        });


     Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
      Route::get('/get-my-notifications', 'getMyNotifications');
      Route::delete('/delete/{id}', 'deleteNotification');



        });



  Route::controller(SocialMediaPlatformController::class)->prefix('social-media-platforms')->group(function () {
    Route::get('/', 'index');
  });


  Route::controller(ChatController::class)->prefix('chat-messages')->group(function () {
    Route::post('/send', 'sendMessage');
    Route::get('/get-my-messages', 'getMessages');
  });
   

    });



        
});















});




//   Route::controller(WhatsappController::class)->prefix('whatsapp')->group(function () {
//             Route::post('/create-session', 'createSession');
//             Route::delete('/delete-session/{id}', 'deleteSession');
//             Route::get('/get-all-sessions', 'getSessions');
//             Route::get('/get-session/{id}', 'getSessionDetails');


// });



