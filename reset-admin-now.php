<?php
/**
 * Emergency admin password reset — DELETE after use
 */
define('ABSPATH', __DIR__ . '/');
require_once __DIR__ . '/wp-load.php';

$new_password = 'KaviAdmin@2024';

$user = get_user_by('login', 'admin');
if (!$user) {
    // Try to find any admin user
    $admins = get_users(['role' => 'administrator', 'number' => 1]);
    $user = !empty($admins) ? $admins[0] : null;
}

if ($user) {
    wp_set_password($new_password, $user->ID);
    echo '<h2 style="font-family:sans-serif;color:green;">Password reset successful!</h2>';
    echo '<p style="font-family:sans-serif;">Username: <strong>' . esc_html($user->user_login) . '</strong></p>';
    echo '<p style="font-family:sans-serif;">New Password: <strong>' . $new_password . '</strong></p>';
    echo '<p style="font-family:sans-serif;"><a href="/wp-login.php">Go to Login</a></p>';
    echo '<p style="font-family:sans-serif;color:red;"><strong>Delete this file after logging in!</strong></p>';
} else {
    echo '<h2 style="font-family:sans-serif;color:red;">No admin user found.</h2>';
    echo '<p>Please complete WordPress installation first at <a href="/wp-admin/install.php">/wp-admin/install.php</a></p>';
}
