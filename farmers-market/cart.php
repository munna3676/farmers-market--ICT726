<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

require_role('customer');

// Remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    verify_csrf();
    $pid = (int) $_POST['remove_item'];
    unset($_SESSION['cart'][$pid]);
    set_flash('success', 'Item removed from cart.');
    redirect('/cart.php');
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    verify_csrf();
    foreach ($_POST['qty'] as $pid => $qty) {
        $qty = (int) $qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][(int)$pid] = $qty;
        }
    }
    set_flash('success', 'Cart updated.');
    redirect('/cart.php');
}

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (isset($products[$pid])) {
            $lineTotal = $products[$pid]['price'] * $qty;
            $total += $lineTotal;
            $cartItems[] = ['product' => $products[$pid], 'qty' => $qty, 'line_total' => $lineTotal];
        }
    }
}

$pageTitle = 'Your Cart | Aussie Farmers Market';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Your Shopping Cart</h1>
  <p>Review your items before checking out.</p>
</div>

<section class="section" style="padding-top:20px;">

  <?php if (empty($cartItems)): ?>
    <p class="text-center">Your cart is empty. <a href="<?php echo BASE_URL; ?>/products.php">Browse fresh produce</a> to get started.</p>
  <?php else: ?>
    <form method="POST" action="<?php echo BASE_URL; ?>/cart.php">
      <?php echo csrf_field(); ?>
      <table>
        <thead>
          <tr><th scope="col">Product</th><th scope="col">Price</th><th scope="col">Quantity</th><th scope="col">Subtotal</th><th scope="col">Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($cartItems as $item): ?>
          <tr>
            <td><?php echo clean($item['product']['product_name']); ?></td>
            <td><?php echo format_price($item['product']['price']); ?> / <?php echo clean($item['product']['unit']); ?></td>
            <td>
              <label class="visually-hidden" for="qty_<?php echo $item['product']['product_id']; ?>">Quantity for <?php echo clean($item['product']['product_name']); ?></label>
              <input type="number" min="0" max="<?php echo (int)$item['product']['stock_quantity']; ?>" style="width:80px;"
                     id="qty_<?php echo $item['product']['product_id']; ?>"
                     name="qty[<?php echo $item['product']['product_id']; ?>]" value="<?php echo (int) $item['qty']; ?>">
            </td>
            <td><?php echo format_price($item['line_total']); ?></td>
            <td>
              <button type="submit" formaction="<?php echo BASE_URL; ?>/cart.php" name="remove_item" value="<?php echo $item['product']['product_id']; ?>" class="btn btn-danger btn-small">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="flex-between mt-2">
        <button type="submit" name="update_qty" class="btn btn-secondary">Update Cart</button>
        <h3>Total: <?php echo format_price($total); ?></h3>
      </div>
    </form>
    <div class="text-center mt-2">
      <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
