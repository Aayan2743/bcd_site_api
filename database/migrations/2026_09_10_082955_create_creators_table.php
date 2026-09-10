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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Basic information
            $table->string('display_name', 60);
            $table->string('tagline', 80)->nullable();
            $table->text('bio')->nullable();
            $table->json('location')->nullable();

            // Categories / Languages
            $table->json('categories')->nullable();
            $table->json('languages')->nullable();

            // Services
            $table->json('services')->nullable();

            // Platforms
            $table->json('platforms')->nullable();

            // Social media links
            $table->json('social_links')->nullable();

            // Media
            $table->string('profile_photo')->nullable();
            $table->json('portfolio_images')->nullable();
            $table->string('featured_reel_url')->nullable();

            // Stats
            $table->unsignedBigInteger('follower_count')->default(0);
            $table->unsignedBigInteger('average_reach')->default(0);

            // Visibility
            $table->boolean('show_follower_count')->default(true);
            $table->boolean('show_average_reach')->default(true);
            $table->boolean('show_enquiry_cta')->default(true);

            // Website visibility
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
