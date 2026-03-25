<?php
// verification_error.php
session_start();

$error = isset($_SESSION['verification_result']) ? $_SESSION['verification_result'] : [
    'success' => false,
    'message' => 'Unknown error occurred',
    'error_code' => 'UNKNOWN_ERROR'
];

// Clear the session data
unset($_SESSION['verification_result']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Failed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f56565 0%, #ed8936 100%);
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
            background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
            color: white;
            text-align: center;
            padding: 40px 30px;
        }
        
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
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
        
        .error-message {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .error-message p {
            color: #991b1b;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .error-code {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 600;
        }
        
        .suggestions {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        
        .suggestions h3 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .suggestions ul {
            padding-left: 20px;
            color: #4b5563;
        }
        
        .suggestions li {
            margin-bottom: 8px;
            line-height: 1.5;
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
            background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
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
            <div class="icon">❌</div>
            <h1>Verification Failed</h1>
            <p class="subtitle">We couldn't verify your email</p>
        </div>
        
        <div class="content">
            <div class="error-message">
                <p><?php echo htmlspecialchars($error['message']); ?></p>
                <?php if (isset($error['error_code'])): ?>
                <div class="error-code">Error: <?php echo htmlspecialchars($error['error_code']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="suggestions">
                <h3>What you can do:</h3>
                <ul>
                    <li>Check if you're using the correct verification link</li>
                    <li>Make sure the link hasn't expired (valid for 24 hours)</li>
                    <li>Try requesting a new verification email</li>
                    <li>Contact support if the problem persists</li>
                </ul>
            </div>
            
            <div class="actions">
                <a href="register.php?resend=true" class="btn btn-primary">Resend Verification Email</a>
                <a href="login.php" class="btn btn-secondary">Go to Login</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>