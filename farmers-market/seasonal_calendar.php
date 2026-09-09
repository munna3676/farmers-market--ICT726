<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$monthNames = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
];

// Selected month comes from the query string; default to the current month.
$selectedMonth = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($selectedMonth < 1 || $selectedMonth > 12) {
    $selectedMonth = (int) date('n');
}

$stmt = $pdo->prepare("SELECT p.*, u.full_name AS farmer_name, u.suburb, c.category_name
                        FROM products p
                        JOIN users u ON p.farmer_id = u.user_id
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.status = 'active'
                          AND FIND_IN_SET(?, p.in_season_months)
                        ORDER BY c.category_name, p.product_name");
$stmt->execute([$selectedMonth]);
$seasonalProducts = $stmt->fetchAll();

$categoryIcons = [
    'Vegetables' => '🥕', 'Fruits' => '🍎', 'Dairy & Eggs' => '🥚',
    'Honey & Preserves' => '🍯', 'Herbs' => '🌿',
];

$pageTitle = $monthNames[$selectedMonth] . " Seasonal Produce Calendar | Aussie Farmers Market";
$pageDescription = "See what Australian fruit, vegetables, dairy and honey is in season during " . $monthNames[$selectedMonth] . ". Plan your shopping around what's freshest right now.";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Seasonal Produce Calendar</h1>
  <p>Australian growing seasons change all year round &mdash; pick a month to see what's freshest and best value.</p>
</div>

<section class="section" style="padding-top:0;">
  <nav aria-label="Choose a month" style="margin-bottom:36px;">
    <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;max-width:820px;margin:0 auto;">
      <?php foreach ($monthNames as $num => $name): ?>
        <a href="<?php echo BASE_URL; ?>/seasonal_calendar.php?month=<?php echo $num; ?>"
           class="btn <?php echo $num === $selectedMonth ? '' : 'btn-outline'; ?>"
           style="<?php echo $num === $selectedMonth ? '' : 'color:var(--green-dark);border-color:var(--green-dark);'; ?> padding:8px 16px;font-size:0.88rem;"
           aria-current="<?php echo $num === $selectedMonth ? 'true' : 'false'; ?>">
          <?php echo substr($name, 0, 3); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <h2 class="section-title"><?php echo clean($monthNames[$selectedMonth]); ?></h2>
  <p class="section-subtitle">
    <?php echo count($seasonalProducts); ?> item<?php echo count($seasonalProducts) === 1 ? '' : 's'; ?> in season this month.
  </p>

  <?php if (empty($seasonalProducts)): ?>
    <p class="text-center">Nothing marked as in-season for <?php echo clean($monthNames[$selectedMonth]); ?> yet &mdash; check back as more farmers join.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($seasonalProducts as $product): ?>
        <article class="card">
          <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($product['image']); ?>"
               alt="Photo of <?php echo clean($product['product_name']); ?>"
               onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-product.jpg'">
          <div class="card-body">
            <span class="organic-tag" style="background:var(--amber);">
              <?php echo $categoryIcons[$product['category_name']] ?? '🧺'; ?> <?php echo clean($product['category_name'] ?? 'General'); ?>
            </span>
            <h3><?php echo clean($product['product_name']); ?></h3>
            <p>From <?php echo clean($product['farmer_name']); ?> &middot; <?php echo clean($product['suburb']); ?></p>
            <p class="price"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-small">View Product</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
