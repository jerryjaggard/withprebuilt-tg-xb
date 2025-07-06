{{-- Telegram Configuration Panel for Admin Dashboard --}}
<div id="telegram-admin-panel" class="telegram-admin-panel">
    <div class="bg-white shadow rounded-lg">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Telegram Integration</h3>
                    <p class="mt-1 text-sm text-gray-500">Configure Telegram bot for user authentication</p>
                </div>
                <div id="telegram_status" class="text-sm">
                    <span class="text-gray-500">Loading...</span>
                </div>
            </div>
        </div>

        {{-- Configuration Form --}}
        <div class="px-6 py-4">
            <form id="telegram-config-form" class="space-y-6">
                
                {{-- Bot Configuration --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Bot Configuration</h4>
                    
                    {{-- Bot Token --}}
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700">
                                Bot Token
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="password" 
                                       id="telegram_bot_token" 
                                       name="bot_token"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                                <button type="button" 
                                        id="test_bot_token"
                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                                    Test
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Get your bot token from <a href="https://t.me/BotFather" target="_blank" class="text-blue-600 hover:text-blue-500">@BotFather</a>
                            </p>
                            <div id="telegram_bot_token_display_container" style="display: none;">
                                <p class="mt-1 text-sm text-gray-600">Current token: <code id="telegram_bot_token_display"></code></p>
                            </div>
                        </div>
                        
                        {{-- Bot Username --}}
                        <div>
                            <label for="telegram_bot_username" class="block text-sm font-medium text-gray-700">
                                Bot Username
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="telegram_bot_username" 
                                       name="bot_username"
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                       placeholder="your_bot_username">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Bot username without @ symbol</p>
                        </div>
                    </div>
                </div>

                {{-- Login Settings --}}
                <div id="telegram_login_section" class="border rounded-lg p-4" style="display: none;">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Login Settings</h4>
                    
                    <div class="space-y-4">
                        {{-- Enable Login --}}
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="telegram_login_enabled" 
                                       name="login_enabled" 
                                       type="checkbox" 
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="telegram_login_enabled" class="font-medium text-gray-700">Enable Telegram Login</label>
                                <p class="text-gray-500">Allow existing users to login with Telegram</p>
                            </div>
                        </div>

                        {{-- Enable Signup --}}
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="telegram_signup_enabled" 
                                       name="signup_enabled" 
                                       type="checkbox" 
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="telegram_signup_enabled" class="font-medium text-gray-700">Enable Telegram Signup</label>
                                <p class="text-gray-500">Allow new users to create accounts via Telegram</p>
                            </div>
                        </div>

                        {{-- Auto Create Account --}}
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="telegram_auto_create_account" 
                                       name="auto_create_account" 
                                       type="checkbox" 
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                       checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="telegram_auto_create_account" class="font-medium text-gray-700">Auto Create Account</label>
                                <p class="text-gray-500">Automatically create account for new Telegram users</p>
                            </div>
                        </div>

                        {{-- Session Timeout --}}
                        <div>
                            <label for="telegram_session_timeout" class="block text-sm font-medium text-gray-700">
                                Session Timeout (seconds)
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       id="telegram_session_timeout" 
                                       name="session_timeout"
                                       min="300" 
                                       max="86400"
                                       value="3600"
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">How long telegram auth data remains valid (300-86400 seconds)</p>
                        </div>
                    </div>
                </div>

                {{-- Notifications --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Notifications</h4>
                    
                    <div class="space-y-4">
                        {{-- Enable Notifications --}}
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="telegram_notifications_enabled" 
                                       name="notifications_enabled" 
                                       type="checkbox" 
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="telegram_notifications_enabled" class="font-medium text-gray-700">Enable Notifications</label>
                                <p class="text-gray-500">Send welcome messages to new users</p>
                            </div>
                        </div>

                        {{-- Welcome Message --}}
                        <div>
                            <label for="telegram_welcome_message" class="block text-sm font-medium text-gray-700">
                                Welcome Message
                            </label>
                            <div class="mt-1">
                                <textarea id="telegram_welcome_message" 
                                          name="welcome_message" 
                                          rows="3" 
                                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="Welcome to our VPN service! Your account has been created successfully."></textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Message sent to users after successful registration</p>
                        </div>
                    </div>
                </div>

                {{-- Advanced Settings --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Advanced Settings</h4>
                    
                    <div class="space-y-4">
                        {{-- Allowed Domains --}}
                        <div>
                            <label for="telegram_allowed_domains" class="block text-sm font-medium text-gray-700">
                                Allowed Domains (Optional)
                            </label>
                            <div class="mt-1">
                                <textarea id="telegram_allowed_domains" 
                                          name="allowed_domains" 
                                          rows="3" 
                                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="example.com&#10;subdomain.example.com"></textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">One domain per line. Leave empty to allow all domains.</p>
                        </div>

                        {{-- Webhook URL --}}
                        <div>
                            <label for="telegram_webhook_url" class="block text-sm font-medium text-gray-700">
                                Webhook URL (Optional)
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="url" 
                                       id="telegram_webhook_url" 
                                       name="webhook_url"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="https://yourdomain.com/api/telegram/webhook">
                                <button type="button" 
                                        id="setup_webhook"
                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                                    Setup
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">For receiving updates from Telegram (advanced users only)</p>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end">
                    <button type="button" 
                            id="save_telegram_config"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        {{-- Statistics Section --}}
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-medium text-gray-900">Statistics</h4>
                <button type="button" 
                        id="load_stats"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Refresh
                </button>
            </div>
            <div id="telegram_stats" class="text-gray-500">
                Loading statistics...
            </div>
        </div>
    </div>
</div>

<script src="/assets/telegram-admin.js"></script>