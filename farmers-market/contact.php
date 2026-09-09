<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$errors = [];
$old = ['full_name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['subject']   = trim($_POST['subject'] ?? '');
    $old['message']   = trim($_POST['message'] ?? '');

    if (strlen($old['full_name']) < 2) $errors['full_name'] = 'Please enter your name.';
    if (!is_valid_email($old['email'])) $errors['email'] = 'Please enter a valid email address.';
    if (strlen($old['subject']) < 3) $errors['subject'] = 'Please enter a subject.';
    if (strlen($old['message']) < 10) $errors['message'] = 'Message should be at least 10 characters.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?,?,?,?)");
        $stmt->execute([$old['full_name'], $old['email'], $old['subject'], $old['message']]);
        set_flash('success', 'Thanks for reaching out! We will get back to you soon.');
        redirect('/contact.php');
    }
}

$pageTitle = 'Contact Us | Aussie Farmers Market';
$pageDescription = 'Get in touch with the Aussie Farmers Market team for questions about orders, selling produce, or general enquiries.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Contact Us</h1>
  <p>Questions about an order, selling produce, or anything else? We'd love to hear from you.</p>
</div>

<section class="section">
  <form class="form-box" method="POST" action="<?php echo BASE_URL; ?>/contact.php" data-validate novalidate>
    <?php echo csrf_field(); ?>

    <div class="form-group">
      <label for="full_name" class="required">Your Name</label>
      <input type="text" id="full_name" name="full_name" required value="<?php echo clean($old['full_name']); ?>">
      <?php if (!empty($errors['full_name'])): ?><div class="error-text"><?php echo clean($errors['full_name']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="email" class="required">Email Address</label>
      <input type="email" id="email" name="email" required value="<?php echo clean($old['email']); ?>">
      <?php if (!empty($errors['email'])): ?><div class="error-text"><?php echo clean($errors['email']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="subject" class="required">Subject</label>
      <input type="text" id="subject" name="subject" required value="<?php echo clean($old['subject']); ?>">
      <?php if (!empty($errors['subject'])): ?><div class="error-text"><?php echo clean($errors['subject']); ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label for="message" class="required">Message</label>
      <textarea id="message" name="message" rows="5" required><?php echo clean($old['message']); ?></textarea>
      <?php if (!empty($errors['message'])): ?><div class="error-text"><?php echo clean($errors['message']); ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn" style="width:100%;">Send Message</button>
  </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
