<?php
// api/admin.php.php - admin.php User Management API

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
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
    
    // Get password requirements from settings
    global $pdo;
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM security_settings WHERE setting_key LIKE 'password.%'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $minLength = isset($settings['password.min_length']) ? (int)$settings['password.min_length'] : 8;
    $requireUppercase = isset($settings['password.require_uppercase']) ? $settings['password.require_uppercase'] === 'true' : true;
    $requireLowercase = isset($settings['password.require_lowercase']) ? $settings['password.require_lowercase'] === 'true' : true;
    $requireNumbers = isset($settings['password.require_numbers']) ? $settings['password.require_numbers'] === 'true' : true;
    $requireSpecialChars = isset($settings['password.require_special_chars']) ? $settings['password.require_special_chars'] === 'true' : true;
    
    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters long";
    }
    if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if ($requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $password)) {
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
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
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

// Helper to validate admin access
function validateAdminAccess($userId) {
    global $pdo;
    
    try {
        // Check if user has admin.php or super_admin.php role
        $query = "SELECT COUNT(*) as count FROM user_roles ur 
                  INNER JOIN roles r ON ur.role_id = r.id 
                  WHERE ur.user_id = :user_id AND r.name IN ('admin.php', 'super_admin.php')";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Failed to validate admin.php access: " . $e->getMessage());
        return false;
    }
}

// Helper to validate session
function validateSession() {
    $headers = getallheaders();
    $sessionId = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    if (!$sessionId) {
        jsonResponse(false, 'Session ID is required', null, 401, 'MISSING_SESSION');
    }
    
    // Remove 'Bearer ' prefix if present
    $sessionId = str_replace('Bearer ', '', $sessionId);
    
    global $pdo;
    
    try {
        $query = "SELECT us.*, u.username, u.email, u.is_active, u.is_locked 
                  FROM user_sessions us
                  INNER JOIN users u ON us.user_id = u.id
                  WHERE us.id = :session_id AND us.expires_at > NOW() 
                  AND u.deleted_at IS NULL";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':session_id' => $sessionId]);
        $session = $stmt->fetch();
        
        if (!$session) {
            jsonResponse(false, 'Invalid or expired session', null, 401, 'INVALID_SESSION');
        }
        
        if ($session['is_locked'] == 1) {
            jsonResponse(false, 'Account is locked', null, 403, 'ACCOUNT_LOCKED');
        }
        
        if ($session['is_active'] == 0) {
            jsonResponse(false, 'Account is inactive', null, 403, 'ACCOUNT_INACTIVE');
        }
        
        // Update session last activity
        $updateQuery = "UPDATE user_sessions SET last_activity = NOW() WHERE id = :session_id";
        $pdo->prepare($updateQuery)->execute([':session_id' => $sessionId]);
        
        return [
            'user_id' => $session['user_id'],
            'username' => $session['username'],
            'email' => $session['email']
        ];
    } catch (PDOException $e) {
        error_log("Session validation error: " . $e->getMessage());
        jsonResponse(false, 'Session validation failed', null, 500, 'SESSION_ERROR');
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
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Get action from path (e.g., /api/admin.php/users -> 'users')
$action = $pathSegments[2] ?? '';

// Validate admin session
$userInfo = validateSession();
if (!validateAdminAccess($userInfo['user_id'])) {
    jsonResponse(false, 'Insufficient permissions', null, 403, 'INSUFFICIENT_PERMISSIONS');
}

// Main API routing based on action and method
switch ($action) {
    // ================ USERS ================
    case 'users':
        switch ($method) {
            // GET /api/admin.php/users - List all users
            case 'GET':
                // Get query parameters
                $status = $_GET['status'] ?? 'all';
                $role = $_GET['role'] ?? 'all';
                $search = $_GET['search'] ?? '';
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;
                
                try {
                    // Build base query
                    $query = "SELECT SQL_CALC_FOUND_ROWS 
                              u.id, u.username, u.email, u.email_verified_at,
                              u.first_name, u.last_name, u.phone, u.avatar_url,
                              u.timezone, u.locale, u.is_active, u.is_locked,
                              u.mfa_enabled, u.last_login_at, u.current_login_at,
                              u.last_login_ip, u.current_login_ip, u.failed_login_attempts,
                              u.password_changed_at, u.must_change_password, u.date_of_birth,
                              u.created_at, u.updated_at,
                              GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') as roles
                              FROM users u
                              LEFT JOIN user_roles ur ON u.id = ur.user_id
                              LEFT JOIN roles r ON ur.role_id = r.id
                              WHERE u.deleted_at IS NULL";
                    
                    $whereParams = [];
                    
                    // Apply status filter
                    if ($status !== 'all') {
                        if ($status === 'active') {
                            $query .= " AND u.is_active = 1 AND u.is_locked = 0";
                        } elseif ($status === 'inactive') {
                            $query .= " AND u.is_active = 0";
                        } elseif ($status === 'locked') {
                            $query .= " AND u.is_locked = 1";
                        }
                    }
                    
                    // Apply role filter
                    if ($role !== 'all') {
                        $query .= " AND r.name = :role";
                        $whereParams[':role'] = $role;
                    }
                    
                    // Apply search filter
                    if (!empty($search)) {
                        $query .= " AND (u.username LIKE :search OR u.email LIKE :search 
                                  OR u.first_name LIKE :search OR u.last_name LIKE :search)";
                        $whereParams[':search'] = "%$search%";
                    }
                    
                    // Group by user id and add ordering/pagination
                    $query .= " GROUP BY u.id 
                                ORDER BY u.created_at DESC 
                                LIMIT :limit OFFSET :offset";
                    
                    // Prepare and execute query
                    $stmt = $pdo->prepare($query);
                    
                    // Bind parameters
                    foreach ($whereParams as $key => $value) {
                        $stmt->bindValue($key, $value);
                    }
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                    
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get total count
                    $totalResult = $pdo->query("SELECT FOUND_ROWS() as total")->fetch();
                    $totalUsers = $totalResult['total'];
                    
                    // Format response
                    $formattedUsers = [];
                    foreach ($users as $user) {
                        $formattedUsers[] = [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'firstName' => $user['first_name'],
                            'lastName' => $user['last_name'],
                            'fullName' => trim($user['first_name'] . ' ' . $user['last_name']),
                            'phone' => $user['phone'],
                            'avatarUrl' => $user['avatar_url'],
                            'timezone' => $user['timezone'],
                            'locale' => $user['locale'],
                            'isActive' => (bool)$user['is_active'],
                            'isLocked' => (bool)$user['is_locked'],
                            'status' => $user['is_locked'] ? 'locked' : ($user['is_active'] ? 'active' : 'inactive'),
                            'emailVerified' => !empty($user['email_verified_at']),
                            'mfaEnabled' => (bool)$user['mfa_enabled'],
                            'lastLogin' => $user['last_login_at'],
                            'currentLogin' => $user['current_login_at'],
                            'failedLoginAttempts' => $user['failed_login_attempts'],
                            'passwordChangedAt' => $user['password_changed_at'],
                            'mustChangePassword' => (bool)$user['must_change_password'],
                            'dateOfBirth' => $user['date_of_birth'],
                            'createdAt' => $user['created_at'],
                            'updatedAt' => $user['updated_at'],
                            'role' => $user['roles'] ? explode(', ', $user['roles'])[0] : 'user',
                            'roles' => $user['roles'] ? explode(', ', $user['roles']) : []
                        ];
                    }
                    
                    // Get statistics
                    $statsQuery = "SELECT 
                        COUNT(*) as total_users,
                        SUM(is_active = 1 AND is_locked = 0) as active_users,
                        SUM(is_locked = 1) as locked_users,
                        SUM(email_verified_at IS NOT NULL) as verified_users
                        FROM users WHERE deleted_at IS NULL";
                    
                    $stats = $pdo->query($statsQuery)->fetch(PDO::FETCH_ASSOC);
                    
                    jsonResponse(true, 'Users retrieved successfully', [
                        'users' => $formattedUsers,
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => $totalUsers,
                            'totalPages' => ceil($totalUsers / $limit)
                        ],
                        'stats' => $stats
                    ]);
                    
                } catch (PDOException $e) {
                    error_log("Get users error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to retrieve users', null, 500, 'DB_ERROR');
                }
                break;
                
            // POST /api/admin.php/users - Create new user
            case 'POST':
                try {
                    // Validate required fields
                    $requiredFields = ['username', 'email', 'firstName', 'lastName', 'role'];
                    foreach ($requiredFields as $field) {
                        if (empty($params[$field])) {
                            jsonResponse(false, "Field '{$field}' is required", null, 400, 'MISSING_FIELD');
                        }
                    }
                    
                    // Validate email
                    if (!isValidEmail($params['email'])) {
                        jsonResponse(false, 'Invalid email format', null, 400, 'INVALID_EMAIL');
                    }
                    
                    // Check if username exists
                    $checkUsername = "SELECT id FROM users WHERE username = :username AND deleted_at IS NULL";
                    $stmt = $pdo->prepare($checkUsername);
                    $stmt->execute([':username' => $params['username']]);
                    if ($stmt->fetch()) {
                        jsonResponse(false, 'Username already exists', null, 409, 'USERNAME_EXISTS');
                    }
                    
                    // Check if email exists
                    $checkEmail = "SELECT id FROM users WHERE email = :email AND deleted_at IS NULL";
                    $stmt = $pdo->prepare($checkEmail);
                    $stmt->execute([':email' => $params['email']]);
                    if ($stmt->fetch()) {
                        jsonResponse(false, 'Email already exists', null, 409, 'EMAIL_EXISTS');
                    }
                    
                    // Validate password if provided
                    $password = $params['password'] ?? null;
                    if ($password) {
                        $passwordErrors = validatePasswordStrength($password);
                        if (!empty($passwordErrors)) {
                            jsonResponse(false, 'Password validation failed', ['errors' => $passwordErrors], 400, 'WEAK_PASSWORD');
                        }
                        
                        if ($password !== ($params['confirmPassword'] ?? '')) {
                            jsonResponse(false, 'Passwords do not match', null, 400, 'PASSWORD_MISMATCH');
                        }
                    } else {
                        // Generate random password if not provided
                        $password = generateToken(12);
                    }
                    
                    // Get role id
                    $roleQuery = "SELECT id FROM roles WHERE name = :role LIMIT 1";
                    $stmt = $pdo->prepare($roleQuery);
                    $stmt->execute([':role' => $params['role']]);
                    $role = $stmt->fetch();
                    
                    if (!$role) {
                        jsonResponse(false, 'Invalid role specified', null, 400, 'INVALID_ROLE');
                    }
                    
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Hash password
                    $passwordHash = hashPassword($password);
                    
                    // Insert user
                    $insertUser = "INSERT INTO users (
                        username, email, password_hash, first_name, last_name,
                        phone, timezone, locale, is_active, is_locked,
                        created_at, updated_at
                    ) VALUES (
                        :username, :email, :password_hash, :first_name, :last_name,
                        :phone, :timezone, :locale, :is_active, :is_locked,
                        NOW(), NOW()
                    )";
                    
                    $stmt = $pdo->prepare($insertUser);
                    $stmt->execute([
                        ':username' => $params['username'],
                        ':email' => $params['email'],
                        ':password_hash' => $passwordHash,
                        ':first_name' => $params['firstName'],
                        ':last_name' => $params['lastName'],
                        ':phone' => $params['phone'] ?? null,
                        ':timezone' => $params['timezone'] ?? 'UTC',
                        ':locale' => $params['locale'] ?? 'en_US',
                        ':is_active' => $params['status'] === 'inactive' ? 0 : 1,
                        ':is_locked' => $params['status'] === 'locked' ? 1 : 0
                    ]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    // Assign role
                    $assignRole = "INSERT INTO user_roles (user_id, role_id, assigned_at, assigned_by) 
                                   VALUES (:user_id, :role_id, NOW(), :assigned_by)";
                    $stmt = $pdo->prepare($assignRole);
                    $stmt->execute([
                        ':user_id' => $userId,
                        ':role_id' => $role['id'],
                        ':assigned_by' => $userInfo['user_id']
                    ]);
                    
                    // Add to password history
                    $historyQuery = "INSERT INTO password_history (user_id, password_hash, changed_at, changed_by) 
                                     VALUES (:user_id, :password_hash, NOW(), :changed_by)";
                    $stmt = $pdo->prepare($historyQuery);
                    $stmt->execute([
                        ':user_id' => $userId,
                        ':password_hash' => $passwordHash,
                        ':changed_by' => $userInfo['user_id']
                    ]);
                    
                    // Add default user settings
                    $defaultSettings = [
                        ['email_notifications', 'true', 'boolean'],
                        ['theme', 'light', 'string'],
                        ['items_per_page', '10', 'integer']
                    ];
                    
                    $settingsQuery = "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type) 
                                     VALUES (:user_id, :key, :value, :type)";
                    $stmt = $pdo->prepare($settingsQuery);
                    
                    foreach ($defaultSettings as [$key, $value, $type]) {
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
                    logSecurityEvent($userInfo['user_id'], 'USER_CREATED_BY_admin.php', [
                        'admin.php_id' => $userInfo['user_id'],
                        'admin.php_username' => $userInfo['username'],
                        'new_user_id' => $userId,
                        'new_user_username' => $params['username'],
                        'new_user_email' => $params['email']
                    ]);
                    
                    // Get created user details
                    $userQuery = "SELECT u.*, r.name as role 
                                  FROM users u
                                  LEFT JOIN user_roles ur ON u.id = ur.user_id
                                  LEFT JOIN roles r ON ur.role_id = r.id
                                  WHERE u.id = :user_id";
                    $stmt = $pdo->prepare($userQuery);
                    $stmt->execute([':user_id' => $userId]);
                    $createdUser = $stmt->fetch();
                    
                    jsonResponse(true, 'User created successfully', [
                        'user' => [
                            'id' => $createdUser['id'],
                            'username' => $createdUser['username'],
                            'email' => $createdUser['email'],
                            'firstName' => $createdUser['first_name'],
                            'lastName' => $createdUser['last_name'],
                            'role' => $createdUser['role'],
                            'status' => $createdUser['is_locked'] ? 'locked' : ($createdUser['is_active'] ? 'active' : 'inactive'),
                            'createdAt' => $createdUser['created_at']
                        ],
                        'generatedPassword' => !isset($params['password']) ? $password : null
                    ], 201);
                    
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log("Create user error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to create user', null, 500, 'DB_ERROR');
                }
                break;
                
            default:
                jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
                break;
        }
        break;
        
    // ================ USER BY ID ================
    case preg_match('/^users\/(\d+)$/', $action, $matches) ? true : false:
        $userId = (int)$matches[1];
        
        switch ($method) {
            // GET /api/admin.php/users/{id} - Get user details
            case 'GET':
                try {
                    $query = "SELECT u.*, 
                              GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') as roles,
                              GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as permissions
                              FROM users u
                              LEFT JOIN user_roles ur ON u.id = ur.user_id
                              LEFT JOIN roles r ON ur.role_id = r.id
                              LEFT JOIN user_permissions up ON u.id = up.user_id AND up.is_granted = 1
                              LEFT JOIN permissions p ON up.permission_id = p.id
                              WHERE u.id = :user_id AND u.deleted_at IS NULL
                              GROUP BY u.id";
                    
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([':user_id' => $userId]);
                    $user = $stmt->fetch();
                    
                    if (!$user) {
                        jsonResponse(false, 'User not found', null, 404, 'USER_NOT_FOUND');
                    }
                    
                    // Get login attempts in last 24 hours
                    $loginAttemptsQuery = "SELECT COUNT(*) as failed_attempts 
                                           FROM login_attempts 
                                           WHERE username = :username 
                                           AND success = 0 
                                           AND attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    $stmt = $pdo->prepare($loginAttemptsQuery);
                    $stmt->execute([':username' => $user['username']]);
                    $loginAttempts = $stmt->fetch();
                    
                    // Get active sessions
                    $sessionsQuery = "SELECT COUNT(*) as active_sessions 
                                      FROM user_sessions 
                                      WHERE user_id = :user_id 
                                      AND expires_at > NOW()";
                    $stmt = $pdo->prepare($sessionsQuery);
                    $stmt->execute([':user_id' => $userId]);
                    $sessions = $stmt->fetch();
                    
                    // Format response
                    $response = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'firstName' => $user['first_name'],
                        'lastName' => $user['last_name'],
                        'fullName' => trim($user['first_name'] . ' ' . $user['last_name']),
                        'phone' => $user['phone'],
                        'avatarUrl' => $user['avatar_url'],
                        'timezone' => $user['timezone'],
                        'locale' => $user['locale'],
                        'isActive' => (bool)$user['is_active'],
                        'isLocked' => (bool)$user['is_locked'],
                        'status' => $user['is_locked'] ? 'locked' : ($user['is_active'] ? 'active' : 'inactive'),
                        'emailVerified' => !empty($user['email_verified_at']),
                        'emailVerifiedAt' => $user['email_verified_at'],
                        'mfaEnabled' => (bool)$user['mfa_enabled'],
                        'lastLoginAt' => $user['last_login_at'],
                        'lastLoginIp' => $user['last_login_ip'],
                        'currentLoginAt' => $user['current_login_at'],
                        'currentLoginIp' => $user['current_login_ip'],
                        'failedLoginAttempts' => $user['failed_login_attempts'],
                        'passwordChangedAt' => $user['password_changed_at'],
                        'mustChangePassword' => (bool)$user['must_change_password'],
                        'dateOfBirth' => $user['date_of_birth'],
                        'createdAt' => $user['created_at'],
                        'updatedAt' => $user['updated_at'],
                        'deletedAt' => $user['deleted_at'],
                        'roles' => $user['roles'] ? explode(', ', $user['roles']) : [],
                        'permissions' => $user['permissions'] ? explode(', ', $user['permissions']) : [],
                        'securityInfo' => [
                            'recentFailedLogins' => (int)$loginAttempts['failed_attempts'],
                            'activeSessions' => (int)$sessions['active_sessions']
                        ]
                    ];
                    
                    jsonResponse(true, 'User details retrieved', $response);
                    
                } catch (PDOException $e) {
                    error_log("Get user details error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to retrieve user details', null, 500, 'DB_ERROR');
                }
                break;
                
            // PUT /api/admin.php/users/{id} - Update user
            case 'PUT':
                try {
                    // Check if user exists
                    $checkUser = "SELECT id, username, email FROM users WHERE id = :user_id AND deleted_at IS NULL";
                    $stmt = $pdo->prepare($checkUser);
                    $stmt->execute([':user_id' => $userId]);
                    $existingUser = $stmt->fetch();
                    
                    if (!$existingUser) {
                        jsonResponse(false, 'User not found', null, 404, 'USER_NOT_FOUND');
                    }
                    
                    // Validate email if changed
                    if (isset($params['email']) && $params['email'] !== $existingUser['email']) {
                        if (!isValidEmail($params['email'])) {
                            jsonResponse(false, 'Invalid email format', null, 400, 'INVALID_EMAIL');
                        }
                        
                        // Check if new email exists
                        $checkEmail = "SELECT id FROM users WHERE email = :email AND id != :user_id AND deleted_at IS NULL";
                        $stmt = $pdo->prepare($checkEmail);
                        $stmt->execute([':email' => $params['email'], ':user_id' => $userId]);
                        if ($stmt->fetch()) {
                            jsonResponse(false, 'Email already exists', null, 409, 'EMAIL_EXISTS');
                        }
                    }
                    
                    // Validate username if changed
                    if (isset($params['username']) && $params['username'] !== $existingUser['username']) {
                        $checkUsername = "SELECT id FROM users WHERE username = :username AND id != :user_id AND deleted_at IS NULL";
                        $stmt = $pdo->prepare($checkUsername);
                        $stmt->execute([':username' => $params['username'], ':user_id' => $userId]);
                        if ($stmt->fetch()) {
                            jsonResponse(false, 'Username already exists', null, 409, 'USERNAME_EXISTS');
                        }
                    }
                    
                    // Validate password if provided
                    if (isset($params['password'])) {
                        $passwordErrors = validatePasswordStrength($params['password']);
                        if (!empty($passwordErrors)) {
                            jsonResponse(false, 'Password validation failed', ['errors' => $passwordErrors], 400, 'WEAK_PASSWORD');
                        }
                        
                        if ($params['password'] !== ($params['confirmPassword'] ?? '')) {
                            jsonResponse(false, 'Passwords do not match', null, 400, 'PASSWORD_MISMATCH');
                        }
                        
                        // Check password history
                        $historyQuery = "SELECT password_hash FROM password_history 
                                        WHERE user_id = :user_id 
                                        ORDER BY changed_at DESC 
                                        LIMIT 5";
                        $stmt = $pdo->prepare($historyQuery);
                        $stmt->execute([':user_id' => $userId]);
                        $oldPasswords = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($oldPasswords as $oldHash) {
                            if (password_verify($params['password'], $oldHash)) {
                                jsonResponse(false, 'New password cannot be the same as previous passwords', null, 400, 'PASSWORD_REUSE');
                            }
                        }
                    }
                    
                    // Get role id if changing role
                    $newRoleId = null;
                    if (isset($params['role'])) {
                        $roleQuery = "SELECT id FROM roles WHERE name = :role LIMIT 1";
                        $stmt = $pdo->prepare($roleQuery);
                        $stmt->execute([':role' => $params['role']]);
                        $role = $stmt->fetch();
                        
                        if (!$role) {
                            jsonResponse(false, 'Invalid role specified', null, 400, 'INVALID_ROLE');
                        }
                        $newRoleId = $role['id'];
                    }
                    
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Build update query
                    $updateFields = [];
                    $updateParams = [':user_id' => $userId];
                    
                    if (isset($params['firstName'])) {
                        $updateFields[] = "first_name = :first_name";
                        $updateParams[':first_name'] = $params['firstName'];
                    }
                    
                    if (isset($params['lastName'])) {
                        $updateFields[] = "last_name = :last_name";
                        $updateParams[':last_name'] = $params['lastName'];
                    }
                    
                    if (isset($params['username'])) {
                        $updateFields[] = "username = :username";
                        $updateParams[':username'] = $params['username'];
                    }
                    
                    if (isset($params['email'])) {
                        $updateFields[] = "email = :email";
                        $updateParams[':email'] = $params['email'];
                    }
                    
                    if (isset($params['phone'])) {
                        $updateFields[] = "phone = :phone";
                        $updateParams[':phone'] = $params['phone'];
                    }
                    
                    if (isset($params['timezone'])) {
                        $updateFields[] = "timezone = :timezone";
                        $updateParams[':timezone'] = $params['timezone'];
                    }
                    
                    if (isset($params['locale'])) {
                        $updateFields[] = "locale = :locale";
                        $updateParams[':locale'] = $params['locale'];
                    }
                    
                    if (isset($params['status'])) {
                        if ($params['status'] === 'locked') {
                            $updateFields[] = "is_locked = 1";
                            $updateFields[] = "is_active = 1";
                        } elseif ($params['status'] === 'inactive') {
                            $updateFields[] = "is_active = 0";
                            $updateFields[] = "is_locked = 0";
                        } else { // active
                            $updateFields[] = "is_active = 1";
                            $updateFields[] = "is_locked = 0";
                            $updateFields[] = "failed_login_attempts = 0";
                        }
                    }
                    
                    // Handle password update
                    if (isset($params['password'])) {
                        $passwordHash = hashPassword($params['password']);
                        $updateFields[] = "password_hash = :password_hash";
                        $updateFields[] = "password_changed_at = NOW()";
                        $updateFields[] = "must_change_password = 0";
                        $updateParams[':password_hash'] = $passwordHash;
                        
                        // Add to password history
                        $historyQuery = "INSERT INTO password_history (user_id, password_hash, changed_at, changed_by) 
                                         VALUES (:user_id, :password_hash, NOW(), :changed_by)";
                        $stmt = $pdo->prepare($historyQuery);
                        $stmt->execute([
                            ':user_id' => $userId,
                            ':password_hash' => $passwordHash,
                            ':changed_by' => $userInfo['user_id']
                        ]);
                    }
                    
                    $updateFields[] = "updated_at = NOW()";
                    
                    // Execute update if there are fields to update
                    if (!empty($updateFields)) {
                        $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
                        $stmt = $pdo->prepare($updateQuery);
                        $stmt->execute($updateParams);
                    }
                    
                    // Update role if changed
                    if ($newRoleId) {
                        // Remove existing roles
                        $deleteRoles = "DELETE FROM user_roles WHERE user_id = :user_id";
                        $stmt = $pdo->prepare($deleteRoles);
                        $stmt->execute([':user_id' => $userId]);
                        
                        // Add new role
                        $addRole = "INSERT INTO user_roles (user_id, role_id, assigned_at, assigned_by) 
                                   VALUES (:user_id, :role_id, NOW(), :assigned_by)";
                        $stmt = $pdo->prepare($addRole);
                        $stmt->execute([
                            ':user_id' => $userId,
                            ':role_id' => $newRoleId,
                            ':assigned_by' => $userInfo['user_id']
                        ]);
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Log security event
                    logSecurityEvent($userInfo['user_id'], 'USER_UPDATED_BY_admin.php', [
                        'admin.php_id' => $userInfo['user_id'],
                        'admin.php_username' => $userInfo['username'],
                        'user_id' => $userId,
                        'updated_fields' => array_keys($params)
                    ]);
                    
                    // Get updated user
                    $userQuery = "SELECT u.*, r.name as role 
                                  FROM users u
                                  LEFT JOIN user_roles ur ON u.id = ur.user_id
                                  LEFT JOIN roles r ON ur.role_id = r.id
                                  WHERE u.id = :user_id";
                    $stmt = $pdo->prepare($userQuery);
                    $stmt->execute([':user_id' => $userId]);
                    $updatedUser = $stmt->fetch();
                    
                    jsonResponse(true, 'User updated successfully', [
                        'user' => [
                            'id' => $updatedUser['id'],
                            'username' => $updatedUser['username'],
                            'email' => $updatedUser['email'],
                            'firstName' => $updatedUser['first_name'],
                            'lastName' => $updatedUser['last_name'],
                            'role' => $updatedUser['role'],
                            'status' => $updatedUser['is_locked'] ? 'locked' : ($updatedUser['is_active'] ? 'active' : 'inactive'),
                            'updatedAt' => $updatedUser['updated_at']
                        ]
                    ]);
                    
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log("Update user error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to update user', null, 500, 'DB_ERROR');
                }
                break;
                
            // DELETE /api/admin.php/users/{id} - Delete user (soft delete)
            case 'DELETE':
                try {
                    // Check if user exists
                    $checkUser = "SELECT id, username FROM users WHERE id = :user_id AND deleted_at IS NULL";
                    $stmt = $pdo->prepare($checkUser);
                    $stmt->execute([':user_id' => $userId]);
                    $user = $stmt->fetch();
                    
                    if (!$user) {
                        jsonResponse(false, 'User not found', null, 404, 'USER_NOT_FOUND');
                    }
                    
                    // Prevent deleting self
                    if ($userId == $userInfo['user_id']) {
                        jsonResponse(false, 'Cannot delete your own account', null, 400, 'SELF_DELETE');
                    }
                    
                    // Soft delete user
                    $deleteQuery = "UPDATE users SET 
                                   deleted_at = NOW(),
                                   is_active = 0,
                                   updated_at = NOW()
                                   WHERE id = :user_id";
                    
                    $stmt = $pdo->prepare($deleteQuery);
                    $stmt->execute([':user_id' => $userId]);
                    
                    // Log security event
                    logSecurityEvent($userInfo['user_id'], 'USER_DELETED_BY_admin.php', [
                        'admin.php_id' => $userInfo['user_id'],
                        'admin.php_username' => $userInfo['username'],
                        'deleted_user_id' => $userId,
                        'deleted_user_username' => $user['username']
                    ]);
                    
                    jsonResponse(true, 'User deleted successfully');
                    
                } catch (PDOException $e) {
                    error_log("Delete user error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to delete user', null, 500, 'DB_ERROR');
                }
                break;
                
            // PATCH /api/admin.php/users/{id}/lock - Lock/Unlock user
            case 'PATCH':
                // Check if action is lock/unlock
                $lockAction = $pathSegments[4] ?? '';
                
                if (!in_array($lockAction, ['lock', 'unlock'])) {
                    jsonResponse(false, 'Invalid action', null, 400, 'INVALID_ACTION');
                }
                
                try {
                    // Check if user exists
                    $checkUser = "SELECT id, username, is_locked FROM users WHERE id = :user_id AND deleted_at IS NULL";
                    $stmt = $pdo->prepare($checkUser);
                    $stmt->execute([':user_id' => $userId]);
                    $user = $stmt->fetch();
                    
                    if (!$user) {
                        jsonResponse(false, 'User not found', null, 404, 'USER_NOT_FOUND');
                    }
                    
                    $lockValue = $lockAction === 'lock' ? 1 : 0;
                    
                    // Check if already in desired state
                    if ($user['is_locked'] == $lockValue) {
                        $message = $lockAction === 'lock' ? 'User is already locked' : 'User is already unlocked';
                        jsonResponse(false, $message, null, 400, 'ALREADY_LOCKED');
                    }
                    
                    // Update lock status
                    $updateQuery = "UPDATE users SET 
                                   is_locked = :is_locked,
                                   failed_login_attempts = 0,
                                   updated_at = NOW()
                                   WHERE id = :user_id";
                    
                    $stmt = $pdo->prepare($updateQuery);
                    $stmt->execute([
                        ':is_locked' => $lockValue,
                        ':user_id' => $userId
                    ]);
                    
                    // Log security event
                    $eventType = $lockAction === 'lock' ? 'USER_LOCKED_BY_admin.php' : 'USER_UNLOCKED_BY_admin.php';
                    logSecurityEvent($userInfo['user_id'], $eventType, [
                        'admin.php_id' => $userInfo['user_id'],
                        'admin.php_username' => $userInfo['username'],
                        'user_id' => $userId,
                        'user_username' => $user['username'],
                        'action' => $lockAction
                    ]);
                    
                    $message = $lockAction === 'lock' ? 'User locked successfully' : 'User unlocked successfully';
                    jsonResponse(true, $message);
                    
                } catch (PDOException $e) {
                    error_log("Lock/Unlock user error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to update user status', null, 500, 'DB_ERROR');
                }
                break;
                
            default:
                jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
                break;
        }
        break;
        
    // ================ STATS ================
    case 'stats':
        if ($method !== 'GET') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        try {
            // Get user statistics
            $userStatsQuery = "SELECT 
                COUNT(*) as total_users,
                SUM(is_active = 1 AND is_locked = 0) as active_users,
                SUM(is_locked = 1) as locked_users,
                SUM(is_active = 0) as inactive_users,
                SUM(email_verified_at IS NOT NULL) as verified_users,
                SUM(mfa_enabled = 1) as mfa_enabled_users
                FROM users WHERE deleted_at IS NULL";
            
            $userStats = $pdo->query($userStatsQuery)->fetch(PDO::FETCH_ASSOC);
            
            // Get recent registrations (last 7 days)
            $recentRegistrationsQuery = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND deleted_at IS NULL
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
            
            $recentRegistrations = $pdo->query($recentRegistrationsQuery)->fetchAll(PDO::FETCH_ASSOC);
            
            // Get failed login attempts in last 24 hours
            $failedLoginsQuery = "SELECT COUNT(*) as count 
                                  FROM login_attempts 
                                  WHERE success = 0 
                                  AND attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $failedLogins = $pdo->query($failedLoginsQuery)->fetch(PDO::FETCH_ASSOC);
            
            // Get active sessions
            $activeSessionsQuery = "SELECT COUNT(*) as count 
                                    FROM user_sessions 
                                    WHERE expires_at > NOW()";
            
            $activeSessions = $pdo->query($activeSessionsQuery)->fetch(PDO::FETCH_ASSOC);
            
            // Get role distribution
            $roleDistributionQuery = "SELECT 
                r.name as role,
                COUNT(DISTINCT u.id) as count
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.deleted_at IS NULL
                GROUP BY r.name
                ORDER BY count DESC";
            
            $roleDistribution = $pdo->query($roleDistributionQuery)->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse(true, 'Statistics retrieved successfully', [
                'userStats' => $userStats,
                'recentRegistrations' => $recentRegistrations,
                'failedLogins24h' => (int)$failedLogins['count'],
                'activeSessions' => (int)$activeSessions['count'],
                'roleDistribution' => $roleDistribution
            ]);
            
        } catch (PDOException $e) {
            error_log("Get stats error: " . $e->getMessage());
            jsonResponse(false, 'Failed to retrieve statistics', null, 500, 'DB_ERROR');
        }
        break;
        
    // ================ ROLES ================
    case 'roles':
        switch ($method) {
            // GET /api/admin.php/roles - Get all roles
            case 'GET':
                try {
                    $query = "SELECT r.*, 
                              COUNT(DISTINCT ur.user_id) as user_count,
                              COUNT(DISTINCT rp.permission_id) as permission_count
                              FROM roles r
                              LEFT JOIN user_roles ur ON r.id = ur.role_id
                              LEFT JOIN role_permissions rp ON r.id = rp.role_id
                              GROUP BY r.id
                              ORDER BY r.name";
                    
                    $roles = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                    
                    jsonResponse(true, 'Roles retrieved successfully', $roles);
                    
                } catch (PDOException $e) {
                    error_log("Get roles error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to retrieve roles', null, 500, 'DB_ERROR');
                }
                break;
                
            default:
                jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
                break;
        }
        break;
        
    // ================ PERMISSIONS ================
    case 'permissions':
        switch ($method) {
            // GET /api/admin.php/permissions - Get all permissions
            case 'GET':
                try {
                    $query = "SELECT * FROM permissions ORDER BY module, action";
                    
                    $permissions = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Group by module for easier consumption
                    $groupedPermissions = [];
                    foreach ($permissions as $perm) {
                        $module = $perm['module'] ?? 'other';
                        if (!isset($groupedPermissions[$module])) {
                            $groupedPermissions[$module] = [];
                        }
                        $groupedPermissions[$module][] = $perm;
                    }
                    
                    jsonResponse(true, 'Permissions retrieved successfully', [
                        'permissions' => $permissions,
                        'groupedPermissions' => $groupedPermissions
                    ]);
                    
                } catch (PDOException $e) {
                    error_log("Get permissions error: " . $e->getMessage());
                    jsonResponse(false, 'Failed to retrieve permissions', null, 500, 'DB_ERROR');
                }
                break;
                
            default:
                jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
                break;
        }
        break;
        
    // ================ AUDIT LOGS ================
    case 'audit-logs':
        if ($method !== 'GET') {
            jsonResponse(false, 'Method not allowed', null, 405, 'METHOD_NOT_ALLOWED');
        }
        
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = ($page - 1) * $limit;
            $eventType = $_GET['event_type'] ?? null;
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            $query = "SELECT SQL_CALC_FOUND_ROWS 
                      al.*, u.username as user_username, u.email as user_email
                      FROM audit_logs al
                      LEFT JOIN users u ON al.user_id = u.id
                      WHERE 1=1";
            
            $params = [];
            
            if ($eventType) {
                $query .= " AND al.event_type = :event_type";
                $params[':event_type'] = $eventType;
            }
            
            if ($userId) {
                $query .= " AND al.user_id = :user_id";
                $params[':user_id'] = $userId;
            }
            
            if ($dateFrom) {
                $query .= " AND DATE(al.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }
            
            if ($dateTo) {
                $query .= " AND DATE(al.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }
            
            $query .= " ORDER BY al.created_at DESC 
                        LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $totalResult = $pdo->query("SELECT FOUND_ROWS() as total")->fetch();
            $totalLogs = $totalResult['total'];
            
            // Parse JSON values
            foreach ($logs as &$log) {
                if (!empty($log['old_values'])) {
                    $log['old_values'] = json_decode($log['old_values'], true);
                }
                if (!empty($log['new_values'])) {
                    $log['new_values'] = json_decode($log['new_values'], true);
                }
            }
            
            jsonResponse(true, 'Audit logs retrieved successfully', [
                'logs' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalLogs,
                    'totalPages' => ceil($totalLogs / $limit)
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("Get audit logs error: " . $e->getMessage());
            jsonResponse(false, 'Failed to retrieve audit logs', null, 500, 'DB_ERROR');
        }
        break;
        
    // ================ DEFAULT ================
    default:
        jsonResponse(false, 'Invalid endpoint', [
            'available_endpoints' => [
                'GET /api/admin.php/users' => 'List users with pagination and filters',
                'POST /api/admin.php/users' => 'Create new user',
                'GET /api/admin.php/users/{id}' => 'Get user details',
                'PUT /api/admin.php/users/{id}' => 'Update user',
                'DELETE /api/admin.php/users/{id}' => 'Delete user',
                'PATCH /api/admin.php/users/{id}/lock' => 'Lock user',
                'PATCH /api/admin.php/users/{id}/unlock' => 'Unlock user',
                'GET /api/admin.php/stats' => 'Get dashboard statistics',
                'GET /api/admin.php/roles' => 'Get all roles',
                'GET /api/admin.php/permissions' => 'Get all permissions',
                'GET /api/admin.php/audit-logs' => 'Get audit logs'
            ]
        ], 400, 'INVALID_ENDPOINT');
        break;
}
?>