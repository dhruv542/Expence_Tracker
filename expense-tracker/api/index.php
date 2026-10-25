<?php
$title = 'Overview';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
include __DIR__ . '/_inc/header.php';
$uid = (int)$_SESSION['user_id'];

$first = date('Y-m-01'); $last = date('Y-m-t');
$pFirst = date('Y-m-01', strtotime('first day of last month')); $pLast = date('Y-m-t', strtotime('last day of last month'));

function total(PDO $pdo, $uid, $a, $b) {
    $s = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND expense_date BETWEEN ? AND ?');
    $s->execute([$uid, $a, $b]); return (float)$s->fetchColumn();
}
$thisM = total($pdo, $uid, $first, $last);
$prevM = total($pdo, $uid, $pFirst, $pLast);
$daysSoFar = (int)date('j');
$avg = $thisM / max(1, $daysSoFar);

$s = $pdo->prepare('SELECT COALESCE(c.name,"Uncategorised") AS name, SUM(e.amount) AS total
  FROM expenses e LEFT JOIN categories c ON c.id=e.category_id
  WHERE e.user_id=? AND e.expense_date BETWEEN ? AND ? GROUP BY name ORDER BY total DESC');
$s->execute([$uid, $first, $last]); $byCat = $s->fetchAll();

$s = $pdo->prepare('SELECT DATE_FORMAT(expense_date,"%Y-%m") ym, SUM(amount) total FROM expenses
  WHERE user_id=? AND expense_date >= ? GROUP BY ym');
$s->execute([$uid, date('Y-m-01', strtotime('-5 months'))]);
$monthly = array_column($s->fetchAll(), 'total', 'ym');
$labels = $vals = [];
for ($i = 5; $i >= 0; $i--) {
    $d = strtotime("first day of -$i months");
    $labels[] = date('M', $d); $vals[] = round((float)($monthly[date('Y-m', $d)] ?? 0), 2);
}

$s = $pdo->prepare('SELECT e.*, c.name cat FROM expenses e LEFT JOIN categories c ON c.id=e.category_id
  WHERE e.user_id=? ORDER BY e.expense_date DESC, e.id DESC LIMIT 6');
$s->execute([$uid]); $recent = $s->fetchAll();

$diff = $prevM > 0 ? (($thisM - $prevM) / $prevM) * 100 : null;
?>
<div class="head">
  <h1><?= date('F Y') ?></h1>
  <a class="btn" href="expense_form.php">Add expense</a>
</div>

<section class="figures">
  <div class="big">
    <span class="lbl">Spent this month</span>
    <strong><?= money($thisM) ?></strong>
    <span class="muted">
      <?php if ($diff === null): ?>No spending last month to compare.
      <?php else: ?><?= abs(round($diff)) ?>% <?= $diff > 0 ? 'more' : 'less' ?> than last month (<?= money($prevM) ?>)
      <?php endif; ?>
    </span>
  </div>
  <div><span class="lbl">Daily average</span><strong><?= money($avg) ?></strong></div>
  <div><span class="lbl">Top category</span><strong><?= $byCat ? e($byCat[0]['name']) : '–' ?></strong></div>
</section>

<section class="grid2">
  <div class="panel"><h2>Last 6 months</h2><canvas id="bar" height="180"></canvas></div>
  <div class="panel"><h2>This month by category</h2>
    <?php if ($byCat): ?><canvas id="pie" height="180"></canvas>
    <?php else: ?><p class="muted">Nothing logged yet this month. <a href="expense_form.php">Add an expense</a> to see the breakdown.</p><?php endif; ?>
  </div>
</section>

<section class="panel">
  <div class="head"><h2>Recent expenses</h2><a href="expenses.php">See all</a></div>
  <?php if (!$recent): ?><p class="muted">No expenses yet.</p><?php else: ?>
  <table>
    <?php foreach ($recent as $r): ?>
    <tr><td><?= e(date('d M', strtotime($r['expense_date']))) ?></td>
        <td><?= e($r['description'] ?: '—') ?></td>
        <td class="muted"><?= e($r['cat'] ?? 'Uncategorised') ?></td>
        <td class="num"><?= money($r['amount']) ?></td></tr>
    <?php endforeach; ?>
  </table><?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const cur = <?= json_encode(CURRENCY) ?>;
new Chart(document.getElementById('bar'), {
  type: 'bar',
  data: { labels: <?= json_encode($labels) ?>, datasets: [{ data: <?= json_encode($vals) ?>, backgroundColor: '#1F5F5B', borderRadius: 3 }] },
  options: { plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => cur + c.parsed.y.toLocaleString() } } },
             scales: { y: { beginAtZero: true, grid: { color: '#E3E7E1' } }, x: { grid: { display: false } } } }
});
<?php if ($byCat): ?>
new Chart(document.getElementById('pie'), {
  type: 'doughnut',
  data: { labels: <?= json_encode(array_column($byCat, 'name')) ?>,
          datasets: [{ data: <?= json_encode(array_map('floatval', array_column($byCat, 'total'))) ?>,
          backgroundColor: ['#1F5F5B','#B3402A','#D9A441','#5B7C99','#7A9E7E','#8E5B8A','#9A9A92'], borderWidth: 2, borderColor: '#F6F7F4' }] },
  options: { plugins: { legend: { position: 'right' }, tooltip: { callbacks: { label: c => c.label + ': ' + cur + c.parsed.toLocaleString() } } } }
});
<?php endif; ?>
</script>
<?php include __DIR__ . '/_inc/footer.php';?>
