<?php

require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../repository/DoctorRepository.php';
require_once __DIR__ . '/../repository/PatientRepository.php';
require_once __DIR__ . '/../models/Appointment.php';

class AppointmentService {
    
    private AppointmentRepository $appointmentRepository;
    private DoctorRepository $doctorRepository;
    private PatientRepository $patientRepository;
    
    public function __construct() {
        $this->appointmentRepository = new AppointmentRepository();
        $this->doctorRepository = new DoctorRepository();
        $this->patientRepository = new PatientRepository();
    }
    
    public function createAppointment(
        int $patientId,
        int $doctorId,
        string $date,
        string $time,
        string $type,
        ?string $reason = null
    ): array {
        
        if (!$this->validateDate($date)) {
            return ['error' => 'Invalid date format'];
        }
        
        if (!$this->validateTime($time)) {
            return ['error' => 'Invalid time format'];
        }
        
        if (!$this->isDateInFuture($date)) {
            return ['error' => 'Appointment date must be in the future'];
        }
        
        if (!$this->doctorRepository->getDoctorById($doctorId)) {
            return ['error' => 'Doctor not found'];
        }
        
        if (!$this->patientRepository->getPatientById($patientId)) {
            return ['error' => 'Patient not found'];
        }
        
        if (!$this->appointmentRepository->isSlotAvailable($doctorId, $date, $time)) {
            return ['error' => 'This time slot is not available'];
        }
        
        if (!$this->isValidAppointmentType($type)) {
            return ['error' => 'Invalid appointment type'];
        }
        
        $data = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'duration' => 30,
            'type' => $type,
            'status' => 'scheduled',
            'reason' => $reason
        ];
        
        try {
            $appointmentId = $this->appointmentRepository->createAppointment($data);
            
            if ($appointmentId) {
                return ['success' => true, 'appointment_id' => $appointmentId];
            }
            
            return ['error' => 'Failed to create appointment'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function cancelAppointment(int $appointmentId, int $userId, string $userRole): array {
        $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        $appointmentObj = new Appointment($appointment);
        
        if ($appointmentObj->isCompleted()) {
            return ['error' => 'Cannot cancel completed appointment'];
        }
        
        if ($appointmentObj->isCancelled()) {
            return ['error' => 'Appointment is already cancelled'];
        }
        
        if ($userRole === 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($userId);
            if (!$patient || $patient['id'] != $appointmentObj->getPatientId()) {
                return ['error' => 'Unauthorized'];
            }
        } elseif ($userRole === 'doctor') {
            $doctor = $this->doctorRepository->getDoctorByUserId($userId);
            if (!$doctor || $doctor['id'] != $appointmentObj->getDoctorId()) {
                return ['error' => 'Unauthorized'];
            }
        }
        
        if ($this->appointmentRepository->cancelAppointment($appointmentId)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to cancel appointment'];
    }
    
    public function confirmAppointment(int $appointmentId): array {
        $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        if ($this->appointmentRepository->confirmAppointment($appointmentId)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to confirm appointment'];
    }
    
    public function completeAppointment(int $appointmentId): array {
        $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        if ($this->appointmentRepository->completeAppointment($appointmentId)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to complete appointment'];
    }
    
    public function getPatientAppointments(int $patientId, string $filter = 'all'): array {
        switch ($filter) {
            case 'upcoming':
                return $this->appointmentRepository->getUpcomingAppointments($patientId);
            case 'past':
                return $this->appointmentRepository->getPastAppointments($patientId);
            default:
                return $this->appointmentRepository->getAppointmentsByPatient($patientId);
        }
    }
    
    public function getDoctorAppointments(int $doctorId, ?string $date = null): array {
        return $this->appointmentRepository->getAppointmentsByDoctor($doctorId, $date);
    }
    
    public function getTodayAppointments(int $doctorId): array {
        return $this->appointmentRepository->getTodayAppointments($doctorId);
    }
    
    public function getAvailableSlots(int $doctorId, string $date): array {
        $slots = [];
        $startHour = 8;
        $endHour = 16;
        $slotDuration = 30;
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $slotDuration) {
                $time = sprintf('%02d:%02d:00', $hour, $minute);
                
                if ($this->appointmentRepository->isSlotAvailable($doctorId, $date, $time)) {
                    $slots[] = [
                        'time' => $time,
                        'display' => sprintf('%02d:%02d', $hour, $minute),
                        'available' => true
                    ];
                }
            }
        }
        
        return $slots;
    }
    
    public function getAppointmentsByDateRange(string $startDate, string $endDate, ?int $doctorId = null): array {
        return $this->appointmentRepository->getAppointmentsByDateRange($startDate, $endDate, $doctorId);
    }
    
    public function rescheduleAppointment(
        int $appointmentId,
        string $newDate,
        string $newTime,
        int $userId,
        string $userRole
    ): array {
        $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            return ['error' => 'Appointment not found'];
        }
        
        $appointmentObj = new Appointment($appointment);
        
        if ($appointmentObj->isCompleted() || $appointmentObj->isCancelled()) {
            return ['error' => 'Cannot reschedule this appointment'];
        }
        
        if (!$this->validateDate($newDate) || !$this->validateTime($newTime)) {
            return ['error' => 'Invalid date or time format'];
        }
        
        if (!$this->isDateInFuture($newDate)) {
            return ['error' => 'New date must be in the future'];
        }
        
        if (!$this->appointmentRepository->isSlotAvailable($appointmentObj->getDoctorId(), $newDate, $newTime)) {
            return ['error' => 'This time slot is not available'];
        }
        
        if ($userRole === 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($userId);
            if (!$patient || $patient['id'] != $appointmentObj->getPatientId()) {
                return ['error' => 'Unauthorized'];
            }
        }
        
        $data = [
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'status' => 'scheduled'
        ];
        
        if ($this->appointmentRepository->updateAppointment($appointmentId, $data)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to reschedule appointment'];
    }
    
    private function validateDate(string $date): bool {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function validateTime(string $time): bool {
        $t = DateTime::createFromFormat('H:i:s', $time);
        if (!$t) {
            $t = DateTime::createFromFormat('H:i', $time);
        }
        return $t !== false;
    }
    
    private function isDateInFuture(string $date): bool {
        $appointmentDate = new DateTime($date);
        $today = new DateTime('today');
        return $appointmentDate >= $today;
    }
    
    private function isValidAppointmentType(string $type): bool {
        return in_array($type, ['nfz', 'private']);
    }
}