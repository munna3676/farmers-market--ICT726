<?php
/**
 * ONE-TIME SETUP SCRIPT.
 * Run this once in your browser after importing database.sql to set a real,
 * securely-hashed password for the admin account. DELETE this file afterwards.
 *
 * Usage: http://localhost/farmers-market/setup_admin_password.php
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if (!is_valid_email($email) || strlen($newPassword) < 8) {
        $message = 'Please provide a valid email and a password of at least 8 characters.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $email]);
        $message = $stmt->rowCount() > 0
            ? 'Admin password updated successfully! Please delete this file now for security.'
            : 'No admin account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Admin Setup</title></head>
<body style="font-family:sans-serif;max-width:420px;margin:60px auto;">
<h2>One-Time Admin Password Setup</h2>
<?php if ($message): ?><p><strong><?php echo htmlspecialchars($message); ?></strong></p><?php endif; ?>
<form method="POST">
  <p><label>Admin Email:<br><input type="email" name="email" value="admin@aussiefarmers.com.au" style="width:100%;padding:8px;"></label></p>
  <p><label>New Password:<br><input type="password" name="password" style="width:100%;padding:8px;"></label></p>
  <button type="submit" style="padding:10px 20px;">Set Password</button>
</form>
<p style="color:#b3261e;"><strong>Important:</strong> Delete this file from the server once you've set the password.</p>
</body>
</html>
