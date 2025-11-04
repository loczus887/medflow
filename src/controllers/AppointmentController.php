<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../repository/DoctorRepository.php';
require_once __DIR__ . '/../repository/SpecializationRepository.php';
require_once __DIR__ . '/../repository/PatientRepository.php';
require_once __DIR__ . '/../services/AppointmentService.php';
require_once __DIR__ . '/../services/ValidationService.php';

class AppointmentController extends AppController {

    private AppointmentRepository $appointmentRepository;
    private DoctorRepository $doctorRepository;
    private SpecializationRepository $specializationRepository;
    private PatientRepository $patientRepository;
    private AppointmentService $appointmentService;

    public function __construct() {
        parent::__construct();
        $this->appointmentRepository = new AppointmentRepository();
        $this->doctorRepository = new DoctorRepository();
        $this->specializationRepository = new SpecializationRepository();
        $this->patientRepository = new PatientRepository();
        $this->appointmentService = new AppointmentService();
    }

    public function bookAppointment() {
        $this->requireRole('patient');
        
        if ($this->isGet()) {
            $doctors = $this->doctorRepository->getDoctorsWithSpecializations();
            $specializations = $this->specializationRepository->getAllSpecializations();
            
            return $this->render('book-appointment', [
                'doctors' => $doctors,
                'specializations' => $specializations
            ]);
        }
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('patient-dashboard');
        }
        
        $doctorId = ValidationService::sanitizeInt($_POST['doctor_id'] ?? 0);
        $date = ValidationService::sanitizeString($_POST['date'] ?? '');
        $time = ValidationService::sanitizeString($_POST['time'] ?? '');
        $type = ValidationService::sanitizeString($_POST['type'] ?? 'nfz');
        $reason = ValidationService::sanitizeString($_POST['reason'] ?? '');
        
        $result = $this->appointmentService->createAppointment(
            $patient['id'],
            $doctorId,
            $date,
            $time,
            $type,
            $reason
        );
        
        if (isset($result['error'])) {
            $this->setFlash('error', $result['error']);
            $this->redirect('book-appointment');
        }
        
        $this->setFlash('success', 'Wizyta została umówiona pomyślnie');
        $this->redirect('patient-dashboard');
    }

    public function myAppointments() {
        $this->requireRole('patient');
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('patient-dashboard');
        }
        
        $filter = $_GET['filter'] ?? 'upcoming';
        $appointments = $this->appointmentService->getPatientAppointments($patient['id'], $filter);
        
        $this->render('my-appointments', [
            'appointments' => $appointments,
            'filter' => $filter
        ]);
    }

    public function cancelAppointment() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect('patient-dashboard');
        }
        
        $appointmentId = ValidationService::sanitizeInt($_POST['appointment_id'] ?? 0);
        $userId = $this->getCurrentUserId();
        $userRole = $this->getUserRole();
        
        $result = $this->appointmentService->cancelAppointment($appointmentId, $userId, $userRole);
        
        if (isset($result['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $result['error']], 400);
        }
        
        $this->jsonResponse(['success' => true, 'message' => 'Wizyta została anulowana']);
    }

    public function getAvailableSlots() {
        $this->requireRole('patient');
        
        $doctorId = ValidationService::sanitizeInt($_GET['doctor_id'] ?? 0);
        $date = ValidationService::sanitizeString($_GET['date'] ?? '');
        
        if (!$doctorId || !$date) {
            $this->jsonResponse(['error' => 'Missing parameters'], 400);
        }
        
        $slots = $this->appointmentService->getAvailableSlots($doctorId, $date);
        
        $this->jsonResponse(['slots' => $slots]);
    }

    public function viewAppointment() {
        $this->requireLogin();
        
        $appointmentId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
        
        if (!$appointmentId) {
            $this->redirect('dashboard');
        }
        
        $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            $this->setFlash('error', 'Appointment not found');
            $this->redirect('dashboard');
        }
        
        $userRole = $this->getUserRole();
        $userId = $this->getCurrentUserId();
        
        if ($userRole === 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($userId);
            if (!$patient || $patient['id'] != $appointment['patient_id']) {
                $this->setFlash('error', 'Unauthorized');
                $this->redirect('patient-dashboard');
            }
        } elseif ($userRole === 'doctor') {
            $doctor = $this->doctorRepository->getDoctorByUserId($userId);
            if (!$doctor || $doctor['id'] != $appointment['doctor_id']) {
                $this->setFlash('error', 'Unauthorized');
                $this->redirect('dashboard');
            }
        }
        
        $this->render('view-appointment', ['appointment' => $appointment]);
    }

    public function calendar() {
        $this->requireAnyRole(['admin', 'receptionist', 'doctor']);
        
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('monday this week'));
        $endDate = $_GET['end'] ?? date('Y-m-d', strtotime('sunday this week'));
        $doctorId = $_GET['doctor_id'] ?? null;
        
        $appointments = $this->appointmentService->getAppointmentsByDateRange(
            $startDate,
            $endDate,
            $doctorId ? ValidationService::sanitizeInt($doctorId) : null
        );
        
        $doctors = $this->doctorRepository->getAllDoctors();
        
        $this->render('appointments-calendar', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedDoctorId' => $doctorId
        ]);
    }

    public function confirmAppointment() {
        $this->requireAnyRole(['admin', 'receptionist', 'doctor']);
        
        if (!$this->isPost()) {
            $this->redirect('dashboard');
        }
        
        $appointmentId = ValidationService::sanitizeInt($_POST['appointment_id'] ?? 0);
        
        $result = $this->appointmentService->confirmAppointment($appointmentId);
        
        if (isset($result['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $result['error']], 400);
        }
        
        $this->jsonResponse(['success' => true, 'message' => 'Wizyta została potwierdzona']);
    }

    public function completeAppointment() {
        $this->requireRole('doctor');
        
        if (!$this->isPost()) {
            $this->redirect('dashboard');
        }
        
        $appointmentId = ValidationService::sanitizeInt($_POST['appointment_id'] ?? 0);
        
        $result = $this->appointmentService->completeAppointment($appointmentId);
        
        if (isset($result['error'])) {
            $this->jsonResponse(['success' => false, 'message' => $result['error']], 400);
        }
        
        $this->jsonResponse(['success' => true, 'message' => 'Wizyta została zakończona']);
    }

    public function rescheduleAppointment() {
        $this->requireRole('patient');
        
        if ($this->isGet()) {
            $appointmentId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
            
            if (!$appointmentId) {
                $this->redirect('my-appointments');
            }
            
            $appointment = $this->appointmentRepository->getAppointmentById($appointmentId);
            
            if (!$appointment) {
                $this->setFlash('error', 'Appointment not found');
                $this->redirect('my-appointments');
            }
            
            $doctors = $this->doctorRepository->getDoctorsWithSpecializations();
            
            return $this->render('reschedule-appointment', [
                'appointment' => $appointment,
                'doctors' => $doctors
            ]);
        }
        
        $userId = $this->getCurrentUserId();
        $appointmentId = ValidationService::sanitizeInt($_POST['appointment_id'] ?? 0);
        $newDate = ValidationService::sanitizeString($_POST['date'] ?? '');
        $newTime = ValidationService::sanitizeString($_POST['time'] ?? '');
        
        $result = $this->appointmentService->rescheduleAppointment(
            $appointmentId,
            $newDate,
            $newTime,
            $userId,
            'patient'
        );
        
        if (isset($result['error'])) {
            $this->setFlash('error', $result['error']);
            $this->redirect('reschedule-appointment?id=' . $appointmentId);
        }
        
        $this->setFlash('success', 'Wizyta została przełożona pomyślnie');
        $this->redirect('my-appointments');
    }
}