<?php
/**
 * Shared header. Expects optional $pageTitle and $pageDescription
 * to be set before including this file (for SEO).
 */
if (!isset($pageTitle)) $pageTitle = 'Aussie Farmers Market - Fresh Local Produce Delivered';
if (!isset($pageDescription)) $pageDescription = 'Buy fresh, local, farm-direct fruit, vegetables, eggs and honey from Australian farmers. Support local growers and reduce food miles.';
?>
<!DOCTYPE html>
<html lang="en-AU">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo clean($pageTitle); ?></title>
<meta name="description" content="<?php echo clean($pageDescription); ?>">
<meta name="keywords" content="Australian farmers market, buy local produce online, fresh fruit and vegetables Australia, farm direct, organic produce Australia, support local farmers">
<meta name="robots" content="index, follow">
<meta name="author" content="Aussie Farmers Market">
<!-- Open Graph tags for social sharing / SEO -->
<meta property="og:title" content="<?php echo clean($pageTitle); ?>">
<meta property="og:description" content="<?php echo clean($pageDescription); ?>">
<meta property="og:type" content="website">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>

<header class="site-header">
  <div class="navbar">
    <a href="<?php echo BASE_URL; ?>/index.php" class="logo" aria-label="Aussie Farmers Market home"><span class="leaf-icon" aria-hidden="true">🌿</span>Aussie <span>Farmers</span> Market</a>
    <nav aria-label="Main navigation">
      <ul>
        <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li><a href="<?php echo BASE_URL; ?>/products.php">Shop Produce</a></li>
        <li><a href="<?php echo BASE_URL; ?>/seasonal_calendar.php">Seasonal Calendar</a></li>
        <li><a href="<?php echo BASE_URL; ?>/farmers.php">Our Farmers</a></li>
        <li><a href="<?php echo BASE_URL; ?>/about.php">About</a></li>
        <li><a href="<?php echo BASE_URL; ?>/faq.php">FAQ</a></li>
        <li><a href="<?php echo BASE_URL; ?>/contact.php">Contact</a></li>
        <?php if (is_logged_in()): ?>
            <?php if (current_role() === 'customer'): ?>
                <li><a href="<?php echo BASE_URL; ?>/cart.php">🛒 Cart<?php if (!empty($_SESSION['cart']) && count($_SESSION['cart'])): ?> (<?php echo count($_SESSION['cart']); ?>)<?php endif; ?></a></li>
                <li><a href="<?php echo BASE_URL; ?>/orders.php">My Orders</a></li>
            <?php elseif (current_role() === 'farmer'): ?>
                <li><a href="<?php echo BASE_URL; ?>/farmer/dashboard.php">Farmer Dashboard</a></li>
            <?php elseif (current_role() === 'admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
            <?php endif; ?>
          <li><a href="<?php echo BASE_URL; ?>/logout.php">Logout <span class="badge-role"><?php echo clean(current_role()); ?></span></a></li>
        <?php else: ?>
          <li><a href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
          <li><a href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="main-content">
<div class="container">
<?php $flash = get_flash(); if ($flash): ?>
  <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>" role="alert">
    <?php echo clean($flash['message']); ?>
  </div>
<?php endif; ?>
