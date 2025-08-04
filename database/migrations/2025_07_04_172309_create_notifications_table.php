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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  
            $table->unsignedBigInteger('user_id')->nullable();   // if admin then user_id is null
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->json('title');
            $table->json('body');
            $table->enum('type' ,[  'withdraw_request_added' ,  'verification_request_added' , 'referral_request_added' ,'message','push','withraw_issue' , 'withraw_success' , 'referral_link_added' , 'discount_code_added' ,'earning_added' , 'account_verified' , 'verification_rejected' ]);
            $table->integer('payload_id');
            $table->string('image')->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
