<?php

namespace App\Http\Controllers\Api\Admin\Notification;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
    
use App\Services\FirebaseService;
use App\Http\Requests\Admin\Notification\PushNotificationRequest;


class NotificationController extends Controller
{



    protected $firebaseService = null;
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }



    public function pushNotifications(PushNotificationRequest $request){


        $user = User::find($request->user_id);
        
        if(!$user){
            return jsonResponse(
                        false,
                        404,
                        __('messages.user_not_found'),
                        // new DiscountCodeResource($discountCode)
                    );
        }
        
        $this->firebaseService->handelNotification($user, $request->type , '1' );
        return jsonResponse(
            true,
            200,
            __('messages.added_successfully'),
            // new DiscountCodeResource($discountCode)
        );
    }

    

}