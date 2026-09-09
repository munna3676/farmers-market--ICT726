<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Aussie Farmers Market | Buy Fresh Local Produce Online in Australia';
$pageDescription = 'Shop fresh, farm-direct fruit, vegetables, eggs and honey from Australian farmers. Order online and support local growers near you.';

// Handle newsletter signup form (validation + duplicate check)
$newsletterError = '';
$newsletterEmailOld = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    verify_csrf();
    $newsletterEmailOld = trim($_POST['newsletter_email']);

    if (!is_valid_email($newsletterEmailOld)) {
        $newsletterError = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->execute([$newsletterEmailOld]);
            set_flash('success', "Thanks for subscribing! We'll send fresh updates to $newsletterEmailOld.");
            redirect('/index.php#newsletter');
        } catch (PDOException $e) {
            // Unique constraint violation = already subscribed
            if ($e->getCode() === '23000') {
                $newsletterError = 'That email is already subscribed — thanks for being with us!';
            } else {
                $newsletterError = 'Something went wrong. Please try again.';
            }
        }
    }
}

// Fetch a handful of featured (active, in-stock) products
$stmt = $pdo->prepare("SELECT p.*, u.full_name AS farmer_name, u.suburb, c.category_name
                        FROM products p
                        JOIN users u ON p.farmer_id = u.user_id
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.status = 'active' AND p.stock_quantity > 0
                        ORDER BY p.created_at DESC
                        LIMIT 8");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

$categories = $pdo->query("SELECT c.*, COUNT(p.product_id) AS product_count
                            FROM categories c
                            LEFT JOIN products p ON p.category_id = c.category_id AND p.status='active'
                            GROUP BY c.category_id
                            ORDER BY c.category_name")->fetchAll();

$categoryIcons = [
    'Vegetables' => '🥕', 'Fruits' => '🍎', 'Dairy & Eggs' => '🥚',
    'Honey & Preserves' => '🍯', 'Herbs' => '🌿',
];

$farmerCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='farmer' AND status='active'")->fetchColumn();
$productCount = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
$orderCount = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Seasonal Produce Highlight — products in season for the current month
$currentMonth = (int) date('n');
$currentMonthName = date('F');
$stmt = $pdo->prepare("SELECT p.*, u.full_name AS farmer_name, u.suburb, c.category_name
                        FROM products p
                        JOIN users u ON p.farmer_id = u.user_id
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.status = 'active' AND p.stock_quantity > 0
                          AND FIND_IN_SET(?, p.in_season_months)
                        ORDER BY p.created_at DESC
                        LIMIT 4");
$stmt->execute([$currentMonth]);
$seasonalProducts = $stmt->fetchAll();

// Static testimonials (no personal data collected — for display only)
$testimonials = [
    ['name' => 'Priya M.', 'suburb' => 'Parramatta, NSW', 'quote' => "The tomatoes taste like they actually grew in soil, not a warehouse. I've stopped buying produce from the supermarket entirely.", 'rating' => 5],
    ['name' => 'Daniel K.', 'suburb' => 'Geelong, VIC', 'quote' => 'Ordering is simple and I love knowing exactly which farm my eggs and honey came from. Delivery has been reliable every time.', 'rating' => 5],
    ['name' => 'Aroha T.', 'suburb' => 'Toowoomba, QLD', 'quote' => "As a home cook, having a seasonal calendar to plan around has genuinely changed how I shop. Fresher food, less waste.", 'rating' => 4],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <span class="eyebrow">🇦🇺 100% Australian Grown</span>
  <h1>Fresh, Local, Farm-Direct Produce</h1>
  <p>Buy fruit, vegetables, eggs and honey straight from Australian farmers near you. Fresher food, fairer prices, fewer food miles.</p>
  <div class="hero-actions">
    <a href="<?php echo BASE_URL; ?>/products.php" class="btn">Shop Fresh Produce</a>
    <a href="<?php echo BASE_URL; ?>/farmers.php" class="btn btn-outline">Meet Our Farmers</a>
  </div>
</section>

<section class="section" style="padding-top:36px;padding-bottom:36px;">
  <div class="trust-strip">
    <div class="item"><span class="big"><?php echo $farmerCount; ?>+</span><span class="label">Local Farmers</span></div>
    <div class="item"><span class="big"><?php echo $productCount; ?>+</span><span class="label">Fresh Products</span></div>
    <div class="item"><span class="big"><?php echo $orderCount; ?>+</span><span class="label">Orders Delivered</span></div>
    <div class="item"><span class="big">0%</span><span class="label">Data Sold to Ads</span></div>
  </div>
</section>

<section class="section" aria-labelledby="category-heading">
  <h2 id="category-heading" class="section-title">Shop by Category</h2>
  <p class="section-subtitle">From vine-ripened vegetables to raw honey &mdash; find exactly what you're after.</p>
  <div class="category-grid">
    <?php foreach ($categories as $cat): ?>
      <a href="<?php echo BASE_URL; ?>/products.php?category=<?php echo $cat['category_id']; ?>" class="category-card">
        <span class="icon" aria-hidden="true"><?php echo $categoryIcons[$cat['category_name']] ?? '🧺'; ?></span>
        <span class="name"><?php echo clean($cat['category_name']); ?></span>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;"><?php echo (int) $cat['product_count']; ?> items</div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section" aria-labelledby="featured-heading">
  <h2 id="featured-heading" class="section-title">This Week's Fresh Picks</h2>
  <p class="section-subtitle">Freshly listed by our farmers &mdash; grab them before they sell out.</p>

  <?php if (empty($featuredProducts)): ?>
    <p class="text-center">No products available right now &mdash; please check back soon!</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($featuredProducts as $product): ?>
        <article class="card">
          <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($product['image']); ?>"
               alt="Photo of <?php echo clean($product['product_name']); ?> from <?php echo clean($product['farmer_name']); ?>'s farm"
               onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-product.jpg'">
          <div class="card-body">
            <?php if ($product['is_organic']): ?><span class="organic-tag">Organic</span><?php endif; ?>
            <h3><?php echo clean($product['product_name']); ?></h3>
            <p>From <?php echo clean($product['farmer_name']); ?> &middot; <?php echo clean($product['suburb']); ?></p>
            <p class="price"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-small">View Product</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-2"><a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-secondary">View All Produce</a></div>
  <?php endif; ?>
</section>

<?php if (!empty($seasonalProducts)): ?>
<section class="section" aria-labelledby="seasonal-heading">
  <div class="section-tinted" style="padding:50px 30px;">
    <h2 id="seasonal-heading" class="section-title">In Season This <?php echo clean($currentMonthName); ?></h2>
    <p class="section-subtitle">Produce at its best right now, picked to match Australia's growing seasons.</p>
    <div class="grid">
      <?php foreach ($seasonalProducts as $product): ?>
        <article class="card">
          <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($product['image']); ?>"
               alt="Photo of <?php echo clean($product['product_name']); ?> from <?php echo clean($product['farmer_name']); ?>'s farm"
               onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-product.jpg'">
          <div class="card-body">
            <span class="organic-tag" style="background:var(--amber);">In Season</span>
            <h3><?php echo clean($product['product_name']); ?></h3>
            <p>From <?php echo clean($product['farmer_name']); ?> &middot; <?php echo clean($product['suburb']); ?></p>
            <p class="price"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo (int) $product['product_id']; ?>" class="btn btn-small">View Product</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-2"><a href="<?php echo BASE_URL; ?>/seasonal_calendar.php" class="btn btn-outline" style="color:var(--green-dark);border-color:var(--green-dark);">See the Full Seasonal Calendar</a></div>
  </div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="why-heading">
  <div class="section-tinted" style="padding:50px 30px;">
    <h2 id="why-heading" class="section-title">Why Buy From Aussie Farmers Market?</h2>
    <div class="grid">
      <div class="card" style="box-shadow:none;border:none;"><div class="card-body">
        <div class="feature-icon">🤝</div>
        <h3>Support Local Growers</h3>
        <p>Every purchase goes directly to Australian farming families, not a supermarket middleman.</p>
      </div></div>
      <div class="card" style="box-shadow:none;border:none;"><div class="card-body">
        <div class="feature-icon">🚚</div>
        <h3>Fresher, Fewer Food Miles</h3>
        <p>Produce is listed by the farmers who grow it, so it reaches you faster and fresher.</p>
      </div></div>
      <div class="card" style="box-shadow:none;border:none;"><div class="card-body">
        <div class="feature-icon">🔒</div>
        <h3>Transparent & Secure</h3>
        <p>Your data is protected with secure logins and we never sell your details to third parties.</p>
      </div></div>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="testimonials-heading">
  <h2 id="testimonials-heading" class="section-title">What Our Customers Say</h2>
  <p class="section-subtitle">Real feedback from people who shop with us every week.</p>
  <div class="grid">
    <?php foreach ($testimonials as $t): ?>
      <div class="card" style="box-shadow:none;">
        <div class="card-body">
          <div aria-hidden="true" style="color:var(--amber);font-size:1.1rem;margin-bottom:8px;">
            <?php echo str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']); ?>
          </div>
          <p style="font-style:italic;color:var(--text-dark);">&ldquo;<?php echo clean($t['quote']); ?>&rdquo;</p>
          <p style="margin-top:14px;font-weight:600;color:var(--green-darker);margin-bottom:0;">
            <?php echo clean($t['name']); ?> <span style="font-weight:400;color:var(--text-muted);">&middot; <?php echo clean($t['suburb']); ?></span>
          </p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section" id="newsletter" aria-labelledby="newsletter-heading">
  <div class="section-tinted" style="padding:50px 30px;text-align:center;">
    <h2 id="newsletter-heading" class="section-title">Get Fresh Picks in Your Inbox</h2>
    <p class="section-subtitle">Join our newsletter for seasonal highlights and new farmer listings. No spam, unsubscribe anytime.</p>
    <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/index.php#newsletter" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;max-width:480px;margin:0 auto;box-shadow:none;background:transparent;padding:0;" data-validate novalidate>
      <?php echo csrf_field(); ?>
      <div class="form-group" style="flex:1;min-width:220px;margin-bottom:0;text-align:left;">
        <label for="newsletter_email" class="required" style="position:absolute;left:-9999px;">Email address</label>
        <input type="email" id="newsletter_email" name="newsletter_email" placeholder="you@example.com" required
               value="<?php echo clean($newsletterEmailOld); ?>">
        <?php if ($newsletterError): ?><div class="error-text"><?php echo clean($newsletterError); ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn" style="flex-shrink:0;">Subscribe</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
