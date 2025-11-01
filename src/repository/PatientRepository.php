<?php

require_once __DIR__ . '/Repository.php';

class PatientRepository extends Repository {
    
    public function createPatient(array $data): ?int {
        return $this->insert('patients', $data);
    }
    
    public function getPatientById(int $id): ?array {
        return $this->findById('patients', $id);
    }
    
    public function getPatientByUserId(int $userId): ?array {
        $query = "SELECT * FROM patients WHERE user_id = :user_id";
        return $this->fetchOne($query, [':user_id' => $userId]);
    }
    
    public function getPatientByPesel(string $pesel): ?array {
        $query = "SELECT * FROM patients WHERE pesel = :pesel";
        return $this->fetchOne($query, [':pesel' => $pesel]);
    }
    
    public function updatePatient(int $id, array $data): bool {
        return $this->update('patients', $id, $data);
    }
    
    public function getAllPatients(): array {
        $query = "
            SELECT p.*, u.email, u.status
            FROM patients p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.last_name, p.first_name
        ";
        return $this->fetchAll($query);
    }
    
    public function searchPatients(string $searchTerm): array {
        $query = "
            SELECT p.*, u.email
            FROM patients p
            JOIN users u ON p.user_id = u.id
            WHERE 
                p.first_name ILIKE :search
                OR p.last_name ILIKE :search
                OR p.pesel ILIKE :search
                OR u.email ILIKE :search
            ORDER BY p.last_name, p.first_name
        ";
        
        $searchParam = "%$searchTerm%";
        return $this->fetchAll($query, [':search' => $searchParam]);
    }
    
    public function peselExists(string $pesel): bool {
        $query = "SELECT COUNT(*) as count FROM patients WHERE pesel = :pesel";
        $result = $this->fetchOne($query, [':pesel' => $pesel]);
        return $result && $result['count'] > 0;
    }
}