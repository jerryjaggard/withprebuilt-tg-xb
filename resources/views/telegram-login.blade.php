{{-- Telegram Login Widget Component --}}
{{-- Usage: @include('telegram-login', ['redirect_url' => '/dashboard']) --}}

<div class="telegram-login-wrapper">
    <div id="telegram-login-container" class="telegram-login-container"></div>
    <div id="telegram-login-status" class="telegram-login-status" style="display: none;">
        <p class="text-blue-600">Processing Telegram login...</p>
    </div>
</div>

<style>
.telegram-login-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.telegram-login-container {
    min-height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.telegram-login-status {
    text-align: center;
    padding: 10px;
    border-radius: 4px;
    background-color: #f0f9ff;
    border: 1px solid #0ea5e9;
}

.telegram-login-status.error {
    background-color: #fef2f2;
    border-color: #ef4444;
    color: #dc2626;
}

.telegram-login-status.success {
    background-color: #f0fdf4;
    border-color: #22c55e;
    color: #16a34a;
}
</style>

<script src="/assets/telegram-login.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Telegram login widget
    const telegramLogin = new TelegramLoginWidget({
        containerId: 'telegram-login-container',
        onAuth: function(data) {
            const statusDiv = document.getElementById('telegram-login-status');
            statusDiv.style.display = 'block';
            statusDiv.className = 'telegram-login-status success';
            statusDiv.innerHTML = '<p>✓ Login successful! Redirecting...</p>';
            
            // Redirect after successful login
            setTimeout(() => {
                const redirectUrl = '{{ $redirect_url ?? "/" }}';
                window.location.href = redirectUrl;
            }, 1500);
        },
        onError: function(error) {
            const statusDiv = document.getElementById('telegram-login-status');
            statusDiv.style.display = 'block';
            statusDiv.className = 'telegram-login-status error';
            statusDiv.innerHTML = '<p>✗ Login failed: ' + error + '</p>';
        }
    });
    
    telegramLogin.init();
});
</script>