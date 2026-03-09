<?php
define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'wp-load.php';
global $wpdb;

// Directly update login and password in DB
$wpdb->query($wpdb->prepare(
    "UPDATE {$wpdb->users} SET user_login=%s, user_pass=%s WHERE ID=1",
    'swechha',
    wp_hash_password('Admin@123')
));

clean_user_cache(1);
wp_cache_flush();

$user = get_user_by('id', 1);
echo '<pre>';
echo "Login: {$user->user_login}\n";
echo "Email: {$user->user_email}\n";
echo "Roles: " . implode(',', $user->roles) . "\n";
echo '</pre>';
echo '<p>Done! <a href="/wp-login.php">Login now</a></p>';
unlink(__FILE__);
