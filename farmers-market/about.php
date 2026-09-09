<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us | Aussie Farmers Market';
$pageDescription = 'Learn about Aussie Farmers Market, an online platform connecting Australian farmers directly with local customers.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>About Aussie Farmers Market</h1>
  <p>Connecting Australian growers with the people who love good food.</p>
</div>

<section class="section">
  <div class="card" style="max-width:760px;margin:0 auto;"><div class="card-body" style="padding:34px;">
    <p>Aussie Farmers Market is an online marketplace that connects local Australian farmers directly with everyday customers. We believe fresh food tastes better and does more good when it travels a shorter distance from paddock to plate.</p>
    <p>Our platform allows registered farmers to list their fresh fruit, vegetables, eggs, dairy and honey for sale, while customers can browse, search, and order produce for delivery to their suburb. An administrator oversees the platform to keep listings accurate and the community safe.</p>
    <h2>Our Mission</h2>
    <ul>
      <li>Support Australian farming families by cutting out unnecessary middlemen.</li>
      <li>Give customers fresher produce at fairer prices.</li>
      <li>Promote transparency, privacy, and ethical data handling for everyone who uses our site.</li>
    </ul>
    <h2>How It Works</h2>
    <ol>
      <li>Farmers register an account and list their available produce.</li>
      <li>Customers browse or search for produce by category or keyword.</li>
      <li>Customers add items to their cart and check out with a delivery address.</li>
      <li>Farmers fulfil the order and our admin team monitors overall platform activity.</li>
    </ol>
  </div></div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
