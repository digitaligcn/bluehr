<?php
namespace BlueHR\Core;
class Router {
    private array $routes = [];
    public function get(string $path, array $handler): void { $this->routes['GET'][$path] = $handler; }
    public function post(string $path, array $handler): void { $this->routes['POST'][$path] = $handler; }
    public function dispatch(): void {
        verify_csrf();
        $method = $_SERVER['REQUEST_METHOD'];
        $path = request_path();
        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) { http_response_code(404); echo '404 Not Found: ' . e($path); return; }
        [$class, $methodName] = $handler;
        (new $class())->$methodName();
    }
}
