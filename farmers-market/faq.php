<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Frequently Asked Questions | Aussie Farmers Market';
$pageDescription = 'Answers to common questions about ordering, delivery, selling produce, and account management on Aussie Farmers Market.';
require_once __DIR__ . '/includes/header.php';

$faqs = [
    ['q' => 'How do I order fresh produce?', 'a' => 'Browse or search for products on the Shop Produce page, add items to your cart, then head to checkout and confirm your delivery address. You will need a free customer account to complete an order.'],
    ['q' => 'How do I become a farmer and sell my produce?', 'a' => 'Click Register and choose "Sell produce (Farmer)" when creating your account. Once registered, go to your Farmer Dashboard and use "Add New Product" to list your first item.'],
    ['q' => 'Is my payment information stored on this site?', 'a' => 'No. This is a student demonstration project, so checkout is set to "Cash on Delivery" and no real payment details are collected or stored.'],
    ['q' => 'How is my personal information protected?', 'a' => 'Passwords are hashed (never stored as plain text), and access to farmer/admin areas is restricted using role-based access control. See our Privacy Notice for full details.'],
    ['q' => 'Can I track the status of my order?', 'a' => 'Yes. Log in and visit "My Orders" to see the status of each order (pending, confirmed, delivered, or cancelled) as it is updated.'],
    ['q' => 'What if a product I want is out of stock?', 'a' => 'Out-of-stock items are clearly marked and cannot be added to your cart. Check back later, as farmers regularly update their stock levels.'],
    ['q' => 'How do I edit or remove a product I listed?', 'a' => 'From your Farmer Dashboard, click "Edit" next to any product to update its details, or "Delete" to remove it permanently.'],
];
?>

<div class="page-header">
  <h1>Frequently Asked Questions</h1>
  <p>Can't find what you're looking for? <a href="<?php echo BASE_URL; ?>/contact.php">Get in touch with us</a>.</p>
</div>

<section class="section" style="max-width:750px;margin:0 auto;">
  <?php foreach ($faqs as $faq): ?>
    <details class="faq-item">
      <summary><?php echo clean($faq['q']); ?></summary>
      <p><?php echo clean($faq['a']); ?></p>
    </details>
  <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
