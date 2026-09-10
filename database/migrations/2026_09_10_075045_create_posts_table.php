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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('category');
            $table->date('published_date');

            $table->text('excerpt')->nullable();
            $table->longText('content');

            $table->string('cover_image')->nullable();
            $table->string('video_url')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug')->unique();
            $table->text('key_phrases')->nullable();

            // Visibility
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);

            // User who created the post
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
