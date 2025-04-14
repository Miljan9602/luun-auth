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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('follower_id')->index();
            $table->unsignedBigInteger('followed_id')->index();
            $table->timestamp('created_at')->nullable();

            $table->unique(['follower_id', 'followed_id'], 'follower_followed_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
