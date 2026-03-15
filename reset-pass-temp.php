<?php
// Direct DB password reset — bypasses WordPress hooks entirely
$host = 'mysql.railway.internal';
$port = 3306;
$db   = 'kavipushp_db';
$user = 'kavipushp_user';
$pass = 'kavipushp_pass_2024';

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    // Fallback: read from wp-config.php
    $config = file_get_contents(__DIR__ . '/wp-config.php');
    preg_match("/define\(\s*'DB_HOST'\s*,\s*'([^']+)'\s*\)/", $config, $h);
    preg_match("/define\(\s*'DB_USER'\s*,\s*'([^']+)'\s*\)/", $config, $u);
    preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'([^']+)'\s*\)/", $config, $p);
    preg_match("/define\(\s*'DB_NAME'\s*,\s*'([^']+)'\s*\)/", $config, $d);
    $conn = new mysqli($h[1] ?? $host, $u[1] ?? $user, $p[1] ?? $pass, $d[1] ?? $db, $port);
}
if ($conn->connect_error) {
    die('DB connection failed: ' . $conn->connect_error);
}

// WordPress accepts plain MD5 and auto-upgrades to phpass on next login
$new_hash = md5('Admin@123');

$stmt = $conn->prepare("UPDATE wp_users SET user_pass = ? WHERE user_login = 'swechha'");
$stmt->bind_param('s', $new_hash);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo 'Password reset successfully for swechha. DELETE this file after logging in!';
} else {
    echo 'User "swechha" not found. Existing users:<br>';
    $res = $conn->query("SELECT user_login, user_email FROM wp_users");
    while ($row = $res->fetch_assoc()) {
        echo $row['user_login'] . ' — ' . $row['user_email'] . '<br>';
    }
}
$conn->close();
