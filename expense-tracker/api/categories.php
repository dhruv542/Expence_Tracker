<?php
require_once __DIR__ . '/_inc/functions.php';
require_login();
$uid = (int)$_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'add') {
        $n = trim($_POST['name'] ?? '');
        if ($n === '' || mb_strlen($n) > 60) flash('Enter a category name (up to 60 characters).', 'err');
        else {
            $s = $pdo->prepare('INSERT IGNORE INTO categories (user_id, name) VALUES (?,?)'); $s->execute([$uid, $n]);
            $s->rowCount() ? flash('Category added.') : flash('You already have a category with that name.', 'err');
        }
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM categories WHERE id=? AND user_id=?')->execute([(int)$_POST['id'], $uid]);
        flash('Category deleted. Its expenses are now uncategorised.');
    }
    header('Location: categories.php'); exit;
}
$s = $pdo->prepare('SELECT c.id, c.name, COUNT(e.id) n, COALESCE(SUM(e.amount),0) total
  FROM categories c LEFT JOIN expenses e ON e.category_id=c.id WHERE c.user_id=? GROUP BY c.id ORDER BY c.name');
$s->execute([$uid]); $cats = $s->fetchAll();
$title = 'Categories';
include __DIR__ . '/_inc/header.php';
?>
<div class="head"><h1>Categories</h1></div>
<form class="filters" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add">
  <label>New category<input name="name" maxlength="60" required></label>
  <button class="btn">Add category</button>
</form>
<section class="panel">
  <table>
    <tr><th>Name</th><th class="num">Expenses</th><th class="num">Total</th><th></th></tr>
    <?php foreach ($cats as $c): ?>
    <tr><td><?= e($c['name']) ?></td><td class="num"><?= $c['n'] ?></td><td class="num"><?= money($c['total']) ?></td>
      <td class="act"><form method="post" onsubmit="return confirm('Delete this category? Its expenses will become uncategorised.')">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
        <button class="link danger">Delete</button></form></td></tr>
    <?php endforeach; ?>
  </table>
</section>
<?php include __DIR__ . '/_inc/footer.php'; ?>
