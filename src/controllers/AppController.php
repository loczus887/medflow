<?php

class AppController {

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
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }

    protected function getUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    protected function requireRole(string $role) {
        $this->requireLogin();
        
        if ($this->getUserRole() !== $role) {
            http_response_code(403);
            die('Access denied');
        }
    }
}