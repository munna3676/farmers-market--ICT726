<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending','confirmed','delivered','cancelled'], true)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);
        set_flash('success', 'Order status updated.');
    }
    redirect('/admin/manage_orders.php');
}

$orders = $pdo->query("SELECT o.*, u.full_name AS customer_name
                        FROM orders o
                        JOIN users u ON o.customer_id = u.user_id
                        ORDER BY o.order_date DESC")->fetchAll();

$pageTitle = 'All Orders | Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Admin navigation">
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_users.php">Manage Users</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_products.php">Manage Products</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_orders.php" class="active">All Orders</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_contacts.php">Contact Messages</a>
    </aside>

    <div class="dashboard-content">
      <h1>All Orders</h1>
      <?php if (empty($orders)): ?>
        <p>No orders placed yet.</p>
      <?php else: ?>
      <table>
        <thead><tr><th scope="col">Order #</th><th scope="col">Customer</th><th scope="col">Date</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Update</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?php echo (int) $o['order_id']; ?></td>
            <td><?php echo clean($o['customer_name']); ?></td>
            <td><?php echo date('d M Y', strtotime($o['order_date'])); ?></td>
            <td><?php echo format_price($o['total_amount']); ?></td>
            <td><span class="status-pill status-<?php echo clean($o['status']); ?>"><?php echo ucfirst(clean($o['status'])); ?></span></td>
            <td>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/manage_orders.php" style="display:flex;gap:6px;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                <select name="status" style="width:auto;">
                  <?php foreach (['pending','confirmed','delivered','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-small">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
