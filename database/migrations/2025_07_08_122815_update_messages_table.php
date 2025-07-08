<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('to_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['to_user_id']);
            $table->dropColumn('to_user_id');
        });
    }


};
