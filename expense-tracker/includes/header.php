<?php
require_once __DIR__ . '/functions.php';
$page = basename($_SERVER['SCRIPT_NAME']);
$nav = ['index.php' => 'Overview', 'expenses.php' => 'Expenses', 'categories.php' => 'Categories'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Ledger') ?> · Ledger</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if (!empty($_SESSION['user_id'])): ?>
<header class="top">
  <a class="brand" href="index.php">Ledger</a>
  <nav>
    <?php foreach ($nav as $file => $label): ?>
      <a href="<?= $file ?>" class="<?= $page === $file ? 'on' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="who"><?= e($_SESSION['user_name']) ?> · <a href="logout.php">Log out</a></div>
</header>
<?php endif; ?>
<main class="wrap">
<?php if ($f = flash()): ?><div class="flash <?= $f[1] ?>"><?= e($f[0]) ?></div><?php endif; ?>
