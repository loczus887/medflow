<?php

require_once __DIR__ . '/Repository.php';

class DoctorRepository extends Repository {
    
    public function createDoctor(array $data): ?int {
        return $this->insert('doctors', $data);
    }
    
    public function getDoctorById(int $id): ?array {
        return $this->findById('doctors', $id);
    }
    
    public function getDoctorByUserId(int $userId): ?array {
        $query = "SELECT * FROM doctors WHERE user_id = :user_id";
        return $this->fetchOne($query, [':user_id' => $userId]);
    }
    
    public function getAllDoctors(): array {
        $query = "
            SELECT d.*, u.email, u.status
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            ORDER BY d.last_name, d.first_name
        ";
        return $this->fetchAll($query);
    }
    
    public function getDoctorsWithSpecializations(): array {
        $query = "
            SELECT 
                d.id,
                d.first_name,
                d.last_name,
                d.title,
                d.license_number,
                d.phone,
                u.email,
                u.status,
                STRING_AGG(s.name, ', ') as specializations
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
            LEFT JOIN specializations s ON ds.specialization_id = s.id
            GROUP BY d.id, d.first_name, d.last_name, d.title, d.license_number, d.phone, u.email, u.status
            ORDER BY d.last_name, d.first_name
        ";
        return $this->fetchAll($query);
    }
    
    public function getDoctorsBySpecialization(int $specializationId): array {
        $query = "
            SELECT d.*, s.name as specialization_name
            FROM doctors d
            JOIN doctor_specializations ds ON d.id = ds.doctor_id
            JOIN specializations s ON ds.specialization_id = s.id
            WHERE ds.specialization_id = :specialization_id
            ORDER BY d.last_name, d.first_name
        ";
        return $this->fetchAll($query, [':specialization_id' => $specializationId]);
    }
    
    public function updateDoctor(int $id, array $data): bool {
        return $this->update('doctors', $id, $data);
    }
    
    public function addSpecialization(int $doctorId, int $specializationId): bool {
        $query = "
            INSERT INTO doctor_specializations (doctor_id, specialization_id)
            VALUES (:doctor_id, :specialization_id)
            ON CONFLICT DO NOTHING
        ";
        $stmt = $this->executeQuery($query, [
            ':doctor_id' => $doctorId,
            ':specialization_id' => $specializationId
        ]);
        return $stmt->rowCount() > 0;
    }
    
    public function removeSpecialization(int $doctorId, int $specializationId): bool {
        $query = "
            DELETE FROM doctor_specializations 
            WHERE doctor_id = :doctor_id AND specialization_id = :specialization_id
        ";
        $stmt = $this->executeQuery($query, [
            ':doctor_id' => $doctorId,
            ':specialization_id' => $specializationId
        ]);
        return $stmt->rowCount() > 0;
    }
}