<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return CURRENCY . number_format((float)$n, 2); }

function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}
function require_login() {
    if (empty($_SESSION['user_id'])) { header('Location: /api/login.php'); exit; }
}
function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function user_categories(PDO $pdo, int $uid) {
    $s = $pdo->prepare('SELECT id, name FROM categories WHERE user_id = ? ORDER BY name');
    $s->execute([$uid]);
    return $s->fetchAll();
}
// Builds WHERE clause from the filter form (GET): from, to, cat, q
function expense_filters(int $uid) {
    $where = ['e.user_id = ?']; $p = [$uid];
    $from = $_GET['from'] ?? ''; $to = $_GET['to'] ?? '';
    $cat = $_GET['cat'] ?? ''; $q = trim($_GET['q'] ?? '');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = 'e.expense_date >= ?'; $p[] = $from; }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[] = 'e.expense_date <= ?'; $p[] = $to; }
    if ($cat !== '' && ctype_digit((string)$cat))   { $where[] = 'e.category_id = ?';  $p[] = (int)$cat; }
    if ($q !== '') { $where[] = 'e.description LIKE ?'; $p[] = '%' . $q . '%'; }
    return [implode(' AND ', $where), $p];
}
