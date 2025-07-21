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
        Schema::create('brand_blocks', function (Blueprint $table) {
            $table->id();
            $table->text('reason');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');  
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->enum('type', ['block', 'unblock'])->default('block');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_blocks');
    }
};
