<?php

namespace App\Http\Controllers\V1\Passport;

use App\Http\Controllers\Controller;
use App\Services\TelegramAuthService;
use App\Models\TelegramBotSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class TelegramController extends Controller
{
    private TelegramAuthService $telegramAuth;

    public function __construct(TelegramAuthService $telegramAuth)
    {
        $this->telegramAuth = $telegramAuth;
    }

    /**
     * Get telegram widget configuration
     */
    public function config(): JsonResponse
    {
        $config = $this->telegramAuth->getWidgetConfig();
        
        return response()->json([
            'data' => $config
        ]);
    }

    /**
     * Handle telegram authentication callback
     */
    public function callback(Request $request): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->isLoginEnabled()) {
            return response()->json([
                'message' => 'Telegram authentication is not enabled'
            ], 403);
        }

        // Get telegram data from request
        $telegramData = [
            'id' => $request->input('id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'username' => $request->input('username'),
            'photo_url' => $request->input('photo_url'),
            'auth_date' => $request->input('auth_date'),
            'hash' => $request->input('hash')
        ];

        // Remove null values
        $telegramData = array_filter($telegramData, function($value) {
            return $value !== null;
        });

        // Validate required fields
        if (!isset($telegramData['id']) || !isset($telegramData['hash'])) {
            return response()->json([
                'message' => 'Missing required telegram data'
            ], 400);
        }

        // Authenticate with telegram
        $result = $this->telegramAuth->authenticate($telegramData);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error'],
                'code' => $result['code'] ?? null
            ], 400);
        }

        $user = $result['user'];
        
        // Create auth token
        $token = $user->createToken('telegram-auth')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'telegram_id' => $user->telegram_id,
                    'telegram_username' => $user->telegram_username,
                    'telegram_first_name' => $user->telegram_first_name,
                    'telegram_last_name' => $user->telegram_last_name,
                    'plan_id' => $user->plan_id,
                    'expired_at' => $user->expired_at,
                    'balance' => $user->balance
                ],
                'action' => $result['action'],
                'message' => $result['message']
            ]
        ]);
    }

    /**
     * Link telegram to existing account
     */
    public function link(Request $request): JsonResponse
    {
        $settings = TelegramBotSetting::current();
        
        if (!$settings || !$settings->isConfigured()) {
            return response()->json([
                'message' => 'Telegram integration is not configured'
            ], 403);
        }

        $user = $request->user();
        
        if ($user->telegram_id) {
            return response()->json([
                'message' => 'Telegram is already linked to this account'
            ], 400);
        }

        // Get telegram data from request
        $telegramData = [
            'id' => $request->input('id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'username' => $request->input('username'),
            'photo_url' => $request->input('photo_url'),
            'auth_date' => $request->input('auth_date'),
            'hash' => $request->input('hash')
        ];

        // Remove null values
        $telegramData = array_filter($telegramData, function($value) {
            return $value !== null;
        });

        // Verify telegram data
        if (!$this->telegramAuth->verifyTelegramData($telegramData)) {
            return response()->json([
                'message' => 'Invalid telegram authentication data'
            ], 400);
        }

        // Check if telegram ID is already used
        $existingUser = $this->telegramAuth->findUserByTelegramId($telegramData['id']);
        if ($existingUser) {
            return response()->json([
                'message' => 'This Telegram account is already linked to another user'
            ], 400);
        }

        // Link telegram to user
        $success = $this->telegramAuth->linkTelegramToUser($user, $telegramData);

        if (!$success) {
            return response()->json([
                'message' => 'Failed to link Telegram account'
            ], 500);
        }

        return response()->json([
            'data' => [
                'message' => 'Telegram account linked successfully',
                'telegram_username' => $user->telegram_username,
                'telegram_first_name' => $user->telegram_first_name
            ]
        ]);
    }

    /**
     * Unlink telegram from account
     */
    public function unlink(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->telegram_id) {
            return response()->json([
                'message' => 'No Telegram account is linked'
            ], 400);
        }

        $user->telegram_id = null;
        $user->telegram_username = null;
        $user->telegram_first_name = null;
        $user->telegram_last_name = null;
        $user->telegram_photo_url = null;
        $user->telegram_linked_at = null;
        $user->save();

        return response()->json([
            'data' => [
                'message' => 'Telegram account unlinked successfully'
            ]
        ]);
    }
}