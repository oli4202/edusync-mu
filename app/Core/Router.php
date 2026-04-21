<?php

namespace App\Core;

/**
 * Router — Handles URL routing to controller methods
 * Maps GET/POST requests to controller actions with optional parameters
 */
class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * Register a GET route
     * Example: $router->get('/dashboard', 'DashboardController@index')
     */
    public function get(string $path, string $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    /**
     * Dispatch a request to the appropriate controller method
     * Returns false if no route matched
     */
    public function dispatch(string $method, string $uri): bool
    {
        // Normalize URI (remove trailing slashes and query strings)
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->callAction($this->routes[$method][$uri]);
        }

        // Check for parameterized routes (e.g., /post/:id)
        foreach ($this->routes[$method] as $route => $action) {
            $pattern = $this->uriToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                return $this->callAction($action, $matches);
            }
        }

        return false;
    }

    /**
     * Convert a route pattern to regex
     * /post/:id -> /post/(\d+)
     * /user/:username -> /user/([a-z0-9_-]+)
     */
    private function uriToRegex(string $uri): string
    {
        $pattern = preg_replace_callback('/:([a-z_]+)/', function ($matches) {
            $param = $matches[1];
            // Special patterns for common parameters
            return match ($param) {
                'id' => '(\d+)',
                'slug' => '([a-z0-9-]+)',
                default => '([^/]+)'
            };
        }, $uri);

        return '#^' . $pattern . '$#i';
    }

    /**
     * Call a controller action
     * Format: "ControllerName@method" or "App\Controllers\ControllerName@method"
     */
    private function callAction(string $action, array $params = []): bool
    {
        [$class, $method] = explode('@', $action);

        // Add App\Controllers namespace if not already present
        if (strpos($class, '\\') === false) {
            $class = 'App\\Controllers\\' . $class;
        }

        // Check if controller exists
        if (!class_exists($class)) {
            http_response_code(404);
            return false;
        }

        // Instantiate controller and call method
        $controller = new $class();
        if (!method_exists($controller, $method)) {
            http_response_code(404);
            return false;
        }

        call_user_func_array([$controller, $method], $params);
        return true;
    }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
