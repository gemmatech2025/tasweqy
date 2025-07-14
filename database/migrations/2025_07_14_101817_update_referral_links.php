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
        Schema::table('referral_links', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'expired'])
                ->default('active'); 
                $table->timestamps();
        });

        Schema::table('discount_codes', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'expired'])
                ->default('active'); 
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('referral_links', function (Blueprint $table) {
            $table->dropColumn('status'); 
        });

        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn('status'); 
        });
    }
};
