/**
 * Telegram Login Widget Integration for Xboard
 * This script handles the Telegram login widget functionality
 */

class TelegramLoginWidget {
    constructor(options = {}) {
        this.options = {
            containerId: 'telegram-login-container',
            buttonText: 'Login with Telegram',
            buttonSize: 'large',
            cornerRadius: 10,
            requestAccess: 'write',
            lang: 'en',
            onAuth: null,
            onError: null,
            ...options
        };
        
        this.config = null;
        this.initialized = false;
    }

    /**
     * Initialize the Telegram login widget
     */
    async init() {
        try {
            // Get telegram configuration from backend
            const response = await fetch('/api/v1/passport/telegram/config');
            const result = await response.json();
            
            if (!result.data.enabled) {
                console.log('Telegram login is not enabled');
                return false;
            }
            
            this.config = result.data;
            this.loadTelegramWidget();
            this.initialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize Telegram login:', error);
            if (this.options.onError) {
                this.options.onError('Failed to initialize Telegram login');
            }
            return false;
        }
    }

    /**
     * Load the Telegram login widget script and create button
     */
    loadTelegramWidget() {
        // Create container if it doesn't exist
        let container = document.getElementById(this.options.containerId);
        if (!container) {
            console.error(`Container with ID '${this.options.containerId}' not found`);
            return;
        }

        // Clear existing content
        container.innerHTML = '';

        // Create telegram login script
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://telegram.org/js/telegram-widget.js?22';
        script.setAttribute('data-telegram-login', this.config.bot_username);
        script.setAttribute('data-size', this.options.buttonSize);
        script.setAttribute('data-radius', this.options.cornerRadius);
        script.setAttribute('data-request-access', this.options.requestAccess);
        script.setAttribute('data-userpic', 'false');
        script.setAttribute('data-lang', this.options.lang);
        script.setAttribute('data-onauth', 'TelegramLogin.onTelegramAuth(user)');

        container.appendChild(script);

        // Setup global callback function
        window.TelegramLogin = {
            onTelegramAuth: this.handleTelegramAuth.bind(this)
        };
    }

    /**
     * Handle Telegram authentication callback
     */
    async handleTelegramAuth(user) {
        try {
            // Show loading state
            this.setLoadingState(true);

            // Send telegram data to backend
            const response = await fetch('/api/v1/passport/telegram/callback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(user)
            });

            const result = await response.json();

            if (response.ok && result.data) {
                // Store token if provided
                if (result.data.token) {
                    localStorage.setItem('auth_token', result.data.token);
                }

                // Call success callback
                if (this.options.onAuth) {
                    this.options.onAuth(result.data);
                } else {
                    // Default behavior - redirect to dashboard
                    window.location.href = '/';
                }
            } else {
                throw new Error(result.message || 'Authentication failed');
            }
        } catch (error) {
            console.error('Telegram authentication error:', error);
            if (this.options.onError) {
                this.options.onError(error.message);
            } else {
                alert('Login failed: ' + error.message);
            }
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Set loading state for the widget
     */
    setLoadingState(loading) {
        const container = document.getElementById(this.options.containerId);
        if (!container) return;

        if (loading) {
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
        } else {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        }
    }

    /**
     * Link Telegram to existing account
     */
    async linkAccount(user) {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                throw new Error('User not authenticated');
            }

            const response = await fetch('/api/v1/user/telegram/link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(user)
            });

            const result = await response.json();

            if (response.ok && result.data) {
                return { success: true, message: result.data.message };
            } else {
                throw new Error(result.message || 'Failed to link Telegram account');
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    /**
     * Unlink Telegram from account
     */
    async unlinkAccount() {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                throw new Error('User not authenticated');
            }

            const response = await fetch('/api/v1/user/telegram/unlink', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (response.ok && result.data) {
                return { success: true, message: result.data.message };
            } else {
                throw new Error(result.message || 'Failed to unlink Telegram account');
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    /**
     * Destroy the widget
     */
    destroy() {
        const container = document.getElementById(this.options.containerId);
        if (container) {
            container.innerHTML = '';
        }
        
        // Clean up global callback
        if (window.TelegramLogin) {
            delete window.TelegramLogin;
        }
        
        this.initialized = false;
    }
}

// Auto-initialize if container exists
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('telegram-login-container');
    if (container && !container.hasAttribute('data-manual-init')) {
        window.telegramLogin = new TelegramLoginWidget();
        window.telegramLogin.init();
    }
});

// Export for manual initialization
window.TelegramLoginWidget = TelegramLoginWidget;