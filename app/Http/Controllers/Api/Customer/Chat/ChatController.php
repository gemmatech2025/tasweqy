<?php

namespace App\Http\Controllers\Api\Customer\Chat;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Message;


use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\Customer\Community\CommentResource;
use App\Http\Requests\Customer\Chat\ChatRequest;

use App\Services\FirebaseService;

use App\Http\Resources\Customer\Chat\MessageResource;

class ChatController extends Controller
{


    protected $firebaseService = null;
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();

    }



    public function sendMessage(ChatRequest $request)
    {


        $user = Auth::user();


        $result = $this->firebaseService->sendChatMessage($request->message , $user->id);
        if (!$result) {
            return jsonResponse(false, 500, __('messages.error_sending_message') );
        }


            return jsonResponse(
                true, 201, __('messages.add_success'),
                $result
                // ['id' => $post->id]
            );
        
        
        }

    public function getMessages()
    {

        $messages = Message::where('user_id', Auth::id())
            ->orWhere('to_user_id', Auth::id())
            // ->with(['user', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->get();



    return jsonResponse(true, 200, __('messages.add_success'),MessageResource::collection($messages));    }



}