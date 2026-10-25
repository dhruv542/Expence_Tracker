<?php
require_once __DIR__ . '/_inc/functions.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
