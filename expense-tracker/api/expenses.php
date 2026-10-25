<?php
$title = 'Expenses';
require_once __DIR__ . '/_inc/functions.php';
require_login();
include __DIR__ . '/_inc/header.php';
$uid = (int)$_SESSION['user_id'];
[$where, $params] = expense_filters($uid);

$s = $pdo->prepare("SELECT e.*, c.name cat FROM expenses e LEFT JOIN categories c ON c.id=e.category_id
  WHERE $where ORDER BY e.expense_date DESC, e.id DESC LIMIT 500");
$s->execute($params); $rows = $s->fetchAll();
$sum = array_sum(array_column($rows, 'amount'));
$cats = user_categories($pdo, $uid);
$qs = http_build_query(array_filter(['from'=>$_GET['from']??'','to'=>$_GET['to']??'','cat'=>$_GET['cat']??'','q'=>$_GET['q']??''], 'strlen'));
?>
<div class="head"><h1>Expenses</h1><a class="btn" href="expense_form.php">Add expense</a></div>

<form class="filters" method="get">
  <label>From<input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>"></label>
  <label>To<input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>"></label>
  <label>Category
    <select name="cat"><option value="">All</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= (($_GET['cat'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select></label>
  <label>Search<input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Description"></label>
  <button class="btn ghost">Apply filters</button>
  <a href="expenses.php">Clear</a>
</form>

<section class="panel">
  <div class="head"><h2><?= count($rows) ?> expense<?= count($rows) === 1 ? '' : 's' ?> · <?= money($sum) ?></h2>
    <a href="export.php?<?= e($qs) ?>">Download CSV</a></div>
  <?php if (!$rows): ?><p class="muted">No expenses match these filters.</p><?php else: ?>
  <div class="scroll"><table>
    <tr><th>Date</th><th>Description</th><th>Category</th><th class="num">Amount</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
    <tr><td><?= e(date('d M Y', strtotime($r['expense_date']))) ?></td>
        <td><?= e($r['description'] ?: '—') ?></td>
        <td class="muted"><?= e($r['cat'] ?? 'Uncategorised') ?></td>
        <td class="num"><?= money($r['amount']) ?></td>
        <td class="act">
          <a href="expense_form.php?id=<?= $r['id'] ?>">Edit</a>
          <form method="post" action="delete.php" onsubmit="return confirm('Delete this expense?')"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="link danger">Delete</button></form>
        </td></tr>
    <?php endforeach; ?>
  </table></div><?php endif; ?>
</section>
<?php include __DIR__ . '/_inc/footer.php'; ?>
