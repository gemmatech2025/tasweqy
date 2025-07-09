<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;
use App\Models\SocialMediaPlatform;
use App\Models\User;
use App\Models\WalletTransaction;

class ReferralEarningSeeder extends Seeder
{
    public function run(): void
    {
       

          $users = User::all();

    if ($users->isEmpty()) {
        $users = User::factory()->count(5)->create();
    }

    foreach ($users as $user) {
        for ($i = 0; $i < 10; $i++) {
            WalletTransaction::create([
                'code' =>  random_int(100000, 999999),
                'amount' => rand(100, 10000) / 100, 
                'status' => ['approved' , 'rejected' , 'pending'][rand(0, 2)],
                'type' => ['referral_link' , 'discount_code' , 'withdraw'][rand(0, 2)],
                'user_id' => $user->id,
                'transatable_type'  => ReferralEarning::class,
                'transatable_id'    => rand(1, 100), // Assuming you have 100 ReferralEarnings
            ]);
        }
    }


        
        $platformIds = SocialMediaPlatform::pluck('id')->toArray();
        $userIds = \App\Models\User::pluck('id')->toArray();

        // Seed earnings for ReferralLinks
        ReferralLink::inRandomOrder()->take(20)->get()->each(function ($link) use ($platformIds, $userIds) {
            ReferralEarning::create([
                'referrable_type'         => ReferralLink::class,
                'referrable_id'           => $link->id,
                'user_id'                 => fake()->randomElement($userIds),
                'social_media_platform_id'=> fake()->randomElement($platformIds),
                'total_clients'           => fake()->numberBetween(5, 50),
                'total_earnings'          => fake()->randomFloat(2, 10, 1000),
            ]);
        });

        // Seed earnings for DiscountCodes
        DiscountCode::inRandomOrder()->take(20)->get()->each(function ($code) use ($platformIds, $userIds) {
            ReferralEarning::create([
                'referrable_type'         => DiscountCode::class,
                'referrable_id'           => $code->id,
                'user_id'                 => fake()->randomElement($userIds),
                'social_media_platform_id'=> fake()->randomElement($platformIds),
                'total_clients'           => fake()->numberBetween(3, 40),
                'total_earnings'          => fake()->randomFloat(2, 5, 900),
            ]);
        });
    }
}
