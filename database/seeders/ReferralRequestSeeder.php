<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferralRequest;
use App\Models\User;
use App\Models\Brand;

class ReferralRequestSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $brandIds = Brand::pluck('id')->toArray();

        if (empty($userIds) || empty($brandIds)) {
            $this->command->warn('Users or Brands table is empty. Skipping ReferralRequest seeding.');
            return;
        }

        foreach (range(1, 10) as $i) {
            ReferralRequest::create([
                'user_id' => $userIds[array_rand($userIds)],
                'type' => ['referral_link', 'discount_code'][array_rand(['referral_link', 'discount_code'])],
                'brand_id' => $brandIds[array_rand($brandIds)],
            ]);
        }
    }
}
