<?php

namespace App\Http\Controllers\Api\Admin\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\DiscountCode;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\Notification;


use Illuminate\Support\Facades\Log;
use App\Services\UploadFilesService;
use App\Services\FirebaseService;
use App\Http\Requests\Admin\Notification\PushNotificationRequest;


class NotificationController extends Controller
{

    protected $firebaseService = null;
    protected $uploadFilesService = null;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
        $this->uploadFilesService = new UploadFilesService();

    }



    public function pushNotifications(PushNotificationRequest $request){


        $user = User::find($request->user_id);
        
        if(!$user){
            return jsonResponse(false, 404 , __('messages.user_not_found'));
        }

        $tokens = FcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();

            $imagePath =  'notification_icon.png';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $this->uploadFilesService->uploadImage($image , 'notifications');
            }

            $notification = Notification::create([
                'user_id'     => $user->id,
                'title'       => $request->title,
                'body'        => $request->body,
                'image'       => $imagePath,
                'type'        => 'push',
                'payload_id'  => '0',
            ]);

            $locale = $user->locale;
            $notificationTitle = $request->title[$locale] ?? $request->title['en'];
            $notificationBody  = $request->body[$locale] ?? $request->body['en'];

            $this->firebaseService->sendNotification($tokens, $notificationTitle , $notificationBody);

        return jsonResponse(
            true,
            200,
            __('messages.added_successfully'),
        );
    }
}