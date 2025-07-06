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
        Schema::create('v2_telegram_bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bot_token')->nullable();
            $table->string('bot_username')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('login_enabled')->default(false);
            $table->boolean('signup_enabled')->default(false);
            $table->boolean('notifications_enabled')->default(false);
            $table->text('welcome_message')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->boolean('require_email_verification')->default(false);
            $table->boolean('auto_create_account')->default(true);
            $table->integer('session_timeout')->default(3600); // 1 hour
            $table->timestamps();
        });

        // Insert default settings
        DB::table('v2_telegram_bot_settings')->insert([
            'login_enabled' => false,
            'signup_enabled' => false,
            'notifications_enabled' => false,
            'welcome_message' => 'Welcome to our VPN service! Your account has been created successfully.',
            'require_email_verification' => false,
            'auto_create_account' => true,
            'session_timeout' => 3600,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('v2_telegram_bot_settings');
    }
};