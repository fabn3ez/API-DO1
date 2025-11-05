// Authentication JavaScript
class AuthService {
    constructor() {
        this.baseUrl = window.location.origin;
        this.token = localStorage.getItem('authToken');
    }

    async request(endpoint, options = {}) {
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async login(username, password) {
        return this.request('/login', {
            method: 'POST',
            body: { username, password }
        });
    }

    async register(userData) {
        return this.request('/register', {
            method: 'POST',
            body: userData
        });
    }

    async verify2FA(tempSessionId, token) {
        return this.request('/verify-2fa', {
            method: 'POST',
            body: { temp_session_id: tempSessionId, token }
        });
    }

    async logout() {
        const result = await this.request('/logout', {
            method: 'POST'
        });
        this.clearAuth();
        return result;
    }

    async validateToken() {
        return this.request('/validate-token');
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('authToken', token);
    }

    clearAuth() {
        this.token = null;
        localStorage.removeItem('authToken');
    }

    isAuthenticated() {
        return !!this.token;
    }
}

// DOM Controller
class AuthController {
    constructor() {
        this.authService = new AuthService();
        this.bindEvents();
    }

    bindEvents() {
        const loginForm = document.getElementById('loginForm');
        const twoFaForm = document.getElementById('2faForm');

        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        if (twoFaForm) {
            twoFaForm.addEventListener('submit', (e) => this.handle2FA(e));
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const username = formData.get('username');
        const password = formData.get('password');

        this.showMessage('Logging in...', 'info');

        try {
            const result = await this.authService.login(username, password);

            if (result.message === '2FA required') {
                this.show2FASection(result.temp_session_id, result.method);
                this.showMessage(`2FA code sent to your ${result.method}`, 'success');
            } else {
                this.authService.setToken(result.token);
                this.showMessage('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1000);
            }
        } catch (error) {
            this.showMessage(error.message, 'error');
        }
    }

    async handle2FA(e) {
        e.preventDefault();
        
        const tempSessionId = document.getElementById('tempSessionId').value;
        const token = document.getElementById('token').value;

        this.showMessage('Verifying code...', 'info');

        try {
            const result = await this.authService.verify2FA(tempSessionId, token);
            this.authService.setToken(result.token);
            this.showMessage('2FA verification successful! Redirecting...', 'success');
            
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } catch (error) {
            this.showMessage(error.message, 'error');
        }
    }

    show2FASection(tempSessionId, method) {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('2faSection').style.display = 'block';
        document.getElementById('tempSessionId').value = tempSessionId;
        document.getElementById('2faMethod').textContent = method;
    }

    showMessage(message, type = 'info') {
        const messageEl = document.getElementById('message');
        messageEl.textContent = message;
        messageEl.className = `message ${type}`;
        messageEl.style.display = 'block';

        if (type !== 'info') {
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AuthController();
});