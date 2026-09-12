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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | User / Creator
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Job Details
            |--------------------------------------------------------------------------
            */

            $table->string('job_title', 255);

            $table->string('department', 100);

            $table->string('job_type', 100);

            $table->string('location', 255);

            $table->string('experience', 100);

            $table->string('work_mode', 100);

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->text('description');

            /*
            |--------------------------------------------------------------------------
            | Responsibilities / Requirements
            |--------------------------------------------------------------------------
            */

            $table->json('responsibilities');

            $table->json('requirements');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

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
        Schema::dropIfExists('careers');
    }
};