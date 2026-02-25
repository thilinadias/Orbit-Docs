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
        // 1. Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 2. Failed Jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 3. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_super_admin')->default(false);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('oauth_provider')->nullable();
            $table->string('oauth_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('active');
            $table->boolean('mfa_enabled')->default(false);
            $table->text('google2fa_secret')->nullable();
            $table->boolean('is_2fa_enforced')->default(false);
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_reset_tokens');
    }
};
