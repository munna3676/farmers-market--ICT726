<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$farmerId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'farmer' AND status = 'active'");
$stmt->execute([$farmerId]);
$farmer = $stmt->fetch();

if (!$farmer) {
    set_flash('error', 'That farmer profile could not be found.');
    redirect('/farmers.php');
}

$productsStmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p
                                LEFT JOIN categories c ON p.category_id = c.category_id
                                WHERE p.farmer_id = ? AND p.status = 'active'
                                ORDER BY p.created_at DESC");
$productsStmt->execute([$farmerId]);
$products = $productsStmt->fetchAll();

function initials($name) {
    $parts = explode(' ', trim($name));
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $p) { $letters .= strtoupper(substr($p, 0, 1)); }
    return $letters ?: '?';
}

$pageTitle = clean($farmer['full_name']) . ' | Aussie Farmers Market';
$pageDescription = 'Meet ' . clean($farmer['full_name']) . ' from ' . clean($farmer['suburb']) . ', ' . clean($farmer['state']) . ' and shop their fresh produce on Aussie Farmers Market.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:30px;">
  <div class="card" style="max-width:800px;margin:0 auto 40px;">
    <div class="card-body" style="text-align:center;padding:36px;">
      <div class="farmer-avatar" style="width:96px;height:96px;font-size:2.1rem;"><?php echo clean(initials($farmer['full_name'])); ?></div>
      <h1><?php echo clean($farmer['full_name']); ?></h1>
      <p class="location" style="color:var(--text-muted);"><?php echo clean($farmer['suburb']); ?>, <?php echo clean($farmer['state']); ?> <?php echo clean($farmer['postcode']); ?></p>
      <p style="max-width:560px;margin:16px auto 0;"><?php echo $farmer['bio'] ? nl2br(clean($farmer['bio'])) : 'This farmer hasn\'t shared their story yet.'; ?></p>
    </div>
  </div>

  <h2 class="section-title">Produce from <?php echo clean($farmer['full_name']); ?></h2>
  <?php if (empty($products)): ?>
    <p class="text-center">This farmer doesn't have any active listings right now.</p>
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
            <p><?php echo clean($product['category_name'] ?? 'General'); ?></p>
            <p class="price"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-small">View Product</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
