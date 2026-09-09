<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if (is_logged_in()) redirect('/index.php');

$errors = [];
$old = ['full_name' => '', 'email' => '', 'role' => 'customer', 'phone' => '', 'address' => '', 'suburb' => '', 'state' => 'NSW', 'postcode' => '', 'bio' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['role']      = $_POST['role'] ?? 'customer';
    $old['phone']     = trim($_POST['phone'] ?? '');
    $old['address']   = trim($_POST['address'] ?? '');
    $old['suburb']    = trim($_POST['suburb'] ?? '');
    $old['state']     = trim($_POST['state'] ?? 'NSW');
    $old['postcode']  = trim($_POST['postcode'] ?? '');
    $old['bio']       = trim($_POST['bio'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirmPassword  = $_POST['confirm_password'] ?? '';

    // ---- Server-side validation ----
    if (strlen($old['full_name']) < 2) $errors['full_name'] = 'Please enter your full name.';
    if (!is_valid_email($old['email'])) $errors['email'] = 'Please enter a valid email address.';
    if (!in_array($old['role'], ['customer', 'farmer'], true)) $errors['role'] = 'Please select a valid account type.';
    if (!is_strong_password($password)) $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
    if ($password !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match.';
    if ($old['phone'] !== '' && !is_valid_au_phone($old['phone'])) $errors['phone'] = 'Please enter a valid Australian phone number.';
    if (!is_valid_postcode($old['postcode'])) $errors['postcode'] = 'Postcode must be exactly 4 digits.';
    if (strlen($old['address']) < 3) $errors['address'] = 'Please enter your street address.';
    if (strlen($old['suburb']) < 2) $errors['suburb'] = 'Please enter your suburb.';

    // Check email uniqueness
    if (empty($errors['email'])) {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$old['email']]);
        if ($check->fetch()) $errors['email'] = 'An account with this email already exists.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone, address, suburb, state, postcode, bio)
                                  VALUES (?,?,?,?,?,?,?,?,?,?)");
        $insert->execute([
            $old['full_name'], $old['email'], $hashedPassword, $old['role'],
            $old['phone'], $old['address'], $old['suburb'], $old['state'], $old['postcode'],
            $old['role'] === 'farmer' ? $old['bio'] : null
        ]);

        set_flash('success', 'Account created successfully! Please log in.');
        redirect('/login.php');
    }
}

$pageTitle = 'Register | Aussie Farmers Market';
$pageDescription = 'Create a free account to buy fresh local produce or sell your farm products on Aussie Farmers Market.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Join Aussie Farmers Market</h1>
  <p>Create a free account to start shopping fresh produce or selling what you grow.</p>
</div>

<section class="section" style="padding-top:20px;">
  <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/register.php" data-validate novalidate>
    <?php echo csrf_field(); ?>

    <div class="form-group">
      <label for="role" class="required">I want to</label>
      <select id="role" name="role" required>
        <option value="customer" <?php echo $old['role']==='customer'?'selected':''; ?>>Buy produce (Customer)</option>
        <option value="farmer" <?php echo $old['role']==='farmer'?'selected':''; ?>>Sell produce (Farmer)</option>
      </select>
      <?php if (!empty($errors['role'])): ?><div class="error-text"><?php echo clean($errors['role']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="full_name" class="required">Full Name</label>
      <input type="text" id="full_name" name="full_name" required value="<?php echo clean($old['full_name']); ?>">
      <?php if (!empty($errors['full_name'])): ?><div class="error-text"><?php echo clean($errors['full_name']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="email" class="required">Email Address</label>
      <input type="email" id="email" name="email" required value="<?php echo clean($old['email']); ?>">
      <?php if (!empty($errors['email'])): ?><div class="error-text"><?php echo clean($errors['email']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="password" class="required">Password</label>
      <input type="password" id="password" name="password" required aria-describedby="pwHint">
      <small id="pwHint">At least 8 characters, including a letter and a number.</small>
      <?php if (!empty($errors['password'])): ?><div class="error-text"><?php echo clean($errors['password']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="confirm_password" class="required">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required>
      <?php if (!empty($errors['confirm_password'])): ?><div class="error-text"><?php echo clean($errors['confirm_password']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="tel" id="phone" name="phone" placeholder="04XX XXX XXX" value="<?php echo clean($old['phone']); ?>">
      <?php if (!empty($errors['phone'])): ?><div class="error-text"><?php echo clean($errors['phone']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="address" class="required">Street Address</label>
      <input type="text" id="address" name="address" required value="<?php echo clean($old['address']); ?>">
      <?php if (!empty($errors['address'])): ?><div class="error-text"><?php echo clean($errors['address']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="suburb" class="required">Suburb</label>
      <input type="text" id="suburb" name="suburb" required value="<?php echo clean($old['suburb']); ?>">
      <?php if (!empty($errors['suburb'])): ?><div class="error-text"><?php echo clean($errors['suburb']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="state" class="required">State</label>
      <select id="state" name="state" required>
        <?php foreach (['NSW','VIC','QLD','WA','SA','TAS','ACT','NT'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $old['state']===$s?'selected':''; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="postcode" class="required">Postcode</label>
      <input type="text" id="postcode" name="postcode" required maxlength="4" value="<?php echo clean($old['postcode']); ?>">
      <?php if (!empty($errors['postcode'])): ?><div class="error-text"><?php echo clean($errors['postcode']); ?></div><?php endif; ?>
    </div>

    <div class="form-group" id="bioGroup">
      <label for="bio">Tell us about your farm (shown on your public profile, farmers only)</label>
      <textarea id="bio" name="bio" rows="3" placeholder="E.g. how long you've been farming, what you grow, your growing practices..."><?php echo clean($old['bio']); ?></textarea>
    </div>

    <button type="submit" class="btn" style="width:100%;">Create Account</button>
    <p class="mt-2 text-center">Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Log in</a></p>
  </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
