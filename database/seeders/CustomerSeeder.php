<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;


class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $countries = Country::pluck('id')->all();

        // Create 10 users and attach a customer to each
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name'     => "User $i",
                'email'    => "user$i@example.com",
                'image'    => 'default.png',
                'password' => Hash::make('password'), // default password
                'role'     => 'customer',
                'phone'    => '010000000' . $i,
                'code' => '20',
            ]);

            Customer::create([
                'user_id'       => $user->id,
                'country_id'    => Arr::random($countries),
                'birthdate'     => now()->subYears(rand(18, 40))->subDays(rand(1, 365)),
                'gender'        => Arr::random(['male', 'female']),
                'total_balance' => rand(100, 5000),
                'is_verified'   => rand(0, 1),
                'is_blocked'    => 0,
            ]);
        }
    }
}

