<?php
function check_role($required_role) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    // Handle both single role and array of roles
    $allowed_roles = is_array($required_role) ? $required_role : [$required_role];
    
    // Get user's actual role from session
    $user_role = $_SESSION['user_role'];
    
    // Debug information (remove in production)
    error_log("User role: " . $user_role);
    error_log("Required roles: " . implode(', ', $allowed_roles));
    
    // Check if user has any of the allowed roles
    $has_access = false;
    foreach ($allowed_roles as $role) {
        if ($user_role === $role) {
            $has_access = true;
            break;
        }
    }
    
    if (!$has_access) {
        // Log the access attempt
        error_log("Access denied for user: " . $_SESSION['username'] . " with role: " . $user_role);
        
        http_response_code(403);
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied - Farm Management System</title>
            <style>
                :root {
                    --forest-green: #228B22;
                    --earth-brown: #8B4513;
                    --sky-blue: #87CEEB;
                    --cream-white: #FFFDD0;
                    --dark-brown: #3E2723;
                }
                
                body {
                    margin: 0;
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, var(--forest-green), var(--sky-blue));
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    color: var(--dark-brown);
                }
                .error-container {
                    background: var(--cream-white);
                    padding: 3rem;
                    border-radius: 20px;
                    text-align: center;
                    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                    border: 5px solid var(--earth-brown);
                    max-width: 500px;
                }
                .error-icon {
                    font-size: 4rem;
                    margin-bottom: 1rem;
                }
                h1 {
                    color: #c62828;
                    margin-bottom: 1rem;
                }
                p {
                    margin-bottom: 1rem;
                    line-height: 1.6;
                }
                .role-info {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 10px;
                    margin: 1rem 0;
                    border-left: 4px solid var(--forest-green);
                }
                .btn {
                    display: inline-block;
                    padding: 12px 25px;
                    background: var(--forest-green);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: bold;
                    transition: all 0.3s ease;
                    margin: 5px;
                }
                .btn:hover {
                    background: var(--earth-brown);
                    transform: translateY(-2px);
                }
                .btn-secondary {
                    background: #666;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">🚫</div>
                <h1>Access Denied</h1>
                <p>You don\'t have permission to access this page.</p>
                
                <div class="role-info">
                    <p><strong>Your Role:</strong> <span style="color: var(--forest-green);">' . get_role_display_name($user_role) . '</span></p>
                    <p><strong>Required Role:</strong> ' . implode(', ', array_map("get_role_display_name", $allowed_roles)) . '</p>
                </div>
                
                <p>Please contact an administrator if you believe this is an error.</p>
                
                <a href="../auth/login.php" class="btn">🔐 Back to Login</a>';
                
        // Show appropriate dashboard button based on user role
        if ($user_role === 'admin') {
            echo '<a href="../users/admin/dashboard.php" class="btn btn-secondary">🏠 Go to Admin Dashboard</a>';
        } elseif ($user_role === 'farmer') {
            echo '<a href="../users/farmer/dashboard.php" class="btn btn-secondary">🏠 Go to Farmer Dashboard</a>';
        } elseif ($user_role === 'customer') {
            echo '<a href="../users/customer/dashboard.php" class="btn btn-secondary">🏠 Go to Customer Dashboard</a>';
        }
        
        echo '
            </div>
        </body>
        </html>';
        exit();
    }
    
    return true;
}

// Helper function to get user role display name
function get_role_display_name($role) {
    $role_names = [
        'admin' => '👑 Administrator',
        'farmer' => '👨‍🌾 Farmer', 
        'customer' => '🛒 Customer'
    ];
    
    return $role_names[$role] ?? ucfirst($role);
}

// Function to check if user has specific permission
function has_permission($permission) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    // Define permissions for each role
    $permissions = [
        'admin' => [
            'manage_users', 'manage_animals', 'manage_inventory', 
            'view_reports', 'manage_finances', 'system_settings',
            'view_all_data', 'export_data', 'manage_orders'
        ],
        'farmer' => [
            'manage_animals', 'view_inventory', 'record_activities',
            'view_own_animals', 'update_animal_health', 'view_farm_reports'
        ],
        'customer' => [
            'place_orders', 'view_animals', 'view_orders',
            'view_products', 'make_payments', 'view_order_history'
        ]
    ];
    
    return in_array($permission, $permissions[$user_role] ?? []);
}

// Function to get all permissions for current user
function get_user_permissions() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_role'])) {
        return [];
    }
    
    $user_role = $_SESSION['user_role'];
    
    $permissions = [
        'admin' => [
            'manage_users', 'manage_animals', 'manage_inventory', 
            'view_reports', 'manage_finances', 'system_settings',
            'view_all_data', 'export_data', 'manage_orders'
        ],
        'farmer' => [
            'manage_animals', 'view_inventory', 'record_activities',
            'view_own_animals', 'update_animal_health', 'view_farm_reports'
        ],
        'customer' => [
            'place_orders', 'view_animals', 'view_orders',
            'view_products', 'make_payments', 'view_order_history'
        ]
    ];
    
    return $permissions[$user_role] ?? [];
}
?>