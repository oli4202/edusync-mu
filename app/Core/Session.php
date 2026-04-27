<?php

namespace App\Core;

/**
 * Session — Manages user sessions and authentication state
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public function userId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public function userRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->userRole() === 'student';
    }

    /**
     * Check if user is faculty or admin
     */
    public function isFaculty(): bool
    {
        return in_array($this->userRole(), ['faculty', 'admin']);
    }

    /**
     * Set user session data (on login)
     */
    public function loginUser(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
    }

    /**
     * Destroy session (on logout)
     */
    public function logout(): void
    {
        session_destroy();
    }

    /**
     * Set flash message
     */
    public function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get and clear flash message
     */
    public function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
