<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

require_role('customer');

if (empty($_SESSION['cart'])) {
    set_flash('error', 'Your cart is empty.');
    redirect('/products.php');
}

// Pre-fill delivery details from the user's profile
$userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->execute([current_user_id()]);
$user = $userStmt->fetch();

$errors = [];
$old = [
    'delivery_address'  => $user['address'],
    'delivery_suburb'   => $user['suburb'],
    'delivery_postcode' => $user['postcode'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['delivery_address']  = trim($_POST['delivery_address'] ?? '');
    $old['delivery_suburb']   = trim($_POST['delivery_suburb'] ?? '');
    $old['delivery_postcode'] = trim($_POST['delivery_postcode'] ?? '');

    if (strlen($old['delivery_address']) < 3) $errors['delivery_address'] = 'Please enter a valid delivery address.';
    if (strlen($old['delivery_suburb']) < 2) $errors['delivery_suburb'] = 'Please enter a valid suburb.';
    if (!is_valid_postcode($old['delivery_postcode'])) $errors['delivery_postcode'] = 'Postcode must be exactly 4 digits.';

    // Re-validate stock at time of order (avoid overselling)
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

    $total = 0;
    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (!isset($products[$pid]) || $products[$pid]['stock_quantity'] < $qty) {
            $errors['general'] = 'Some items in your cart are no longer available in the requested quantity. Please review your cart.';
            break;
        }
        $total += $products[$pid]['price'] * $qty;
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("INSERT INTO orders (customer_id, delivery_address, delivery_suburb, delivery_postcode, total_amount, status)
                                         VALUES (?,?,?,?,?, 'pending')");
            $orderStmt->execute([current_user_id(), $old['delivery_address'], $old['delivery_suburb'], $old['delivery_postcode'], $total]);
            $orderId = $pdo->lastInsertId();

            $itemStmt  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, farmer_id, quantity, price_at_purchase) VALUES (?,?,?,?,?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

            foreach ($_SESSION['cart'] as $pid => $qty) {
                $itemStmt->execute([$orderId, $pid, $products[$pid]['farmer_id'], $qty, $products[$pid]['price']]);
                $stockStmt->execute([$qty, $pid]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            set_flash('success', 'Your order has been placed! Order #' . $orderId);
            redirect('/orders.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout | Aussie Farmers Market';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Checkout</h1>
  <p>Confirm your delivery details to place your order.</p>
</div>

<section class="section" style="padding-top:20px;">

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error" role="alert"><?php echo clean($errors['general']); ?></div>
  <?php endif; ?>

  <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/checkout.php" data-validate novalidate>
    <?php echo csrf_field(); ?>

    <div class="form-group">
      <label for="delivery_address" class="required">Delivery Address</label>
      <input type="text" id="delivery_address" name="delivery_address" required value="<?php echo clean($old['delivery_address']); ?>">
      <?php if (!empty($errors['delivery_address'])): ?><div class="error-text"><?php echo clean($errors['delivery_address']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="delivery_suburb" class="required">Suburb</label>
      <input type="text" id="delivery_suburb" name="delivery_suburb" required value="<?php echo clean($old['delivery_suburb']); ?>">
      <?php if (!empty($errors['delivery_suburb'])): ?><div class="error-text"><?php echo clean($errors['delivery_suburb']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="delivery_postcode" class="required">Postcode</label>
      <input type="text" id="delivery_postcode" name="delivery_postcode" required maxlength="4" value="<?php echo clean($old['delivery_postcode']); ?>">
      <?php if (!empty($errors['delivery_postcode'])): ?><div class="error-text"><?php echo clean($errors['delivery_postcode']); ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn" style="width:100%;">Place Order (Cash on Delivery)</button>
    <p class="mt-2" style="font-size:0.85rem;color:#666;">This is a class assignment demo &mdash; no real payment is processed.</p>
  </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
