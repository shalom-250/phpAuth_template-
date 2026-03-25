<?php
// config/email.php

class EmailSender {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtpUsername = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->smtpPassword = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $this->smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $this->fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@yourdomain.com';
        $this->fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Security System';
    }

    public function sendWelcomeEmail($toEmail, $userName, $userId) {
        $subject = "Welcome to Security System!";
        
        // HTML Email content
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .features { margin: 20px 0; }
                .feature-item { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Security System!</h1>
                </div>
                <div class='content'>
                    <h2>Hello $userName!</h2>
                    <p>Congratulations! Your email has been verified and your account is now fully activated.</p>
                    
                    <div class='features'>
                        <h3>Here's what you can do now:</h3>
                        <div class='feature-item'>
                            <strong>🔐 Secure Login</strong>
                            <p>Access your account with enhanced security features</p>
                        </div>
                        <div class='feature-item'>
                            <strong>👤 Profile Management</strong>
                            <p>Update your personal information and preferences</p>
                        </div>
                        <div class='feature-item'>
                            <strong>📊 Dashboard Access</strong>
                            <p>View your security dashboard and activity logs</p>
                        </div>
                    </div>
                    
                    <p><strong>Get started:</strong> <a href='http://localhost/security_template/dashboard.php'>Go to Dashboard</a></p>
                    
                    <p>If you have any questions, please don't hesitate to contact our support team.</p>
                    
                    <p>Best regards,<br>Security System Team</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version
        $textContent = "Hello $userName!\n\n";
        $textContent .= "Congratulations! Your email has been verified and your account is now fully activated.\n\n";
        $textContent .= "You can now:\n";
        $textContent .= "1. Access your account with enhanced security features\n";
        $textContent .= "2. Update your personal information and preferences\n";
        $textContent .= "3. View your security dashboard and activity logs\n\n";
        $textContent .= "Get started: http://localhost/security_template/dashboard.php\n\n";
        $textContent .= "If you have any questions, please don't hesitate to contact our support team.\n\n";
        $textContent .= "Best regards,\nSecurity System Team";
        
        return $this->sendEmail($toEmail, $subject, $htmlContent, $textContent);
    }
    
    public function sendVerificationEmail($toEmail, $userName, $token, $userId) {
        $subject = "Verify Your Email Address";
        
        // Create verification link
        $verificationLink = "http://localhost/security_template/api/auth.php?action=verify-email&token=" . urlencode($token);
        
        // HTML Email content
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background: #4361ee; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #ddd; 
                    color: #666; 
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Email Verification</h1>
                </div>
                <div class='content'>
                    <h2>Hello $userName!</h2>
                    <p>Thank you for registering with our Security System. Please verify your email address by clicking the button below:</p>
                    
                    <a href='$verificationLink' class='button'>Verify Email Address</a>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='background: #eee; padding: 10px; border-radius: 5px; word-break: break-all;'>
                        $verificationLink
                    </p>
                    
                    <p>This link will expire in 24 hours.</p>
                    
                    <p>If you didn't create an account, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                    <p>© " . date('Y') . " Security System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version
        $textContent = "Hello $userName!\n\n";
        $textContent .= "Thank you for registering with our Security System.\n\n";
        $textContent .= "Please verify your email address by visiting this link:\n";
        $textContent .= "$verificationLink\n\n";
        $textContent .= "This link will expire in 24 hours.\n\n";
        $textContent .= "If you didn't create an account, please ignore this email.\n\n";
        $textContent .= "Best regards,\nSecurity System Team";
        
        return $this->sendEmail($toEmail, $subject, $htmlContent, $textContent);
    }
    
    public function sendPasswordResetEmail($toEmail, $userName, $token, $userId) {
        $subject = "Password Reset Request";
        
        // Create reset link
        $resetLink = "http://localhost/security_template/reset-password.php?token=" . urlencode($token);
        
        // HTML Email content
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background: #dc3545; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .warning { 
                    background: #fff3cd; 
                    border: 1px solid #ffeaa7; 
                    padding: 15px; 
                    border-radius: 5px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset</h1>
                </div>
                <div class='content'>
                    <h2>Hello $userName!</h2>
                    <p>We received a request to reset your password for the Security System account.</p>
                    
                    <div class='warning'>
                        <p><strong>Important:</strong> If you didn't request a password reset, please ignore this email.</p>
                    </div>
                    
                    <p>To reset your password, click the button below:</p>
                    
                    <a href='$resetLink' class='button'>Reset Password</a>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='background: #eee; padding: 10px; border-radius: 5px; word-break: break-all;'>
                        $resetLink
                    </p>
                    
                    <p>This link will expire in 1 hour.</p>
                    
                    <p>For security reasons, this link can only be used once.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($toEmail, $subject, $htmlContent);
    }
    
    private function sendEmail($to, $subject, $htmlContent, $textContent = '') {
        error_log("=== EMAIL SENDING ATTEMPT ===");
        error_log("To: $to");
        error_log("Subject: $subject");
        error_log("SMTP Host: " . $this->smtpHost);
        error_log("SMTP Username: " . $this->smtpUsername);
        
        // Path to PHPMailer
        $phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        
        // Try to load PHPMailer
        if (file_exists($phpmailerPath)) {
            require_once $phpmailerPath;
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
            
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $this->smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->smtpUsername;
                $mail->Password   = $this->smtpPassword;
                $mail->SMTPSecure = $this->smtpSecure;
                $mail->Port       = $this->smtpPort;
                $mail->SMTPDebug  = 2; // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer [$level]: $str");
                };
                
                // Recipients
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($to);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlContent;
                
                if (!empty($textContent)) {
                    $mail->AltBody = $textContent;
                }
                
                $mail->send();
                error_log("✓ Email sent successfully to: $to");
                error_log("Message ID: " . $mail->getLastMessageID());
                return true;
                
            } catch (Exception $e) {
                error_log("✗ PHPMailer failed to send to $to");
                error_log("Error Info: " . $mail->ErrorInfo);
                error_log("Exception: " . $e->getMessage());
                
                // Fall back to mail() function
                return $this->configureAndSendMail($to, $subject, $htmlContent, $textContent);
            }
        } else {
            error_log("✗ PHPMailer not found at: $phpmailerPath");
            // PHPMailer not found, use mail() function
            return $this->configureAndSendMail($to, $subject, $htmlContent, $textContent);
        }
    }
    
    private function configureAndSendMail($to, $subject, $htmlContent, $textContent = '') {
        error_log("Attempting to send via PHP mail() function");
        
        // Configure PHP's mail() settings
        ini_set('SMTP', $this->smtpHost);
        ini_set('smtp_port', $this->smtpPort);
        ini_set('sendmail_from', $this->fromEmail);
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->fromName . " <" . $this->fromEmail . ">\r\n";
        $headers .= "Reply-To: " . $this->fromEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Log what we're trying to send
        error_log("mail() headers: " . str_replace("\r\n", " | ", $headers));
        
        if (mail($to, $subject, $htmlContent, $headers)) {
            error_log("✓ mail() function succeeded for: $to");
            return true;
        } else {
            error_log("✗ mail() function failed for: $to");
            return false;
        }
    }
}