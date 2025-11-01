<?php

require_once __DIR__ . '/Repository.php';

class UserRepository extends Repository {
    
    public function findByEmail(string $email): ?array {
        $query = "SELECT * FROM users WHERE email = :email";
        return $this->fetchOne($query, [':email' => $email]);
    }
    
    public function findByEmailWithRole(string $email): ?array {
        $query = "
            SELECT u.*, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email
        ";
        return $this->fetchOne($query, [':email' => $email]);
    }
    
    public function createUser(string $email, string $password, int $roleId): ?int {
        $data = [
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'status' => 'active'
        ];
        
        return $this->insert('users', $data);
    }
    
    public function updateLastLogin(int $userId): bool {
        $query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->executeQuery($query, [':id' => $userId]);
        return $stmt->rowCount() > 0;
    }
    
    public function updateStatus(int $userId, string $status): bool {
        return $this->update('users', $userId, ['status' => $status]);
    }
    
    public function findAllByRole(int $roleId): array {
        $query = "SELECT * FROM users WHERE role_id = :role_id ORDER BY created_at DESC";
        return $this->fetchAll($query, [':role_id' => $roleId]);
    }
    
    public function getUserById(int $id): ?array {
        return $this->findById('users', $id);
    }
    
    public function emailExists(string $email): bool {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $result = $this->fetchOne($query, [':email' => $email]);
        return $result && $result['count'] > 0;
    }
}