<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$productId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, u.full_name AS farmer_name, u.suburb, u.state, c.category_name
                        FROM products p
                        JOIN users u ON p.farmer_id = u.user_id
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.product_id = ? AND p.status = 'active'");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    set_flash('error', 'That product could not be found.');
    redirect('/products.php');
}

// Handle "Add to cart"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    verify_csrf();

    if (!is_logged_in() || current_role() !== 'customer') {
        set_flash('error', 'Please log in as a customer to add items to your cart.');
        redirect('/login.php');
    }

    $qty = (int) ($_POST['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;
    if ($qty > $product['stock_quantity']) {
        set_flash('error', 'Sorry, only ' . $product['stock_quantity'] . ' ' . $product['unit'] . ' available.');
    } else {
        if (empty($_SESSION['cart'])) $_SESSION['cart'] = [];
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        set_flash('success', clean($product['product_name']) . ' added to your cart.');
    }
    redirect('/product.php?id=' . $productId);
}

$pageTitle = clean($product['product_name']) . ' | Aussie Farmers Market';
$pageDescription = 'Buy ' . clean($product['product_name']) . ' fresh from ' . clean($product['farmer_name']) . ' in ' . clean($product['suburb']) . ', ' . clean($product['state']) . '. Order online today.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="grid" style="grid-template-columns: 1fr 1fr;">
    <div>
      <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($product['image']); ?>"
           alt="Photo of <?php echo clean($product['product_name']); ?>"
           style="width:100%;border-radius:10px;box-shadow:var(--shadow);"
           onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-product.jpg'">
    </div>
    <div>
      <?php if ($product['is_organic']): ?><span class="organic-tag">Organic</span><?php endif; ?>
      <h1><?php echo clean($product['product_name']); ?></h1>
      <p>Category: <?php echo clean($product['category_name'] ?? 'General'); ?></p>
      <p>Grown by <strong><?php echo clean($product['farmer_name']); ?></strong> in <?php echo clean($product['suburb']); ?>, <?php echo clean($product['state']); ?></p>
      <p class="price" style="font-size:1.5rem;"><?php echo format_price($product['price']); ?> / <?php echo clean($product['unit']); ?></p>
      <p><?php echo nl2br(clean($product['description'])); ?></p>
      <p><?php echo $product['stock_quantity'] > 0 ? clean($product['stock_quantity']) . ' ' . clean($product['unit']) . '(s) in stock' : '<strong style="color:#b3261e;">Currently out of stock</strong>'; ?></p>

      <?php if ($product['stock_quantity'] > 0): ?>
      <form method="POST" action="<?php echo BASE_URL; ?>/product.php?id=<?php echo $productId; ?>" data-validate novalidate style="max-width:260px;">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="quantity" class="required">Quantity (<?php echo clean($product['unit']); ?>)</label>
          <input type="number" id="quantity" name="quantity" min="1" max="<?php echo (int) $product['stock_quantity']; ?>" value="1" required>
        </div>
        <button type="submit" name="add_to_cart" class="btn" style="width:100%;">Add to Cart</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
