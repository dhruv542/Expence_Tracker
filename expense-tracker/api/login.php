<?php
require_once __DIR__ . '/_inc/functions.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$err = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $s = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $s->execute([$email]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id']; $_SESSION['user_name'] = $u['name'];
        header('Location: index.php'); exit;
    }
    $err = 'Email or password is incorrect.';
}
$title = 'Log in';
include __DIR__ . '/_inc/header.php';
?>
<section class="auth">
  <h1>Log in</h1>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label>Email<input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn">Log in</button>
  </form>
  <p class="muted">New here? <a href="register.php">Create an account</a></p>
</section>
<?php include __DIR__ . '/_inc/footer.php'; ?>
