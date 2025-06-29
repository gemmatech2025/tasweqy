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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->decimal('amount' , 10 ,2);
            $table->enum('status' ,['approved' , 'rejected' , 'pending'])->default('pending');
            $table->enum('type' ,['referral_link' , 'discount_code' , 'withdraw']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
