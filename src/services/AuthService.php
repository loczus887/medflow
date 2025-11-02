<?php

require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/RoleRepository.php';
require_once __DIR__ . '/../models/User.php';

class AuthService {
    
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    
    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->roleRepository = new RoleRepository();
    }
    
    public function login(string $email, string $password): ?array {
        $userData = $this->userRepository->findByEmailWithRole($email);
        
        if (!$userData) {
            return null;
        }
        
        $user = new User($userData);
        
        if (!$user->isActive()) {
            return ['error' => 'Account is not active'];
        }
        
        if (!$user->verifyPassword($password)) {
            return null;
        }
        
        $this->userRepository->updateLastLogin($user->getId());
        
        $this->createSession($user);
        
        return $user->toSessionArray();
    }
    
    public function register(string $email, string $password, string $roleName = 'patient'): ?array {
        if ($this->userRepository->emailExists($email)) {
            return ['error' => 'Email already exists'];
        }
        
        $role = $this->roleRepository->findByName($roleName);
        if (!$role) {
            return ['error' => 'Invalid role'];
        }
        
        $hashedPassword = User::hashPassword($password);
        
        $userId = $this->userRepository->createUser($email, $hashedPassword, (int)$role['id']);
        
        if (!$userId) {
            return ['error' => 'Failed to create user'];
        }
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    public function logout(): void {
        $this->destroySession();
    }
    
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getCurrentUserEmail(): ?string {
        return $_SESSION['email'] ?? null;
    }
    
    public function getCurrentUserRole(): ?string {
        return $_SESSION['role_name'] ?? null;
    }
    
    public function hasRole(string $roleName): bool {
        return $this->getCurrentUserRole() === $roleName;
    }
    
    public function isAdmin(): bool {
        return $this->hasRole('admin');
    }
    
    public function isDoctor(): bool {
        return $this->hasRole('doctor');
    }
    
    public function isReceptionist(): bool {
        return $this->hasRole('receptionist');
    }
    
    public function isPatient(): bool {
        return $this->hasRole('patient');
    }
    
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }
    
    public function requireRole(string $roleName): void {
        $this->requireLogin();
        
        if (!$this->hasRole($roleName)) {
            http_response_code(403);
            die('Access denied');
        }
    }
    
    public function requireAnyRole(array $roleNames): void {
        $this->requireLogin();
        
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($roleName)) {
                return;
            }
        }
        
        http_response_code(403);
        die('Access denied');
    }
    
    private function createSession(User $user): void {
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['role_id'] = $user->getRoleId();
        $_SESSION['role_name'] = $user->getRoleName();
        $_SESSION['status'] = $user->getStatus();
        $_SESSION['logged_in_at'] = time();
        
        session_regenerate_id(true);
    }
    
    private function destroySession(): void {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    public function validateSession(): bool {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $sessionLifetime = 3600;
        $loggedInAt = $_SESSION['logged_in_at'] ?? 0;
        
        if (time() - $loggedInAt > $sessionLifetime) {
            $this->destroySession();
            return false;
        }
        
        $_SESSION['logged_in_at'] = time();
        
        return true;
    }
    
    public function getSessionData(): array {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role_id' => $_SESSION['role_id'] ?? null,
            'role_name' => $_SESSION['role_name'] ?? null,
            'status' => $_SESSION['status'] ?? null,
            'logged_in_at' => $_SESSION['logged_in_at'] ?? null
        ];
    }
}