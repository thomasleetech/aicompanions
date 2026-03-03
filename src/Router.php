<?php

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable $handler, array $mw = []): self
    {
        $this->routes['GET'][$path] = ['handler' => $handler, 'middleware' => $mw];
        return $this;
    }

    public function post(string $path, callable $handler, array $mw = []): self
    {
        $this->routes['POST'][$path] = ['handler' => $handler, 'middleware' => $mw];
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Remove base path (e.g., /public)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
            $uri = '/' . ltrim($uri, '/');
        }

        $routes = $this->routes[$method] ?? [];
        $params = [];

        foreach ($routes as $pattern => $route) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $result = call_user_func($mw);
                    if ($result === false) return;
                }

                call_user_func($route['handler'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found']);
        } else {
            View::render('errors/404');
        }
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json')
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
}
