<?php
/**
 * config/database.php — PDO Database Connection
 * Owner: All members (common)
 *
 * Creates global $pdo used by all models.
 * Connection values are read from environment variables so local and hosted
 * environments can use different databases without changing source code.
 */
define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT') ?: '3306');
define('DB_NAME',    getenv('DB_NAME') ?: 'vinfast_db');
define('DB_USER',    getenv('DB_USER') ?: 'root');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_SSL_CA',  getenv('DB_SSL_CA') ?: '');
define('DB_SSL_CA_CONTENT', getenv('DB_SSL_CA_CONTENT') ?: '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $sslCaPath = DB_SSL_CA;
    if ($sslCaPath !== '' && !is_file($sslCaPath)) {
        $secretPath = '/etc/secrets/' . basename($sslCaPath);
        if (is_file($secretPath)) {
            $sslCaPath = $secretPath;
        }
    }

    if (DB_SSL_CA_CONTENT !== '') {
        $sslCaPath = tempnam(sys_get_temp_dir(), 'aiven-ca-');
        if ($sslCaPath === false || file_put_contents($sslCaPath, DB_SSL_CA_CONTENT) === false) {
            throw new RuntimeException('Unable to create a temporary CA certificate file.');
        }
    }

    if ($sslCaPath !== '') {
        if (!is_file($sslCaPath) || !is_readable($sslCaPath)) {
            throw new RuntimeException('DB_SSL_CA file was not found or is not readable.');
        }

        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCaPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection error.');
} catch (RuntimeException $e) {
    error_log('DB configuration error: ' . $e->getMessage());
    http_response_code(500);
    die('Database configuration error.');
}
