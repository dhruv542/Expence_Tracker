<?php
require 'includes/functions.php';
require_login();
$uid = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$cats = user_categories($pdo, $uid);
$exp = ['amount' => '', 'category_id' => '', 'description' => '', 'expense_date' => date('Y-m-d')];

if ($id) {
    $s = $pdo->prepare('SELECT * FROM expenses WHERE id=? AND user_id=?'); $s->execute([$id, $uid]);
    $exp = $s->fetch();
    if (!$exp) { flash('That expense was not found.', 'err'); header('Location: expenses.php'); exit; }
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $amount = (float)($_POST['amount'] ?? 0);
    $date = $_POST['expense_date'] ?? '';
    $desc = trim($_POST['description'] ?? '');
    $cat = $_POST['category_id'] ?? '';
    $okCat = null;
    foreach ($cats as $c) if ($c['id'] == $cat) $okCat = (int)$cat;   // only the user's own categories
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($amount <= 0 || $amount > 9999999999) $err = 'Enter an amount greater than zero.';
    elseif (!$d || $d->format('Y-m-d') !== $date) $err = 'Choose a valid date.';
    elseif (mb_strlen($desc) > 255) $err = 'Description must be 255 characters or fewer.';
    else {
        if ($id) $pdo->prepare('UPDATE expenses SET amount=?, category_id=?, description=?, expense_date=? WHERE id=? AND user_id=?')
                     ->execute([$amount, $okCat, $desc, $date, $id, $uid]);
        else     $pdo->prepare('INSERT INTO expenses (user_id, amount, category_id, description, expense_date) VALUES (?,?,?,?,?)')
                     ->execute([$uid, $amount, $okCat, $desc, $date]);
        flash($id ? 'Expense updated.' : 'Expense added.');
        header('Location: expenses.php'); exit;
    }
    $exp = ['amount' => $_POST['amount'], 'category_id' => $cat, 'description' => $desc, 'expense_date' => $date];
}
$title = $id ? 'Edit expense' : 'Add expense';
include 'includes/header.php';
?>
<section class="auth wide">
  <h1><?= e($title) ?></h1>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <label>Amount (<?= CURRENCY ?>)<input type="number" name="amount" step="0.01" min="0.01" value="<?= e($exp['amount']) ?>" required autofocus></label>
    <label>Date<input type="date" name="expense_date" value="<?= e($exp['expense_date']) ?>" required></label>
    <label>Category
      <select name="category_id"><option value="">Uncategorised</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $exp['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select></label>
    <label>Description<input name="description" maxlength="255" value="<?= e($exp['description']) ?>" placeholder="e.g. Groceries at the market"></label>
    <div class="row"><button class="btn">Save expense</button><a href="expenses.php">Cancel</a></div>
  </form>
</section>
<?php include 'includes/footer.php'; ?>
