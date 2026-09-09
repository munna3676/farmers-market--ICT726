<?php
/**
 * Reusable helper functions: sanitization, validation, formatting.
 */

// Auto-detect the app's base URL path so the site works whether it's hosted
// at the domain root (e.g. a live host: https://example.com/) or inside a
// subfolder (e.g. local XAMPP: http://localhost/farmers-market/).
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Entry scripts can live one level down in /admin or /farmer — strip that
    // off so BASE_URL always points at the site's own root folder.
    foreach (['/admin', '/farmer'] as $sub) {
        if (substr($scriptDir, -strlen($sub)) === $sub) {
            $scriptDir = substr($scriptDir, 0, -strlen($sub));
            break;
        }
    }
    define('BASE_URL', rtrim($scriptDir, '/'));
}

// Clean any user-submitted string to prevent XSS when echoed back
function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// Basic email format check
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Australian mobile / landline loose validation (10 digits, optional +61)
function is_valid_au_phone($phone) {
    return (bool) preg_match('/^(\+?61|0)[2-478]\d{8}$/', preg_replace('/\s+/', '', $phone));
}

// Australian postcode: exactly 4 digits
function is_valid_postcode($postcode) {
    return (bool) preg_match('/^\d{4}$/', $postcode);
}

// Password strength: min 8 chars, at least 1 letter and 1 number
function is_strong_password($password) {
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password);
}

// Format currency in AUD
function format_price($amount) {
    return '$' . number_format((float) $amount, 2);
}

// Redirect helper
function redirect($path) {
    // If given a site-root-relative path like '/login.php', prefix it with
    // BASE_URL so it still resolves correctly when hosted in a subfolder.
    if (isset($path[0]) && $path[0] === '/') {
        $path = BASE_URL . $path;
    }
    header("Location: $path");
    exit();
}

// Flash message helpers (uses session)
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Handle a product image upload safely; returns filename or default on failure
function handle_product_image_upload($fileInputName, $uploadDir = __DIR__ . '/../uploads/products/') {
    if (empty($_FILES[$fileInputName]['name'])) {
        return 'default-product.jpg';
    }

    $file = $_FILES[$fileInputName];
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'default-product.jpg';
    }
    if (!in_array($ext, $allowed)) {
        return false; // invalid type - caller should show an error
    }
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
        return false;
    }

    $newName = 'product_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
        return $newName;
    }
    return false;
}
