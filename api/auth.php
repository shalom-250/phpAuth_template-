<?php
// api/auth.php - Complete Authentication API for Security System

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/db.php';
session_start();

// Ensure server errors are returned as JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);



// Convert PHP warnings/notices into exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});


// Handle fatal errors on shutdown
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'error_code' => 'FATAL_ERROR',
            'data' => null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
});

// Helper function for JSON response
function jsonResponse($success, $message, $data = null, $httpCode = 200, $errorCode = null) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'error_code' => $errorCode,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Helper function to execute queries
function executeQuery($query, $params = [], $returnStmt = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute($params);
        
        if ($returnStmt) {
            return $stmt;
        }
        
        return [
            'success' => $success,
            'stmt' => $stmt,
            'lastInsertId' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage() . " | Query: " . $query);
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => 'DB_ERROR'
        ];
    }
}

// Helper to get request parameters
function getRequestParams() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            return is_array($data) ? $data : [];
        } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            return $_POST;
        } elseif (strpos($contentType, 'multipart/form-data') !== false) {
            return array_merge($_POST, $_FILES);
        }
    }
    return $_GET;
}

// Helper to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper to validate password strength
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}

// Helper to generate secure token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Helper to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
}

// Helper to log security events
function logSecurityEvent($userId, $eventType, $details = null) {
    global $pdo;
    
    try {
        $query = "INSERT INTO audit_logs (
    user_id,
    event_type,
    ip_address,
    user_agent,
    created_at
) VALUES (
    :user_id,
    :event_type,
    :ip_address,
    :user_agent,
    NOW()
)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':event_type' => $eventType,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // If additional details provided, log them
        if ($details) {
            $logId = $pdo->lastInsertId();
            $detailsQuery = "UPDATE audit_logs SET new_values = :details WHERE id = :log_id";
            $stmt = $pdo->prepare($detailsQuery);
            $stmt->execute([':details' => json_encode($details), ':log_id' => $logId]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log security event: " . $e->getMessage());
        return false;
    }
}

// Helper to log login attempt
function logLoginAttempt($username, $success, $failureReason = null) {
    global $pdo;
    
    try {
        $query = "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at) 
                  VALUES (:username, :ip_address, :user_agent, :success, :failure_reason, NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':username' => $username,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ':success' => $success ? 1 : 0,
            ':failure_reason' => $failureReason
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
        return false;
    }
}

// Helper to check if IP is blocked
function isIpBlocked($ip = null) {
    global $pdo;
    
    $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    try {
        $query = "SELECT COUNT(*) as count FROM ip_access_rules 
                  WHERE ip_address = :ip AND rule_type = 'blacklist' AND is_active = 1 
                  AND (expires_at IS NULL OR expires_at > NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':ip' => $ip]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Failed to check IP block status: " . $e->getMessage());
        return false;
    }
}

// Helper to get user roles and permissions
function getUserPermissions($userId) {
    global $pdo;
    
    try {
        // Get user roles
        $rolesQuery = "SELECT r.id, r.name FROM roles r
                      INNER JOIN user_roles ur ON r.id = ur.role_id
                      WHERE ur.user_id = :user_id";
        
        $stmt = $pdo->prepare($rolesQuery);
        $stmt->execute([':user_id' => $userId]);
        $roles = $stmt->fetchAll();
        
        // Get permissions from roles
        $permissionsQuery = "SELECT DISTINCT p.id, p.name, p.module, p.action 
                            FROM permissions p
                            INNER JOIN role_permissions rp ON p.id = rp.permission_id
                            WHERE rp.role_id IN (SELECT role_id FROM user_roles WHERE user_id = :user_id)";
        
        $stmt = $pdo->prepare($permissionsQuery);
        $stmt->execute([':user_id' => $userId]);
        $rolePermissions = $stmt->fetchAll();
        
        // Get direct user permissions
        $userPermissionsQuery = "SELECT p.id, p.name, p.module, p.action, up.is_granted
                                FROM permissions p
                                INNER JOIN user_permissions up ON p.id = up.permission_id
                                WHERE up.user_id = :user_id";
        
        $stmt = $pdo->prepare($userPermissionsQuery);
        $stmt->execute([':user_id' => $userId]);
        $directPermissions = $stmt->fetchAll();
        
        // Merge and process permissions
        $permissions = [];
        foreach ($rolePermissions as $perm) {
            $permissions[$perm['name']] = [
                'id' => $perm['id'],
                'name' => $perm['name'],
                'module' => $perm['module'],
                'action' => $perm['action'],
                'granted' => true,
                'source' => 'role'
            ];
        }
        
        // Override with direct permissions
        foreach ($directPermissions as $perm) {
            $permissions[$perm['name']] = [
                'id' => $perm['id'],
                'name' => $perm['name'],
                'module' => $perm['module'],
                'action' => $perm['action'],
                'granted' => (bool)$perm['is_granted'],
                'source' => 'direct'
            ];
        }
        
        return [
            'roles' => array_column($roles, 'name'),
            'permissions' => array_values($permissions)
        ];
    } catch (PDOException $e) {
        error_log("Failed to get user permissions: " . $e->getMessage());
        return [
            'roles' => [],
            'permissions' => []
        ];
    }
}

// Fix for CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and parameters
$method = $_SERVER['REQUEST_METHOD'];
$params = getRequestParams();
$action = $params['action'] ?? $_GET['action'] ?? '';

// Available endpoints
$availableEndpoints = [
    'GET' => [
        'health',
        'validate-token',
        'user-profile',
        'logout'
    ],
    'POST' => [
        'login',
        'register',
        'verify-email',
        'forgot-password',
        'reset-password',
        'refresh-token'
    ]
];

// If debug flag is present
if ((isset($_GET['debug']) && $_GET['debug'] === '1') || (isset($params['debug']) && $params['debug'] === '1')) {
    jsonResponse(true, 'Debug information', [
        'method' => $method,
        'action' => $action,
        'server' => [
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ],
        'params' => $params
    ]);
}

// Main API routing
switch ($action) {
    // ================ LOGIN ================
        case 'login':
        if ($method !== 'POST') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        $username = trim($params['username'] ?? '');
        $password = $params['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            jsonResponse(false, 'Username and password are required', null, 400, 'MISSING_CREDENTIALS');
        }
        
        // Check if IP is blocked
        if (isIpBlocked()) {
            logSecurityEvent(null, 'IP_BLOCKED_ATTEMPT', ['username' => $username]);
            jsonResponse(false, 'Access denied', null, 403, 'IP_BLOCKED');
        }
        
        try {
            // Check if user exists
            $query = "SELECT u.*, 
          (SELECT COUNT(*) FROM login_attempts la 
           WHERE la.username = u.username AND la.success = 0 
           AND la.attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as recent_failed_attempts
          FROM users u 
          WHERE (u.username = :username OR u.email = :email_param) 
          AND u.deleted_at IS NULL";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':username' => $username,
    ':email_param' => $username  // Same value, different parameter name
]);
            $user = $stmt->fetch();
            
            if (!$user) {
                logLoginAttempt($username, false, 'USER_NOT_FOUND');
                jsonResponse(false, 'Invalid credentials', null, 401, 'INVALID_CREDENTIALS');
            }
            
            // Check if account is locked
            if ($user['is_locked'] == 1) {
                logLoginAttempt($username, false, 'ACCOUNT_LOCKED');
                logSecurityEvent($user['id'], 'LOGIN_ATTEMPT_LOCKED_ACCOUNT');
                jsonResponse(false, 'Account is locked. Please contact administrator or try again later.', null, 423, 'ACCOUNT_LOCKED');
            }
            
            // Check if account is active
            if ($user['is_active'] == 0) {
                logLoginAttempt($username, false, 'ACCOUNT_INACTIVE');
                jsonResponse(false, 'Account is deactivated', null, 403, 'ACCOUNT_INACTIVE');
            }
            
            // maximum user attempts to login  
            $stmt = $pdo->prepare("SELECT setting_value FROM security_settings WHERE setting_key = 'max_login_attempts'");
            $stmt->execute();
            $MAX_LOGIN_ATTEMPTS = (int) $stmt->fetchColumn();

            // Check failed login attempts
            if ($user['failed_login_attempts'] >= $MAX_LOGIN_ATTEMPTS) {
                // Lock the account
                $lockQuery = "UPDATE users SET is_locked = 1, updated_at = NOW() WHERE id = :user_id";
                $lockStmt = $pdo->prepare($lockQuery);
                $lockStmt->execute([':user_id' => $user['id']]);
                
                logLoginAttempt($username, false, 'MAX_ATTEMPTS_EXCEEDED');
                logSecurityEvent($user['id'], 'ACCOUNT_LOCKED_AUTO');
                
                jsonResponse(false, 'Too many failed attempts. Account has been locked.', null, 429, 'MAX_ATTEMPTS_EXCEEDED');
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                // Increment failed login attempts
                $failedAttempts = $user['failed_login_attempts'] + 1;
                $updateQuery = "UPDATE users SET failed_login_attempts = :attempts, updated_at = NOW() WHERE id = :user_id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([
                    ':attempts' => $failedAttempts,
                    ':user_id' => $user['id']
                ]);
                
                logLoginAttempt($username, false, 'INVALID_PASSWORD');
                logSecurityEvent($user['id'], 'LOGIN_FAILED', ['attempts' => $failedAttempts]);
                
                $remainingAttempts = $MAX_LOGIN_ATTEMPTS - $failedAttempts;
                if ($remainingAttempts > 0) {
                    jsonResponse(false, "Invalid credentials. {$remainingAttempts} attempts remaining.", null, 401, 'INVALID_CREDENTIALS');
                } else {
                    jsonResponse(false, 'Too many failed attempts. Account has been locked.', null, 429, 'MAX_ATTEMPTS_EXCEEDED');
                }
            }
            
            // Check if password needs to be changed
            if ($user['must_change_password'] == 1) {
                logLoginAttempt($username, true, 'PASSWORD_CHANGE_REQUIRED');
                jsonResponse(true, 'Login successful but password change required', [
                    'requires_password_change' => true,
                    'user_id' => $user['id'],
                    'username' => $user['username']
                ], 200, 'PASSWORD_CHANGE_REQUIRED');
            }
            
            // Login successful - reset failed attempts
            $resetQuery = "UPDATE users SET 
                          failed_login_attempts = 0,
                          last_login_at = current_login_at,
                          last_login_ip = current_login_ip,
                          current_login_at = NOW(),
                          current_login_ip = :ip_address,
                          updated_at = NOW()
                          WHERE id = :user_id";
            
            $resetStmt = $pdo->prepare($resetQuery);
            $resetStmt->execute([
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_id' => $user['id']
            ]);
            
            // Generate session/token
            $sessionId = generateToken();
            $sessionQuery = "INSERT INTO user_sessions (id, user_id, ip_address, user_agent, payload, last_activity, expires_at) 
                            VALUES (:session_id, :user_id, :ip_address, :user_agent, :payload, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))";
            
            $sessionStmt = $pdo->prepare($sessionQuery);
            $sessionStmt->execute([
                ':session_id' => $sessionId,
                ':user_id' => $user['id'],
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ':payload' => json_encode(['login_time' => date('Y-m-d H:i:s')])
            ]);
            
            // Get user permissions
            $userPermissions = getUserPermissions($user['id']);
            
            // Prepare response data
            $userData = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'phone' => $user['phone'],
                'avatar_url' => $user['avatar_url'],
                'mfa_enabled' => (bool)$user['mfa_enabled'],
                'timezone' => $user['timezone'],
                'locale' => $user['locale'],
                'roles' => $userPermissions['roles'],
                'permissions' => $userPermissions['permissions']
            ];
            
            logLoginAttempt($username, true);
            logSecurityEvent($user['id'], 'LOGIN_SUCCESS');
            
            jsonResponse(true, 'Login successful', [
                'user' => $userData,
                'session' => [
                    'session_id' => $sessionId,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours'))
                ],
                'requires_mfa' => (bool)$user['mfa_enabled']
            ]);
            
        } catch (PDOException $e) {
    // TEMPORARY: Add detailed error information
    error_log("=== LOGIN PDO ERROR ===");
    error_log("Message: " . $e->getMessage());
    error_log("Code: " . $e->getCode());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());
    
    // For now, return the actual error for debugging
    jsonResponse(false, 'Database Error: ' . $e->getMessage(), [
        'error_details' => $e->getMessage(),
        'error_code' => $e->getCode()
    ], 503, 'DB_ERROR');
}
        break;
    
    // ================ REGISTER/SIGNUP ================
    case 'register':
    case 'signup':
    // Handle resend verification
if (!empty($params['resend']) && $params['resend'] === 'true') {

    $email = trim($params['email'] ?? '');

    if (empty($email)) {
        jsonResponse(false, 'Email is required', null, 400, 'EMAIL_REQUIRED');
    }

    $stmt = $pdo->prepare("
        SELECT id, email_verified_at 
        FROM users 
        WHERE email = :email 
        AND deleted_at IS NULL
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(false, 'User not found', null, 404, 'USER_NOT_FOUND');
    }

    if ($user['email_verified_at'] !== null) {
        jsonResponse(false, 'Email already verified', null, 409, 'EMAIL_ALREADY_VERIFIED');
    }

    $token = generateToken();
    $stmt = $pdo->prepare("SELECT NOW() + INTERVAL 24 HOUR AS expires_at");
            $stmt->execute();
            $expires = $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (user_id, token, email, expires_at, created_at)
        VALUES (:user_id, :token, :email, :expires_at, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user['id'],
        ':token' => $token,
        ':email' => $email,
        ':expires_at' => $expires
    ]);

    jsonResponse(true, 'Verification email resent', [
        'email' => $email,
        'expires_at' => $expires
    ]);
}



        if ($method !== 'POST') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        // Get and validate input
        $username = trim($params['username'] ?? '');
        $email = trim($params['email'] ?? '');
        $password = $params['password'] ?? '';
        $confirmPassword = $params['confirm_password'] ?? '';
        $firstName = trim($params['first_name'] ?? '');
        $lastName = trim($params['last_name'] ?? '');
        $phone = trim($params['phone'] ?? '');
        
        // Validation
        $errors = [];
        
        // Username validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must not exceed 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, dots, underscores, and hyphens';
        }
        
        // Email validation
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // Password validation
        if (empty($password)) {
            $errors[] = 'Password is required';
        } else {
            $passwordErrors = validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                $errors = array_merge($errors, $passwordErrors);
            }
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        // First name validation
        if (!empty($firstName) && strlen($firstName) > 50) {
            $errors[] = 'First name must not exceed 50 characters';
        }
        
        // Last name validation
        if (!empty($lastName) && strlen($lastName) > 50) {
            $errors[] = 'Last name must not exceed 50 characters';
        }
        
        // Phone validation
        if (!empty($phone) && !preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $phone)) {
            $errors[] = 'Invalid phone number format';
        }
        
        // Return validation errors
        if (!empty($errors)) {
            jsonResponse(false, 'Validation failed', ['errors' => $errors], 400, 'VALIDATION_ERROR');
        }
        
        try {
            // Check if username already exists
            $checkUsernameQuery = "SELECT id FROM users WHERE username = :username AND deleted_at IS NULL";
            $stmt = $pdo->prepare($checkUsernameQuery);
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Username already exists', null, 409, 'USERNAME_EXISTS');
            }
            
            // Check if email already exists
            $checkEmailQuery = "
    SELECT id, email_verified_at
    FROM users
    WHERE email = :email
    AND deleted_at IS NULL
";
$stmt = $pdo->prepare($checkEmailQuery);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['email_verified_at'] !== null) {
        jsonResponse(false, 'Email already verified', null, 409, 'EMAIL_VERIFIED_EXISTS');
    }
    jsonResponse(false, 'Email already registered', null, 409, 'EMAIL_EXISTS');
}
            
            // Check if email is verified in another account
            $checkVerifiedEmailQuery = "SELECT id FROM users WHERE email = :email AND email_verified_at IS NOT NULL AND deleted_at IS NULL";
            $stmt = $pdo->prepare($checkVerifiedEmailQuery);
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Email already verified with another account', null, 409, 'EMAIL_VERIFIED_EXISTS');
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Hash password
            $passwordHash = hashPassword($password);
            
            // Insert user
            $insertUserQuery = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, timezone, locale, created_at, updated_at) 
                               VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone, :timezone, :locale, NOW(), NOW())";
            
            $stmt = $pdo->prepare($insertUserQuery);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':phone' => $phone,
                ':timezone' => $params['timezone'] ?? 'UTC',
                ':locale' => $params['locale'] ?? 'en_US'
            ]);
            
            $userId = $pdo->lastInsertId();
            
            // Assign default user role
            $roleQuery = "SELECT id FROM roles WHERE name = 'user' LIMIT 1";
            $stmt = $pdo->prepare($roleQuery);
            $stmt->execute();
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$role) {
                throw new Exception('Default role "user" not found');
            }

            
            if ($role) {
                $assignRoleQuery = "INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (:user_id, :role_id, NOW())";
                $stmt = $pdo->prepare($assignRoleQuery);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':role_id' => $role['id']
                ]);
            }
            
            // Generate email verification token
            $verificationToken = generateToken();
            $stmt = $pdo->prepare("SELECT NOW() + INTERVAL 24 HOUR AS expires_at");
            $stmt->execute();
            $verificationExpiry = $stmt->fetchColumn();

            $verificationQuery = "INSERT INTO email_verification_tokens (user_id, token, email, expires_at, created_at) 
                                 VALUES (:user_id, :token, :email, NOW() + INTERVAL 24 HOUR, NOW())";
            $stmt = $pdo->prepare($verificationQuery);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $verificationToken,
                ':email' => $email
            
            ]);

            // In the register case, AFTER creating the verification token, add:
            error_log("=== DEBUG: Before email sending ===");
            error_log("User ID: $userId");
            error_log("Email: $email");
            error_log("Token: $verificationToken");

// Check if email file exists
$emailFile = __DIR__ . '/../config/email.php';
error_log("Email file exists: " . (file_exists($emailFile) ? 'YES' : 'NO'));

// Try to include and send
if (file_exists($emailFile)) {
    require_once $emailFile;
    
    if (class_exists('EmailSender')) {
        error_log("EmailSender class found");
        
        $emailSender = new EmailSender();
        $userName = trim($firstName . ' ' . $lastName);
        if (empty($userName)) {
            $userName = $username;
        }
        
        error_log("Attempting to send email to: $email");
        
        try {
            $emailSent = $emailSender->sendVerificationEmail(
                $email,
                $userName,
                $verificationToken,
                $userId
            );
            
            error_log("Email send result: " . ($emailSent ? 'SUCCESS' : 'FAILED'));
            
            // Add to response
            $responseData['email_sent'] = $emailSent;
            
        } catch (Exception $e) {
            error_log("Email sending exception: " . $e->getMessage());
            $responseData['email_error'] = $e->getMessage();
        }
    } else {
        error_log("EmailSender class NOT found");
    }
} else {
    error_log("Email config file not found at: $emailFile");
}



error_log("=== DEBUG: After email sending ===");
// Log email result
error_log("Verification email sent to $email: " . ($emailSent ? 'SUCCESS' : 'FAILED'));


            // Generate welcome session token (for auto-login after verification)
            $welcomeToken = generateToken();
            
            $settings = [
    ['email_notifications', 'true', 'boolean'],
    ['theme', 'light', 'string'],
    ['items_per_page', '10', 'integer']
];

$settingsQuery = "
    INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
    VALUES (:user_id, :key, :value, :type)
";

$stmt = $pdo->prepare($settingsQuery);

foreach ($settings as [$key, $value, $type]) {
    $stmt->execute([
        ':user_id' => $userId,
        ':key' => $key,
        ':value' => $value,
        ':type' => $type
    ]);
}



            
            // Commit transaction
            $pdo->commit();
            
            // Log security event
            logSecurityEvent($userId, 'USER_REGISTERED', [
                'username' => $username,
                'email' => $email,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            // Prepare verification email data (in production, send actual email)
            $verificationLink = "http://localhost/security_template/api/auth.php?action=verify-email&token=" . urlencode($verificationToken);
            
            // Prepare response data
$responseData = [
    'user_id' => $userId,
    'username' => $username,
    'email' => $email,
    'verification_required' => true,
    'verification_token' => $verificationToken,
    'verification_expiry' => $verificationExpiry,
    'welcome_token' => $welcomeToken,
];

// Add email status if we tried to send
if (isset($emailSent)) {
    $responseData['email_sent'] = $emailSent;
    $message = $emailSent 
        ? 'A verification email has been sent to your email address.'
        : 'Registration successful but email could not be sent. Please contact support.';
} else {
    $responseData['email_sent'] = false;
    $message = 'Registration successful. Email service not configured.';
}

jsonResponse(true, 'Registration successful. Please check your email to verify your account.', 
            $responseData, 201);
            
        }catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(false, $e->getMessage(), null, 500, 'DEBUG_ERROR');
        }
        break;
        
    
   case 'verify-email':

    if ($method !== 'GET') {
        jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
    }

    $token = trim($_GET['token'] ?? '');

    if ($token === '') {
        header('Location: ../verification_error.php');
        exit;
    }

    try {
        // Fetch token + user
        $stmt = $pdo->prepare("
            SELECT 
                evt.id AS token_id,
                evt.expires_at,
                evt.verified_at AS token_verified_at,
                u.id AS user_id,
                u.username,
                u.email,
                u.email_verified_at
            FROM email_verification_tokens evt
            JOIN users u ON u.id = evt.user_id
            WHERE evt.token = :token
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            header('Location: ../verification_success.php');
        }
        // ❌ Token does not exist
        else if (!$row) {
            header('Location: ../verification_error.php');
            exit;
        }

        // ✅ Already verified (THIS IS YOUR MAIN CASE)
        if (!empty($row['email_verified_at'])) {
            $_SESSION['verification_result'] = [
                'success' => true,
                'message' => 'Email already verified',
                'username' => $row['username'],
                'email' => $row['email'],
                'verified_at' => $row['email_verified_at']
            ];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $row['user_id'];

            header('Location: ../verification_success.php');
            exit;
        }

        // ❌ Token expired
       // Fetch DB current time
$now = $pdo->query("SELECT NOW()")->fetchColumn();

if (strtotime($row['expires_at']) < strtotime($now)) {
    header('Location: ../verification_error.php');
    exit;
}

        // ✅ FIRST-TIME VERIFICATION
        $pdo->beginTransaction();

        $pdo->prepare("
            UPDATE users 
            SET email_verified_at = NOW(), updated_at = NOW()
            WHERE id = :id
        ")->execute([':id' => $row['user_id']]);

        $pdo->prepare("
            UPDATE email_verification_tokens 
            SET verified_at = NOW()
            WHERE id = :id
        ")->execute([':id' => $row['token_id']]);

        $pdo->commit();

        session_start();
        $_SESSION['verification_result'] = [
            'success' => true,
            'message' => 'Email verified successfully',
            'username' => $row['username'],
            'email' => $row['email'],
            'verified_at' => date('Y-m-d H:i:s')
        ];
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $row['user_id'];

        header('Location: ../verification_success.php');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: ../verification_error.php');
        exit;
    }

    
   // ================ FORGOT PASSWORD ================
case 'forgot-password':
    if ($method !== 'POST') {
        jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        exit; 
    }
    
    $email = trim($params['email'] ?? '');
    if (empty($email) || !isValidEmail($email)) {
        jsonResponse(false, 'Valid email is required', null, 400, 'INVALID_EMAIL');
        exit;
    }

    try {
        // Find user
        $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, is_active 
                               FROM users 
                               WHERE email = :email AND is_active = 1 AND deleted_at IS NULL");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(true, 'If your email is registered, you will receive a password reset link.', null, 200);
            exit;
        }

        // Check existing token
        $stmt = $pdo->prepare("SELECT id FROM password_reset_tokens 
                               WHERE user_id = :user_id AND expires_at > NOW() AND used_at IS NULL");
        $stmt->execute([':user_id' => $user['id']]);
        if ($stmt->fetch()) {
            jsonResponse(true, 'A password reset link has already been sent. Please check your email.', null, 200);
            exit;
        }

        // Generate token
        $resetToken = generateToken(); // 64-character token
        //$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insert token
        try {
            $stmt = $pdo->prepare("
    INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
    VALUES (:user_id, :token, NOW() + INTERVAL 1 HOUR, NOW())
");
            $stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $resetToken
            ]);
        } catch (PDOException $e) {
            error_log("Token insert failed: " . $e->getMessage());
            jsonResponse(false, 'Failed to generate reset token', null, 500, 'TOKEN_INSERT_FAILED');
            exit;
        }

        // Send email
        require_once __DIR__ . '/../config/email.php';
        $emailSender = new EmailSender();
        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        if (empty($userName)) $userName = $user['username'];

        try {
            $emailSent = $emailSender->sendPasswordResetEmail($user['email'], $userName, $resetToken, $user['id']);
        } catch (Exception $ex) {
            error_log("Password reset email failed: " . $ex->getMessage());
            jsonResponse(false, 'Failed to send reset email', null, 500, 'EMAIL_FAILED');
            exit;
        }

        jsonResponse(true, 'Password reset link sent to your email');
        exit;

    } catch (PDOException $e) {
        error_log("Forgot password error: " . $e->getMessage());
        jsonResponse(false, 'Failed to process request', null, 500, 'RESET_REQUEST_FAILED');
        exit;
    }break;


    
    // ================ RESET PASSWORD ================
    case 'reset-password':
        if ($method !== 'POST') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        $token = trim($params['token'] ?? '');
        $newPassword = $params['new_password'] ?? '';
        $confirmPassword = $params['confirm_password'] ?? '';
        
        // Validate input
        if (empty($token)) {
            jsonResponse(false, 'Reset token is required', null, 400, 'MISSING_TOKEN');
        }
        
        if (empty($newPassword)) {
            jsonResponse(false, 'New password is required', null, 400, 'MISSING_PASSWORD');
        }
        
        if ($newPassword !== $confirmPassword) {
            jsonResponse(false, 'Passwords do not match', null, 400, 'PASSWORD_MISMATCH');
        }
        
        // Validate password strength
        $passwordErrors = validatePasswordStrength($newPassword);
        if (!empty($passwordErrors)) {
            jsonResponse(false, 'Password validation failed', ['errors' => $passwordErrors], 400, 'WEAK_PASSWORD');
        }
        
        try {
            // Find valid reset token
            $query = "SELECT prt.*, u.id as user_id, u.username, u.email 
                      FROM password_reset_tokens prt
                      INNER JOIN users u ON prt.user_id = u.id
                      WHERE prt.token = :token AND prt.expires_at > NOW() AND prt.used_at IS NULL
                      AND u.is_active = 1 AND u.deleted_at IS NULL";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([':token' => $token]);
            $resetToken = $stmt->fetch();
            
            if (!$resetToken) {
                jsonResponse(false, 'Invalid or expired reset token', null, 400, 'INVALID_TOKEN');
            }
            
            // Check password history (prevent reuse)
            $passwordHistoryQuery = "SELECT password_hash FROM password_history 
                                    WHERE user_id = :user_id 
                                    ORDER BY changed_at DESC 
                                    LIMIT 5";
            
            $stmt = $pdo->prepare($passwordHistoryQuery);
            $stmt->execute([':user_id' => $resetToken['user_id']]);
            $oldPasswords = $stmt->fetchAll();
            
            $newPasswordHash = hashPassword($newPassword);
            
            foreach ($oldPasswords as $oldPassword) {
                if (password_verify($newPassword, $oldPassword['password_hash'])) {
                    jsonResponse(false, 'New password cannot be the same as previous passwords', null, 400, 'PASSWORD_REUSE');
                }
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update user password
            $updateQuery = "UPDATE users SET 
                           password_hash = :password_hash,
                           password_changed_at = NOW(),
                           must_change_password = 0,
                           failed_login_attempts = 0,
                           is_locked = 0,
                           updated_at = NOW()
                           WHERE id = :user_id";
            
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([
                ':password_hash' => $newPasswordHash,
                ':user_id' => $resetToken['user_id']
            ]);
            
            // Log password in history
            $historyQuery = "INSERT INTO password_history (user_id, password_hash, changed_at) 
                            VALUES (:user_id, :password_hash, NOW())";
            $stmt = $pdo->prepare($historyQuery);
            $stmt->execute([
                ':user_id' => $resetToken['user_id'],
                ':password_hash' => $newPasswordHash
            ]);
            
            // Mark reset token as used
            $tokenQuery = "UPDATE password_reset_tokens SET used_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($tokenQuery);
            $stmt->execute([':id' => $resetToken['id']]);
            
            // Commit transaction
            $pdo->commit();
            
            // Log security event
            logSecurityEvent($resetToken['user_id'], 'PASSWORD_RESET_SUCCESSFUL');
            
            jsonResponse(true, 'Password reset successful', [
                'user_id' => $resetToken['user_id'],
                'username' => $resetToken['username'],
                'email' => $resetToken['email'],
                'password_changed_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (PDOException $e) {
            // Rollback on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("Reset password error: " . $e->getMessage());
            jsonResponse(false, 'Failed to reset password', null, 500, 'RESET_FAILED');
        }
        break;
    
    // ================ LOGOUT ================
    case 'logout':
        if ($method !== 'POST') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        $sessionId = trim($params['session_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        
        if (empty($sessionId)) {
            jsonResponse(false, 'Session ID is required', null, 400, 'MISSING_SESSION');
        }
        
        try {
            // Clean up session
            $query = "DELETE FROM user_sessions WHERE id = :session_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':session_id' => $sessionId]);
            
            // If we have user ID from params, log the event
            $userId = $params['user_id'] ?? null;
            if ($userId) {
                logSecurityEvent($userId, 'LOGOUT');
            }
            
            jsonResponse(true, 'Logout successful');
            
        } catch (PDOException $e) {
            error_log("Logout error: " . $e->getMessage());
            jsonResponse(false, 'Logout failed', null, 500, 'LOGOUT_FAILED');
        }
        break;
    
    // ================ HEALTH CHECK ================
    case 'health':
        if ($method !== 'GET') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        try {
            // Check database connection
            $stmt = $pdo->query("SELECT 1 as status");
            $dbStatus = $stmt->fetch()['status'] == 1;
            
            // Get system stats
            $userCount = $pdo->query("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")->fetch()['count'];
            $activeSessions = $pdo->query("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at > NOW()")->fetch()['count'];
            
            jsonResponse(true, 'System is healthy', [
                'status' => 'OK',
                'timestamp' => date('Y-m-d H:i:s'),
                'services' => [
                    'database' => $dbStatus ? 'OK' : 'FAILED',
                    'api' => 'OK'
                ],
                'metrics' => [
                    'total_users' => (int)$userCount,
                    'active_sessions' => (int)$activeSessions
                ]
            ]);
            
        } catch (PDOException $e) {
            jsonResponse(false, 'System health check failed', [
                'status' => 'DEGRADED',
                'database' => 'FAILED',
                'error' => $e->getMessage()
            ], 503, 'HEALTH_CHECK_FAILED');
        }
        break;
    
    // ================ DEFAULT ================
    default:
        jsonResponse(false, 'Invalid or missing action parameter', [
            'available_actions' => $availableEndpoints,
            'current_method' => $method
        ], 400, 'INVALID_ACTION');
        break;
}
?>