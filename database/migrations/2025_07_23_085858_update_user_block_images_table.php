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
        Schema::table('user_block_images', function (Blueprint $table) {
            $table->dropForeign(['user_block_id']);
            $table->foreign('user_block_id')
                  ->references('id')
                  ->on('user_blocks')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_block_images', function (Blueprint $table) {
            //
        });
    }
};
