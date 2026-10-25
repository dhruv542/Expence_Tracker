<?php
// Edit these to match your MySQL setup (XAMPP default: root, empty password)
const DB_HOST = 'localhost';
const DB_NAME = 'expense_tracker';
const DB_USER = 'root';
const DB_PASS = '';
const CURRENCY = '₹';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    exit('Database connection failed. Check config/db.php and that you imported schema.sql.');
}
