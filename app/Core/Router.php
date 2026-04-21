<?php

namespace App\Core;

/**
 * Router — matches URLs to controller methods.
 *
 * Usage in index.php:
 *   $router = new Router();
 *   $router->get('/login', [$auth, 'showLogin']);
 *   $router->post('/login', [$auth, 'login']);
 *   $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
 */
class Router
{
    private array $routes = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, array|callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, array|callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Dispatch the current request to the matching route handler.
     */
    public function dispatch(string $uri, string $method): void
    {
        // Strip query string and base path
        $basePath = rtrim(getenv('BASE_PATH') ?: '', '/');
        $path = parse_url($uri, PHP_URL_PATH);

        // Remove base path prefix if set
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        // Normalize: ensure leading slash, remove trailing slash
        $path = '/' . ltrim($path, '/');
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        // Look for an exact match first
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            call_user_func($handler);
            return;
        }

        // Try pattern matching (e.g. /question/:id)
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#:([a-zA-Z_]+)#', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // remove full match
                call_user_func_array($handler, $matches);
                return;
            }
        }

        // No route found → 404
        http_response_code(404);
        if (file_exists(__DIR__ . '/../Views/404.php')) {
            include __DIR__ . '/../Views/404.php';
        } else {
            echo '<h1>404 Not Found</h1>';
        }
    }
}
