<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$search = trim($_GET['search'] ?? '');
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$sql = "SELECT p.*, u.full_name AS farmer_name, u.suburb, c.category_name
        FROM products p
        JOIN users u ON p.farmer_id = u.user_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categoryId > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

$pageTitle = 'Shop Fresh Local Produce Online | Aussie Farmers Market';
$pageDescription = 'Browse fresh Australian fruit, vegetables, dairy, eggs, honey and herbs from local farmers. Filter by category and order online.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Shop Fresh Produce</h1>
  <p>Browse everything currently in season from our network of local Australian farmers.</p>
</div>

<section class="section">
  <form method="GET" action="<?php echo BASE_URL; ?>/products.php" role="search" aria-label="Search produce" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;max-width:800px;margin-left:auto;margin-right:auto;">
    <input type="text" name="search" placeholder="Search for tomatoes, honey, eggs..." value="<?php echo clean($search); ?>" style="flex:2;min-width:200px;" aria-label="Search produce by name">
    <select name="category" style="flex:1;min-width:150px;" aria-label="Filter by category">
      <option value="0">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['category_id']; ?>" <?php echo $categoryId === (int)$cat['category_id'] ? 'selected' : ''; ?>>
          <?php echo clean($cat['category_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Filter</button>
  </form>

  <?php if (empty($products)): ?>
    <p class="text-center">No products matched your search. Try a different keyword or category.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($products as $product): ?>
        <article class="card">
          <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($product['image']); ?>"
               alt="Photo of <?php echo clean($product['product_name']); ?>"
               onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-product.jpg'">
          <div class="card-body">
            <?php if ($product['is_organic']): ?><span class="organic-tag">Organic</span><?php endif; ?>
            <h3><?php echo clean($product['product_name']); ?></h3>
            <p><?php echo clean($product['category_name'] ?? 'General'); ?> &middot; <?php echo clean($product['suburb']); ?></p>
            <p class="price"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
            <p><?php echo $product['stock_quantity'] > 0 ? clean($product['stock_quantity']) . ' in stock' : '<span style="color:#b3261e;">Out of stock</span>'; ?></p>
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-small">View Product</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
