<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_media_platform_id')
                ->nullable()
                ->constrained('social_media_platforms')
                ->nullOnDelete();

            $table->decimal('total_earnings' , 10 ,2)->default(0);
            $table->morphs('referrable');
            $table->integer('total_clients')->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
                        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_earnings');
    }
};
