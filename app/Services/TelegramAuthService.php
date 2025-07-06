<?php

namespace App\Services;

use App\Models\User;
use App\Models\TelegramBotSetting;
use App\Utils\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TelegramAuthService
{
    private TelegramBotSetting $settings;

    public function __construct()
    {
        $this->settings = TelegramBotSetting::current();
    }

    /**
     * Verify Telegram widget data
     */
    public function verifyTelegramData(array $data): bool
    {
        if (!$this->settings || !$this->settings->bot_token) {
            return false;
        }

        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);

        $dataCheckString = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $dataCheckString .= "{$key}={$value}\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        $secretKey = hash('sha256', $this->settings->bot_token, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
    }

    /**
     * Check if telegram auth data is not too old
     */
    public function isDataFresh(array $data): bool
    {
        $authDate = $data['auth_date'] ?? 0;
        $maxAge = $this->settings->session_timeout ?? 3600;
        
        return (time() - $authDate) <= $maxAge;
    }

    /**
     * Find user by telegram ID
     */
    public function findUserByTelegramId(int $telegramId): ?User
    {
        return User::where('telegram_id', $telegramId)->first();
    }

    /**
     * Create new user from telegram data
     */
    public function createUserFromTelegram(array $telegramData): User
    {
        $email = $this->generateEmailFromTelegram($telegramData);
        
        $user = new User();
        $user->email = $email;
        $user->password = Hash::make(Helper::guid()); // Random password
        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        
        // Telegram data
        $user->telegram_id = $telegramData['id'];
        $user->telegram_username = $telegramData['username'] ?? null;
        $user->telegram_first_name = $telegramData['first_name'] ?? null;
        $user->telegram_last_name = $telegramData['last_name'] ?? null;
        $user->telegram_photo_url = $telegramData['photo_url'] ?? null;
        $user->telegram_linked_at = time();

        // Default settings
        $user->remind_expire = admin_setting('default_remind_expire', 1);
        $user->remind_traffic = admin_setting('default_remind_traffic', 1);
        
        // Try out plan if enabled
        $this->setTryOutPlan($user);
        
        $user->save();

        return $user;
    }

    /**
     * Link existing user account with telegram
     */
    public function linkTelegramToUser(User $user, array $telegramData): bool
    {
        try {
            $user->telegram_id = $telegramData['id'];
            $user->telegram_username = $telegramData['username'] ?? null;
            $user->telegram_first_name = $telegramData['first_name'] ?? null;
            $user->telegram_last_name = $telegramData['last_name'] ?? null;
            $user->telegram_photo_url = $telegramData['photo_url'] ?? null;
            $user->telegram_linked_at = time();
            
            return $user->save();
        } catch (\Exception $e) {
            Log::error('Failed to link telegram to user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process telegram authentication
     */
    public function authenticate(array $telegramData): array
    {
        // Verify telegram data
        if (!$this->verifyTelegramData($telegramData)) {
            return [
                'success' => false,
                'error' => 'Invalid telegram authentication data',
                'code' => 'INVALID_DATA'
            ];
        }

        // Check if data is fresh
        if (!$this->isDataFresh($telegramData)) {
            return [
                'success' => false,
                'error' => 'Authentication data is too old',
                'code' => 'DATA_EXPIRED'
            ];
        }

        $telegramId = $telegramData['id'];
        $existingUser = $this->findUserByTelegramId($telegramId);

        if ($existingUser) {
            // User exists, log them in
            if ($existingUser->banned) {
                return [
                    'success' => false,
                    'error' => 'Your account has been suspended',
                    'code' => 'ACCOUNT_BANNED'
                ];
            }

            // Update telegram data
            $this->updateUserTelegramData($existingUser, $telegramData);

            return [
                'success' => true,
                'user' => $existingUser,
                'action' => 'login',
                'message' => 'Successfully logged in with Telegram'
            ];
        } else {
            // New user - check if signup is enabled
            if (!$this->settings->isSignupEnabled()) {
                return [
                    'success' => false,
                    'error' => 'Telegram signup is not enabled',
                    'code' => 'SIGNUP_DISABLED'
                ];
            }

            // Check domain restrictions
            $domain = request()->getHost();
            if (!$this->settings->isDomainAllowed($domain)) {
                return [
                    'success' => false,
                    'error' => 'Telegram signup is not allowed for this domain',
                    'code' => 'DOMAIN_RESTRICTED'
                ];
            }

            // Create new user
            try {
                $newUser = $this->createUserFromTelegram($telegramData);
                
                // Send welcome message if enabled
                if ($this->settings->notifications_enabled && $this->settings->welcome_message) {
                    $this->sendWelcomeMessage($telegramId, $this->settings->welcome_message);
                }

                return [
                    'success' => true,
                    'user' => $newUser,
                    'action' => 'signup',
                    'message' => 'Account created successfully with Telegram'
                ];
            } catch (\Exception $e) {
                Log::error('Failed to create user from telegram: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Failed to create account',
                    'code' => 'CREATE_FAILED'
                ];
            }
        }
    }

    /**
     * Update user telegram data
     */
    private function updateUserTelegramData(User $user, array $telegramData): void
    {
        $user->telegram_username = $telegramData['username'] ?? $user->telegram_username;
        $user->telegram_first_name = $telegramData['first_name'] ?? $user->telegram_first_name;
        $user->telegram_last_name = $telegramData['last_name'] ?? $user->telegram_last_name;
        $user->telegram_photo_url = $telegramData['photo_url'] ?? $user->telegram_photo_url;
        $user->last_login_at = time();
        $user->save();
    }

    /**
     * Generate email from telegram data
     */
    private function generateEmailFromTelegram(array $telegramData): string
    {
        $username = $telegramData['username'] ?? null;
        $telegramId = $telegramData['id'];
        
        if ($username) {
            $baseEmail = strtolower($username) . '@telegram.local';
        } else {
            $baseEmail = 'user' . $telegramId . '@telegram.local';
        }

        // Ensure email is unique
        $counter = 1;
        $email = $baseEmail;
        while (User::where('email', $email)->exists()) {
            $email = str_replace('@telegram.local', $counter . '@telegram.local', $baseEmail);
            $counter++;
        }

        return $email;
    }

    /**
     * Set try out plan for new user
     */
    private function setTryOutPlan(User $user): void
    {
        $tryOutPlanId = admin_setting('try_out_plan_id', 0);
        if (!$tryOutPlanId) return;

        $plan = \App\Models\Plan::find($tryOutPlanId);
        if (!$plan) return;

        $user->plan_id = $plan->id;
        $user->group_id = $plan->group_id;
        $user->transfer_enable = $plan->transfer_enable * 1073741824;
        $user->speed_limit = $plan->speed_limit;
        $user->expired_at = time() + (admin_setting('try_out_hour', 1) * 3600);
    }

    /**
     * Send welcome message to telegram user
     */
    private function sendWelcomeMessage(int $telegramId, string $message): void
    {
        try {
            $url = $this->settings->getBotApiUrl() . '/sendMessage';
            $data = [
                'chat_id' => $telegramId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($data)
                ]
            ]);

            file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            Log::error('Failed to send telegram welcome message: ' . $e->getMessage());
        }
    }

    /**
     * Get telegram login widget configuration
     */
    public function getWidgetConfig(): array
    {
        if (!$this->settings || !$this->settings->isLoginEnabled()) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'bot_username' => $this->settings->bot_username,
            'auth_url' => url('/api/v1/passport/telegram/callback'),
            'request_access' => 'write',
            'size' => 'large',
            'corner_radius' => '10',
            'lang' => app()->getLocale()
        ];
    }
}