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
        Schema::create('account_verification_requests', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('type' , ['id' , 'passport']);

            $table->string('front_image');
            $table->string('back_image')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_verification_requests');
    }
};
