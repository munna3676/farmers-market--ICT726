<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('farmer');
$farmerId = current_user_id();

$productCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE farmer_id = ?");
$productCount->execute([$farmerId]);
$totalProducts = $productCount->fetchColumn();

$orderCount = $pdo->prepare("SELECT COUNT(DISTINCT order_id) FROM order_items WHERE farmer_id = ?");
$orderCount->execute([$farmerId]);
$totalOrders = $orderCount->fetchColumn();

$revenue = $pdo->prepare("SELECT COALESCE(SUM(quantity * price_at_purchase),0) FROM order_items WHERE farmer_id = ?");
$revenue->execute([$farmerId]);
$totalRevenue = $revenue->fetchColumn();

$products = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE farmer_id = ? ORDER BY created_at DESC");
$products->execute([$farmerId]);
$products = $products->fetchAll();

$pageTitle = 'Farmer Dashboard | Aussie Farmers Market';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Farmer navigation">
      <a href="<?php echo BASE_URL; ?>/farmer/dashboard.php" class="active">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/farmer/add_product.php">Add New Product</a>
      <a href="<?php echo BASE_URL; ?>/farmer/orders.php">My Product Orders</a>
    </aside>

    <div class="dashboard-content">
      <h1>Welcome, <?php echo clean($_SESSION['full_name']); ?></h1>

      <div class="stat-cards">
        <div class="stat-card"><div class="num"><?php echo (int) $totalProducts; ?></div><div>Products Listed</div></div>
        <div class="stat-card"><div class="num"><?php echo (int) $totalOrders; ?></div><div>Orders Received</div></div>
        <div class="stat-card"><div class="num"><?php echo format_price($totalRevenue); ?></div><div>Total Sales</div></div>
      </div>

      <div class="flex-between mb-2">
        <h2>My Products</h2>
        <a href="<?php echo BASE_URL; ?>/farmer/add_product.php" class="btn">+ Add Product</a>
      </div>

      <?php if (empty($products)): ?>
        <p>You haven't listed any products yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th scope="col">Product</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?php echo clean($p['product_name']); ?></td>
              <td><?php echo clean($p['category_name'] ?? 'General'); ?></td>
              <td><?php echo format_price($p['price']); ?> / <?php echo clean($p['unit']); ?></td>
              <td><?php echo (int) $p['stock_quantity']; ?></td>
              <td><?php echo ucfirst(clean($p['status'])); ?></td>
              <td>
                <a href="<?php echo BASE_URL; ?>/farmer/edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                <form method="POST" action="<?php echo BASE_URL; ?>/farmer/delete_product.php" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                  <button type="submit" class="btn btn-small btn-danger">Delete</button>
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
