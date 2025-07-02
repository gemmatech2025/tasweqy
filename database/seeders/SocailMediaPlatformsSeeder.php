<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialMediaPlatform;

class SocialMediaPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name' => [
                    'en' => 'Facebook',
                    'ar' => 'فيسبوك'
                ],
                'logo' => 'logos/facebook.png'
            ],
            [
                'name' => [
                    'en' => 'Instagram',
                    'ar' => 'إنستغرام'
                ],
                'logo' => 'logos/instagram.png'
            ],
            [
                'name' => [
                    'en' => 'TikTok',
                    'ar' => 'تيك توك'
                ],
                'logo' => 'logos/tiktok.png'
            ],
            [
                'name' => [
                    'en' => 'Snapchat',
                    'ar' => 'سناب شات'
                ],
                'logo' => 'logos/snapchat.png'
            ],
            [
                'name' => [
                    'en' => 'YouTube',
                    'ar' => 'يوتيوب'
                ],
                'logo' => 'logos/youtube.png'
            ],
            [
                'name' => [
                    'en' => 'X (Twitter)',
                    'ar' => 'تويتر (X)'
                ],
                'logo' => 'logos/x.png'
            ],
            [
                'name' => [
                    'en' => 'LinkedIn',
                    'ar' => 'لينكد إن'
                ],
                'logo' => 'logos/linkedin.png'
            ],
            [
                'name' => [
                    'en' => 'Telegram',
                    'ar' => 'تليجرام'
                ],
                'logo' => 'logos/telegram.png'
            ],
            [
                'name' => [
                    'en' => 'WhatsApp',
                    'ar' => 'واتساب'
                ],
                'logo' => 'logos/whatsapp.png'
            ],
            [
                'name' => [
                    'en' => 'Pinterest',
                    'ar' => 'بينترست'
                ],
                'logo' => 'logos/pinterest.png'
            ],
        ];

        foreach ($platforms as $platform) {
            SocialMediaPlatform::updateOrCreate(
                ['name->en' => $platform['name']['en']],
                [
                    'name' => $platform['name'],
                    'logo' => $platform['logo'],
                ]
            );
        }
    }
}
