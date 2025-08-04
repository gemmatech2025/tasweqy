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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('birthdate')->nullable();
            $table->enum('gender' , ['male' , 'female']);
            $table->decimal('total_balance' , 10,2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->unsignedBigInteger('padge_id')->nullable();
            $table->foreign('padge_id')->references('id')->on('padges')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
