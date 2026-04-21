<?php
/**
 * index.php — Front Controller
 * Single entry point for all requests
 * Handles routing to controllers based on URL
 */

// Autoload classes via Composer
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
require_once __DIR__ . '/config/database.php';

// Load helpers
require_once __DIR__ . '/app/helpers.php';

// Use namespaces
use App\Core\Router;

// Initialize router
$router = new Router();

// ============================================================
// ROUTE DEFINITIONS
// ============================================================

// Authentication routes
$router->get('/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/signup', 'AuthController@signup');
$router->post('/auth/signup', 'AuthController@doSignup');
$router->get('/logout', 'AuthController@logout');

// Dashboard routes
$router->get('/dashboard', 'DashboardController@index');
$router->get('/analytics', 'DashboardController@analytics');

// Page routes (dynamic)
$router->get('/:page', 'PageController@page');

// API/AJAX routes (if needed)
// $router->post('/api/task/create', 'AjaxController@createTask');
// $router->post('/api/task/update/:id', 'AjaxController@updateTask');

// ============================================================
// DISPATCH REQUEST
// ============================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Handle root redirect
if ($uri === '/' || $uri === '') {
    if ((new \App\Core\Session())->isLoggedIn()) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
}

// Dispatch to router
$dispatched = $router->dispatch($method, $uri);

if (!$dispatched) {
    // Route not found — show 404
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>404 Not Found</title>
        <style>
            body { font-family: sans-serif; background: #0a0e1a; color: #e2e8f0; padding: 50px; text-align: center; }
            h1 { color: #f87171; }
        </style>
    </head>
    <body>
        <h1>404 — Page Not Found</h1>
        <p>The requested page does not exist.</p>
        <a href="/login">← Back to Login</a>
    </body>
    </html>';
}
