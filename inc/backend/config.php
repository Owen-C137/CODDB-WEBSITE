<?php
/**
 * backend/config.php
 *
 * Author: pixelcave
 *
 * Backend pages configuration file
 */

// **************************************************************************************************
// INCLUDED VIEWS
// **************************************************************************************************

$cb->inc_side_overlay = __DIR__ . '/views/inc_side_overlay.php';
$cb->inc_sidebar      = __DIR__ . '/views/inc_sidebar.php';
$cb->inc_header       = __DIR__ . '/views/inc_header.php';
$cb->inc_footer       = __DIR__ . '/views/inc_footer.php';

// **************************************************************************************************
// MAIN MENU
// **************************************************************************************************

// Always show the “Main Stuff” section:
$mainNav = [
    [
        'name' => 'Main Stuff',
        'type' => 'heading'
    ],
    [
        'name' => 'Dashboard',
        'icon' => 'fa fa-house-user',
        'url'  => '/index.php'
    ],
    [
        'name' => 'Chatbox',
        'icon' => 'fa fa-commenting',
        'url'  => '/chat.php'
    ]
];

// === Determine current user’s role ===
// If you stored the role name in $_SESSION['role_name'] at login, use that.
// Otherwise, do a quick query. Example below checks $_SESSION first, then DB.

// Only start a session if none exists yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserId   = $_SESSION['user_id'] ?? null;
$currentRoleName = $_SESSION['role_name'] ?? null;

if (!$currentRoleName && $currentUserId) {
    // Fallback: fetch from database if not already in session
    $stmt = $pdo->prepare("
        SELECT r.name 
        FROM `demon_roles` AS r
        JOIN `demon_users` AS u ON u.role_id = r.id
        WHERE u.id = :uid
        LIMIT 1
    ");
    $stmt->execute([':uid' => $currentUserId]);
    $row = $stmt->fetch();
    if ($row) {
        $currentRoleName = $row['name'];
        // (Optionally persist it)
        $_SESSION['role_name'] = $currentRoleName;
    }
}

// === Only show “Admin Section” if the role is “admin” ===
if ($currentRoleName === 'admin') {
    $mainNav[] = [
        'name' => 'Admin Section',
        'type' => 'heading'
    ];
    $mainNav[] = [
        'name' => 'Admin Dashboard',
        'icon' => 'fa fa-tools',
        'url'  => '/admin/admin-dashboard.php'
    ];
    $mainNav[] = [
        'name' => 'Admin Config',
        'icon' => 'fa fa-cog',
        'url'  => '/admin/admin-config.php'
    ];

    // “User Management” dropdown right under “Admin Config”
    $mainNav[] = [
        'name' => 'User Management',
        'icon' => 'fa fa-address-card',
        'sub'  => [
            [
                'name' => 'Users',
                'icon' => 'fa fa-users',
                'sub'  => [
                    [
                        'name' => 'All Users',
                        'url'  => '/admin/admin-users.php'
                    ],
                    [
                        'name' => 'Add New User',
                        'url'  => '/admin/admin-user-add.php'
                    ],
                    [
                        'name' => 'Roles & Permissions',
                        'url'  => '/admin/admin-roles.php'
                    ]
                ]
            ],
        ]
    ];
        $mainNav[] = [
        'name' => 'Activity Logs',
        'icon' => 'fa fa-clipboard-list',
        'url'  => '/admin/admin-logs.php'
    ];
}

// Finally assign to $cb->main_nav so the template knows about it
$cb->main_nav = $mainNav;
