<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

require_role('customer');

$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$stmt->execute([current_user_id()]);
$orders = $stmt->fetchAll();

$itemsStmt = $pdo->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");

$pageTitle = 'My Orders | Aussie Farmers Market';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>My Orders</h1>
  <p>Track the status of everything you've ordered.</p>
</div>

<section class="section" style="padding-top:20px;">

  <?php if (empty($orders)): ?>
    <p class="text-center">You haven't placed any orders yet. <a href="<?php echo BASE_URL; ?>/products.php">Start shopping</a>.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php $itemsStmt->execute([$order['order_id']]); $items = $itemsStmt->fetchAll(); ?>
      <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
          <div class="flex-between">
            <h3>Order #<?php echo (int) $order['order_id']; ?></h3>
            <span class="status-pill status-<?php echo clean($order['status']); ?>"><?php echo ucfirst(clean($order['status'])); ?></span>
          </div>
          <p>Placed on <?php echo date('d M Y, g:i a', strtotime($order['order_date'])); ?></p>
          <p>Deliver to: <?php echo clean($order['delivery_address']); ?>, <?php echo clean($order['delivery_suburb']); ?> <?php echo clean($order['delivery_postcode']); ?></p>
          <table>
            <thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Price</th></tr></thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><?php echo clean($item['product_name']); ?></td>
                  <td><?php echo (int) $item['quantity']; ?></td>
                  <td><?php echo format_price($item['price_at_purchase']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <p class="price mt-2">Total: <?php echo format_price($order['total_amount']); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
