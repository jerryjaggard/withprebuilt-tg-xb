<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TelegramConfigController extends Controller
{
    /**
     * Get current telegram bot configuration
     */
    public function index(): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings) {
            return response()->json([
                'message' => 'Telegram settings not found'
            ], 404);
        }

        // Don't expose full bot token in response
        $data = $settings->toArray();
        if ($data['bot_token']) {
            $data['bot_token_masked'] = 'bot' . str_repeat('*', strlen($data['bot_token']) - 6) . substr($data['bot_token'], -3);
            $data['has_bot_token'] = true;
        } else {
            $data['has_bot_token'] = false;
        }
        unset($data['bot_token']);

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Update telegram bot configuration
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'bot_token' => 'nullable|string|regex:/^\d+:[A-Za-z0-9_-]+$/',
            'bot_username' => 'nullable|string|alpha_dash',
            'webhook_url' => 'nullable|url',
            'login_enabled' => 'boolean',
            'signup_enabled' => 'boolean',
            'notifications_enabled' => 'boolean',
            'welcome_message' => 'nullable|string|max:4096',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string',
            'require_email_verification' => 'boolean',
            'auto_create_account' => 'boolean',
            'session_timeout' => 'integer|min:300|max:86400', // 5 minutes to 24 hours
        ]);

        $settings = TelegramBotSetting::current();
        
        if (!$settings) {
            return response()->json([
                'message' => 'Telegram settings not found'
            ], 404);
        }

        $updateData = $request->only([
            'bot_username',
            'webhook_url', 
            'login_enabled',
            'signup_enabled',
            'notifications_enabled',
            'welcome_message',
            'allowed_domains',
            'require_email_verification',
            'auto_create_account',
            'session_timeout'
        ]);

        // Handle bot token update
        if ($request->has('bot_token') && $request->bot_token) {
            $updateData['bot_token'] = $request->bot_token;
        }

        $success = $settings->updateSettings($updateData);

        if (!$success) {
            return response()->json([
                'message' => 'Failed to update telegram settings'
            ], 500);
        }

        return response()->json([
            'data' => [
                'message' => 'Telegram settings updated successfully'
            ]
        ]);
    }

    /**
     * Test bot token validity
     */
    public function testBot(Request $request): JsonResponse
    {
        $request->validate([
            'bot_token' => 'required|string|regex:/^\d+:[A-Za-z0-9_-]+$/'
        ]);

        $settings = TelegramBotSetting::current();
        if (!$settings) {
            return response()->json([
                'message' => 'Telegram settings not found'
            ], 404);
        }

        // Temporarily set the token for testing
        $originalToken = $settings->bot_token;
        $settings->bot_token = $request->bot_token;

        $result = $settings->testBotToken();

        // Restore original token
        $settings->bot_token = $originalToken;

        if ($result['success']) {
            return response()->json([
                'data' => [
                    'valid' => true,
                    'bot_info' => $result['bot_info'],
                    'message' => 'Bot token is valid'
                ]
            ]);
        } else {
            return response()->json([
                'data' => [
                    'valid' => false,
                    'error' => $result['error'],
                    'message' => 'Bot token is invalid'
                ]
            ], 400);
        }
    }

    /**
     * Get telegram bot statistics
     */
    public function stats(): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->isConfigured()) {
            return response()->json([
                'data' => [
                    'configured' => false,
                    'message' => 'Telegram bot is not configured'
                ]
            ]);
        }

        // Get user statistics
        $totalUsers = \App\Models\User::count();
        $telegramUsers = \App\Models\User::whereNotNull('telegram_id')->count();
        $telegramLinkedToday = \App\Models\User::whereNotNull('telegram_id')
            ->where('telegram_linked_at', '>=', strtotime('today'))
            ->count();
        $telegramLinkedThisWeek = \App\Models\User::whereNotNull('telegram_id')
            ->where('telegram_linked_at', '>=', strtotime('-7 days'))
            ->count();

        return response()->json([
            'data' => [
                'configured' => true,
                'login_enabled' => $settings->login_enabled,
                'signup_enabled' => $settings->signup_enabled,
                'total_users' => $totalUsers,
                'telegram_users' => $telegramUsers,
                'telegram_percentage' => $totalUsers > 0 ? round(($telegramUsers / $totalUsers) * 100, 2) : 0,
                'linked_today' => $telegramLinkedToday,
                'linked_this_week' => $telegramLinkedThisWeek,
                'bot_username' => $settings->bot_username
            ]
        ]);
    }

    /**
     * Setup webhook for telegram bot
     */
    public function setupWebhook(Request $request): JsonResponse
    {
        $request->validate([
            'webhook_url' => 'required|url'
        ]);

        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->bot_token) {
            return response()->json([
                'message' => 'Bot token is not configured'
            ], 400);
        }

        try {
            $webhookUrl = $request->webhook_url;
            $apiUrl = $settings->getBotApiUrl() . '/setWebhook';
            
            $data = [
                'url' => $webhookUrl
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($data)
                ]
            ]);

            $response = file_get_contents($apiUrl, false, $context);
            $result = json_decode($response, true);

            if ($result['ok']) {
                // Update webhook URL in settings
                $settings->updateSettings(['webhook_url' => $webhookUrl]);
                
                return response()->json([
                    'data' => [
                        'success' => true,
                        'message' => 'Webhook setup successfully',
                        'webhook_info' => $result['result']
                    ]
                ]);
            } else {
                return response()->json([
                    'data' => [
                        'success' => false,
                        'error' => $result['description'] ?? 'Unknown error'
                    ]
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to setup telegram webhook: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to setup webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook info
     */
    public function webhookInfo(): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->bot_token) {
            return response()->json([
                'message' => 'Bot token is not configured'
            ], 400);
        }

        try {
            $apiUrl = $settings->getBotApiUrl() . '/getWebhookInfo';
            $response = file_get_contents($apiUrl);
            $result = json_decode($response, true);

            if ($result['ok']) {
                return response()->json([
                    'data' => $result['result']
                ]);
            } else {
                return response()->json([
                    'message' => $result['description'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get webhook info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->bot_token) {
            return response()->json([
                'message' => 'Bot token is not configured'
            ], 400);
        }

        try {
            $apiUrl = $settings->getBotApiUrl() . '/deleteWebhook';
            $response = file_get_contents($apiUrl);
            $result = json_decode($response, true);

            if ($result['ok']) {
                // Clear webhook URL in settings
                $settings->updateSettings(['webhook_url' => null]);
                
                return response()->json([
                    'data' => [
                        'success' => true,
                        'message' => 'Webhook deleted successfully'
                    ]
                ]);
            } else {
                return response()->json([
                    'message' => $result['description'] ?? 'Unknown error'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete webhook: ' . $e->getMessage()
            ], 500);
        }
    }
}