<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$farmers = $pdo->query("SELECT u.*, COUNT(p.product_id) AS product_count
                         FROM users u
                         LEFT JOIN products p ON p.farmer_id = u.user_id AND p.status = 'active'
                         WHERE u.role = 'farmer' AND u.status = 'active'
                         GROUP BY u.user_id
                         ORDER BY u.full_name")->fetchAll();

$pageTitle = 'Meet Our Farmers | Aussie Farmers Market';
$pageDescription = 'Meet the local Australian farmers behind Aussie Farmers Market and browse the fresh produce each one grows.';
require_once __DIR__ . '/includes/header.php';

function initials($name) {
    $parts = explode(' ', trim($name));
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $p) { $letters .= strtoupper(substr($p, 0, 1)); }
    return $letters ?: '?';
}

// Plain substr-based truncate (avoids depending on the mbstring extension,
// which isn't guaranteed to be enabled on free hosting plans)
function truncate_text($text, $length = 110) {
    $text = trim($text);
    if (strlen($text) <= $length) return $text;
    return rtrim(substr($text, 0, $length)) . '...';
}
?>

<div class="page-header">
  <h1>Meet Our Farmers</h1>
  <p>Every product on Aussie Farmers Market is grown by a real Australian farmer. Get to know the people behind your food.</p>
</div>

<section class="section">
  <?php if (empty($farmers)): ?>
    <p class="text-center">No farmers have joined yet &mdash; <a href="<?php echo BASE_URL; ?>/register.php">be the first to sign up</a>!</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($farmers as $f): ?>
        <div class="farmer-card">
          <div class="farmer-avatar"><?php echo clean(initials($f['full_name'])); ?></div>
          <h3><?php echo clean($f['full_name']); ?></h3>
          <p class="location"><?php echo clean($f['suburb']); ?>, <?php echo clean($f['state']); ?></p>
          <p style="font-size:0.9rem;color:var(--text-muted);min-height:40px;">
            <?php echo $f['bio'] ? clean(truncate_text($f['bio'], 110)) : 'This farmer hasn\'t added a bio yet.'; ?>
          </p>
          <p style="font-size:0.85rem;margin-bottom:14px;"><?php echo (int) $f['product_count']; ?> product<?php echo $f['product_count']==1?'':'s'; ?> listed</p>
          <a href="<?php echo BASE_URL; ?>/farmer_profile.php?id=<?php echo (int) $f['user_id']; ?>" class="btn btn-small">View Farm & Produce</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
