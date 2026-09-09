<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);
$pageTitle = 'Page Not Found | Aussie Farmers Market';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section text-center">
  <h1>404 - Page Not Found</h1>
  <p>Sorry, the page you're looking for doesn't exist or may have moved.</p>
  <a href="<?php echo BASE_URL; ?>/index.php" class="btn">Back to Home</a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
