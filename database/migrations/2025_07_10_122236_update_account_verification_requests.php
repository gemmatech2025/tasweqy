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
        Schema::table('account_verification_requests', function (Blueprint $table) {
            $table->string('reason')->nullable();
            $table->dropColumn('approved');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_verification_requests', function (Blueprint $table) {
            $table->dropColumn(['reason' , 'status']);
            $table->boolean('approved')->default(false);

            
        });
    }
};
