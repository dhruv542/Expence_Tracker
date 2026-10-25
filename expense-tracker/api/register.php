<?php
require_once __DIR__ . '/api/_inc/functions.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$err = ''; $name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    if ($name === '' || mb_strlen($name) > 80) $err = 'Enter your name (up to 80 characters).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address.';
    elseif (strlen($pass) < 8) $err = 'Password must be at least 8 characters.';
    else {
        $s = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $s->execute([$email]);
        if ($s->fetch()) $err = 'An account with this email already exists. Log in instead.';
        else {
            $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $uid = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO categories (user_id, name) VALUES (?,?)');
            foreach (['Food','Transport','Bills','Shopping','Health','Entertainment','Other'] as $c) $ins->execute([$uid, $c]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $uid; $_SESSION['user_name'] = $name;
            flash('Welcome! Your account is ready. Add your first expense.');
            header('Location: /api/expense_form.php'); exit;
        }
    }
}
$title = 'Create account';
include __DIR__ . '/_inc/header.php';
?>
<section class="auth">
  <h1>Create your account</h1>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label>Name<input name="name" value="<?= e($name) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Password<input type="password" name="password" minlength="8" required></label>
    <button class="btn">Create account</button>
  </form>
  <p class="muted">Already registered? <a href="/api/login.php">Log in</a></p>
</section>
<?php include __DIR__ . '/_inc/footer.php'; ?>
