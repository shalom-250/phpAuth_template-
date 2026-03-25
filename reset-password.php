<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Security System</title>
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
        
        .reset-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .reset-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .reset-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        
        .reset-form {
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
        
        .btn-reset {
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
        
        .btn-reset:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }
        
        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
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
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .invalid-feedback {
            margin-top: 5px;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        
        .strength-weak {
            background-color: var(--error-color);
            width: 25%;
        }
        
        .strength-medium {
            background-color: #f39c12;
            width: 50%;
        }
        
        .strength-strong {
            background-color: var(--success-color);
            width: 100%;
        }
        
        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
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
        
        @media (max-width: 768px) {
            .reset-form {
                padding: 30px 20px;
            }
            
            .reset-header {
                padding: 20px;
            }
            
            .toast-container {
                top: 10px;
                right: 10px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification Container -->
    <div class="toast-container"></div>

    <div class="container">
        <div class="reset-container">
            <div class="reset-header">
                <h1>Reset Your Password</h1>
                <p>Create a new password for your account</p>
            </div>
            
            <div class="reset-form">
                <form id="resetPasswordForm" novalidate>
                    <input type="hidden" id="resetToken" value="<?php echo $_GET['token'] ?? ''; ?>">
                    
                    <div class="form-group">
                        <div class="position-relative">
                            <i class="fas fa-lock form-icon"></i>
                            <input type="password" class="form-control" id="newPassword" placeholder="New Password" required>
                            <button type="button" class="password-toggle" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback" id="newPasswordError">
                                Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                            </div>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="position-relative">
                            <i class="fas fa-lock form-icon"></i>
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm New Password" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">
                                Passwords do not match.
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your new password must be at least 8 characters long and include uppercase, lowercase, number, and special character.
                    </div>
                    
                    <button type="submit" class="btn btn-reset" id="submitBtn">
                        <span id="submitText">
                            <i class="fas fa-key me-2"></i> Reset Password
                        </span>
                        <span id="submitSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Resetting...
                        </span>
                    </button>
                    
                    <div class="login-link">
                        Remember your password? <a href="login.php">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // API Configuration
        const API_URL = 'api/auth.php';

        // DOM Elements
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const resetTokenInput = document.getElementById('resetToken');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        const newPasswordError = document.getElementById('newPasswordError');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            validatePasswordStrength();
            
            // Check if token exists
            const token = resetTokenInput.value.trim();
            if (!token) {
                showToast('Invalid or missing reset token. Please request a new password reset link.', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            }
        });

        function setupEventListeners() {
            // Form submission
            resetPasswordForm.addEventListener('submit', handleResetPassword);

            // Password toggles
            toggleNewPassword.addEventListener('click', () => togglePasswordVisibility(newPasswordInput, toggleNewPassword));
            toggleConfirmPassword.addEventListener('click', () => togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword));

            // Real-time validation
            newPasswordInput.addEventListener('input', validatePasswordStrength);
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }

        function togglePasswordVisibility(input, toggleBtn) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Update icon
            const icon = toggleBtn.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function validatePasswordStrength() {
            const password = newPasswordInput.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 1;
            
            // Number check
            if (/[0-9]/.test(password)) strength += 1;
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength indicator
            passwordStrength.className = 'password-strength';
            
            if (strength <= 2) {
                passwordStrength.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = 'var(--error-color)';
            } else if (strength <= 4) {
                passwordStrength.classList.add('strength-medium');
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#f39c12';
            } else {
                passwordStrength.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = 'var(--success-color)';
            }
            
            // Validate password requirements
            const errors = [];
            if (password.length < 8) errors.push('at least 8 characters');
            if (!/[A-Z]/.test(password)) errors.push('one uppercase letter');
            if (!/[a-z]/.test(password)) errors.push('one lowercase letter');
            if (!/[0-9]/.test(password)) errors.push('one number');
            if (!/[^A-Za-z0-9]/.test(password)) errors.push('one special character');
            
            if (errors.length > 0) {
                newPasswordError.textContent = `Password must contain: ${errors.join(', ')}`;
                showValidationError(newPasswordInput, 'Password does not meet requirements');
                return false;
            }
            
            showValidationSuccess(newPasswordInput);
            return true;
        }

        function validatePasswordMatch() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                showValidationError(confirmPasswordInput, 'Passwords do not match');
                return false;
            }
            
            showValidationSuccess(confirmPasswordInput);
            return true;
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

        async function handleResetPassword(e) {
            e.preventDefault();
            
            // Validate all fields
            const isPasswordValid = validatePasswordStrength();
            const isPasswordMatch = validatePasswordMatch();
            
            if (!isPasswordValid || !isPasswordMatch) {
                showToast('Please fix all validation errors before submitting', 'error');
                return;
            }
            
            const token = resetTokenInput.value.trim();
            if (!token) {
                showToast('Invalid reset token', 'error');
                return;
            }
            
            // Prepare reset data
            const resetData = {
                token: token,
                new_password: newPasswordInput.value,
                confirm_password: confirmPasswordInput.value
            };
            
            // Disable submit button and show loading state
            setLoadingState(true);
            
            try {
                const response = await resetPassword(resetData);
                
                if (response.success) {
                    showToast('Password reset successful! Redirecting to login...', 'success');
                    
                    // Redirect to login after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    // Handle specific error cases
                    if (response.error_code === 'INVALID_TOKEN') {
                        showToast('Invalid or expired reset token. Please request a new password reset link.', 'error');
                    } else if (response.error_code === 'PASSWORD_REUSE') {
                        showToast('New password cannot be the same as previous passwords.', 'error');
                    } else if (response.error_code === 'WEAK_PASSWORD') {
                        showToast('Password does not meet security requirements.', 'error');
                    } else {
                        showToast(response.message || 'Password reset failed. Please try again.', 'error');
                    }
                }
            } catch (error) {
                console.error('Reset password error:', error);
                showToast('Network error. Please check your connection and try again.', 'error');
            } finally {
                setLoadingState(false);
            }
        }

        async function resetPassword(data) {
            const formData = new FormData();
            
            // Append all data to formData
            Object.keys(data).forEach(key => {
                if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            
            formData.append('action', 'reset-password');
            
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

        function setLoadingState(isLoading) {
            if (isLoading) {
                submitBtn.disabled = true;
                submitText.classList.add('d-none');
                submitSpinner.classList.remove('d-none');
            } else {
                submitBtn.disabled = false;
                submitText.classList.remove('d-none');
                submitSpinner.classList.add('d-none');
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