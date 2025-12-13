<?php

class User {
    
    private int $id;
    private string $email;
    private string $password;
    private int $roleId;
    private string $status;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $lastLogin;
    private ?string $roleName;
    
    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->roleId = (int)$data['role_id'];
        $this->status = $data['status'];
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->lastLogin = $data['last_login'] ?? null;
        $this->roleName = $data['role_name'] ?? null;
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function getPassword(): string {
        return $this->password;
    }
    
    public function getRoleId(): int {
        return $this->roleId;
    }
    
    public function getStatus(): string {
        return $this->status;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }
    
    public function getLastLogin(): ?string {
        return $this->lastLogin;
    }
    
    public function getRoleName(): ?string {
        return $this->roleName;
    }
    
    public function isActive(): bool {
        return $this->status === 'active';
    }
    
    public function isInactive(): bool {
        return $this->status === 'inactive';
    }
    
    public function isSuspended(): bool {
        return $this->status === 'suspended';
    }
    
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_login' => $this->lastLogin
        ];
    }
    
    public function toSessionArray(): array {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'status' => $this->status
        ];
    }
}