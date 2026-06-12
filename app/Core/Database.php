<?php
namespace BlueHR\Core;
use PDO;
use PDOException;
class Database {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (self::$pdo) return self::$pdo;
        $cfg = config('database');
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
        try {
            self::$pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return self::$pdo;
        } catch (PDOException $e) { throw new \RuntimeException('Database connection failed: ' . $e->getMessage()); }
    }
    public static function one(string $sql, array $params = []): ?array { $s=self::pdo()->prepare($sql); $s->execute($params); return $s->fetch() ?: null; }
    public static function all(string $sql, array $params = []): array { $s=self::pdo()->prepare($sql); $s->execute($params); return $s->fetchAll(); }
    public static function exec(string $sql, array $params = []): int { $s=self::pdo()->prepare($sql); $s->execute($params); return $s->rowCount(); }
    public static function insert(string $sql, array $params = []): string { $s=self::pdo()->prepare($sql); $s->execute($params); return self::pdo()->lastInsertId(); }
}
