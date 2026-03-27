<?php
/**
 * config/database.php — PDO Database Connection
 * Owner: All members (common)
 *
 * Creates global $pdo used by all models.
 * Edit DB_HOST / DB_NAME / DB_USER / DB_PASS to match your environment.
 */
define('DB_HOST',    'localhost');
define('DB_NAME',    'vinfast_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
    ]);
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    die('Database connecationa error.');
}
