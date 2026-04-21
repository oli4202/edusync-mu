<?php

namespace App\Core;

/**
 * Controller — base class that all controllers extend.
 *
 * Provides the view() method so controllers can render HTML templates.
 * Each controller handles one area of the app (auth, dashboard, pages, etc.).
 */
abstract class Controller
{
    /**
     * Render a view template.
     *
     * @param string $path  Path to the view file relative to app/Views/
     * @param array  $data  Variables to make available inside the template
     *
     * Example: $this->view('dashboard.php', ['user' => $user])
     *          This makes $user available inside app/Views/dashboard.php
     */
    protected function view(string $path, array $data = []): void
    {
        // extract() turns array keys into variables: ['user' => $u] creates $user
        extract($data);
        include __DIR__ . '/../Views/' . $path;
    }

    /**
     * Send a JSON response and exit.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get JSON body from request.
     */
    protected function jsonBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
