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

        // Insert default settings with user's bot configuration
        DB::table('v2_telegram_bot_settings')->insert([
            'bot_token' => '7826280720:AAG5cfK1K4sv4PttW-cHJnvzDgCUPI8JEFI',
            'bot_username' => 'netflarechina',
            'webhook_url' => 'https://top.netflare.co/api/v1/telegram/webhook',
            'login_enabled' => true,
            'signup_enabled' => true,
            'notifications_enabled' => true,
            'welcome_message' => 'Welcome to NetFlare VPN! Your account has been created successfully. Enjoy fast and secure internet access! 🚀',
            'allowed_domains' => json_encode(['top.netflare.co']),
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