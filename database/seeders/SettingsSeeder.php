<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSettings = [
            ['key' => 'site_name_ar',                 'value' => 'Taswiqi'],
            ['key' => 'site_name_en',                 'value' => 'تسويقى'],
            ['key' => 'site_email',                   'value' => 'support@taswiqi.com'],
            ['key' => 'default_language',             'value' => 'en'],
            ['key' => 'logo',                         'value' => 'logo.png'],
            ['key' => 'max_withdraw_amount',          'value' => '100'],
            
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
