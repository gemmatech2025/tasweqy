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
        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('link');
            $table->decimal('earning_precentage' ,4,2 );
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('clients')->default(0);
  $table->enum('status', ['active', 'inactive', 'expired'])
                ->default('active'); 
                            $table->string('link_code');
            $table->string('inactive_reason')->nullable()->after('status');  

                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_links');
    }
};
