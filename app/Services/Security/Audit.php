<?php
namespace BlueHR\Services\Security;
use BlueHR\Core\Database;
class Audit {
    public static function log(string $action, string $entityType = '', ?int $entityId = null, array $meta = []): void {
        try {
            Database::insert('INSERT INTO audit_logs(user_id, action, entity_type, entity_id, ip_address, user_agent, meta_json, created_at) VALUES(?,?,?,?,?,?,?,?)', [
                $_SESSION['user']['id'] ?? null, $action, $entityType, $entityId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', json_encode($meta), now()
            ]);
        } catch (\Throwable $e) {}
    }
}
