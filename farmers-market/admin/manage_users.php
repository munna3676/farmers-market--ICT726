<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = (int) ($_POST['user_id'] ?? 0);

    // Prevent an admin from suspending/deleting their own account by accident
    if ($userId === current_user_id()) {
        set_flash('error', 'You cannot modify your own admin account here.');
        redirect('/admin/manage_users.php');
    }

    if (isset($_POST['toggle_status'])) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','suspended','active') WHERE user_id = ?");
        $stmt->execute([$userId]);
        set_flash('success', 'User status updated.');
    } elseif (isset($_POST['delete_user'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        set_flash('success', 'User deleted.');
    }
    redirect('/admin/manage_users.php');
}

$roleFilter = $_GET['role'] ?? '';
$sql = "SELECT * FROM users";
$params = [];
if (in_array($roleFilter, ['admin','farmer','customer'], true)) {
    $sql .= " WHERE role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users | Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="dashboard-wrap">
    <aside class="sidebar" aria-label="Admin navigation">
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_users.php" class="active">Manage Users</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_products.php">Manage Products</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_orders.php">All Orders</a>
      <a href="<?php echo BASE_URL; ?>/admin/manage_contacts.php">Contact Messages</a>
    </aside>

    <div class="dashboard-content">
      <h1>Manage Users</h1>

      <form method="GET" action="<?php echo BASE_URL; ?>/admin/manage_users.php" style="margin-bottom:20px;">
        <label for="role" style="display:inline;">Filter by role:</label>
        <select id="role" name="role" onchange="this.form.submit()" style="width:auto;display:inline;">
          <option value="">All</option>
          <option value="admin" <?php echo $roleFilter==='admin'?'selected':''; ?>>Admin</option>
          <option value="farmer" <?php echo $roleFilter==='farmer'?'selected':''; ?>>Farmer</option>
          <option value="customer" <?php echo $roleFilter==='customer'?'selected':''; ?>>Customer</option>
        </select>
      </form>

      <table>
        <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Location</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo clean($u['full_name']); ?></td>
            <td><?php echo clean($u['email']); ?></td>
            <td><?php echo ucfirst(clean($u['role'])); ?></td>
            <td><?php echo clean($u['suburb']); ?>, <?php echo clean($u['state']); ?></td>
            <td><?php echo ucfirst(clean($u['status'])); ?></td>
            <td>
              <?php if ($u['user_id'] != current_user_id()): ?>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/manage_users.php" style="display:inline;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                <button type="submit" name="toggle_status" class="btn btn-small btn-secondary">
                  <?php echo $u['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                </button>
              </form>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/manage_users.php" style="display:inline;" onsubmit="return confirm('Delete this user permanently?');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                <button type="submit" name="delete_user" class="btn btn-small btn-danger">Delete</button>
              </form>
              <?php else: ?>
                <em>(You)</em>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
