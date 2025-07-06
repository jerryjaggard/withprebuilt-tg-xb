<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\TelegramBotSetting
 *
 * @property int $id
 * @property string|null $bot_token
 * @property string|null $bot_username
 * @property string|null $webhook_url
 * @property bool $login_enabled
 * @property bool $signup_enabled
 * @property bool $notifications_enabled
 * @property string|null $welcome_message
 * @property array|null $allowed_domains
 * @property bool $require_email_verification
 * @property bool $auto_create_account
 * @property int $session_timeout
 * @property int $created_at
 * @property int $updated_at
 */
class TelegramBotSetting extends Model
{
    protected $table = 'v2_telegram_bot_settings';
    protected $dateFormat = 'U';
    
    protected $fillable = [
        'bot_token',
        'bot_username',
        'webhook_url',
        'login_enabled',
        'signup_enabled',
        'notifications_enabled',
        'welcome_message',
        'allowed_domains',
        'require_email_verification',
        'auto_create_account',
        'session_timeout',
    ];

    protected $casts = [
        'login_enabled' => 'boolean',
        'signup_enabled' => 'boolean', 
        'notifications_enabled' => 'boolean',
        'allowed_domains' => 'array',
        'require_email_verification' => 'boolean',
        'auto_create_account' => 'boolean',
        'session_timeout' => 'integer',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    /**
     * Get the current telegram bot settings (singleton pattern)
     */
    public static function current(): ?self
    {
        return Cache::remember('telegram_bot_settings', 3600, function () {
            return self::first();
        });
    }

    /**
     * Update settings and clear cache
     */
    public function updateSettings(array $data): bool
    {
        $result = $this->update($data);
        Cache::forget('telegram_bot_settings');
        return $result;
    }

    /**
     * Check if telegram login is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->bot_token) && !empty($this->bot_username);
    }

    /**
     * Check if telegram login is enabled and configured
     */
    public function isLoginEnabled(): bool
    {
        return $this->login_enabled && $this->isConfigured();
    }

    /**
     * Check if telegram signup is enabled and configured
     */
    public function isSignupEnabled(): bool
    {
        return $this->signup_enabled && $this->isConfigured();
    }

    /**
     * Get domain validation rules
     */
    public function getDomainRestrictions(): array
    {
        return $this->allowed_domains ?? [];
    }

    /**
     * Validate if domain is allowed for telegram auth
     */
    public function isDomainAllowed(string $domain): bool
    {
        $allowedDomains = $this->getDomainRestrictions();
        
        if (empty($allowedDomains)) {
            return true; // No restrictions
        }
        
        return in_array($domain, $allowedDomains);
    }

    /**
     * Get the bot API URL
     */
    public function getBotApiUrl(): string
    {
        return "https://api.telegram.org/bot{$this->bot_token}";
    }

    /**
     * Test bot token validity
     */
    public function testBotToken(): array
    {
        if (!$this->bot_token) {
            return ['success' => false, 'error' => 'Bot token is required'];
        }

        try {
            $response = file_get_contents($this->getBotApiUrl() . '/getMe');
            $data = json_decode($response, true);
            
            if ($data['ok']) {
                return [
                    'success' => true,
                    'bot_info' => $data['result']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $data['description'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to connect to Telegram API: ' . $e->getMessage()
            ];
        }
    }
}