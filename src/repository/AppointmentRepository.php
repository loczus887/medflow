<?php

require_once __DIR__ . '/Repository.php';

class AppointmentRepository extends Repository {
    
    public function createAppointment(array $data): ?int {
        return $this->insert('appointments', $data);
    }
    
    public function getAppointmentById(int $id): ?array {
        return $this->findById('appointments', $id);
    }
    
    public function updateAppointment(int $id, array $data): bool {
        return $this->update('appointments', $id, $data);
    }
    
    public function deleteAppointment(int $id): bool {
        return $this->delete('appointments', $id);
    }
    
    public function getAppointmentsByPatient(int $patientId): array {
        $query = "
            SELECT a.*, 
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name,
                   d.title as doctor_title
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = :patient_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";
        return $this->fetchAll($query, [':patient_id' => $patientId]);
    }
    
    public function getAppointmentsByDoctor(int $doctorId, ?string $date = null): array {
        if ($date) {
            $query = "
                SELECT a.*,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       p.pesel as patient_pesel
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.doctor_id = :doctor_id
                AND a.appointment_date = :date
                ORDER BY a.appointment_time
            ";
            return $this->fetchAll($query, [
                ':doctor_id' => $doctorId,
                ':date' => $date
            ]);
        } else {
            $query = "
                SELECT a.*,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       p.pesel as patient_pesel
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.doctor_id = :doctor_id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ";
            return $this->fetchAll($query, [':doctor_id' => $doctorId]);
        }
    }
    
    public function getUpcomingAppointments(int $patientId): array {
        $query = "
            SELECT a.*,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name,
                   d.title as doctor_title,
                   s.name as specialization
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
            LEFT JOIN specializations s ON ds.specialization_id = s.id
            WHERE a.patient_id = :patient_id
            AND a.appointment_date >= CURRENT_DATE
            AND a.status IN ('scheduled', 'confirmed')
            ORDER BY a.appointment_date, a.appointment_time
        ";
        return $this->fetchAll($query, [':patient_id' => $patientId]);
    }
    
    public function getPastAppointments(int $patientId): array {
        $query = "
            SELECT a.*,
                   d.first_name as doctor_first_name,
                   d.last_name as doctor_last_name,
                   d.title as doctor_title
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = :patient_id
            AND a.status = 'completed'
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";
        return $this->fetchAll($query, [':patient_id' => $patientId]);
    }
    
    public function getTodayAppointments(int $doctorId): array {
        $query = "
            SELECT a.*,
                   p.first_name as patient_first_name,
                   p.last_name as patient_last_name,
                   p.pesel as patient_pesel,
                   p.phone as patient_phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = :doctor_id
            AND a.appointment_date = CURRENT_DATE
            AND a.status NOT IN ('cancelled')
            ORDER BY a.appointment_time
        ";
        return $this->fetchAll($query, [':doctor_id' => $doctorId]);
    }
    
    public function getAppointmentsByDateRange(string $startDate, string $endDate, ?int $doctorId = null): array {
        if ($doctorId) {
            $query = "
                SELECT a.*,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       d.first_name as doctor_first_name,
                       d.last_name as doctor_last_name,
                       d.title as doctor_title
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.appointment_date BETWEEN :start_date AND :end_date
                AND a.doctor_id = :doctor_id
                ORDER BY a.appointment_date, a.appointment_time
            ";
            return $this->fetchAll($query, [
                ':start_date' => $startDate,
                ':end_date' => $endDate,
                ':doctor_id' => $doctorId
            ]);
        } else {
            $query = "
                SELECT a.*,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       d.first_name as doctor_first_name,
                       d.last_name as doctor_last_name,
                       d.title as doctor_title
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.appointment_date BETWEEN :start_date AND :end_date
                ORDER BY a.appointment_date, a.appointment_time
            ";
            return $this->fetchAll($query, [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
        }
    }
    
    public function isSlotAvailable(int $doctorId, string $date, string $time): bool {
        $query = "
            SELECT COUNT(*) as count
            FROM appointments
            WHERE doctor_id = :doctor_id
            AND appointment_date = :date
            AND appointment_time = :time
            AND status NOT IN ('cancelled')
        ";
        $result = $this->fetchOne($query, [
            ':doctor_id' => $doctorId,
            ':date' => $date,
            ':time' => $time
        ]);
        return $result && $result['count'] == 0;
    }
    
    public function cancelAppointment(int $id): bool {
        return $this->update('appointments', $id, ['status' => 'cancelled']);
    }
    
    public function confirmAppointment(int $id): bool {
        return $this->update('appointments', $id, ['status' => 'confirmed']);
    }
    
    public function completeAppointment(int $id): bool {
        return $this->update('appointments', $id, ['status' => 'completed']);
    }
    
    public function getAppointmentStatsByDoctor(int $doctorId): array {
        $query = "
            SELECT 
                COUNT(*) as total_appointments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COUNT(CASE WHEN appointment_date >= CURRENT_DATE AND status IN ('scheduled', 'confirmed') THEN 1 END) as upcoming
            FROM appointments
            WHERE doctor_id = :doctor_id
        ";
        return $this->fetchOne($query, [':doctor_id' => $doctorId]) ?? [];
    }
}