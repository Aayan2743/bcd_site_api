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
        Schema::create('growth_audit_requests', function (Blueprint $table) {
             $table->id();
             // First screen
            $table->string('business_type');
            $table->string('primary_goal');
            $table->string('main_channel')->nullable();
            $table->string('biggest_challenge');
            $table->string('monthly_budget')->nullable();
            $table->string('timeline')->nullable();

            // Second screen
            $table->string('client_name');
            $table->string('phone');
            $table->string('email');
            $table->string('company_name');

            // Optional recommended package
            $table->string('recommended_package')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_audit_requests');
    }
};