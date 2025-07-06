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
        Schema::table('v2_user', function (Blueprint $table) {
            $table->bigInteger('telegram_id')->nullable()->unique()->after('email');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->string('telegram_first_name')->nullable()->after('telegram_username');
            $table->string('telegram_last_name')->nullable()->after('telegram_first_name');
            $table->string('telegram_photo_url')->nullable()->after('telegram_last_name');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_photo_url');
            
            // Add index for faster telegram lookups
            $table->index('telegram_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_user', function (Blueprint $table) {
            $table->dropIndex(['telegram_id']);
            $table->dropColumn([
                'telegram_id',
                'telegram_username', 
                'telegram_first_name',
                'telegram_last_name',
                'telegram_photo_url',
                'telegram_linked_at'
            ]);
        });
    }
};