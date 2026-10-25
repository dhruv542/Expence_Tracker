<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->prepare('DELETE FROM expenses WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $_SESSION['user_id']]);
    flash('Expense deleted.');
}
header('Location: expenses.php');
