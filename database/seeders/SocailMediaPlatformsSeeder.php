<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialMediaPlatform;

class SocialMediaPlatformSeeder extends Seeder
{
    public function run(): void
    {

        SocialMediaPlatform::all()->delete();
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
                'logo' => 'logos/Instagram.png'
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
                    'en' => 'Messanger',
                    'ar' => 'ماسنجر'
                ],
                'logo' => 'logos/messanger.png'
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
