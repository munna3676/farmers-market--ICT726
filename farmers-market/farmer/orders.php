<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('farmer');

$stmt = $pdo->prepare("SELECT oi.*, p.product_name, o.order_date, o.status, o.delivery_suburb, o.delivery_postcode, u.full_name AS customer_name
                        FROM order_items oi
                        JOIN orders o ON oi.order_id = o.order_id
                        JOIN products p ON oi.product_id = p.product_id
                        JOIN users u ON o.customer_id = u.user_id
                        WHERE oi.farmer_id = ?
                        ORDER BY o.order_date DESC");
$stmt->execute([current_user_id()]);
$orderItems = $stmt->fetchAll();

$pageTitle = 'Orders for My Products | Farmer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Farmer navigation">
      <a href="<?php echo BASE_URL; ?>/farmer/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/farmer/add_product.php">Add New Product</a>
      <a href="<?php echo BASE_URL; ?>/farmer/orders.php" class="active">My Product Orders</a>
    </aside>

    <div class="dashboard-content">
      <h1>Orders Containing My Products</h1>
      <?php if (empty($orderItems)): ?>
        <p>No orders yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th scope="col">Order Date</th><th scope="col">Product</th><th scope="col">Qty</th><th scope="col">Customer</th><th scope="col">Deliver To</th><th scope="col">Status</th></tr></thead>
          <tbody>
          <?php foreach ($orderItems as $item): ?>
            <tr>
              <td><?php echo date('d M Y', strtotime($item['order_date'])); ?></td>
              <td><?php echo clean($item['product_name']); ?></td>
              <td><?php echo (int) $item['quantity']; ?></td>
              <td><?php echo clean($item['customer_name']); ?></td>
              <td><?php echo clean($item['delivery_suburb']); ?> <?php echo clean($item['delivery_postcode']); ?></td>
              <td><span class="status-pill status-<?php echo clean($item['status']); ?>"><?php echo ucfirst(clean($item['status'])); ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
