<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\WalletTransaction;
use App\Models\User;
class WalletTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                'status' => ['pending', 'completed', 'rejected'][rand(0, 2)],
                'type' => ['deposit', 'withdrawal'][rand(0, 1)],
                'user_id' => $user->id,
            ]);
        }
    }
}
}
