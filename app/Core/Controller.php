<?php
namespace BlueHR\Core;
class Controller {
    protected function view(string $view, array $data = []): void {
        extract($data);
        $viewFile = base_path('app/Views/' . $view . '.php');
        if (!is_file($viewFile)) throw new \RuntimeException('View not found: ' . $view);
        require base_path('app/Views/layouts/main.php');
    }
    protected function json(array $payload, int $status = 200): void {
        http_response_code($status); header('Content-Type: application/json'); echo json_encode($payload); exit;
    }
}
