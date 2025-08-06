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
use App\Http\Requests\Admin\Notification\PushNotificationTestingRequest;
use App\Http\Resources\Admin\Notification\NotificationResource;

class NotificationController extends Controller
{

    protected $firebaseService = null;
    protected $uploadFilesService = null;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
        $this->uploadFilesService = new UploadFilesService();

    }


    public function pushNotifications(PushNotificationRequest $request)
    {
        $tokens = [];
        $users = [];
            // dd($request->target );

        if ($request->target == 'all_users') {
            $users = User::where('role', 'customer')->get();
            $tokens = FcmToken::whereIn('user_id', $users->pluck('id'))->pluck('fcm_token')->toArray();
        } else {
            if (!$request->users) {
                return jsonResponse(false, 404, __('messages.user_ids_is_required'));
            }

            $users = User::whereIn('id', $request->users)->get();

            $tokens = FcmToken::whereIn('user_id', $request->users)->pluck('fcm_token')->toArray();
        }

        $imagePath = 'notification_icon.png';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $this->uploadFilesService->uploadImage($image, 'notifications');
        }

        foreach ($users as $user) {
            $notification = Notification::create([
                'user_id'    => $user->id,
                'title'      => $request->title,
                'body'       => $request->body,
                'image'      => $imagePath,
                'type'       => 'push',
                'payload_id' => 0,
            ]);

            $locale = $user->locale ?? 'en';
            $notificationTitle = $request->title[$locale] ?? $request->title['en'];
            $notificationBody  = $request->body[$locale] ?? $request->body['en'];

            $userTokens = FcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();
            $this->firebaseService->sendNotification($userTokens, $notificationTitle, $notificationBody);
        }

        return jsonResponse(true, 200, __('messages.added_successfully'));
    }



     public function pushNotificationTestWeb(PushNotificationRequest $request)
    {
        $tokens = [];
        $users = [];
            // dd($request->target );

            $users = User::where('role', 'admin')->get();
            $tokens = FcmToken::whereIn('user_id', $users->pluck('id'))->pluck('fcm_token')->toArray();
       
        $imagePath = 'notification_icon.png';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $this->uploadFilesService->uploadImage($image, 'notifications');
        }

        foreach ($users as $user) {
            $notification = Notification::create([
                'user_id'    => null,
                'title'      => $request->title,
                'body'       => $request->body,
                'image'      => $imagePath,
                'type'       => 'push',
                'payload_id' => 0,
            ]);

            $locale = $user->locale ?? 'en';
            $notificationTitle = $request->title[$locale] ?? $request->title['en'];
            $notificationBody  = $request->body[$locale] ?? $request->body['en'];

            $userTokens = FcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();
            $this->firebaseService->sendNotification($userTokens, $notificationTitle, $notificationBody);
        }

        return jsonResponse(true, 200, __('messages.added_successfully'));
    }




    // public function pushNotifications(PushNotificationRequest $request){


    //     // $user = User::find($request->user_id);
        
    //     // if(!$user){
    //     //     return jsonResponse(false, 404 , __('messages.user_not_found'));
    //     // }

    //     //  'target'                       => 'required|in:all_users,given_ids',
    //     //  'users'                        => 'nullable|array',
    //     //  'users.*'                      => 'required|exists:users,id',
    //     $tokens = [];    
    //     if($request->target == 'all_users'){
    //         $tokens = FcmToken::whereHas('user',function($q){
    //             $q->where('role' , 'customer');
    //         })->pluck('fcm_token')->toArray();
    //     }else{
    //         if(!$request->users){
    //             return jsonResponse(false, 404 , __('messages.user_ids_is_required'));
    //         }

    //         $tokens = FcmToken::whereIn('user_id' ,$request->users)->pluck('fcm_token')->toArray();


    //         foreach($request->users as $user_id){

    //         }
    //     }

    //     // $tokens = FcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();

    //         $imagePath =  'notification_icon.png';
    //         if ($request->hasFile('image')) {
    //             $image = $request->file('image');
    //             $imagePath = $this->uploadFilesService->uploadImage($image , 'notifications');
    //         }

    //         $notification = Notification::create([
    //             'user_id'     => $user->id,
    //             'title'       => $request->title,
    //             'body'        => $request->body,
    //             'image'       => $imagePath,
    //             'type'        => 'push',
    //             'payload_id'  => '0',
    //         ]);

    //         $locale = $user->locale;
    //         $notificationTitle = $request->title[$locale] ?? $request->title['en'];
    //         $notificationBody  = $request->body[$locale] ?? $request->body['en'];

    //         $this->firebaseService->sendNotification($tokens, $notificationTitle , $notificationBody);

    //     return jsonResponse(
    //         true,
    //         200,
    //         __('messages.added_successfully'),
    //     );
    // }




    public function pushNotificationTesting(PushNotificationTestingRequest $request){


        $user = User::find($request->user_id);
        
        if(!$user){
            return jsonResponse(false, 404 , __('messages.user_not_found'));
        }

            $this->firebaseService->handelNotification($user, $request->type , $request->payload);
        return jsonResponse(
            true,
            200,
            __('messages.added_successfully'),
        );
    }



    public function getMyNotifications(Request $request){
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $query = Notification::where('user_id' , null);


        $data = $query->paginate($perPage, ['*'], 'page', $page);



        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];



        return jsonResponse(
            true,
            200,
            __('messages.success'),
            NotificationResource::collection($data),
            $pagination
        );
    }


}