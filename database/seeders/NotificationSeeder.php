<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure you have some users first
        $users = User::inRandomOrder()->take(5)->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => [
                    'en' => 'Welcome!',
                    'ar' => 'مرحبًا!',
                ],
                'body' => [
                    'en' => 'Thank you for joining our platform.',
                    'ar' => 'شكرًا لانضمامك إلى منصتنا.',
                ],
                'image' => 'https://via.placeholder.com/150',
                'is_read' => false,
                'read_at' => null,
            ]);
        }
    }
}
