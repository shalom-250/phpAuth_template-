<?php
// dashboard.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Optional: Check if email is verified
require_once 'config/db.php';
$stmt = $pdo->prepare("SELECT email_verified_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['email_verified_at']) {
    // Redirect to verification required page
    header('Location: verification_required.php');
    exit;
}

// Rest of your dashboard code...
?>
























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
            --border-color: #e0e0e0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--dark-bg);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .logo h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .logo span {
            color: var(--primary-color);
        }

        .nav-links {
            padding: 20px 0;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #b0b7c3;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-links a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(52, 152, 219, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }

        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }

        .user-details h4 {
            font-size: 0.9rem;
        }

        .user-details p {
            font-size: 0.8rem;
            color: #b0b7c3;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--secondary-color);
            font-size: 1.8rem;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.users {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.active {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .stat-icon.locked {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stat-icon.verified {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        /* Table */
        .table-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: var(--light-bg);
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .user-cell {
            display: flex;
            align-items: center;
        }

        .user-name {
            font-weight: 500;
        }

        .user-email {
            font-size: 0.85rem;
            color: #666;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .status-inactive {
            background-color: rgba(189, 195, 199, 0.2);
            color: #7f8c8d;
        }

        .status-locked {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-btn.edit {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .action-btn.delete {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .action-btn.lock {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .action-btn.view {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        /* Filters */
        .filters {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 0.85rem;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        select, input {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 0.9rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .pagination-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--secondary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 0.95rem;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .permissions-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .permission-item input {
            width: auto;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .logo h2, 
            .sidebar .nav-links a span,
            .sidebar .user-details {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .nav-links a {
                justify-content: center;
                padding: 15px 0;
            }
            
            .nav-links a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Admin<span>Panel</span></h2>
            </div>
            
            <div class="nav-links">
                <a href="#" class="active">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="#">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </a>
                <a href="#">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#">
                    <i class="fas fa-file-alt"></i>
                    <span>Audit Logs</span>
                </a>
                <a href="#">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">AD</div>
                <div class="user-details">
                    <h4>Admin User</h4>
                    <p>Super Administrator</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>User Management Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-outline" id="refreshUsers">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" id="addUserBtn">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3 id="totalUsers">0</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3 id="activeUsers">0</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="stat-icon active">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3 id="lockedUsers">0</h3>
                        <p>Locked Users</p>
                    </div>
                    <div class="stat-icon locked">
                        <i class="fas fa-user-lock"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3 id="verifiedUsers">0</h3>
                        <p>Verified Emails</p>
                    </div>
                    <div class="stat-icon verified">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="locked">Locked</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="roleFilter">Role</label>
                    <select id="roleFilter">
                        <option value="all">All Roles</option>
                        <option value="admin">Administrator</option>
                        <option value="user">User</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="searchUser">Search</label>
                    <input type="text" id="searchUser" placeholder="Search by name or email">
                </div>
                
                <button class="btn btn-outline" id="applyFilters">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                
                <button class="btn" id="clearFilters">
                    Clear
                </button>
            </div>
            
            <!-- Users Table -->
            <div class="table-container">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <!-- Pagination will be generated here -->
            </div>
        </div>
    </div>
    
    <!-- Add/Edit User Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New User</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="guest">Guest</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <select id="timezone" name="timezone">
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">Eastern Time</option>
                                <option value="America/Chicago">Central Time</option>
                                <option value="America/Denver">Mountain Time</option>
                                <option value="America/Los_Angeles">Pacific Time</option>
                                <option value="Europe/London">London</option>
                                <option value="Europe/Paris">Paris</option>
                                <option value="Asia/Tokyo">Tokyo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="locale">Locale</label>
                            <select id="locale" name="locale">
                                <option value="en_US">English (US)</option>
                                <option value="en_GB">English (UK)</option>
                                <option value="fr_FR">French</option>
                                <option value="es_ES">Spanish</option>
                                <option value="de_DE">German</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <div style="display: flex; gap: 20px; margin-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="status" value="active" checked> Active
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="status" value="inactive"> Inactive
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="status" value="locked"> Locked
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small style="color: #666; font-size: 0.85rem;">Password must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <div class="form-group" id="permissionsSection" style="display: none;">
                        <label>Permissions</label>
                        <div class="permissions-container">
                            <div class="permission-item">
                                <input type="checkbox" id="perm_user_create" name="permissions[]" value="user.create">
                                <label for="perm_user_create">Create Users</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" id="perm_user_read" name="permissions[]" value="user.read">
                                <label for="perm_user_read">View Users</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" id="perm_user_update" name="permissions[]" value="user.update">
                                <label for="perm_user_update">Edit Users</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" id="perm_user_delete" name="permissions[]" value="user.delete">
                                <label for="perm_user_delete">Delete Users</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" id="perm_role_manage" name="permissions[]" value="role.manage">
                                <label for="perm_role_manage">Manage Roles</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" id="perm_security_logs" name="permissions[]" value="security.logs.view">
                                <label for="perm_security_logs">View Security Logs</label>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="userId" name="userId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="saveUserBtn">Save User</button>
            </div>
        </div>
    </div>
    
    <!-- View User Modal -->
    <div class="modal" id="viewUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="viewModalTitle">User Details</h3>
                <button class="close-modal" id="closeViewModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userDetails">
                    <!-- User details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" id="closeViewBtn">Close</button>
                <button class="btn btn-primary" id="editUserBtn">Edit User</button>
            </div>
        </div>
    </div>
    
    <script>
        // Sample data - In production, this would come from API
        let users = [];
        let currentPage = 1;
        const usersPerPage = 10;
        let filteredUsers = [];
        let currentUserId = null;
        
        // DOM Elements
        const usersTableBody = document.getElementById('usersTableBody');
        const paginationDiv = document.getElementById('pagination');
        const userModal = document.getElementById('userModal');
        const viewUserModal = document.getElementById('viewUserModal');
        const closeModalBtn = document.getElementById('closeModal');
        const closeViewModalBtn = document.getElementById('closeViewModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const closeViewBtn = document.getElementById('closeViewBtn');
        const addUserBtn = document.getElementById('addUserBtn');
        const saveUserBtn = document.getElementById('saveUserBtn');
        const editUserBtn = document.getElementById('editUserBtn');
        const refreshUsersBtn = document.getElementById('refreshUsers');
        const applyFiltersBtn = document.getElementById('applyFilters');
        const clearFiltersBtn = document.getElementById('clearFilters');
        const userForm = document.getElementById('userForm');
        const permissionsSection = document.getElementById('permissionsSection');
        const roleSelect = document.getElementById('role');
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            setupEventListeners();
        });
        
        // Setup event listeners
        function setupEventListeners() {
            // Modal controls
            closeModalBtn.addEventListener('click', () => userModal.style.display = 'none');
            closeViewModalBtn.addEventListener('click', () => viewUserModal.style.display = 'none');
            cancelBtn.addEventListener('click', () => userModal.style.display = 'none');
            closeViewBtn.addEventListener('click', () => viewUserModal.style.display = 'none');
            
            // Add user button
            addUserBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add New User';
                userForm.reset();
                document.getElementById('userId').value = '';
                userModal.style.display = 'flex';
            });
            
            // Save user button
            saveUserBtn.addEventListener('click', saveUser);
            
            // Edit user button in view modal
            editUserBtn.addEventListener('click', () => {
                viewUserModal.style.display = 'none';
                editUser(currentUserId);
            });
            
            // Refresh users
            refreshUsersBtn.addEventListener('click', loadUsers);
            
            // Apply filters
            applyFiltersBtn.addEventListener('click', applyFilters);
            
            // Clear filters
            clearFiltersBtn.addEventListener('click', clearFilters);
            
            // Role change to show/hide permissions
            roleSelect.addEventListener('change', function() {
                if (this.value === 'admin' || this.value === 'super_admin') {
                    permissionsSection.style.display = 'block';
                } else {
                    permissionsSection.style.display = 'none';
                }
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === userModal) {
                    userModal.style.display = 'none';
                }
                if (event.target === viewUserModal) {
                    viewUserModal.style.display = 'none';
                }
            });
        }
        
        // Load users from API
        function loadUsers() {
            // In production, this would be an API call
            // For now, we'll simulate with sample data
            simulateAPICall('/api/users', 'GET')
                .then(data => {
                    users = data.users || [];
                    filteredUsers = [...users];
                    updateStats();
                    renderUsersTable();
                    renderPagination();
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    showNotification('Failed to load users', 'error');
                });
        }
        
        // Update dashboard statistics
        function updateStats() {
            const totalUsers = users.length;
            const activeUsers = users.filter(u => u.status === 'active').length;
            const lockedUsers = users.filter(u => u.status === 'locked').length;
            const verifiedUsers = users.filter(u => u.emailVerified).length;
            
            document.getElementById('totalUsers').textContent = totalUsers;
            document.getElementById('activeUsers').textContent = activeUsers;
            document.getElementById('lockedUsers').textContent = lockedUsers;
            document.getElementById('verifiedUsers').textContent = verifiedUsers;
        }
        
        // Render users table
        function renderUsersTable() {
            const startIndex = (currentPage - 1) * usersPerPage;
            const endIndex = startIndex + usersPerPage;
            const usersToShow = filteredUsers.slice(startIndex, endIndex);
            
            usersTableBody.innerHTML = '';
            
            if (usersToShow.length === 0) {
                usersTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="fas fa-users" style="font-size: 2rem; color: #ddd; margin-bottom: 10px; display: block;"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            usersToShow.forEach(user => {
                const row = document.createElement('tr');
                
                // Get initials for avatar
                const initials = getInitials(user.firstName, user.lastName);
                
                // Format last login date
                const lastLogin = user.lastLogin ? formatDate(user.lastLogin) : 'Never';
                
                // Determine status badge class
                let statusClass = 'status-active';
                if (user.status === 'inactive') statusClass = 'status-inactive';
                if (user.status === 'locked') statusClass = 'status-locked';
                
                row.innerHTML = `
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-small">${initials}</div>
                            <div>
                                <div class="user-name">${user.firstName} ${user.lastName}</div>
                                <div class="user-username">@${user.username}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="user-email">${user.email}</div>
                        ${user.emailVerified ? '<small style="color: #27ae60;"><i class="fas fa-check-circle"></i> Verified</small>' : '<small style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Not verified</small>'}
                    </td>
                    <td><span class="status-badge ${statusClass}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
                    <td><span class="role-badge">${user.role}</span></td>
                    <td>${lastLogin}</td>
                    <td>
                        <div class="action-buttons">
                            <div class="action-btn view" title="View" onclick="viewUser(${user.id})">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="action-btn edit" title="Edit" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </div>
                            ${user.status === 'locked' ? 
                                `<div class="action-btn lock" title="Unlock" onclick="toggleUserLock(${user.id}, false)">
                                    <i class="fas fa-unlock"></i>
                                </div>` : 
                                `<div class="action-btn lock" title="Lock" onclick="toggleUserLock(${user.id}, true)">
                                    <i class="fas fa-lock"></i>
                                </div>`
                            }
                            <div class="action-btn delete" title="Delete" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </div>
                        </div>
                    </td>
                `;
                
                usersTableBody.appendChild(row);
            });
        }
        
        // Render pagination
        function renderPagination() {
            const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
            
            if (totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <div class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}" onclick="changePage(${currentPage - 1})">
                    <i class="fas fa-chevron-left"></i>
                </div>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `
                        <div class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                            ${i}
                        </div>
                    `;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += `<div class="pagination-btn disabled">...</div>`;
                }
            }
            
            // Next button
            paginationHTML += `
                <div class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''}" onclick="changePage(${currentPage + 1})">
                    <i class="fas fa-chevron-right"></i>
                </div>
            `;
            
            paginationDiv.innerHTML = paginationHTML;
        }
        
        // Change page
        function changePage(page) {
            const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
            
            if (page < 1 || page > totalPages) return;
            
            currentPage = page;
            renderUsersTable();
            renderPagination();
        }
        
        // Apply filters
        function applyFilters() {
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;
            const searchTerm = document.getElementById('searchUser').value.toLowerCase();
            
            filteredUsers = users.filter(user => {
                // Status filter
                if (statusFilter !== 'all' && user.status !== statusFilter) {
                    return false;
                }
                
                // Role filter
                if (roleFilter !== 'all' && user.role !== roleFilter) {
                    return false;
                }
                
                // Search filter
                if (searchTerm) {
                    const searchStr = `${user.firstName} ${user.lastName} ${user.email} ${user.username}`.toLowerCase();
                    if (!searchStr.includes(searchTerm)) {
                        return false;
                    }
                }
                
                return true;
            });
            
            currentPage = 1;
            renderUsersTable();
            renderPagination();
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('roleFilter').value = 'all';
            document.getElementById('searchUser').value = '';
            
            filteredUsers = [...users];
            currentPage = 1;
            renderUsersTable();
            renderPagination();
        }
        
        // View user details
        function viewUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;
            
            currentUserId = userId;
            
            const userDetailsDiv = document.getElementById('userDetails');
            document.getElementById('viewModalTitle').textContent = `${user.firstName} ${user.lastName}`;
            
            // Format user data for display
            const lastLogin = user.lastLogin ? formatDate(user.lastLogin, true) : 'Never';
            const createdDate = formatDate(user.createdAt, true);
            const passwordChanged = user.passwordChangedAt ? formatDate(user.passwordChangedAt, true) : 'Never';
            
            userDetailsDiv.innerHTML = `
                <div style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                    <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.2rem;">${getInitials(user.firstName, user.lastName)}</div>
                    <div style="margin-left: 15px;">
                        <h3 style="margin-bottom: 5px;">${user.firstName} ${user.lastName}</h3>
                        <p style="color: #666;">@${user.username}</p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="margin-bottom: 10px; color: #555;">Contact Information</h4>
                        <p><strong>Email:</strong> ${user.email}</p>
                        <p><strong>Phone:</strong> ${user.phone || 'Not provided'}</p>
                        <p><strong>Email Verified:</strong> ${user.emailVerified ? '<span style="color: #27ae60;">Yes</span>' : '<span style="color: #e74c3c;">No</span>'}</p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 10px; color: #555;">Account Information</h4>
                        <p><strong>Role:</strong> <span class="role-badge">${user.role}</span></p>
                        <p><strong>Status:</strong> <span class="status-badge ${user.status === 'active' ? 'status-active' : user.status === 'locked' ? 'status-locked' : 'status-inactive'}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></p>
                        <p><strong>MFA Enabled:</strong> ${user.mfaEnabled ? 'Yes' : 'No'}</p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 10px; color: #555;">Login Information</h4>
                        <p><strong>Last Login:</strong> ${lastLogin}</p>
                        <p><strong>Failed Attempts:</strong> ${user.failedLoginAttempts}</p>
                        <p><strong>Last Password Change:</strong> ${passwordChanged}</p>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 10px; color: #555;">Preferences</h4>
                        <p><strong>Timezone:</strong> ${user.timezone}</p>
                        <p><strong>Locale:</strong> ${user.locale}</p>
                        <p><strong>Account Created:</strong> ${createdDate}</p>
                    </div>
                </div>
                
                ${user.permissions && user.permissions.length > 0 ? `
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 10px; color: #555;">Permissions</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        ${user.permissions.map(perm => `<span class="role-badge" style="font-size: 0.75rem;">${perm}</span>`).join('')}
                    </div>
                </div>
                ` : ''}
            `;
            
            viewUserModal.style.display = 'flex';
        }
        
        // Edit user
        function editUser(userId) {
            let user = null;
            
            if (userId) {
                user = users.find(u => u.id === userId);
                if (!user) return;
                
                currentUserId = userId;
                document.getElementById('modalTitle').textContent = `Edit User: ${user.firstName} ${user.lastName}`;
                document.getElementById('userId').value = user.id;
                
                // Fill form with user data
                document.getElementById('firstName').value = user.firstName;
                document.getElementById('lastName').value = user.lastName;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('phone').value = user.phone || '';
                document.getElementById('role').value = user.role;
                document.getElementById('timezone').value = user.timezone;
                document.getElementById('locale').value = user.locale;
                
                // Set status radio
                document.querySelector(`input[name="status"][value="${user.status}"]`).checked = true;
                
                // Show/hide permissions section based on role
                if (user.role === 'admin' || user.role === 'super_admin') {
                    permissionsSection.style.display = 'block';
                } else {
                    permissionsSection.style.display = 'none';
                }
                
                // Clear password fields for edit
                document.getElementById('password').required = false;
                document.getElementById('confirmPassword').required = false;
                document.getElementById('password').value = '';
                document.getElementById('confirmPassword').value = '';
            } else {
                document.getElementById('modalTitle').textContent = 'Add New User';
                document.getElementById('userId').value = '';
                userForm.reset();
                document.getElementById('password').required = true;
                document.getElementById('confirmPassword').required = true;
            }
            
            userModal.style.display = 'flex';
        }
        
        // Save user (create or update)
        function saveUser() {
            const formData = new FormData(userForm);
            const userData = Object.fromEntries(formData.entries());
            
            // Validate form
            if (!validateUserForm(userData)) {
                return;
            }
            
            // Prepare API payload
            const payload = {
                username: userData.username,
                email: userData.email,
                firstName: userData.firstName,
                lastName: userData.lastName,
                phone: userData.phone || null,
                role: userData.role,
                status: userData.status,
                timezone: userData.timezone,
                locale: userData.locale
            };
            
            // Only include password if provided (for edit) or required (for new)
            if (userData.password) {
                payload.password = userData.password;
                payload.confirmPassword = userData.confirmPassword;
            }
            
            // Include permissions if admin role
            if (userData.role === 'admin' || userData.role === 'super_admin') {
                const permissions = formData.getAll('permissions[]');
                payload.permissions = permissions;
            }
            
            // Determine if this is an update or create
            const isUpdate = userData.userId !== '';
            const url = isUpdate ? `/api/users/${userData.userId}` : '/api/users';
            const method = isUpdate ? 'PUT' : 'POST';
            
            // Simulate API call
            simulateAPICall(url, method, payload)
                .then(data => {
                    showNotification(isUpdate ? 'User updated successfully' : 'User created successfully', 'success');
                    userModal.style.display = 'none';
                    loadUsers(); // Refresh the user list
                })
                .catch(error => {
                    console.error('Error saving user:', error);
                    showNotification(`Failed to save user: ${error.message}`, 'error');
                });
        }
        
        // Toggle user lock status
        function toggleUserLock(userId, lock) {
            const action = lock ? 'lock' : 'unlock';
            const confirmMessage = lock 
                ? 'Are you sure you want to lock this user account?' 
                : 'Are you sure you want to unlock this user account?';
            
            if (!confirm(confirmMessage)) return;
            
            simulateAPICall(`/api/users/${userId}/${action}`, 'POST')
                .then(data => {
                    showNotification(`User ${lock ? 'locked' : 'unlocked'} successfully`, 'success');
                    loadUsers(); // Refresh the user list
                })
                .catch(error => {
                    console.error(`Error ${action}ing user:`, error);
                    showNotification(`Failed to ${action} user`, 'error');
                });
        }
        
        // Delete user
        function deleteUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;
            
            if (!confirm(`Are you sure you want to delete user "${user.firstName} ${user.lastName}"? This action cannot be undone.`)) {
                return;
            }
            
            simulateAPICall(`/api/users/${userId}`, 'DELETE')
                .then(data => {
                    showNotification('User deleted successfully', 'success');
                    loadUsers(); // Refresh the user list
                })
                .catch(error => {
                    console.error('Error deleting user:', error);
                    showNotification('Failed to delete user', 'error');
                });
        }
        
        // Validate user form
        function validateUserForm(data) {
            // Check required fields
            if (!data.firstName || !data.lastName || !data.username || !data.email) {
                showNotification('Please fill in all required fields', 'warning');
                return false;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.email)) {
                showNotification('Please enter a valid email address', 'warning');
                return false;
            }
            
            // For new users or when password is provided, validate it
            if ((!data.userId || data.password) && data.password) {
                if (data.password.length < 8) {
                    showNotification('Password must be at least 8 characters long', 'warning');
                    return false;
                }
                
                if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d])/.test(data.password)) {
                    showNotification('Password must contain uppercase, lowercase, number, and special character', 'warning');
                    return false;
                }
                
                if (data.password !== data.confirmPassword) {
                    showNotification('Passwords do not match', 'warning');
                    return false;
                }
            }
            
            return true;
        }
        
        // Helper functions
        function getInitials(firstName, lastName) {
            return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase();
        }
        
        function formatDate(dateString, includeTime = false) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            if (includeTime) {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            }
            return date.toLocaleDateString();
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            `;
            
            // Set background color based on type
            if (type === 'success') {
                notification.style.backgroundColor = '#27ae60';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#e74c3c';
            } else if (type === 'warning') {
                notification.style.backgroundColor = '#f39c12';
            } else {
                notification.style.backgroundColor = '#3498db';
            }
            
            notification.textContent = message;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
            
            // Add CSS for animation
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Simulate API call (replace with actual API calls)
        function simulateAPICall(url, method, data = null) {
            return new Promise((resolve, reject) => {
                // Simulate network delay
                setTimeout(() => {
                    // Sample data for simulation
                    const sampleUsers = [
                        {
                            id: 1,
                            username: 'john_doe',
                            email: 'john@example.com',
                            firstName: 'John',
                            lastName: 'Doe',
                            phone: '+1234567890',
                            role: 'admin',
                            status: 'active',
                            timezone: 'UTC',
                            locale: 'en_US',
                            emailVerified: true,
                            mfaEnabled: false,
                            lastLogin: '2025-02-03T14:15:44Z',
                            failedLoginAttempts: 0,
                            passwordChangedAt: '2025-01-15T10:30:00Z',
                            createdAt: '2025-01-01T00:00:00Z',
                            permissions: ['user.create', 'user.read', 'user.update']
                        },
                        {
                            id: 6,
                            username: 'shan',
                            email: 'niyoshalom680@gmail.com',
                            firstName: 'Shalom',
                            lastName: 'Niyonkuru',
                            phone: '0785675680',
                            role: 'user',
                            status: 'active',
                            timezone: 'Africa/Johannesburg',
                            locale: 'en-US',
                            emailVerified: false,
                            mfaEnabled: false,
                            lastLogin: '2026-02-02T21:44:23Z',
                            failedLoginAttempts: 0,
                            passwordChangedAt: null,
                            createdAt: '2026-02-02T17:47:51Z',
                            permissions: []
                        },
                        {
                            id: 10,
                            username: 'test1',
                            email: 'codingstack250@gmail.com',
                            firstName: 'Shalom',
                            lastName: 'Shalom',
                            phone: '0785675680',
                            role: 'user',
                            status: 'active',
                            timezone: 'Africa/Johannesburg',
                            locale: 'en-US',
                            emailVerified: false,
                            mfaEnabled: false,
                            lastLogin: '2026-02-04T22:24:17Z',
                            failedLoginAttempts: 0,
                            passwordChangedAt: null,
                            createdAt: '2026-02-02T22:09:27Z',
                            permissions: []
                        },
                        {
                            id: 22,
                            username: 'irambona',
                            email: 'niyonkurushalom20003@gmail.com',
                            firstName: 'Edson',
                            lastName: 'Ra',
                            phone: '0793117690',
                            role: 'user',
                            status: 'locked',
                            timezone: 'Africa/Johannesburg',
                            locale: 'en-US',
                            emailVerified: true,
                            mfaEnabled: false,
                            lastLogin: '2026-02-04T19:57:26Z',
                            failedLoginAttempts: 0,
                            passwordChangedAt: '2026-02-04T19:56:01Z',
                            createdAt: '2026-02-03T14:19:47Z',
                            permissions: []
                        },
                        {
                            id: 37,
                            username: 'shanobbmb',
                            email: 'niyonkurushalom2003@gmail.com',
                            firstName: 'Hkhk',
                            lastName: 'Khkhkh',
                            phone: '0793117690',
                            role: 'user',
                            status: 'inactive',
                            timezone: 'Africa/Johannesburg',
                            locale: 'en-US',
                            emailVerified: true,
                            mfaEnabled: false,
                            lastLogin: null,
                            failedLoginAttempts: 0,
                            passwordChangedAt: null,
                            createdAt: '2026-02-04T21:35:46Z',
                            permissions: []
                        }
                    ];
                    
                    // Handle different endpoints
                    if (url === '/api/users' && method === 'GET') {
                        resolve({ users: sampleUsers });
                    } else if (url.startsWith('/api/users/') && method === 'DELETE') {
                        resolve({ success: true, message: 'User deleted' });
                    } else if (url.startsWith('/api/users/') && url.includes('/lock') && method === 'POST') {
                        resolve({ success: true, message: 'User locked' });
                    } else if (url.startsWith('/api/users/') && url.includes('/unlock') && method === 'POST') {
                        resolve({ success: true, message: 'User unlocked' });
                    } else if ((url === '/api/users' && method === 'POST') || (url.startsWith('/api/users/') && method === 'PUT')) {
                        resolve({ success: true, message: 'User saved', user: data });
                    } else {
                        reject(new Error('API endpoint not found'));
                    }
                }, 500); // Simulate 500ms delay
            });
        }
    </script>
</body>
</html>