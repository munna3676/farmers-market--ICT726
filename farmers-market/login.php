<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if (is_logged_in()) redirect('/index.php');

$errors = [];
$email = '';

// Very basic login rate limiting per session to slow brute force attempts
if (empty($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($_SESSION['login_attempts'] >= 6) {
        $errors['general'] = 'Too many login attempts. Please wait a few minutes and try again.';
    } else {
        if (!is_valid_email($email) || $password === '') {
            $errors['general'] = 'Please enter a valid email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'suspended') {
                    $errors['general'] = 'This account has been suspended. Please contact support.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['login_attempts'] = 0;

                    set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');

                    if ($user['role'] === 'admin') redirect('/admin/dashboard.php');
                    if ($user['role'] === 'farmer') redirect('/farmer/dashboard.php');
                    redirect('/index.php');
                }
            } else {
                $_SESSION['login_attempts']++;
                $errors['general'] = 'Incorrect email or password.';
            }
        }
    }
}

$pageTitle = 'Login | Aussie Farmers Market';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Welcome Back</h1>
  <p>Log in to shop fresh produce or manage your farm listings.</p>
</div>

<section class="section" style="padding-top:20px;">
  <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/login.php" data-validate novalidate>
    <?php echo csrf_field(); ?>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error" role="alert"><?php echo clean($errors['general']); ?></div>
    <?php endif; ?>

    <div class="form-group">
      <label for="email" class="required">Email Address</label>
      <input type="email" id="email" name="email" required value="<?php echo clean($email); ?>">
    </div>

    <div class="form-group">
      <label for="password" class="required">Password</label>
      <input type="password" id="password" name="password" required>
    </div>

    <button type="submit" class="btn" style="width:100%;">Log In</button>
    <p class="mt-2 text-center">Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register here</a></p>
  </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
