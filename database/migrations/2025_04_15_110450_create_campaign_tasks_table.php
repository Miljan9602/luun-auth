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
        Schema::create('campaign_tasks', function (Blueprint $table) {
            $table->id();

            $table->string('campaign_id')->index();
            $table->string('user_address')->index();
            $table->string('user_id')->index();
            $table->boolean('is_completed')->default(false);
            $table->date('completed_at')->nullable()->index();

            $table->unique(['campaign_id', 'user_id'], 'campaign_user_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_tasks');
    }
};
