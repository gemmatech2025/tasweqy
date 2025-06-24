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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            // $table->decimal('discount_code_earning' , 4 ,2);
            // $table->decimal('referral_link_earning' , 4 ,2);
            $table->integer('total_marketers')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
