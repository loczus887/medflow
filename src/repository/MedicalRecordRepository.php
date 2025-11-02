<?php

require_once __DIR__ . '/Repository.php';

class MedicalRecordRepository extends Repository {
    
    public function createMedicalRecord(array $data): ?int {
        return $this->insert('medical_records', $data);
    }
    
    public function getMedicalRecordById(int $id): ?array {
        return $this->findById('medical_records', $id);
    }
    
    public function getMedicalRecordByAppointment(int $appointmentId): ?array {
        $query = "SELECT * FROM medical_records WHERE appointment_id = :appointment_id";
        return $this->fetchOne($query, [':appointment_id' => $appointmentId]);
    }
    
    public function updateMedicalRecord(int $id, array $data): bool {
        return $this->update('medical_records', $id, $data);
    }
    
    public function getMedicalRecordsByPatient(int $patientId): array {
        $query = "
            SELECT mr.*,
                   a.appointment_date,
                   a.appointment_time,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name,
                   d.title as doctor_title,
                   s.name as specialization
            FROM medical_records mr
            JOIN appointments a ON mr.appointment_id = a.id
            JOIN doctors d ON mr.doctor_id = d.id
            LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
            LEFT JOIN specializations s ON ds.specialization_id = s.id
            WHERE mr.patient_id = :patient_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";
        return $this->fetchAll($query, [':patient_id' => $patientId]);
    }
    
    public function getMedicalRecordsByDoctor(int $doctorId): array {
        $query = "
            SELECT mr.*,
                   a.appointment_date,
                   a.appointment_time,
                   p.first_name as patient_first_name,
                   p.last_name as patient_last_name,
                   p.pesel as patient_pesel
            FROM medical_records mr
            JOIN appointments a ON mr.appointment_id = a.id
            JOIN patients p ON mr.patient_id = p.id
            WHERE mr.doctor_id = :doctor_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";
        return $this->fetchAll($query, [':doctor_id' => $doctorId]);
    }
    
    public function getPatientMedicalHistory(int $patientId, int $limit = 10): array {
        $query = "
            SELECT mr.*,
                   a.appointment_date,
                   a.appointment_time,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name,
                   d.title as doctor_title
            FROM medical_records mr
            JOIN appointments a ON mr.appointment_id = a.id
            JOIN doctors d ON mr.doctor_id = d.id
            WHERE mr.patient_id = :patient_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT :limit
        ";
        return $this->fetchAll($query, [
            ':patient_id' => $patientId,
            ':limit' => $limit
        ]);
    }
    
    public function searchMedicalRecordsByDiagnosis(string $searchTerm): array {
        $query = "
            SELECT mr.*,
                   a.appointment_date,
                   p.first_name as patient_first_name,
                   p.last_name as patient_last_name,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name
            FROM medical_records mr
            JOIN appointments a ON mr.appointment_id = a.id
            JOIN patients p ON mr.patient_id = p.id
            JOIN doctors d ON mr.doctor_id = d.id
            WHERE mr.diagnosis_icd10 ILIKE :search
            OR mr.diagnosis_description ILIKE :search
            ORDER BY a.appointment_date DESC
        ";
        $searchParam = "%$searchTerm%";
        return $this->fetchAll($query, [':search' => $searchParam]);
    }
    
    public function getRecordsWithUpcomingFollowUp(): array {
        $query = "
            SELECT mr.*,
                   a.appointment_date,
                   p.first_name as patient_first_name,
                   p.last_name as patient_last_name,
                   p.phone as patient_phone,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name
            FROM medical_records mr
            JOIN appointments a ON mr.appointment_id = a.id
            JOIN patients p ON mr.patient_id = p.id
            JOIN doctors d ON mr.doctor_id = d.id
            WHERE mr.next_visit_date IS NOT NULL
            AND mr.next_visit_date >= CURRENT_DATE
            AND mr.next_visit_date <= CURRENT_DATE + INTERVAL '7 days'
            ORDER BY mr.next_visit_date
        ";
        return $this->fetchAll($query);
    }
    
    public function getRecordStatsByDoctor(int $doctorId): array {
        $query = "
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT mr.patient_id) as unique_patients,
                COUNT(CASE WHEN mr.next_visit_date IS NOT NULL THEN 1 END) as follow_up_required
            FROM medical_records mr
            WHERE mr.doctor_id = :doctor_id
        ";
        return $this->fetchOne($query, [':doctor_id' => $doctorId]) ?? [];
    }
}