/**
 * Telegram Login Widget for Xboard Theme
 * This script integrates the Telegram login widget into the Xboard theme
 */

// Wait for the theme to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a login/signup page
    const isLoginPage = window.location.pathname.includes('/login') || 
                       window.location.pathname.includes('/register') ||
                       document.querySelector('.login-form') ||
                       document.querySelector('.auth-form');
    
    if (!isLoginPage) return;

    // Create telegram login section
    createTelegramLoginSection();
});

function createTelegramLoginSection() {
    // Find the main login form
    const loginForm = document.querySelector('.login-form') || 
                     document.querySelector('.auth-form') ||
                     document.querySelector('form[action*="login"]') ||
                     document.querySelector('.card-body');
    
    if (!loginForm) {
        console.log('Login form not found, trying alternative approach');
        setTimeout(createTelegramLoginSection, 1000); // Retry after 1 second
        return;
    }

    // Create telegram login container
    const telegramContainer = document.createElement('div');
    telegramContainer.className = 'telegram-login-section mt-4';
    telegramContainer.innerHTML = `
        <div class="text-center">
            <div class="relative mb-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>
            <div id="telegram-login-container" class="telegram-login-container mb-3"></div>
            <div id="telegram-login-status" class="telegram-login-status" style="display: none;"></div>
        </div>
    `;

    // Insert after the login form
    loginForm.parentNode.insertBefore(telegramContainer, loginForm.nextSibling);

    // Initialize telegram login widget
    initializeTelegramWidget();
}

function initializeTelegramWidget() {
    // Load telegram login script if not already loaded
    if (!window.TelegramLoginWidget) {
        const script = document.createElement('script');
        script.src = '/assets/telegram-login.js';
        script.onload = function() {
            setupTelegramWidget();
        };
        document.head.appendChild(script);
    } else {
        setupTelegramWidget();
    }
}

function setupTelegramWidget() {
    const telegramLogin = new TelegramLoginWidget({
        containerId: 'telegram-login-container',
        buttonSize: 'large',
        cornerRadius: 8,
        onAuth: function(data) {
            const statusDiv = document.getElementById('telegram-login-status');
            statusDiv.style.display = 'block';
            statusDiv.className = 'telegram-login-status success';
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Welcome, ${data.user.telegram_first_name || 'User'}! Redirecting to dashboard...
                </div>
            `;
            
            // Store auth token
            if (data.token) {
                localStorage.setItem('auth_token', data.token);
            }
            
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        },
        onError: function(error) {
            const statusDiv = document.getElementById('telegram-login-status');
            statusDiv.style.display = 'block';
            statusDiv.className = 'telegram-login-status error';
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Login failed: ${error}
                </div>
            `;
        }
    });
    
    telegramLogin.init().then(success => {
        if (!success) {
            console.log('Telegram login is not enabled or not configured');
            // Hide the telegram section if not configured
            const section = document.querySelector('.telegram-login-section');
            if (section) {
                section.style.display = 'none';
            }
        }
    });
}

// CSS Styles for integration
const styles = `
<style>
.telegram-login-section {
    margin: 20px 0;
    padding: 20px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.telegram-login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 50px;
}

.telegram-login-status {
    margin-top: 15px;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
}

.telegram-login-status.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.telegram-login-status.error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.telegram-login-status .alert {
    margin: 0;
    padding: 8px 12px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.telegram-login-status .alert-success {
    background-color: transparent;
    border: none;
    color: inherit;
}

.telegram-login-status .alert-danger {
    background-color: transparent;
    border: none;
    color: inherit;
}

/* Dark theme compatibility */
.dark .telegram-login-section {
    background: #374151;
    border-color: #4b5563;
}

.dark .telegram-login-status.success {
    background-color: #065f46;
    border-color: #047857;
    color: #d1fae5;
}

.dark .telegram-login-status.error {
    background-color: #7f1d1d;
    border-color: #991b1b;
    color: #fecaca;
}

/* Responsive design */
@media (max-width: 768px) {
    .telegram-login-section {
        margin: 15px 0;
        padding: 15px;
    }
}
</style>
`;

// Add styles to head
document.head.insertAdjacentHTML('beforeend', styles);