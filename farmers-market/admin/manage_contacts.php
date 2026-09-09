<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC")->fetchAll();

$pageTitle = 'Contact Messages | Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Admin navigation">
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_users.php">Manage Users</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_products.php">Manage Products</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_orders.php">All Orders</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_contacts.php" class="active">Contact Messages</a>
    </aside>

    <div class="dashboard-content">
      <h1>Contact Form Messages</h1>
      <?php if (empty($messages)): ?>
        <p>No messages received yet.</p>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
              <div class="flex-between">
                <h3><?php echo clean($m['subject']); ?></h3>
                <small><?php echo date('d M Y, g:i a', strtotime($m['submitted_at'])); ?></small>
              </div>
              <p>From: <?php echo clean($m['full_name']); ?> (<?php echo clean($m['email']); ?>)</p>
              <p><?php echo nl2br(clean($m['message'])); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
