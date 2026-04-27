<?php

namespace App\Core;

/**
 * Controller — Base class for all controllers
 * Provides view rendering and authentication checks
 */
abstract class Controller
{
    protected Session $session;
    protected array $viewData = [];

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Render a view template
     * @param string $view Path to view relative to app/Views/ (without .php)
     * @param array $data Variables to make available in the view
     */
    protected function view(string $view, array $data = []): void
    {
        $this->viewData = $data;
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }

        include $viewPath;
    }

    /**
     * Render view with layout wrapper
     */
    protected function render(string $view, array $data = []): void
    {
        $this->viewData = $data;
        extract($data);

        // Start output buffering to capture view content
        ob_start();
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        $content = ob_get_clean();

        // Include layout and make $content available
        include __DIR__ . '/../Views/layouts/layout.php';
    }

    /**
     * Require user to be logged in
     */
    protected function requireLogin(): void
    {
        if (!$this->session->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Require user to be admin
     */
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if ($this->session->userRole() !== 'admin') {
            http_response_code(403);
            die('Unauthorized');
        }
    }

    /**
     * Require user to be faculty or admin
     */
    protected function requireFaculty(): void
    {
        $this->requireLogin();
        if (!$this->session->isFaculty()) {
            http_response_code(403);
            die('Unauthorized: Faculty access required');
        }
    }

    /**
     * Get current logged-in user
     */
    protected function currentUser(): ?array
    {
        if (!$this->session->isLoggedIn()) {
            return null;
        }
        // Will be populated when User model is created
        return null;
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send redirect response
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
