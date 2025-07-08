<?php

namespace App\Services;

use App\Models\Message;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\AuthException;

use Kreait\Firebase\Messaging\CloudMessage as FirebaseCloudMessage;

class FirebaseService
{
    protected $auth;
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = public_path('assets/taswiqi-c0b96-firebase-adminsdk-fbsvc-ee2b844627.json');
        Log::info($credentialsPath);
        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase service account file not found at: {$credentialsPath}");
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->auth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
    }




    public function sendNotification(array $deviceTokens, string $title, string $body): bool
    {
        try {
            foreach ($deviceTokens as $token) {
                $message = FirebaseCloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body));

                $this->messaging->send($message);
            }

            return true;
        } catch (\Throwable $e) {
            \Log::channel('firebase')->error('Error sending notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }




 

    // public function sendChatMessage(string $message, string $userId, string $toUserId = null)
    // {
    //     // $messageableClass = $this->getMessageableClass($messageableType);

    //     $messageData = [
    //         'message' => $message,
    //         'user_id' => $userId,
    //         'to_user_id' => $toUserId,
    //         'media' => null,
    //         'is_read' => false,
    //         'read_at' => null,
    //     ];

      

    //     try {
    //         if(!$toUserId) {
    //         $tokens = FcmToken::whereHas('user' , function ($query) {
    //             $query->where('role', 'admin');
    //         })->pulck('fcm_token')->toArray();


    //         $this->sendNotification($tokens, 'New Message', $message);
    //         }else{
    //         $tokens = FcmToken::where('user_id', $toUserId)->pluck('fcm_token')->toArray();
    //         $this->sendNotification($tokens, 'New Message', $message);
    //     }

    //         return Message::create($messageData);
    //     } catch (\Exception $e) {
    //         Log::error('Error saving message to SQL database: ' . $e->getMessage());
    //         return null;
    //     }
    // }



    public function sendChatMessage(string $message, string $userId, string $toUserId = null)
    {
        $messageData = [
            'message' => $message,
            'user_id' => $userId,
            'to_user_id' => $toUserId,
            'media' => null,
            'is_read' => false,
            'read_at' => null,
        ];

        try {

            $tokens = [];
            if (!$toUserId) {
                $tokens = FcmToken::whereHas('user', function ($query) {
                    $query->where('role', 'admin');
                })->pluck('fcm_token')->toArray();
            } else {
                $tokens = FcmToken::where('user_id', $toUserId)->pluck('fcm_token')->toArray();
            }

            if (!empty($tokens)) {
                $this->sendNotification($tokens, 'New Message', $message);
            }

            return Message::create($messageData);
        } catch (\Exception $e) {
            Log::error('Error saving message to SQL database: ' . $e->getMessage());

return [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
];
            // return null;
        }
    }


 

}
