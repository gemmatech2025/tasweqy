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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('type' ,[  'withdraw_request_added' ,  'verification_request_added' , 'referral_request_added' ,'message','push','withraw_issue' , 'withraw_success' , 'referral_link_added' , 'discount_code_added' ,'earning_added' , 'account_verified' , 'verification_rejected' ])->change();

        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
        });
    }
};
