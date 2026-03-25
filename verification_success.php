<?php
// verification_success.php
session_start();

// Check if we have verification result
if (!isset($_SESSION['verification_result'])) {
    header('Location: login.php');
    exit;
}

$result = $_SESSION['verification_result'];

// Clear session after use
unset($_SESSION['verification_result']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - Security System</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 40px; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .success-icon { 
            font-size: 80px; 
            color: #28a745; 
            margin-bottom: 20px;
        }
        .error-icon { 
            font-size: 80px; 
            color: #dc3545; 
            margin-bottom: 20px;
        }
        .success { 
            color: #28a745; 
            font-size: 24px; 
            margin: 20px 0;
        }
        .error { 
            color: #dc3545; 
            font-size: 24px; 
            margin: 20px 0;
        }
        .user-info { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            margin: 25px 0; 
            text-align: left;
        }
        .btn { 
            display: inline-block; 
            padding: 12px 30px; 
            margin: 10px; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-login { background: #4361ee; }
        .btn-login:hover { background: #3a56d4; transform: translateY(-2px); }
        .btn-dashboard { background: #28a745; }
        .btn-dashboard:hover { background: #218838; transform: translateY(-2px); }
        .btn-resend { background: #ffc107; color: #212529; }
        .btn-resend:hover { background: #e0a800; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($result['success']): ?>
            <div class="success-icon">✅</div>
            <h1>Email Verified Successfully!</h1>
            <div class="success">Your email has been verified and your account is now active.</div>
            
            <div class="user-info">
                <p><strong>Welcome, <?php echo htmlspecialchars($result['username']); ?>!</strong></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($result['email']); ?></p>
                <p><strong>Verified at:</strong> <?php echo htmlspecialchars($result['verified_at']); ?></p>
                <p><strong>Status:</strong> <span style="color: #28a745;">✓ Account Active</span></p>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="login.php" class="btn btn-login">Login to Your Account</a>
                <a href="dashboard.php" class="btn btn-dashboard">Go to Dashboard</a>
            </div>
            
        <?php else: ?>
            <div class="error-icon">❌</div>
            <h1>Verification Failed</h1>
            <div class="error"><?php echo htmlspecialchars($result['message']); ?></div>
            
            <div class="user-info">
                <p><strong>Error Code:</strong> <?php echo htmlspecialchars($result['error_code'] ?? 'UNKNOWN'); ?></p>
                <p>Please try again or request a new verification email.</p>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="login.php" class="btn btn-login">Return to Login</a>
                <a href="resend_verification.php" class="btn btn-resend">Resend Verification Email</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 30px; color: #6c757d; font-size: 14px;">
        <p>Need help? <a href="contact.php" style="color: #4361ee;">Contact Support</a></p>
    </div>
</body>
</html>

<?php
// verification_success.php
session_start();

// Check if verification was successful
if (!isset($_SESSION['verification_result']) || !$_SESSION['verification_result']['success']) {
    header('Location: verification_error.php');
    exit;
}

$result = $_SESSION['verification_result'];

// Clear the session data after use
unset($_SESSION['verification_result']);

// Optionally, keep user logged in
if (isset($_SESSION['user_id'])) {
    $loggedIn = true;
} else {
    $loggedIn = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Successful</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            text-align: center;
            padding: 40px 30px;
        }
        
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 1s infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0px); }
            to { transform: translateY(-10px); }
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .subtitle {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .success-message {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .success-message p {
            color: #1e40af;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .user-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #4b5563;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #1f2937;
            font-weight: 500;
        }
        
        .actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .btn {
            padding: 16px 24px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .timer {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
        }
        
        .auto-redirect {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .countdown {
            font-weight: 700;
            color: #d97706;
            font-size: 18px;
        }
        
        @media (max-width: 480px) {
            .container {
                border-radius: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .btn {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Email Verified!</h1>
            <p class="subtitle">Your account is now fully activated</p>
        </div>
        
        <div class="content">
            <div class="success-message">
                <p>🎉 Congratulations! Your email has been successfully verified. You can now access all features of your account.</p>
            </div>
            
            <div class="user-info">
                <div class="info-row">
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo htmlspecialchars($result['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($result['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Verified at:</span>
                    <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($result['verified_at'])); ?></span>
                </div>
            </div>
            
            <?php if ($loggedIn): ?>
            <div class="auto-redirect">
                <p>You will be automatically redirected to your dashboard in <span id="countdown" class="countdown">10</span> seconds</p>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <?php if ($loggedIn): ?>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <a href="profile.php" class="btn btn-secondary">View My Profile</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary">Login to Your Account</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($loggedIn): ?>
    <script>
        // Auto-redirect after 10 seconds
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'dashboard.php';
            }
        }, 1000);
        
        // Stop auto-redirect if user clicks any button
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', () => {
                clearInterval(countdownInterval);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>