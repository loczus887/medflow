<?php

require_once __DIR__ . '/Repository.php';

class SpecializationRepository extends Repository {
    
    public function getSpecializationById(int $id): ?array {
        return $this->findById('specializations', $id);
    }
    
    public function getAllSpecializations(): array {
        return $this->findAll('specializations', 'name');
    }
    
    public function findByName(string $name): ?array {
        $query = "SELECT * FROM specializations WHERE name = :name";
        return $this->fetchOne($query, [':name' => $name]);
    }
    
    public function createSpecialization(string $name, ?string $description = null): ?int {
        $data = ['name' => $name];
        if ($description) {
            $data['description'] = $description;
        }
        return $this->insert('specializations', $data);
    }
}