<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Notice | Aussie Farmers Market';
$pageDescription = 'Read how Aussie Farmers Market collects, uses, and protects your personal information.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Privacy Notice</h1>
  <p>How we collect, use, and protect your personal information.</p>
</div>

<section class="section">
  <div class="card" style="max-width:760px;margin:0 auto;"><div class="card-body" style="padding:34px;">
    <p>This notice explains, in plain language, how Aussie Farmers Market collects, uses, and protects your personal information. This platform is a student project built for educational purposes.</p>

    <h2>What We Collect</h2>
    <ul>
      <li>Account details: full name, email address, phone number, and delivery address.</li>
      <li>Order information: items purchased, quantities, and delivery details.</li>
      <li>Messages you send us through the Contact Us form.</li>
    </ul>

    <h2>How We Use Your Information</h2>
    <ul>
      <li>To create and manage your account and verify who is logging in.</li>
      <li>To process and deliver your orders, and let farmers know what to prepare.</li>
      <li>To respond to enquiries submitted through our contact form.</li>
    </ul>

    <h2>How We Protect Your Information</h2>
    <ul>
      <li>Passwords are never stored in plain text &mdash; they are hashed using industry-standard one-way hashing before being saved.</li>
      <li>Access to farmer and admin areas is restricted using role-based access control, so customers cannot view farmer or admin pages, and vice versa.</li>
      <li>All forms validate and sanitise input on both the browser and the server to prevent malicious data from being stored or displayed.</li>
      <li>Session data is protected and destroyed when you log out.</li>
    </ul>

    <h2>Sharing of Information</h2>
    <p>We do not sell or share your personal information with third-party advertisers. Your delivery details are only shared with the farmer fulfilling your specific order.</p>

    <h2>Your Rights</h2>
    <p>You may contact us at any time via the <a href="<?php echo BASE_URL; ?>/contact.php">Contact Us</a> page to request that your account and associated data be corrected or deleted.</p>
  </div></div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
