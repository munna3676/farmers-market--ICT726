<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $productId = (int) ($_POST['product_id'] ?? 0);

    if (isset($_POST['toggle_status'])) {
        $stmt = $pdo->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE product_id = ?");
        $stmt->execute([$productId]);
        set_flash('success', 'Product status updated.');
    } elseif (isset($_POST['delete_product'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        set_flash('success', 'Product deleted.');
    }
    redirect('/admin/manage_products.php');
}

$products = $pdo->query("SELECT p.*, u.full_name AS farmer_name, c.category_name
                          FROM products p
                          JOIN users u ON p.farmer_id = u.user_id
                          LEFT JOIN categories c ON p.category_id = c.category_id
                          ORDER BY p.created_at DESC")->fetchAll();

$pageTitle = 'Manage Products | Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Admin navigation">
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_users.php">Manage Users</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_products.php" class="active">Manage Products</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_orders.php">All Orders</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_contacts.php">Contact Messages</a>
    </aside>

    <div class="dashboard-content">
      <h1>Manage Products</h1>
      <table>
        <thead><tr><th scope="col">Product</th><th scope="col">Farmer</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?php echo clean($p['product_name']); ?></td>
            <td><?php echo clean($p['farmer_name']); ?></td>
            <td><?php echo clean($p['category_name'] ?? 'General'); ?></td>
            <td><?php echo format_price($p['price']); ?></td>
            <td><?php echo (int) $p['stock_quantity']; ?></td>
            <td><?php echo ucfirst(clean($p['status'])); ?></td>
            <td>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/manage_products.php" style="display:inline;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                <button type="submit" name="toggle_status" class="btn btn-small btn-secondary">
                  <?php echo $p['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                </button>
              </form>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/manage_products.php" style="display:inline;" onsubmit="return confirm('Delete this product permanently?');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                <button type="submit" name="delete_product" class="btn btn-small btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
