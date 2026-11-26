<?php

/**
 * app/core/Db.php — Kết nối PostgreSQL + MongoDB (singleton)
 *
 *   Db::pg()     PDO pgsql (ERRMODE_EXCEPTION, FETCH_ASSOC, prepared thật)
 *   Db::mongo()  MongoDB\Database
 *   Db::transaction(fn(PDO $pdo) => ..., 'SERIALIZABLE')
 *
 * Cấu hình qua biến môi trường (docker-compose.yml đặt sẵn):
 *   PG_HOST, PG_PORT, PG_DB, PG_USER, PG_PASSWORD, MONGO_URI, MONGO_DB
 */
final class Db
{
    private static ?PDO $pg = null;
    private static ?MongoDB\Database $mongo = null;

    public static function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return ($value === false || $value === '') ? $default : $value;
    }

    public static function pg(): PDO
    {
        if (self::$pg === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                self::env('PG_HOST', 'localhost'),
                self::env('PG_PORT', '5432'),
                self::env('PG_DB', 'vinfast')
            );
            self::$pg = new PDO($dsn, self::env('PG_USER', 'vinfast'), self::env('PG_PASSWORD', 'vinfast'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$pg->exec("SET TIME ZONE 'Asia/Ho_Chi_Minh'");
        }
        return self::$pg;
    }

    public static function mongo(): MongoDB\Database
    {
        if (self::$mongo === null) {
            $client = new MongoDB\Client(self::env('MONGO_URI', 'mongodb://localhost:27017'), [], [
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]);
            self::$mongo = $client->selectDatabase(self::env('MONGO_DB', 'vinfast'));
        }
        return self::$mongo;
    }

    /**
     * Chạy $fn trong 1 transaction PG. Lỗi -> rollback và ném lại.
     * Mã SQLSTATE 40001/40P01/55P03 -> ConcurrentUpdateException, 23514 -> OutOfStockException.
     */
    public static function transaction(callable $fn, ?string $isolation = null)
    {
        $pdo = self::pg();
        $pdo->beginTransaction();
        try {
            if ($isolation !== null) {
                $level = strtoupper($isolation);
                if (!in_array($level, ['READ COMMITTED', 'REPEATABLE READ', 'SERIALIZABLE'], true)) {
                    throw new InvalidArgumentException("Invalid isolation level: {$isolation}");
                }
                $pdo->exec("SET TRANSACTION ISOLATION LEVEL {$level}");
            }
            $result = $fn($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof PDOException) {
                $state = (string)($e->errorInfo[0] ?? $e->getCode());
                if (in_array($state, ['40001', '40P01', '55P03'], true)) {
                    throw new ConcurrentUpdateException($e->getMessage(), 0, $e);
                }
                if ($state === '23514') {
                    throw new OutOfStockException($e->getMessage(), 0, $e);
                }
            }
            throw $e;
        }
    }
}
