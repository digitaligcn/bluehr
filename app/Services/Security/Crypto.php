<?php
namespace BlueHR\Services\Security;
class Crypto {
    public static function encrypt(?string $plain): ?string {
        if ($plain === null || $plain === '') return $plain;
        $key = hash('sha256', config('app.key'), true);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipher);
    }
    public static function decrypt(?string $payload): ?string {
        if ($payload === null || $payload === '') return $payload;
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 17) return null;
        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $key = hash('sha256', config('app.key'), true);
        return openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv) ?: null;
    }
}
