<?php
/**
 * Database connection using PDO.
 * Update the credentials below to match your hosting environment.
 */

// ---- EDIT THESE VALUES FOR YOUR SERVER ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'farmers_market');
define('DB_USER', 'root');
define('DB_PASS', '');
// --------------------------------------------

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Never leak DB credentials/details to the browser in production
    die("Database connection failed. Please try again later.");
}
