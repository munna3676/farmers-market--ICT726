<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');

$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFarmers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'farmer'")->fetchColumn();
$totalCustomers= $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders")->fetchColumn();

$pageTitle = 'Admin Dashboard | Aussie Farmers Market';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Admin navigation">
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="active">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_users.php">Manage Users</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_products.php">Manage Products</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_orders.php">All Orders</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_contacts.php">Contact Messages</a>
    </aside>

    <div class="dashboard-content">
      <h1>Admin Overview</h1>
      <div class="stat-cards">
        <div class="stat-card"><div class="num"><?php echo (int) $totalUsers; ?></div><div>Total Users</div></div>
        <div class="stat-card"><div class="num"><?php echo (int) $totalFarmers; ?></div><div>Farmers</div></div>
        <div class="stat-card"><div class="num"><?php echo (int) $totalCustomers; ?></div><div>Customers</div></div>
        <div class="stat-card"><div class="num"><?php echo (int) $totalProducts; ?></div><div>Products Listed</div></div>
        <div class="stat-card"><div class="num"><?php echo (int) $totalOrders; ?></div><div>Orders Placed</div></div>
        <div class="stat-card"><div class="num"><?php echo format_price($totalRevenue); ?></div><div>Total Sales Value</div></div>
      </div>
      <p>Use the menu on the left to manage users, moderate product listings, review orders, and view contact form messages.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
