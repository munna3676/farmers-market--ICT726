<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('farmer');

$productId = (int) ($_GET['id'] ?? $_POST['product_id'] ?? 0);

// Ensure the product belongs to the logged-in farmer (access control)
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND farmer_id = ?");
$stmt->execute([$productId, current_user_id()]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found or you do not have permission to edit it.');
    redirect('/farmer/dashboard.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$errors = [];
$old = $product;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['product_name']   = trim($_POST['product_name'] ?? '');
    $old['category_id']    = (int) ($_POST['category_id'] ?? 0);
    $old['description']    = trim($_POST['description'] ?? '');
    $old['price']          = trim($_POST['price'] ?? '');
    $old['unit']           = trim($_POST['unit'] ?? 'kg');
    $old['stock_quantity'] = trim($_POST['stock_quantity'] ?? '');
    $old['is_organic']     = isset($_POST['is_organic']) ? 1 : 0;
    $old['status']         = $_POST['status'] ?? 'active';

    if (strlen($old['product_name']) < 2) $errors['product_name'] = 'Please enter a product name.';
    if ($old['category_id'] <= 0) $errors['category_id'] = 'Please select a category.';
    if (strlen($old['description']) < 10) $errors['description'] = 'Please provide a longer description.';
    if (!is_numeric($old['price']) || $old['price'] <= 0) $errors['price'] = 'Please enter a valid price.';
    if (!ctype_digit((string)$old['stock_quantity']) || (int)$old['stock_quantity'] < 0) $errors['stock_quantity'] = 'Please enter a valid stock quantity.';

    $imageName = $product['image'];
    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $result = handle_product_image_upload('image');
        if ($result === false) {
            $errors['image'] = 'Please upload a valid image (jpg, png, webp) under 2MB.';
        } else {
            $imageName = $result;
        }
    }

    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE products SET category_id=?, product_name=?, description=?, price=?, unit=?, stock_quantity=?, is_organic=?, status=?, image=?
                                  WHERE product_id = ? AND farmer_id = ?");
        $update->execute([
            $old['category_id'], $old['product_name'], $old['description'], $old['price'],
            $old['unit'], $old['stock_quantity'], $old['is_organic'], $old['status'], $imageName,
            $productId, current_user_id()
        ]);
        set_flash('success', 'Product updated successfully!');
        redirect('/farmer/dashboard.php');
    }
}

$pageTitle = 'Edit Product | Farmer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Farmer navigation">
      <a href="<?php echo BASE_URL; ?>/farmer/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/farmer/add_product.php">Add New Product</a>
      <a href="<?php echo BASE_URL; ?>/farmer/orders.php">My Product Orders</a>
    </aside>

    <div class="dashboard-content">
      <h1>Edit Product</h1>
      <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/farmer/edit_product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data" data-validate novalidate style="max-width:100%;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">

        <div class="form-group">
          <label for="product_name" class="required">Product Name</label>
          <input type="text" id="product_name" name="product_name" required value="<?php echo clean($old['product_name']); ?>">
          <?php if (!empty($errors['product_name'])): ?><div class="error-text"><?php echo clean($errors['product_name']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
          <label for="category_id" class="required">Category</label>
          <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['category_id']; ?>" <?php echo $old['category_id']==$cat['category_id']?'selected':''; ?>><?php echo clean($cat['category_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="description" class="required">Description</label>
          <textarea id="description" name="description" rows="4" required><?php echo clean($old['description']); ?></textarea>
          <?php if (!empty($errors['description'])): ?><div class="error-text"><?php echo clean($errors['description']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
          <label for="price" class="required">Price (AUD)</label>
          <input type="number" step="0.01" min="0.01" id="price" name="price" required value="<?php echo clean($old['price']); ?>">
          <?php if (!empty($errors['price'])): ?><div class="error-text"><?php echo clean($errors['price']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
          <label for="unit" class="required">Unit</label>
          <input type="text" id="unit" name="unit" required value="<?php echo clean($old['unit']); ?>">
        </div>

        <div class="form-group">
          <label for="stock_quantity" class="required">Stock Quantity</label>
          <input type="number" min="0" id="stock_quantity" name="stock_quantity" required value="<?php echo clean($old['stock_quantity']); ?>">
          <?php if (!empty($errors['stock_quantity'])): ?><div class="error-text"><?php echo clean($errors['stock_quantity']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
          <label><input type="checkbox" name="is_organic" style="width:auto;display:inline;" <?php echo $old['is_organic']?'checked':''; ?>> Certified organic</label>
        </div>

        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="active" <?php echo $old['status']==='active'?'selected':''; ?>>Active (visible to customers)</option>
            <option value="inactive" <?php echo $old['status']==='inactive'?'selected':''; ?>>Inactive (hidden)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Current Image</label><br>
          <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo clean($old['image']); ?>" alt="Current product image" style="max-width:120px;border-radius:8px;">
        </div>

        <div class="form-group">
          <label for="image">Replace Image (optional)</label>
          <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
          <?php if (!empty($errors['image'])): ?><div class="error-text"><?php echo clean($errors['image']); ?></div><?php endif; ?>
        </div>

        <button type="submit" class="btn">Update Product</button>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
