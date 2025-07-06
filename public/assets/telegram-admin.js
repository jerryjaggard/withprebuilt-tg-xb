/**
 * Telegram Admin Configuration Panel for Xboard
 * Handles the admin dashboard telegram configuration
 */

class TelegramAdminPanel {
    constructor() {
        this.apiBase = '/api/v2/admin/telegram';
        this.init();
    }

    async init() {
        await this.loadConfiguration();
        this.bindEvents();
    }

    /**
     * Load current telegram configuration
     */
    async loadConfiguration() {
        try {
            const response = await this.apiRequest('GET', '/config');
            if (response.data) {
                this.populateForm(response.data);
                this.updateUI(response.data);
            }
        } catch (error) {
            this.showError('Failed to load telegram configuration: ' + error.message);
        }
    }

    /**
     * Populate form with configuration data
     */
    populateForm(data) {
        const fields = [
            'bot_username', 'webhook_url', 'welcome_message',
            'session_timeout', 'login_enabled', 'signup_enabled',
            'notifications_enabled', 'require_email_verification',
            'auto_create_account'
        ];

        fields.forEach(field => {
            const element = document.getElementById(`telegram_${field}`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = Boolean(data[field]);
                } else {
                    element.value = data[field] || '';
                }
            }
        });

        // Handle allowed domains array
        const domainsElement = document.getElementById('telegram_allowed_domains');
        if (domainsElement && data.allowed_domains) {
            domainsElement.value = data.allowed_domains.join('\n');
        }

        // Show masked bot token
        const tokenElement = document.getElementById('telegram_bot_token_display');
        if (tokenElement) {
            if (data.has_bot_token) {
                tokenElement.textContent = data.bot_token_masked;
                tokenElement.parentElement.style.display = 'block';
            } else {
                tokenElement.parentElement.style.display = 'none';
            }
        }
    }

    /**
     * Update UI based on configuration
     */
    updateUI(data) {
        const statusElement = document.getElementById('telegram_status');
        if (statusElement) {
            if (data.has_bot_token && data.bot_username) {
                statusElement.innerHTML = '<span class="text-green-600">✓ Configured</span>';
            } else {
                statusElement.innerHTML = '<span class="text-red-600">✗ Not Configured</span>';
            }
        }

        // Show/hide sections based on configuration
        const loginSection = document.getElementById('telegram_login_section');
        if (loginSection) {
            loginSection.style.display = data.has_bot_token ? 'block' : 'none';
        }
    }

    /**
     * Bind form events
     */
    bindEvents() {
        // Save configuration
        const saveBtn = document.getElementById('save_telegram_config');
        if (saveBtn) {
            saveBtn.addEventListener('click', this.saveConfiguration.bind(this));
        }

        // Test bot token
        const testBtn = document.getElementById('test_bot_token');
        if (testBtn) {
            testBtn.addEventListener('click', this.testBotToken.bind(this));
        }

        // Setup webhook
        const webhookBtn = document.getElementById('setup_webhook');
        if (webhookBtn) {
            webhookBtn.addEventListener('click', this.setupWebhook.bind(this));
        }

        // Load stats
        const statsBtn = document.getElementById('load_stats');
        if (statsBtn) {
            statsBtn.addEventListener('click', this.loadStats.bind(this));
        }

        // Auto-load stats on page load
        this.loadStats();
    }

    /**
     * Save telegram configuration
     */
    async saveConfiguration() {
        try {
            const formData = this.getFormData();
            const response = await this.apiRequest('PUT', '/config', formData);
            
            this.showSuccess('Telegram configuration saved successfully');
            await this.loadConfiguration(); // Reload to get updated data
        } catch (error) {
            this.showError('Failed to save configuration: ' + error.message);
        }
    }

    /**
     * Test bot token
     */
    async testBotToken() {
        const tokenInput = document.getElementById('telegram_bot_token');
        if (!tokenInput || !tokenInput.value) {
            this.showError('Please enter a bot token to test');
            return;
        }

        try {
            const response = await this.apiRequest('POST', '/test-bot', {
                bot_token: tokenInput.value
            });

            if (response.data.valid) {
                const botInfo = response.data.bot_info;
                this.showSuccess(`Bot token is valid! Bot: @${botInfo.username} (${botInfo.first_name})`);
                
                // Auto-fill bot username
                const usernameInput = document.getElementById('telegram_bot_username');
                if (usernameInput) {
                    usernameInput.value = botInfo.username;
                }
            } else {
                this.showError('Bot token is invalid: ' + response.data.error);
            }
        } catch (error) {
            this.showError('Failed to test bot token: ' + error.message);
        }
    }

    /**
     * Setup webhook
     */
    async setupWebhook() {
        const webhookInput = document.getElementById('telegram_webhook_url');
        if (!webhookInput || !webhookInput.value) {
            this.showError('Please enter a webhook URL');
            return;
        }

        try {
            const response = await this.apiRequest('POST', '/webhook/setup', {
                webhook_url: webhookInput.value
            });

            if (response.data.success) {
                this.showSuccess('Webhook setup successfully');
            } else {
                this.showError('Failed to setup webhook: ' + response.data.error);
            }
        } catch (error) {
            this.showError('Failed to setup webhook: ' + error.message);
        }
    }

    /**
     * Load telegram statistics
     */
    async loadStats() {
        try {
            const response = await this.apiRequest('GET', '/stats');
            this.displayStats(response.data);
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    }

    /**
     * Display statistics
     */
    displayStats(stats) {
        const statsContainer = document.getElementById('telegram_stats');
        if (!statsContainer) return;

        if (!stats.configured) {
            statsContainer.innerHTML = '<p class="text-gray-500">Telegram bot is not configured</p>';
            return;
        }

        statsContainer.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-blue-700">Total Users</h4>
                    <p class="text-2xl font-bold text-blue-900">${stats.total_users}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-green-700">Telegram Users</h4>
                    <p class="text-2xl font-bold text-green-900">${stats.telegram_users}</p>
                    <p class="text-sm text-green-600">${stats.telegram_percentage}% of total</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-yellow-700">Linked Today</h4>
                    <p class="text-2xl font-bold text-yellow-900">${stats.linked_today}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-purple-700">Linked This Week</h4>
                    <p class="text-2xl font-bold text-purple-900">${stats.linked_this_week}</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold">Bot Status</h4>
                <p><strong>Username:</strong> @${stats.bot_username}</p>
                <p><strong>Login:</strong> ${stats.login_enabled ? 'Enabled' : 'Disabled'}</p>
                <p><strong>Signup:</strong> ${stats.signup_enabled ? 'Enabled' : 'Disabled'}</p>
            </div>
        `;
    }

    /**
     * Get form data
     */
    getFormData() {
        const data = {};
        
        // Text fields
        const textFields = [
            'bot_token', 'bot_username', 'webhook_url', 'welcome_message'
        ];
        textFields.forEach(field => {
            const element = document.getElementById(`telegram_${field}`);
            if (element && element.value) {
                data[field] = element.value;
            }
        });

        // Checkbox fields
        const checkboxFields = [
            'login_enabled', 'signup_enabled', 'notifications_enabled',
            'require_email_verification', 'auto_create_account'
        ];
        checkboxFields.forEach(field => {
            const element = document.getElementById(`telegram_${field}`);
            if (element) {
                data[field] = element.checked;
            }
        });

        // Number fields
        const sessionTimeout = document.getElementById('telegram_session_timeout');
        if (sessionTimeout && sessionTimeout.value) {
            data.session_timeout = parseInt(sessionTimeout.value);
        }

        // Array fields
        const domainsElement = document.getElementById('telegram_allowed_domains');
        if (domainsElement && domainsElement.value) {
            data.allowed_domains = domainsElement.value
                .split('\n')
                .map(domain => domain.trim())
                .filter(domain => domain.length > 0);
        }

        return data;
    }

    /**
     * Make API request
     */
    async apiRequest(method, endpoint, data = null) {
        const token = localStorage.getItem('auth_token') || 
                     document.querySelector('meta[name="api-token"]')?.content;

        const options = {
            method,
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        const response = await fetch(this.apiBase + endpoint, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Request failed');
        }

        return result;
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    /**
     * Show error message
     */
    showError(message) {
        this.showMessage(message, 'error');
    }

    /**
     * Show message
     */
    showMessage(message, type) {
        // Try to use existing notification system
        if (window.notification && window.notification.show) {
            window.notification.show(message, type);
            return;
        }

        // Fallback to alert
        if (type === 'error') {
            alert('Error: ' + message);
        } else {
            alert(message);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('telegram-admin-panel')) {
        window.telegramAdmin = new TelegramAdminPanel();
    }
});