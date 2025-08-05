<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            [
                'type' => 'withdraw_request_added',
                'title' => [
                    'ar' => 'تم إرسال طلب سحب',
                    'en' => 'Withdraw Request Sent',
                ],
                'body' => [
                    'ar' => 'تم إرسال طلب سحب من المسوق أحمد.',
                    'en' => 'A withdrawal request has been submitted by marketer Ahmed.',
                ],
                'image' => 'notifications/empty-wallet-add.png',
                'payload_id' => rand(1, 100),
            ],
            [
                'type' => 'verification_request_added',
                'title' => [
                    'ar' => 'تم إرسال طلب التحقق',
                    'en' => 'Verification Request Sent',
                ],
                'body' => [
                    'ar' => 'تم إرسال طلب تحقق من المسوق سارة.',
                    'en' => 'A verification request has been submitted by marketer Sarah.',
                ],
                'image' => 'notifications/shield-tick.png',
                'payload_id' => rand(1, 100),
            ],
            [
                'type' => 'referral_request_added',
                'title' => [
                    'ar' => 'تم إرسال طلب الإحالة',
                    'en' => 'Referral Request Sent',
                ],
                'body' => [
                    'ar' => 'تم إرسال طلب إحالة من المسوق خالد.',
                    'en' => 'A referral request has been submitted by marketer Khaled.',
                ],
                'image' => 'notifications/link.png',
                'payload_id' => rand(1, 100),
            ],
            // [
            //     'type' => 'generic',
            //     'title' => [
            //         'ar' => 'إشعار',
            //         'en' => 'Notification',
            //     ],
            //     'body' => [
            //         'ar' => 'لديك إشعار جديد.',
            //         'en' => 'You have a new notification.',
            //     ],
            //     'image' => 'notification_icon.png',
            //     'payload_id' => rand(1, 100),
            // ],
        ];

        foreach ($notifications as $data) {
            Notification::create([
                'user_id' => null,
                'title' => $data['title'],
                'body' => $data['body'],
                'image' => $data['image'],
                'is_read' => false,
                'type' => $data['type'],
                'payload_id' => $data['payload_id'],
            ]);
        }
    }
}
