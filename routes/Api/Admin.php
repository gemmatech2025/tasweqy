

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\WhatsappController;
use App\Http\Controllers\Api\Admin\General\CountryController;
use App\Http\Controllers\Api\Admin\Auth\AuthController;
use App\Http\Controllers\Api\Admin\Customer\ApprovalRequestController;
use App\Http\Controllers\Api\Admin\Brand\CategoryController;
use App\Http\Controllers\Api\Admin\Brand\BrandController;
use App\Http\Controllers\Api\Admin\Referral\ReferralLinkController;
use App\Http\Controllers\Api\Admin\Referral\DiscountCodeController;
use App\Http\Controllers\Api\Admin\General\SocialMediaPlatformController;
use App\Http\Controllers\Api\Customer\Wallet\WithdrawRequestController;
use App\Http\Controllers\Api\Admin\Referral\ReferralRequestController;
use App\Http\Controllers\Api\Admin\Customer\CustomerController;
use App\Http\Controllers\Api\Admin\Dashboard\DashboardController;

use App\Http\Controllers\Api\Admin\Customer\ChatController;
use App\Http\Controllers\Api\Admin\Customer\UserBlockController;



Route::middleware(['set-locale'])->group(function () {

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


            
            Route::get('/get-customer-requests/{customer_id}', 'getApprovalRequestsByCustomerId');

            Route::get('/', 'getRequests');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'delete');
            Route::put('/update-approval/{id}', 'updateApproval');

            



        });


        
        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
        });

        Route::controller(BrandController::class)->prefix('brands')->group(function () {
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
        });



      Route::controller(ReferralLinkController::class)->prefix('referral-link')->group(function () {

        
            Route::get('/get-numbers', 'getReferralLinksNumbers');


            Route::put('/update-status/{id}', 'updateStatus');

            Route::get('/export-template', 'exportReferralLinksTemplate');
            Route::post('/import-data', 'importReferralLinks');
            Route::post('/store-list', 'storeList');
            
            Route::post('/', 'store');

            
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');

        });


        Route::controller(DiscountCodeController::class)->prefix('discount-code')->group(function () {
        
            Route::get('/get-numbers', 'getDiscountCodesNumbers');
            Route::put('/update-status/{id}', 'updateStatus');
            Route::post('/store-list', 'storeList');


            Route::get('/export-template', 'exportDiscountCodesTemplate');
            Route::post('/import-data', 'importDiscountCodes');
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
        });

        Route::controller(SocialMediaPlatformController::class)->prefix('social-media-platforms')->group(function () {
        
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');

        });

        Route::controller(WithdrawRequestController::class)->prefix('withdraw-requests')->group(function () {
            Route::put('/update-status/{request_id}/{status}', 'updateRequestStatus');
        });

        Route::controller(ReferralRequestController::class)->prefix('referral-requests')->group(function () {
            
            Route::get('/get-numbers', 'getNumbers');

            Route::post('/assign-referral', 'assifnReferralToCustomer');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');

            
        });


        Route::controller(CustomerController::class)->prefix('customers')->group(function () {  
            Route::get('/get-distinguished-customer', 'getDistinguishedCustomers');
            Route::get('/get-blocked-customer-details/{id}', 'getBlockedCustomerDetails');
            Route::get('/get-blocked-customers', 'getBlockedCustomers');
            Route::get('/get-brands/{id}', 'getBrands');
            Route::get('/wallet-requests/{id}', 'walletWithdrawRequests');
            Route::get('/get-referrals/{id}/{type}', 'getAllReferral');
            Route::get('/', 'getCustomers');
            Route::get('/{id}', 'show');
        });


        Route::controller(DashboardController::class)->prefix('dashboard')->group(function () {
            Route::get('/get-info', 'dashboardInfo');
            Route::get('/{id}', 'show');
        });



        Route::controller(ChatController::class)->prefix('chat')->group(function () {
            Route::post('/send-message', 'sendMessage');
            Route::get('/get-messages-by-user/{user_id}', 'getMessagesByUserId');
            Route::get('/get-chats', 'getChats');

        });


        Route::controller(UserBlockController::class)->prefix('user-blocks')->group(function () {
            Route::get('/get-customer-blocks/{id}', 'getCustomersBlocks');
            Route::post('/', 'store');
            Route::delete('/{id}', 'delete');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
        });

    });



        
});


});



