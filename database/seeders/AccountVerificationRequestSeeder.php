<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountVerificationRequest;
use App\Models\User;

class AccountVerificationRequestSeeder extends Seeder
{
    public function run(): void
    {
        $exampleImages = [
            'notifications/empty-wallet-add.png',
            'notifications/shield-tick.png',
            'notifications/link.png',
            'notification_icon.png',
        ];

        $customers = User::where('role', 'customer')->get();

        foreach ($customers as $customer) {
            $type = fake()->randomElement(['id', 'passport']);
            $frontImage = fake()->randomElement($exampleImages);
            $backImage = $type === 'id' ? fake()->randomElement($exampleImages) : null;

            AccountVerificationRequest::create([
                'name'        => $customer->name,
                'type'        => $type,
                'front_image' => $frontImage,
                'back_image'  => $backImage,
                'user_id'     => $customer->id,
                'reason'      => null,
                'status'      => 'pending',
                'approved_by' => null,
            ]);
        }
    }
}
