<?php
/**
 * Session bootstrap + authentication / role-based access control helpers.
 * Include this file at the very top of any page (before any HTML output).
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie settings
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Require the visitor to be logged in; otherwise redirect to login
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('/login.php');
    }
}

// Require a specific role (or one of several roles)
function require_role($roles) {
    require_login();
    $roles = (array) $roles;
    if (!in_array(current_role(), $roles, true)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect('/index.php');
    }
}

// Simple CSRF token helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Invalid request (CSRF check failed). Please go back and try again.');
    }
}
