<?php
/**
 * WordPress Configuration — Kavipushp Bridals
 * Supports both local XAMPP and Railway deployment.
 *
 * @package WordPress
 */

// ** Database settings — Railway env vars with local fallback ** //
define( 'DB_NAME',    getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'kavipushp_db' );
define( 'DB_USER',    getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root' );
define( 'DB_PASSWORD',getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '' );
define( 'DB_HOST',    ( getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost' )
                    . ':'
                    . ( getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         'kp!x9#Rv$mN2@qL8&wZ5*jT3^pY7%hU1+cB6=dF4-gK0' );
define( 'SECURE_AUTH_KEY',  'Qw2$eR4#tY6@uI8!oP0^aS3&dF5*gH7%jK9+lZ1=xC2' );
define( 'LOGGED_IN_KEY',    'Zx1!cV3@bN5#mL7$kJ9%hG2^fD4&sA6*wE8+qR0=tY1' );
define( 'NONCE_KEY',        'Mn3@bV5#cX7$zL9%kJ1^hG2&fD4*sA6!wE8+qR0=tY2' );
define( 'AUTH_SALT',        'Pl4#oK6@iJ8!uY0$tR2%eW4^qA6&sD8*fG0+hJ2=kL4' );
define( 'SECURE_AUTH_SALT', 'Ws5@eD7#rF9$tG1%yH3^uJ5&iK7*oL9+pZ1=aX3-cV5' );
define( 'LOGGED_IN_SALT',   'Qp6#aZ8@sX0!dC2$fV4%gB6^hN8&jM0*kL2+wE4=rT6' );
define( 'NONCE_SALT',       'Ty7@uI9#oP1$aS3%dF5^gH7&jK9*lZ1+xC3=vB5-nM7' );

/**
 * WordPress database table prefix.
 */
$table_prefix = 'kp_';

/**
 * Debugging — disable on Railway production.
 */
$is_railway = (bool) getenv('RAILWAY_ENVIRONMENT');
define( 'WP_DEBUG',     ! $is_railway );
define( 'WP_DEBUG_LOG', ! $is_railway );

/**
 * Dynamic URL detection.
 * - Railway: uses RAILWAY_PUBLIC_DOMAIN env var
 * - Tunnel (ngrok/cloudflare): detects from HTTP_HOST
 * - Local: uses localhost path
 */
if ( $is_railway && getenv('RAILWAY_PUBLIC_DOMAIN') ) {
    $site_url = 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN');
    define( 'WP_HOME',    $site_url );
    define( 'WP_SITEURL', $site_url );
    // Railway terminates SSL at the load balancer — tell PHP/WordPress the connection is HTTPS
    $_SERVER['HTTPS']               = 'on';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
} elseif ( isset( $_SERVER['HTTP_HOST'] ) ) {
    $is_tunnel = ( strpos( $_SERVER['HTTP_HOST'], 'trycloudflare.com' ) !== false
                || strpos( $_SERVER['HTTP_HOST'], 'serveo.net' )       !== false
                || strpos( $_SERVER['HTTP_HOST'], 'ngrok' )            !== false );
    if ( $is_tunnel || ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) ) {
        $scheme = 'https';
        $_SERVER['HTTPS'] = 'on';
    } else {
        $scheme = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) ? 'https' : 'http';
    }
    $dynamic_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/kavipushp';
    define( 'WP_HOME',    $dynamic_url );
    define( 'WP_SITEURL', $dynamic_url );
}

/* That's all, stop editing! Happy publishing. */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
