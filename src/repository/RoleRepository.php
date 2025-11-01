<?php

require_once __DIR__ . '/Repository.php';

class RoleRepository extends Repository {
    
    public function findByName(string $name): ?array {
        $query = "SELECT * FROM roles WHERE name = :name";
        return $this->fetchOne($query, [':name' => $name]);
    }
    
    public function getRoleById(int $id): ?array {
        return $this->findById('roles', $id);
    }
    
    public function getAllRoles(): array {
        return $this->findAll('roles', 'name');
    }
    
    public function getRoleIdByName(string $name): ?int {
        $role = $this->findByName($name);
        return $role ? (int)$role['id'] : null;
    }
}