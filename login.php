<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Access Your Account</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --error-color: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .login-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .login-form {
            padding: 40px;
        }
        
        .form-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
        .form-control {
            padding-left: 45px;
            height: 50px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .social-login {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin: 0 5px;
            color: white;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            color: white;
        }
        
        .btn-google {
            background-color: #dd4b39;
        }
        
        .btn-facebook {
            background-color: #3b5998;
        }
        
        .btn-twitter {
            background-color: #1da1f2;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .validation-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }
        
        .valid-feedback, .invalid-feedback {
            margin-top: 5px;
        }
        
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
        
        .toast {
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .toast-success {
            border-left: 4px solid var(--success-color);
        }
        
        .toast-error {
            border-left: 4px solid var(--error-color);
        }
        
        .toast-info {
            border-left: 4px solid var(--primary-color);
        }
        
        .toast-header {
            border-bottom: none;
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
        }
        
        .mfa-modal {
            background: rgba(0, 0, 0, 0.5);
        }
        
        @media (max-width: 768px) {
            .login-form {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .toast-container {
                top: 10px;
                right: 10px;
                left: 10px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification Container -->
    <div class="toast-container"></div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Your Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <i class="fas fa-key fa-3x text-primary mb-3 d-block text-center"></i>
                        <p class="text-center">Enter your email address and we'll send you a link to reset your password.</p>
                    </div>
                    
                    <form id="forgotPasswordForm">
                        <div class="form-group mb-3">
                            <label for="forgotEmail" class="form-label">Email Address</label>
                            <div class="position-relative">
                                <i class="fas fa-envelope form-icon"></i>
                                <input type="email" class="form-control" id="forgotEmail" placeholder="Enter your email address" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            For security reasons, we'll only send a reset link to verified email addresses.
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100" id="submitForgotPassword">
                                <span id="forgotSubmitText">
                                    <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                                </span>
                                <span id="forgotSubmitSpinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MFA Modal -->
    <div class="modal fade" id="mfaModal" tabindex="-1" aria-labelledby="mfaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mfaModalLabel">Two-Factor Authentication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-4x text-primary mb-3"></i>
                        <h4>Enter Authentication Code</h4>
                        <p class="text-muted">Please enter the 6-digit code from your authenticator app</p>
                    </div>
                    
                    <form id="mfaForm">
                        <div class="form-group mb-4">
                            <div class="otp-inputs d-flex justify-content-center gap-2">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" style="width: 50px; height: 60px; font-size: 1.5rem;">
                            </div>
                            <input type="hidden" id="mfaCode">
                            <div class="invalid-feedback mt-2">
                                Please enter a valid 6-digit code
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Open your authenticator app to get the verification code.
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100" id="submitMFA">
                                <span id="mfaSubmitText">
                                    <i class="fas fa-check-circle me-2"></i> Verify & Continue
                                </span>
                                <span id="mfaSubmitSpinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Verifying...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-container">
                    <div class="row">
                        <!-- Left side with branding -->
                        <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center" style="background: linear-gradient(to bottom right, #4361ee, #3a0ca3); color: white; padding: 40px;">
                            <div class="text-center">
                                <i class="fas fa-sign-in-alt fa-5x mb-4" style="opacity: 0.9;"></i>
                                <h2 class="fw-bold mb-3">Welcome Back</h2>
                                <p class="mb-0">Sign in to your account to access personalized features and continue your journey with us.</p>
                            </div>
                        </div>
                        
                        <!-- Right side with form -->
                        <div class="col-md-6">
                            <div class="login-header d-md-none">
                                <h1>Welcome Back</h1>
                                <p>Sign in to your account</p>
                            </div>
                            
                            <div class="login-form">
                                <h3 class="mb-4 d-none d-md-block">Sign In to Your Account</h3>
                                
                                <form id="loginForm" novalidate>
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-user form-icon"></i>
                                            <input type="text" class="form-control" id="username" placeholder="Username or Email" required>
                                            <div class="valid-feedback validation-icon">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </div>
                                            <div class="invalid-feedback">
                                                Please enter your username or email.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-lock form-icon"></i>
                                            <input type="password" class="form-control" id="password" placeholder="Password" required>
                                            <button type="button" class="password-toggle" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="invalid-feedback">
                                                Please enter your password.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="remember-forgot">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="rememberMe">
                                            <label class="form-check-label" for="rememberMe">
                                                Remember me
                                            </label>
                                        </div>
                                        <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                            <i class="fas fa-question-circle me-1"></i> Forgot password?
                                        </a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-login" id="submitBtn">
                                        <span id="submitText">
                                            <i class="fas fa-sign-in-alt me-2"></i> Sign In
                                        </span>
                                        <span id="submitSpinner" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Signing in...
                                        </span>
                                    </button>
                                    
                                    <div class="social-login">
                                        <p class="mb-3">Or sign in with</p>
                                        <a href="#" class="social-btn btn-google">
                                            <i class="fab fa-google"></i>
                                        </a>
                                        <a href="#" class="social-btn btn-facebook">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                        <a href="#" class="social-btn btn-twitter">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="signup-link">
                                        Don't have an account? <a href="signup.php">Create Account</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // API Configuration
        const API_URL = 'api/auth.php';

        // DOM Elements
        const loginForm = document.getElementById('loginForm');
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const mfaForm = document.getElementById('mfaForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const usernameInput = document.getElementById('username');
        const rememberMeCheckbox = document.getElementById('rememberMe');
        const forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        const mfaModal = new bootstrap.Modal(document.getElementById('mfaModal'));
        const forgotEmailInput = document.getElementById('forgotEmail');
        const submitForgotPasswordBtn = document.getElementById('submitForgotPassword');
        const forgotSubmitText = document.getElementById('forgotSubmitText');
        const forgotSubmitSpinner = document.getElementById('forgotSubmitSpinner');
        const otpInputs = document.querySelectorAll('.otp-input');
        const mfaCodeInput = document.getElementById('mfaCode');
        const submitMFABtn = document.getElementById('submitMFA');
        const mfaSubmitText = document.getElementById('mfaSubmitText');
        const mfaSubmitSpinner = document.getElementById('mfaSubmitSpinner');

        // Login response data storage
        let loginResponseData = null;
        let requiresMFA = false;
        let sessionId = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            
            // Check if there's a saved username
            const savedUsername = localStorage.getItem('rememberedUsername');
            if (savedUsername) {
                usernameInput.value = savedUsername;
                rememberMeCheckbox.checked = true;
            }
        });

        function setupEventListeners() {
            // Login form submission
            loginForm.addEventListener('submit', handleLogin);

            // Forgot password form submission
            forgotPasswordForm.addEventListener('submit', handleForgotPassword);

            // MFA form submission
            mfaForm.addEventListener('submit', handleMFA);

            // Password toggle
            togglePassword.addEventListener('click', togglePasswordVisibility);

            // OTP input handling
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    // Auto-focus next input
                    if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                    
                    // Update hidden input
                    updateMFACode();
                });
                
                input.addEventListener('keydown', (e) => {
                    // Handle backspace
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });
                
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text');
                    if (pasteData.length === 6 && /^\d+$/.test(pasteData)) {
                        pasteData.split('').forEach((char, idx) => {
                            if (otpInputs[idx]) {
                                otpInputs[idx].value = char;
                            }
                        });
                        updateMFACode();
                        otpInputs[5].focus();
                    }
                });
            });
        }

        function updateMFACode() {
            const code = Array.from(otpInputs).map(input => input.value).join('');
            mfaCodeInput.value = code;
        }

        function togglePasswordVisibility() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update icon
            const icon = togglePassword.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function validateLoginForm() {
            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            
            let isValid = true;
            
            if (!username) {
                showValidationError(usernameInput, 'Please enter your username or email');
                isValid = false;
            } else {
                showValidationSuccess(usernameInput);
            }
            
            if (!password) {
                showValidationError(passwordInput, 'Please enter your password');
                isValid = false;
            } else {
                showValidationSuccess(passwordInput);
            }
            
            return isValid;
        }

        function showValidationError(input, message) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            
            const feedback = input.nextElementSibling?.querySelector('.invalid-feedback') || 
                           input.parentElement.nextElementSibling;
            
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
                feedback.style.display = 'block';
            }
        }

        function showValidationSuccess(input) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            
            const feedback = input.nextElementSibling?.querySelector('.invalid-feedback') || 
                           input.parentElement.nextElementSibling;
            
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateLoginForm()) {
                showToast('Please fill in all required fields', 'error');
                return;
            }
            
            // Prepare login data
            const loginData = {
                username: usernameInput.value.trim(),
                password: passwordInput.value
            };
            
            // Save username if remember me is checked
            if (rememberMeCheckbox.checked) {
                localStorage.setItem('rememberedUsername', loginData.username);
            } else {
                localStorage.removeItem('rememberedUsername');
            }
            
            // Disable submit button and show loading state
            setLoadingState(true);
            
            try {
                const response = await loginUser(loginData);
                
                if (response.success) {
                    if (response.requires_mfa) {
                        // Store login data and show MFA modal
                        loginResponseData = response;
                        requiresMFA = true;
                        sessionId = response.session?.session_id;
                        mfaModal.show();
                        showToast('Please enter your MFA code to continue', 'info');
                    } else {
                        // Login successful - handle redirection
                        handleSuccessfulLogin(response);
                    }
                } else {
                    // Handle specific error cases
                    handleLoginError(response);
                }
            } catch (error) {
                console.error('Login error:', error);
                showToast('Network error. Please check your connection and try again.', 'error');
            } finally {
                setLoadingState(false);
            }
        }

        async function loginUser(data) {
            const formData = new FormData();
            
            // Append all data to formData
            Object.keys(data).forEach(key => {
                if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            
            formData.append('action', 'login');
            
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            
            // Check if response is JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return await response.json();
            } else {
                // If not JSON, there's an error
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }
        }

        function handleSuccessfulLogin(response) {
            // Store session data
            if (response.session) {
                localStorage.setItem('session_id', response.session.session_id);
                localStorage.setItem('user_data', JSON.stringify(response.user));
                localStorage.setItem('session_expires', response.session.expires_at);
            }
            
            showToast('Login successful! Redirecting...', 'success');
            
            // Redirect to dashboard after 1.5 seconds
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        }

        function handleLoginError(response) {
            if (response.error_code === 'INVALID_CREDENTIALS') {
                showValidationError(usernameInput, 'Invalid username or password');
                showValidationError(passwordInput, 'Invalid username or password');
                showToast('Invalid username or password. Please try again.', 'error');
            } else if (response.error_code === 'ACCOUNT_LOCKED') {
                showToast('Your account has been locked. Please contact support or try again later.', 'error');
            } else if (response.error_code === 'ACCOUNT_INACTIVE') {
                showToast('Your account is deactivated. Please contact support.', 'error');
            } else if (response.error_code === 'MAX_ATTEMPTS_EXCEEDED') {
                showToast('Too many failed attempts. Account has been locked.', 'error');
            } else if (response.error_code === 'PASSWORD_CHANGE_REQUIRED') {
                showToast('Please change your password to continue.', 'warning');
                // You could redirect to password change page here
                if (response.data?.user_id) {
                    localStorage.setItem('user_id_pwd_change', response.data.user_id);
                    window.location.href = 'change-password.php';
                }
            } else if (response.error_code === 'IP_BLOCKED') {
                showToast('Access denied from your IP address.', 'error');
            } else {
                showToast(response.message || 'Login failed. Please try again.', 'error');
            }
        }

        async function handleForgotPassword(e) {
            e.preventDefault();
            
            const email = forgotEmailInput.value.trim();
            
            if (!email) {
                showValidationError(forgotEmailInput, 'Please enter your email address');
                return;
            }
            
            // Simple email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                showValidationError(forgotEmailInput, 'Please enter a valid email address');
                return;
            }
            
            setLoadingState(true, submitForgotPasswordBtn);
            
            try {
                const response = await sendPasswordResetEmail(email);
                
                if (response.success) {
                    showToast(response.message || 'Password reset link sent to your email.', 'success');
                    forgotPasswordModal.hide();
                    
                    // Clear form
                    forgotPasswordForm.reset();
                } else {
                    showToast(response.message || 'Failed to send reset link. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                showToast('Failed to process your request. Please try again.', 'error');
            } finally {
                setLoadingState(false, submitForgotPasswordBtn);
            }
        }

        async function sendPasswordResetEmail(email) {
            const formData = new FormData();
            formData.append('action', 'forgot-password');
            formData.append('email', email);
            
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }
        }

        async function handleMFA(e) {
            e.preventDefault();
            
            const code = mfaCodeInput.value;
            
            if (!code || code.length !== 6 || !/^\d+$/.test(code)) {
                showToast('Please enter a valid 6-digit code', 'error');
                otpInputs[0].focus();
                return;
            }
            
            setLoadingState(true, submitMFABtn);
            
            try {
                // In a real implementation, you would verify the MFA code with the server
                // For now, we'll assume it's valid if we have loginResponseData
                if (loginResponseData) {
                    // Simulate MFA verification delay
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                    // Close MFA modal
                    mfaModal.hide();
                    
                    // Complete the login
                    handleSuccessfulLogin(loginResponseData);
                } else {
                    showToast('MFA verification failed. Please try again.', 'error');
                }
            } catch (error) {
                console.error('MFA error:', error);
                showToast('MFA verification failed. Please try again.', 'error');
            } finally {
                setLoadingState(false, submitMFABtn);
                // Clear OTP inputs
                otpInputs.forEach(input => input.value = '');
                updateMFACode();
            }
        }

        function setLoadingState(isLoading, button = submitBtn) {
            if (isLoading) {
                button.disabled = true;
                if (button === submitBtn) {
                    submitText.classList.add('d-none');
                    submitSpinner.classList.remove('d-none');
                } else if (button === submitForgotPasswordBtn) {
                    forgotSubmitText.classList.add('d-none');
                    forgotSubmitSpinner.classList.remove('d-none');
                } else if (button === submitMFABtn) {
                    mfaSubmitText.classList.add('d-none');
                    mfaSubmitSpinner.classList.remove('d-none');
                } else {
                    const originalText = button.innerHTML;
                    button.setAttribute('data-original-text', originalText);
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
                }
            } else {
                button.disabled = false;
                if (button === submitBtn) {
                    submitText.classList.remove('d-none');
                    submitSpinner.classList.add('d-none');
                } else if (button === submitForgotPasswordBtn) {
                    forgotSubmitText.classList.remove('d-none');
                    forgotSubmitSpinner.classList.add('d-none');
                } else if (button === submitMFABtn) {
                    mfaSubmitText.classList.remove('d-none');
                    mfaSubmitSpinner.classList.add('d-none');
                } else {
                    const originalText = button.getAttribute('data-original-text');
                    if (originalText) {
                        button.innerHTML = originalText;
                    }
                }
            }
        }

        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = `toast toast-${type}`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Determine icon and color based on type
            let icon, bgColor;
            switch (type) {
                case 'success':
                    icon = 'check-circle';
                    bgColor = 'var(--success-color)';
                    break;
                case 'error':
                    icon = 'exclamation-circle';
                    bgColor = 'var(--error-color)';
                    break;
                case 'warning':
                    icon = 'exclamation-triangle';
                    bgColor = '#f39c12';
                    break;
                default:
                    icon = 'info-circle';
                    bgColor = 'var(--primary-color)';
            }
            
            toastEl.innerHTML = `
                <div class="toast-header">
                    <i class="fas fa-${icon} me-2" style="color: ${bgColor};"></i>
                    <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            // Add to container
            toastContainer.appendChild(toastEl);
            
            // Initialize Bootstrap toast
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: type === 'error' ? 7000 : 5000
            });
            
            // Show toast
            toast.show();
            
            // Remove from DOM after hiding
            toastEl.addEventListener('hidden.bs.toast', function() {
                toastEl.remove();
            });
        }

        // Auto-hide validation messages when user starts typing
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.nextElementSibling?.querySelector('.invalid-feedback') || 
                                   this.parentElement.nextElementSibling;
                    
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>
</html>