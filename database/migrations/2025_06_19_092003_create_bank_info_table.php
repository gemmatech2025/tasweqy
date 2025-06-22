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
        Schema::create('bank_info', function (Blueprint $table) {
            $table->id();
            $table->string('iban');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('bank_name');
            $table->string('swift_code');
            $table->string('address');
            $table->boolean('is_default')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_info');
    }
};
