<?php
require 'includes/functions.php';
require_login();
[$where, $params] = expense_filters((int)$_SESSION['user_id']);
$s = $pdo->prepare("SELECT e.expense_date, c.name cat, e.description, e.amount FROM expenses e
  LEFT JOIN categories c ON c.id=e.category_id WHERE $where ORDER BY e.expense_date DESC");
$s->execute($params);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="expenses-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Date', 'Category', 'Description', 'Amount']);
$safe = fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : $v;   // block spreadsheet formula injection
while ($r = $s->fetch()) fputcsv($out, [$r['expense_date'], $safe($r['cat'] ?? 'Uncategorised'), $safe($r['description']), $r['amount']]);
