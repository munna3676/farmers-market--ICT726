<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('farmer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $productId = (int) ($_POST['product_id'] ?? 0);

    // Ownership check baked into the WHERE clause (access control)
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ? AND farmer_id = ?");
    $stmt->execute([$productId, current_user_id()]);

    if ($stmt->rowCount() > 0) {
        set_flash('success', 'Product deleted.');
    } else {
        set_flash('error', 'Product not found or you do not have permission to delete it.');
    }
}
redirect('/farmer/dashboard.php');
