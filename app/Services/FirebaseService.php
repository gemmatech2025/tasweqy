<?php

namespace App\Services;

use App\Models\Message;
use App\Models\FcmToken;
use App\Models\Notification as MyNotification;
use App\Models\User;
use App\Models\Setting;
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
        $credentialsPath = storage_path('app/firebase/taswiqi-c0b96-firebase-adminsdk-fbsvc-457d39aae8.json');
        Log::info($credentialsPath);
        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase service account file not found at: {$credentialsPath}");
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->auth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
    }



    public function handelNotification(User $user,$type,$payload): bool
    {
        try {
        $title = [];
        $body = [];
        $image = 'notification_icon.png';
        
        switch ($type) {
            case 'withraw_issue':
                $title['ar'] = 'مشكلة في السحب';
                $title['en'] = 'Withdraw Issue';

                $body['ar'] = 'حدثت مشكلة أثناء محاولة سحب الأرباح. يرجى التحقق من التفاصيل.';
                $body['en'] = 'There was an issue while trying to withdraw your earnings. Please check the details.';

                $image = 'notifications/empty-wallet-remove.png';
                break;

            case 'withraw_success':
                $title['ar'] = 'تم السحب بنجاح';
                $title['en'] = 'Withdraw Successful';

                $body['ar'] = 'تمت معالجة عملية السحب الخاصة بك بنجاح.';
                $body['en'] = 'Your withdrawal has been successfully processed.';
                $image = 'notifications/empty-wallet-add.png';
                break;

            case 'referral_link_added':
                $title['ar'] = 'تمت إضافة رابط الإحالة';
                $title['en'] = 'Referral Link Added';

                $body['ar'] = 'تمت إضافة رابط الإحالة الخاص بك بنجاح.';
                $body['en'] = 'Your referral link has been successfully added.';
                $image = 'notifications/link.png';

                break;

            case 'discount_code_added':
                $title['ar'] = 'تمت إضافة كود الخصم';
                $title['en'] = 'Discount Code Added';

                $body['ar'] = 'تمت إضافة كود الخصم الخاص بك بنجاح.';
                $body['en'] = 'Your discount code has been successfully added.';
                $image = 'notifications/ticket-discount.png';

                break;

            case 'earning_added':
                $title['ar'] = 'تمت إضافة أرباح جديدة';
                $title['en'] = 'New Earning Added';

                $body['ar'] = 'تمت إضافة أرباح جديدة إلى حسابك.';
                $body['en'] = 'New earnings have been added to your account.';
                $image = 'notifications/empty-wallet-add.png';

                break;

            case 'account_verified':
                $title['ar'] = 'تم التحقق من الحساب';
                $title['en'] = 'Account Verified';

                $body['ar'] = 'تم التحقق من حسابك بنجاح. مرحبًا بك!';
                $body['en'] = 'Your account has been successfully verified. Welcome!';
                $image = 'notifications/shield-tick.png';

                break;

            case 'verification_rejected':
                $title['ar'] = 'رفض التحقق من الحساب';
                $title['en'] = 'Account Verification Rejected';

                $body['ar'] = 'تم رفض طلب التحقق من حسابك. يرجى إعادة المحاولة بعد مراجعة الملاحظات.';
                $body['en'] = 'Your account verification request was rejected. Please try again after reviewing the feedback.';
                $image = 'notifications/shield-cross.png';

                break;

            default:
                $title['ar'] = 'إشعار';
                $title['en'] = 'Notification';

                $body['ar'] = 'لديك إشعار جديد.';
                $body['en'] = 'You have a new notification.';
                $image = 'notification_icon.png';

                break;
        }



            $fcmTokens = FcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();
            $notification = MyNotification::create([
                'user_id'     => $user->id,
                'title'       => $title,
                'body'        => $body,
                'image'       => $image,
                'type'        => $type,
                'payload_id'  => $payload,
            ]);

            $locale = $user->locale;
            $notificationTitle = $title[$locale] ?? $title['en'] ;
            $notificationBody  = $body[$locale] ?? $body['en'] ;


            if($user->is_notification_active){
                $this->sendNotification($fcmTokens , $notificationTitle, $notificationBody , $type , $payload);
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




    public function sendNotification(array $deviceTokens, string $title, string $body , string $type = 'pushed' ,string $payload = '' ): bool
    {
        try {
            foreach ($deviceTokens as $token) {
                $message = FirebaseCloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData([
                    'type'      => $type,
                    'payload'   => $payload,
                ]);
                // ->withData([
                //    'data' =>[
                //     'type'      => $type,
                //     'payload'   => $payload,
                //    ]
                // ]);

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

//     public function sendChatMessage(string $message, string $userId, string $toUserId = null)
//     {
//         $messageData = [
//             'message' => $message,
//             'user_id' => $userId,
//             'to_user_id' => $toUserId,
//             'media' => null,
//             'is_read' => false,
//             'read_at' => null,
//         ];

//         try {

//             $tokens = [];
//             $user = null;
//             $sender = User::find($userId); 
//             $senderName = $sender?->name ?? 'مستخدم';

//             if (!$toUserId) {
//                 $tokens = FcmToken::whereHas('user', function ($query) {
//                     $query->where('role', 'admin');
//                 })->pluck('fcm_token')->toArray();
//             } else {
//                 $user = User::find($toUserI);
//                 $tokens = FcmToken::where('user_id', $toUserId)->pluck('fcm_token')->toArray();
//             }

//             if (!empty($tokens)) {
//                 if($user){
//                     if($user->is_notification_active){
//                         $locale = $user->locale;
//             $notificationTitle = $title[$locale] ?? $title['en'] ;
//             $notificationBody  = $body[$locale] ?? $body['en'] ;

//                         $this->sendNotification($tokens, $notificationTitle, $notificationBody, 'message');
//                     }

                     

//                 }else{

//                     $locale = 'ar';
//             $setting = Setting::where('key' , 'default_language')->first();

//             if($setting){
//                 $locale = $setting->value;
//             }
//             $notificationTitle = $title[$locale] ?? $title['ar'] ;
//             $notificationBody  = $body[$locale]  ?? $body['ar'] ;



//                     $this->sendNotification($tokens, $notificationTitle, $notificationBody , 'message');
//                 }
//             }
//             $image = 'notifications/chat.png';


//             $message =Message::create($messageData);


//             if($user){
//                     $notification = MyNotification::create([
//                         'user_id'     => $user->id,
//                         'title'       => $title,
//                         'body'        => $body,
//                         'image'       => $image,
//                         'type'        => 'message',
//                         'payload_id'  => $message->id,
//                     ]);

                     

//                 }else{
// $notification = MyNotification::create([
//                         'user_id'     => null,
//                         'title'       => $title,
//                         'body'        => $body,
//                         'image'       => $image,
//                         'type'        => 'message',
//                         'payload_id'  => $message->id,
//                     ]);                }

            


//             return $message;
//         } catch (\Exception $e) {
//             Log::error('Error saving message to SQL database: ' . $e->getMessage());

//             return [
//                             'message' => $e->getMessage(),
//                             'file'    => $e->getFile(),
//                             'line'    => $e->getLine(),
//             ];
//         }
//     }
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
            $user = null;
            $sender = User::find($userId);
            $senderName = $sender?->name ?? 'مستخدم';

            if (!$toUserId) {
                $tokens = FcmToken::whereHas('user', function ($query) {
                    $query->where('role', 'admin');
                })->pluck('fcm_token')->toArray();
            } else {
                $user = User::find($toUserId); 
                $tokens = FcmToken::where('user_id', $toUserId)->pluck('fcm_token')->toArray();
            }

            $title = [
                'ar' => "رسالة جديدة من {$senderName}",
                'en' => "New message from {$senderName}",
            ];
            $body = [
                'ar' => 'لديك رسالة جديدة: "' . $message . '"',
                'en' => 'You have a new message: "' . $message . '"',
            ];
            $image = 'notifications/chat.png';

            if (!empty($tokens)) {
                if ($user) {
                    if ($user->is_notification_active) {
                        $locale = $user->locale;
                        $notificationTitle = $title[$locale] ?? $title['en'];
                        $notificationBody  = $body[$locale] ?? $body['en'];

                        $this->sendNotification($tokens, $notificationTitle, $notificationBody, 'message');
                    }
                } else {
                    $locale = 'ar';
                    $setting = Setting::where('key', 'default_language')->first();
                    if ($setting) {
                        $locale = $setting->value;
                    }
                    $notificationTitle = $title[$locale] ?? $title['ar'];
                    $notificationBody  = $body[$locale] ?? $body['ar'];

                    $this->sendNotification($tokens, $notificationTitle, $notificationBody, 'message');
                }
            }

            $message = Message::create($messageData);

            MyNotification::create([
                'user_id'     => $user?->id,
                'title'       => $title,
                'body'        => $body,
                'image'       => $image,
                'type'        => 'message',
                'payload_id'  => $message->id,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Error saving message to SQL database: ' . $e->getMessage());

            return [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }
    }



    public function sendAdminNotification($type , $payload , User $user): bool
    {
        try {
        $title = [];
        $body = [];
        $image = 'notification_icon.png';

        $marketerName = $user->name;
        switch ($type) {
            case 'withdraw_request_added':
                $title['ar'] = 'تم إرسال طلب سحب';
                $title['en'] = 'Withdraw Request Sent';

                $body['ar'] = 'تم إرسال طلب سحب من المسوق ' . $marketerName . '.';
                $body['en'] = 'A withdrawal request has been submitted by marketer ' . $marketerName . '.';

                $image = 'notifications/empty-wallet-add.png';
                break;

            case 'verification_request_added':
                $title['ar'] = 'تم إرسال طلب التحقق';
                $title['en'] = 'Verification Request Sent';

                $body['ar'] = 'تم إرسال طلب تحقق من المسوق ' . $marketerName . '.';
                $body['en'] = 'A verification request has been submitted by marketer ' . $marketerName . '.';

                $image = 'notifications/shield-tick.png';
                break;

            case 'referral_request_added':
                $title['ar'] = 'تم إرسال طلب الإحالة';
                $title['en'] = 'Referral Request Sent';

                $body['ar'] = 'تم إرسال طلب إحالة من المسوق ' . $marketerName . '.';
                $body['en'] = 'A referral request has been submitted by marketer ' . $marketerName . '.';

                $image = 'notifications/link.png';
                break;

            default:
                $title['ar'] = 'إشعار';
                $title['en'] = 'Notification';

                $body['ar'] = 'لديك إشعار جديد.';
                $body['en'] = 'You have a new notification.';

                $image = 'notification_icon.png';
                break;
        }


            $tokens = FcmToken::whereHas('user', function ($query) {
                    $query->where('role', 'admin');
                })->pluck('fcm_token')->toArray();

            $notification = MyNotification::create([
                'user_id'     => null,
                'title'       => $title,
                'body'        => $body,
                'image'       => $image,
                'type'        => $type,
                'payload_id'  => $payload,
            ]);


            $locale = 'ar';
            $setting = Setting::where('key' , 'default_language')->first();

            if($setting){
                $locale = $setting->value;
            }
            $notificationTitle = $title[$locale] ?? $title['ar'] ;
            $notificationBody  = $body[$locale]  ?? $body['ar'] ;


                $this->sendNotification($fcmTokens , $notificationTitle, $notificationBody , $type , $payload);
            
            return true;
        } catch (\Throwable $e) {
            \Log::channel('firebase')->error('Error sending notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
