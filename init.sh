#!/bin/bash

rm -rf composer.phar
wget https://github.com/composer/composer/releases/latest/download/composer.phar -O composer.phar
php composer.phar install -vvv
php artisan xboard:install

# Run Telegram integration setup
echo "🚀 Setting up Telegram integration..."
chmod +x setup_telegram.sh
./setup_telegram.sh

if [ -f "/etc/init.d/bt" ] || [ -f "/.dockerenv" ]; then
  chown -R www:www $(pwd);
fi

echo "✅ Xboard with Telegram integration is ready!"
echo "📱 Telegram login/signup is now available at your domain"
echo "🎯 Bot: @netflarechina is configured and ready"