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
        Schema::create('brand_block_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_block_id')->constrained('brand_blocks')->onDelete('cascade');  
            $table->string('image');          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_block_images');
    }
};
