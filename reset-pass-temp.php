<?php
require_once('wp-load.php');
$user = get_user_by('login', 'swechha');
if ($user) {
    wp_set_password('Admin@123', $user->ID);
    echo 'Password reset for: ' . $user->user_login;
} else {
    echo 'User not found. Available users: ';
    $users = get_users();
    foreach ($users as $u) echo $u->user_login . ' (' . $u->user_email . ') | ';
}
