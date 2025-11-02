<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/SessionManager.php';

class AppController {

    protected AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    protected function isGet(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    protected function isPost(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    protected function render(string $template = null, array $variables = []) {
        $templatePath = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';
        
        if (file_exists($templatePath)) {
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            http_response_code(404);
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        
        echo $output;
    }

    protected function redirect(string $path) {
        header("Location: /$path");
        exit();
    }

    protected function isLoggedIn(): bool {
        return $this->authService->isLoggedIn();
    }

    protected function requireLogin() {
        $this->authService->requireLogin();
    }

    protected function getUserRole(): ?string {
        return $this->authService->getCurrentUserRole();
    }

    protected function requireRole(string $role) {
        $this->authService->requireRole($role);
    }

    protected function requireAnyRole(array $roles) {
        $this->authService->requireAnyRole($roles);
    }

    protected function getCurrentUserId(): ?int {
        return $this->authService->getCurrentUserId();
    }

    protected function setFlash(string $key, $value): void {
        SessionManager::setFlash($key, $value);
    }

    protected function getFlash(string $key, $default = null) {
        return SessionManager::getFlash($key, $default);
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}