#!/bin/bash

# Xboard Telegram Integration Setup Script
# Run this after deploying to https://top.netflare.co

echo "🚀 Starting Xboard Telegram Integration Setup..."

# Set proper permissions
echo "📁 Setting file permissions..."
chmod -R 755 /app
chmod -R 777 /app/storage
chmod -R 777 /app/bootstrap/cache
chmod 644 /app/.env

# Install/update composer dependencies
echo "📦 Installing PHP dependencies..."
cd /app
composer install --no-dev --optimize-autoloader

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate application key if needed
echo "🔑 Checking application key..."
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup Telegram webhook
echo "📡 Setting up Telegram webhook..."
php artisan tinker --execute="
\$settings = \App\Models\TelegramBotSetting::first();
if (\$settings && \$settings->bot_token) {
    \$webhookUrl = 'https://top.netflare.co/api/v1/telegram/webhook';
    \$apiUrl = \$settings->getBotApiUrl() . '/setWebhook';
    \$data = json_encode(['url' => \$webhookUrl]);
    \$context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => \$data
        ]
    ]);
    \$response = file_get_contents(\$apiUrl, false, \$context);
    \$result = json_decode(\$response, true);
    if (\$result['ok']) {
        echo 'Telegram webhook setup successfully' . PHP_EOL;
    } else {
        echo 'Failed to setup webhook: ' . (\$result['description'] ?? 'Unknown error') . PHP_EOL;
    }
} else {
    echo 'Telegram bot not configured' . PHP_EOL;
}
"

# Create symbolic links for storage
echo "🔗 Creating storage links..."
php artisan storage:link

# Set final permissions
echo "🔐 Setting final permissions..."
chown -R www-data:www-data /app
chmod -R 755 /app
chmod -R 777 /app/storage

echo ""
echo "✅ Setup completed successfully!"
echo ""
echo "🎯 Your Xboard is now ready with Telegram integration:"
echo "   • Domain: https://top.netflare.co"
echo "   • Bot: @netflarechina"
echo "   • Telegram login/signup: ENABLED"
echo ""
echo "📋 Next steps:"
echo "   1. Login to admin panel: https://top.netflare.co/admin"
echo "   2. Check Telegram settings in admin dashboard"
echo "   3. Test Telegram login on your site"
echo ""
echo "🚀 Everything is configured and ready to use!"