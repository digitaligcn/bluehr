<?php
function base_path(string $path = ''): string { return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : ''); }
function storage_path(string $path = ''): string { return base_path('storage') . ($path ? '/' . ltrim($path, '/') : ''); }
function public_path(string $path = ''): string { return base_path('public') . ($path ? '/' . ltrim($path, '/') : ''); }
function env(string $key, $default = null) {
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $file = base_path('.env');
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v);
            }
        }
    }
    return $vars[$key] ?? $default;
}
function config(string $key, $default = null) {
    static $cache = [];
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
    if (!isset($cache[$file])) $cache[$file] = require base_path('config/' . $file . '.php');
    return $item ? ($cache[$file][$item] ?? $default) : $cache[$file];
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

function runtime_base_url(): string {
    if (PHP_SAPI === 'cli' || empty($_SERVER['HTTP_HOST'])) {
        return rtrim(config('app.url'), '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');

    // If index.php is accessed directly under /public, this returns /project/public.
    // If Apache vhost DocumentRoot already points to /public, this returns an empty path.
    return $scheme . '://' . $host . $basePath;
}

function url(string $path = ''): string {
    return rtrim(runtime_base_url(), '/') . '/' . ltrim($path, '/');
}
function asset(string $path): string { return url('assets/' . ltrim($path, '/')); }
function redirect(string $path): void { header('Location: ' . url($path)); exit; }
function flash(string $type, string $message): void { $_SESSION["_flash"][] = ["type" => $type, "message" => $message]; }
function flash_get(): array { $messages = $_SESSION["_flash"] ?? []; unset($_SESSION["_flash"]); return $messages; }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void { if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf']))) { http_response_code(419); exit('CSRF token mismatch'); } }
function money_id($amount): string { return 'Rp ' . number_format((float)$amount, 0, ',', '.'); }
function now(): string { return date('Y-m-d H:i:s'); }

function request_path(): string {
    $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $script = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($script && str_starts_with($path, $script)) $path = trim(substr($path, strlen($script)), '/');
    if (str_starts_with($path, 'index.php')) $path = trim(substr($path, strlen('index.php')), '/');
    return '/' . trim($path, '/');
}
function setting(string $key, $default='') {
    try { $row = \BlueHR\Core\Database::one('SELECT setting_value FROM app_settings WHERE setting_key=?', [$key]); return $row['setting_value'] ?? $default; }
    catch (Throwable $e) { return $default; }
}
function save_setting(string $key, string $value, string $group='general'): void {
    \BlueHR\Core\Database::exec('INSERT INTO app_settings(setting_key, setting_value, setting_group) VALUES(?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_group=VALUES(setting_group)', [$key,$value,$group]);
}
