<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Create Your Account</title>
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
        
        .signup-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .signup-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .signup-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .signup-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .signup-form {
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
        
        .btn-signup {
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
        
        .btn-signup:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }
        
        .btn-signup:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .social-signup {
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
        
        .terms-text {
            font-size: 0.9rem;
            color: #666;
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
        
        .verification-modal {
            background: rgba(0, 0, 0, 0.5);
        }
        
        @media (max-width: 768px) {
            .signup-form {
                padding: 30px 20px;
            }
            
            .signup-header {
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

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationModalLabel">Verify Your Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-envelope-circle-check fa-4x text-primary mb-3"></i>
                        <h4>Check Your Email</h4>
                        <p class="text-muted">We've sent a verification link to <span id="userEmail" class="fw-bold"></span></p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please check your inbox and click the verification link to activate your account.
                    </div>
                    
                    <div class="mt-4">
                        <button class="btn btn-outline-primary me-2" id="resendVerification">
                            <i class="fas fa-redo me-1"></i> Resend Email
                        </button>
                        <button class="btn btn-primary" id="continueToLogin" data-bs-dismiss="modal">
                            <i class="fas fa-sign-in-alt me-1"></i> Continue to Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="signup-container">
                    <div class="row">
                        <!-- Left side with branding -->
                        <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center" style="background: linear-gradient(to bottom right, #4361ee, #3a0ca3); color: white; padding: 40px;">
                            <div class="text-center">
                                <i class="fas fa-user-plus fa-5x mb-4" style="opacity: 0.9;"></i>
                                <h2 class="fw-bold mb-3">Join Our Community</h2>
                                <p class="mb-0">Create an account to access exclusive features, personalized content, and connect with others.</p>
                            </div>
                        </div>
                        
                        <!-- Right side with form -->
                        <div class="col-md-6">
                            <div class="signup-header d-md-none">
                                <h1>Create Account</h1>
                                <p>Fill in your details to get started</p>
                            </div>
                            
                            <div class="signup-form">
                                <h3 class="mb-4 d-none d-md-block">Create Your Account</h3>
                                
                                <form id="signupForm" novalidate>
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-user form-icon"></i>
                                            <input type="text" class="form-control" id="fullName" placeholder="Full Name" required>
                                            <div class="valid-feedback validation-icon">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </div>
                                            <div class="invalid-feedback">
                                                Please enter your full name.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="position-relative">
                                                    <i class="fas fa-user form-icon"></i>
                                                    <input type="text" class="form-control" id="firstName" placeholder="First Name" required>
                                                    <div class="invalid-feedback">
                                                        Please enter your first name.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="position-relative">
                                                    <i class="fas fa-user form-icon"></i>
                                                    <input type="text" class="form-control" id="lastName" placeholder="Last Name" required>
                                                    <div class="invalid-feedback">
                                                        Please enter your last name.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-at form-icon"></i>
                                            <input type="text" class="form-control" id="username" placeholder="Username" required>
                                            <div class="valid-feedback validation-icon">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </div>
                                            <div class="invalid-feedback">
                                                Username must be 3-50 characters (letters, numbers, dots, underscores, hyphens).
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-envelope form-icon"></i>
                                            <input type="email" class="form-control" id="email" placeholder="Email Address" required>
                                            <div class="valid-feedback validation-icon">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </div>
                                            <div class="invalid-feedback">
                                                Please enter a valid email address.
                                            </div>
                                        </div>
                                    </div>                                    
                                    
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-phone form-icon"></i>
                                            <input type="tel" class="form-control" id="phone" placeholder="Phone Number (Optional)">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-lock form-icon"></i>
                                            <input type="password" class="form-control" id="password" placeholder="Password" required>
                                            <button type="button" class="password-toggle" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="invalid-feedback" id="passwordError">
                                                Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                                            </div>
                                        </div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <div class="strength-text" id="strengthText"></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="position-relative">
                                            <i class="fas fa-lock form-icon"></i>
                                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
                                            <div class="invalid-feedback">
                                                Passwords do not match.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms" required>
                                            <label class="form-check-label terms-text" for="terms">
                                                I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                            </label>
                                            <div class="invalid-feedback">
                                                You must agree to the terms and conditions.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-signup" id="submitBtn">
                                        <span id="submitText">
                                            <i class="fas fa-user-plus me-2"></i> Create Account
                                        </span>
                                        <span id="submitSpinner" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Creating Account...
                                        </span>
                                    </button>
                                    
                                    <div class="social-signup">
                                        <p class="mb-3">Or sign up with</p>
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
                                    
                                    <div class="login-link">
                                        Already have an account? <a href="login.php">Log In</a>
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
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
        const userEmailSpan = document.getElementById('userEmail');
        const resendVerificationBtn = document.getElementById('resendVerification');
        const continueToLoginBtn = document.getElementById('continueToLogin');
        const fullNameInput = document.getElementById('fullName');
        const firstNameInput = document.getElementById('firstName');
        const lastNameInput = document.getElementById('lastName');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const termsCheckbox = document.getElementById('terms');

        // Password strength indicators
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        const passwordError = document.getElementById('passwordError');

        // User data storage for verification
        let userRegistrationData = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            splitFullNameToFirstLast();
        });

        function setupEventListeners() {
            // Form submission
            signupForm.addEventListener('submit', handleSignup);

            // Password toggle
            togglePassword.addEventListener('click', togglePasswordVisibility);

            // Real-time validation
            usernameInput.addEventListener('input', validateUsername);
            emailInput.addEventListener('input', validateEmail);
            passwordInput.addEventListener('input', validatePasswordStrength);
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
            fullNameInput.addEventListener('input', splitFullNameToFirstLast);
            firstNameInput.addEventListener('input', updateFullName);
            lastNameInput.addEventListener('input', updateFullName);

            // Verification modal buttons
            resendVerificationBtn.addEventListener('click', resendVerificationEmail);
            continueToLoginBtn.addEventListener('click', () => {
                window.location.href = 'login.php';
            });
        }

        function splitFullNameToFirstLast() {
            const fullName = fullNameInput.value.trim();
            if (fullName) {
                const names = fullName.split(' ');
                if (names.length >= 2) {
                    firstNameInput.value = names[0];
                    lastNameInput.value = names.slice(1).join(' ');
                } else {
                    firstNameInput.value = fullName;
                    lastNameInput.value = '';
                }
            }
        }

        function updateFullName() {
            const firstName = firstNameInput.value.trim();
            const lastName = lastNameInput.value.trim();
            fullNameInput.value = `${firstName} ${lastName}`.trim();
        }

        function togglePasswordVisibility() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            confirmPasswordInput.setAttribute('type', type);
            
            // Update icon
            const icon = togglePassword.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function validateUsername() {
            const username = usernameInput.value.trim();
            const pattern = /^[a-zA-Z0-9_.-]+$/;
            
            if (username.length < 3) {
                showValidationError(usernameInput, 'Username must be at least 3 characters');
                return false;
            }
            
            if (username.length > 50) {
                showValidationError(usernameInput, 'Username must not exceed 50 characters');
                return false;
            }
            
            if (!pattern.test(username)) {
                showValidationError(usernameInput, 'Username can only contain letters, numbers, dots, underscores, and hyphens');
                return false;
            }
            
            showValidationSuccess(usernameInput);
            return true;
        }

        function validateEmail() {
            const email = emailInput.value.trim();
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!pattern.test(email)) {
                showValidationError(emailInput, 'Please enter a valid email address');
                return false;
            }
            
            showValidationSuccess(emailInput);
            return true;
        }

        function validatePasswordStrength() {
            const password = passwordInput.value;
            let strength = 0;
            let feedback = '';
            
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
                passwordError.textContent = `Password must contain: ${errors.join(', ')}`;
                showValidationError(passwordInput, 'Password does not meet requirements');
                return false;
            }
            
            showValidationSuccess(passwordInput);
            return true;
        }

        function validatePasswordMatch() {
            const password = passwordInput.value;
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

        async function handleSignup(e) {
            e.preventDefault();
            
            // Validate all fields
            const isUsernameValid = validateUsername();
            const isEmailValid = validateEmail();
            const isPasswordValid = validatePasswordStrength();
            const isPasswordMatch = validatePasswordMatch();
            const isTermsAccepted = termsCheckbox.checked;
            
            // Check first and last name
            const firstName = firstNameInput.value.trim();
            const lastName = lastNameInput.value.trim();
            if (!firstName || !lastName) {
                showToast('Please enter both first and last name', 'error');
                return;
            }
            
            if (!isUsernameValid || !isEmailValid || !isPasswordValid || !isPasswordMatch || !isTermsAccepted) {
                showToast('Please fix all validation errors before submitting', 'error');
                return;
            }
            
            // Prepare registration data
            const registrationData = {
                username: usernameInput.value.trim(),
                email: emailInput.value.trim(),
                first_name: firstName,
                last_name: lastName,
                phone: phoneInput.value.trim() || '',
                password: passwordInput.value,
                confirm_password: confirmPasswordInput.value,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
                locale: navigator.language || 'en_US'
            };
            
            // Store for potential resend
            userRegistrationData = registrationData;
            
            // Disable submit button and show loading state
            setLoadingState(true);
            
            try {
                const response = await registerUser(registrationData);
                
                if (response.success) {
                    // Show verification modal
                    userEmailSpan.textContent = registrationData.email;
                    verificationModal.show();
                    
                    // Show success toast
                    showToast('Registration successful! Please check your email for verification.', 'success');
                } else {
                    // Handle specific error cases
                    if (response.error_code === 'USERNAME_EXISTS') {
                        showValidationError(usernameInput, 'Username already exists');
                        showToast('Username is already taken. Please choose another.', 'error');
                    } else if (response.error_code === 'EMAIL_EXISTS') {
                        showValidationError(emailInput, 'Email already registered');
                        showToast('Email is already registered. Please use another email or try logging in.', 'error');
                    } else if (response.error_code === 'EMAIL_VERIFIED_EXISTS') {
                        showValidationError(emailInput, 'Email already verified with another account');
                        showToast('This email is already verified with another account.', 'error');
                    } else {
                        showToast(response.message || 'Registration failed. Please try again.', 'error');
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                showToast('Network error. Please check your connection and try again.', 'error');
            } finally {
                setLoadingState(false);
            }
        }

        async function registerUser(data) {
            const formData = new FormData();
            
            // Append all data to formData
            Object.keys(data).forEach(key => {
                if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            
            formData.append('action', 'register');
            
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

        async function resendVerificationEmail() {
            if (!userRegistrationData) {
                showToast('Unable to resend verification email', 'error');
                return;
            }
            
            setLoadingState(true, resendVerificationBtn);
            resendVerificationBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'register');
                formData.append('email', userRegistrationData.email);
                formData.append('resend', 'true');
                
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Verification email resent successfully!', 'success');
                    
                    // Update modal message
                    const modalBody = document.querySelector('#verificationModal .modal-body');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success mt-3';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i> New verification email sent!';
                    modalBody.appendChild(alertDiv);
                    
                    // Remove alert after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                } else {
                    showToast('Failed to resend verification email', 'error');
                }
            } catch (error) {
                console.error('Resend error:', error);
                showToast('Failed to resend verification email', 'error');
            } finally {
                setLoadingState(false, resendVerificationBtn);
                resendVerificationBtn.disabled = false;
            }
        }

        function setLoadingState(isLoading, button = submitBtn) {
            if (isLoading) {
                button.disabled = true;
                if (button === submitBtn) {
                    submitText.classList.add('d-none');
                    submitSpinner.classList.remove('d-none');
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

        // Add password strength meter on page load
        validatePasswordStrength();
    </script>
</body>
</html>