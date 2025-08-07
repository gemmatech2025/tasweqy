<?php

namespace App\Http\Controllers\Api\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\AccountVerificationRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\Customer\AccountVerificationRequestResource;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Admin\Customer\UpdateAccountApprovalRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\Customer;


use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\Chat\MessageRequest;
use App\Services\FirebaseService;
use App\Http\Resources\Customer\Chat\MessageResource;


class ChatController extends Controller
{


    protected $firebaseService = null;
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();

    }



    public function sendMessage(MessageRequest $request)
    {
        $user = Auth::user();
        $customer = User::find($request->customer_id);
        if (!$customer) {
            return jsonResponse(false, 404, __('messages.customer_not_found'));
        }
        $result = $this->firebaseService->sendChatMessage($request->message , $user->id , $customer->id);
        if (!$result) {
            return jsonResponse(false, 500, __('messages.error_sending_message') );
        }
        return jsonResponse(
            true, 201, __('messages.add_success'),
            $result
        );
    }

    public function getMessagesByUserId(Request $request ,$user_id)
    {
        $customer = Customer::find($user_id);

        if (!$customer) {
            return jsonResponse(false, 404, __('messages.customer_not_found'));
        }


        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);



        $messages = Message::where('user_id', $customer->user_id)
            ->orWhere('to_user_id', $customer->user_id)
            ->orderBy('created_at', 'desc');

        $data = $messages->paginate($perPage, ['*'], 'page', $page);




         $pagination = [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'last_page' => $data->lastPage(),
            ];

        return jsonResponse(true, 200, __('messages.add_success'),MessageResource::collection($data) ,  $pagination);    
    }



    public function getChats()
    {

        $users = User::whereHas('messages')
            ->where('role', 'customer')
            // ->orderBy('created_at', 'desc')
            ->get();


            $chats = [];
        foreach ($users as $user) {
            $lastMessage = Message::where('user_id', $user->id)
                ->orWhere('to_user_id', $user->id)
                ->orderBy('created_at', 'desc') 
                ->first();


            $chats[] = [
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email , 'image' => $user->image ? asset($user->image) : null],
                'last_message' => $lastMessage ? [
                    'id'             => $lastMessage->id,
                    'message'        => $lastMessage->message,
                    'is_mine'        => auth()->user() ? auth()->user()->id == $lastMessage->user->id ? true :false:false,      
                    'created_since'  => $lastMessage->created_at->diffForHumans(),
                    'is_read'        => $lastMessage->is_read,
                ] : null,
            ];
            
            
            }
    return jsonResponse(true, 200, __('messages.success'),$chats);    
    }




    public function sendMessageTesting(MessageRequest $request)
    {
        $user = Auth::user();
        $customer = User::find($request->customer_id);
        if (!$customer) {
            return jsonResponse(false, 404, __('messages.customer_not_found'));
        }
        $result = $this->firebaseService->sendChatMessage($request->message , $customer->id);
        if (!$result) {
            return jsonResponse(false, 500, __('messages.error_sending_message') );
        }
            return jsonResponse(
                true, 201, __('messages.add_success'),
                $result
            );
        }

    public function deleteMessage($message_id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            return jsonResponse(false, 404, __('messages.message_not_found'));
        }

        $message->delete();

            return jsonResponse(
                true, 203, __('messages.success'),
            );
        }



}