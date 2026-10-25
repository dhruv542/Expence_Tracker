<?php

$envPath = __DIR__ . '/../.env';
$env = [];

if (is_file($envPath)) {
    $parsedEnv = parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if ($parsedEnv === false) {
        throw new RuntimeException('Unable to read the database settings in .env.');
    }
    $env = $parsedEnv;
}

$getSetting = static function (string $name) use ($env): string {
    $value = getenv($name);
    if ($value !== false) {
        return $value;
    }

    return (string)($env[$name] ?? '');
};

$host = $getSetting('DB_HOST');
$port = $getSetting('DB_PORT') ?: '3306';
$dbname = $getSetting('DB_NAME');
$username = $getSetting('DB_USER');
$password = $getSetting('DB_PASSWORD');

define('CURRENCY', '₹');

if ($host === '' || $dbname === '' || $username === '') {
    throw new RuntimeException('Set DB_HOST, DB_NAME, and DB_USER in .env or the server environment.');
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Check your FreeDB settings and ensure the PHP PDO MySQL extension is enabled.");
}
?>
