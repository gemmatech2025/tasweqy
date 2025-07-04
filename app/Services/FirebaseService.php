<?php

namespace App\Services;

use App\Models\Message;
use App\Models\FirebaseToken;
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
        $credentialsPath = public_path('assets/my-account.json');
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




    public function sendRawDataNotification(array $deviceTokens, array $data): bool
    {
        try {

            $factory = (new Factory)->withServiceAccount(public_path('assets/hayatuk-f9eb7-firebase-adminsdk-fbsvc-d6ca35607f.json'));
            $messaging = $factory->createMessaging();

            foreach ($deviceTokens as $token) {
                $message = FirebaseCloudMessage::withTarget('token', $token)
                    ->withData($data);

                $messaging->send($message);
            }

            return true;
        } catch (\Throwable $e) {
            \Log::channel('firebase')->error('Error sending raw data notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }


    public function sendChatMessage(string $chatRoomId, string $userId, string $message, ?string $fileUrl = null, string $messageableType)
    {
        $messageableClass = $this->getMessageableClass($messageableType);

        $messageData = [
            'chat_room_id' => $chatRoomId,
            'messageable_id' => $userId,
            'messageable_type' => $messageableClass,
            'message' => $message,
            'file' => $fileUrl,
        ];

        try {
            return Message::create($messageData);
        } catch (\Exception $e) {
            Log::error('Error saving message to SQL database: ' . $e->getMessage());
            return null;
        }
    }

 

}
