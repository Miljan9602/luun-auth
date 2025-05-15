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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('twitter_name');
            $table->string('twitter_username');
            $table->string('twitter_id')->unique();
            $table->string('type')->index();
            $table->string('website');
            $table->json('socials');

            $table->string('ticker');
            $table->string('logo_url', 1023);
            $table->text('description');

            $table->boolean('is_featured_enabled')->default(false);
            $table->string('featured_image_url', 1023)->nullable();
            $table->unsignedInteger('campaigns_count')->default(0);
            $table->unsignedInteger('projects_count')->default(0);
            $table->double('active_rewards_usd')->default(0);
            $table->double('rewards_distributed_usd')->default(0);
            $table->string('ecosystem_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
