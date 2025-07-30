<?php

namespace App\Http\Controllers\Api\Customer\Notification;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Notification;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\Customer\Notification\NotificationResource;

class NotificationController extends Controller
{



  public function getMyNotifications(Request $request)
{
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 20);

    $user = Auth::user();

    $notifications = Notification::where('user_id', $user->id)
                                 ->orderBy('created_at', 'desc');

    $data = $notifications->paginate($perPage, ['*'], 'page', $page);

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


    public function deleteNotification($id)
    {

        $user = Auth::user();

        
        $notification = Notification::find($id);

        if (!$notification) {
        return jsonResponse(false, 404, __('messages.not_found'));
        }


        if($user-> id != $notification->user_id ){
            return jsonResponse(false, 401, __('messages.not_owner'));
        }


        $notification->delete();
        return jsonResponse(
            true,
            200,
            __('messages.success'),
        );
    }


}