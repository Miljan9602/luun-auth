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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('project_twitter_id')->index();
            $table->string('campaign_name');
            $table->text('description');
            $table->string('resolve_url');

            $table->date('start_date');
            $table->date('end_date');
            $table->double('reward_usd')->default(0);

            $table->string('authorization_token')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
