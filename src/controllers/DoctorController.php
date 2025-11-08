<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/DoctorRepository.php';
require_once __DIR__ . '/../repository/SpecializationRepository.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../repository/MedicalRecordRepository.php';
require_once __DIR__ . '/../services/ValidationService.php';

class DoctorController extends AppController {

    private DoctorRepository $doctorRepository;
    private SpecializationRepository $specializationRepository;
    private AppointmentRepository $appointmentRepository;
    private MedicalRecordRepository $medicalRecordRepository;

    public function __construct() {
        parent::__construct();
        $this->doctorRepository = new DoctorRepository();
        $this->specializationRepository = new SpecializationRepository();
        $this->appointmentRepository = new AppointmentRepository();
        $this->medicalRecordRepository = new MedicalRecordRepository();
    }

    public function listDoctors() {
        $this->requireAnyRole(['admin', 'receptionist']);
        
        $doctors = $this->doctorRepository->getDoctorsWithSpecializations();
        
        $this->render('doctors-list', ['doctors' => $doctors]);
    }

    public function viewDoctor() {
        $this->requireAnyRole(['admin', 'receptionist']);
        
        $doctorId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
        
        if (!$doctorId) {
            $this->redirect('doctors-list');
        }
        
        $doctor = $this->doctorRepository->getDoctorById($doctorId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor not found');
            $this->redirect('doctors-list');
        }
        
        $stats = $this->appointmentRepository->getAppointmentStatsByDoctor($doctorId);
        $todayAppointments = $this->appointmentRepository->getTodayAppointments($doctorId);
        
        $this->render('view-doctor', [
            'doctor' => $doctor,
            'stats' => $stats,
            'todayAppointments' => $todayAppointments
        ]);
    }

    public function createDoctor() {
        $this->requireRole('admin');
        
        if ($this->isGet()) {
            $specializations = $this->specializationRepository->getAllSpecializations();
            return $this->render('create-doctor', ['specializations' => $specializations]);
        }
        
        $email = ValidationService::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = ValidationService::sanitizeString($_POST['first_name'] ?? '');
        $lastName = ValidationService::sanitizeString($_POST['last_name'] ?? '');
        $title = ValidationService::sanitizeString($_POST['title'] ?? '');
        $licenseNumber = ValidationService::sanitizeString($_POST['license_number'] ?? '');
        $phone = ValidationService::sanitizeString($_POST['phone'] ?? '');
        $bio = ValidationService::sanitizeString($_POST['bio'] ?? '');
        $specializationIds = $_POST['specializations'] ?? [];
        
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'license_number'];
        $validation = ValidationService::validateRequiredFields($_POST, $requiredFields);
        
        if (!$validation['valid']) {
            $this->setFlash('error', implode(', ', $validation['errors']));
            $this->redirect('create-doctor');
        }
        
        $emailValidation = ValidationService::validateEmail($email);
        if (!$emailValidation['valid']) {
            $this->setFlash('error', $emailValidation['error']);
            $this->redirect('create-doctor');
        }
        
        $passwordValidation = ValidationService::validatePassword($password);
        if (!$passwordValidation['valid']) {
            $this->setFlash('error', $passwordValidation['error']);
            $this->redirect('create-doctor');
        }
        
        require_once __DIR__ . '/../repository/UserRepository.php';
        require_once __DIR__ . '/../repository/RoleRepository.php';
        require_once __DIR__ . '/../models/User.php';
        
        $userRepository = new UserRepository();
        $roleRepository = new RoleRepository();
        
        if ($userRepository->emailExists($email)) {
            $this->setFlash('error', 'Email already exists');
            $this->redirect('create-doctor');
        }
        
        $roleId = $roleRepository->getRoleIdByName('doctor');
        
        if (!$roleId) {
            $this->setFlash('error', 'Doctor role not found');
            $this->redirect('create-doctor');
        }
        
        try {
            $this->doctorRepository->beginTransaction();
            
            $hashedPassword = User::hashPassword($password);
            $userId = $userRepository->createUser($email, $hashedPassword, $roleId);
            
            if (!$userId) {
                throw new Exception('Failed to create user');
            }
            
            $doctorData = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'title' => $title,
                'license_number' => $licenseNumber,
                'phone' => $phone,
                'bio' => $bio
            ];
            
            $doctorId = $this->doctorRepository->createDoctor($doctorData);
            
            if (!$doctorId) {
                throw new Exception('Failed to create doctor');
            }
            
            foreach ($specializationIds as $specializationId) {
                $this->doctorRepository->addSpecialization($doctorId, (int)$specializationId);
            }
            
            $this->doctorRepository->commit();
            
            $this->setFlash('success', 'Doctor created successfully');
            $this->redirect('view-doctor?id=' . $doctorId);
            
        } catch (Exception $e) {
            $this->doctorRepository->rollback();
            $this->setFlash('error', 'Failed to create doctor: ' . $e->getMessage());
            $this->redirect('create-doctor');
        }
    }

    public function editDoctor() {
        $this->requireRole('admin');
        
        $doctorId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
        
        if (!$doctorId) {
            $this->redirect('doctors-list');
        }
        
        $doctor = $this->doctorRepository->getDoctorById($doctorId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor not found');
            $this->redirect('doctors-list');
        }
        
        if ($this->isGet()) {
            $specializations = $this->specializationRepository->getAllSpecializations();
            return $this->render('edit-doctor', [
                'doctor' => $doctor,
                'specializations' => $specializations
            ]);
        }
        
        $firstName = ValidationService::sanitizeString($_POST['first_name'] ?? '');
        $lastName = ValidationService::sanitizeString($_POST['last_name'] ?? '');
        $title = ValidationService::sanitizeString($_POST['title'] ?? '');
        $phone = ValidationService::sanitizeString($_POST['phone'] ?? '');
        $bio = ValidationService::sanitizeString($_POST['bio'] ?? '');
        
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'title' => $title,
            'phone' => $phone,
            'bio' => $bio
        ];
        
        if ($this->doctorRepository->updateDoctor($doctorId, $data)) {
            $this->setFlash('success', 'Doctor updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update doctor');
        }
        
        $this->redirect('view-doctor?id=' . $doctorId);
    }

    public function myProfile() {
        $this->requireRole('doctor');
        
        $userId = $this->getCurrentUserId();
        $doctor = $this->doctorRepository->getDoctorByUserId($userId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor profile not found');
            $this->redirect('dashboard');
        }
        
        $stats = $this->appointmentRepository->getAppointmentStatsByDoctor($doctor['id']);
        
        $this->render('doctor-profile', [
            'doctor' => $doctor,
            'stats' => $stats
        ]);
    }

    public function myPatients() {
        $this->requireRole('doctor');
        
        $userId = $this->getCurrentUserId();
        $doctor = $this->doctorRepository->getDoctorByUserId($userId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor profile not found');
            $this->redirect('dashboard');
        }
        
        $appointments = $this->appointmentRepository->getAppointmentsByDoctor($doctor['id']);
        
        $patientIds = array_unique(array_column($appointments, 'patient_id'));
        
        $patients = [];
        foreach ($patientIds as $patientId) {
            require_once __DIR__ . '/../repository/PatientRepository.php';
            $patientRepository = new PatientRepository();
            $patient = $patientRepository->getPatientById($patientId);
            if ($patient) {
                $patients[] = $patient;
            }
        }
        
        $this->render('doctor-patients', [
            'doctor' => $doctor,
            'patients' => $patients
        ]);
    }

    public function mySchedule() {
        $this->requireRole('doctor');
        
        $userId = $this->getCurrentUserId();
        $doctor = $this->doctorRepository->getDoctorByUserId($userId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor profile not found');
            $this->redirect('dashboard');
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $appointments = $this->appointmentRepository->getAppointmentsByDoctor($doctor['id'], $date);
        
        $this->render('doctor-schedule', [
            'doctor' => $doctor,
            'appointments' => $appointments,
            'date' => $date
        ]);
    }

    public function addSpecialization() {
        $this->requireRole('admin');
        
        if (!$this->isPost()) {
            $this->redirect('doctors-list');
        }
        
        $doctorId = ValidationService::sanitizeInt($_POST['doctor_id'] ?? 0);
        $specializationId = ValidationService::sanitizeInt($_POST['specialization_id'] ?? 0);
        
        if (!$doctorId || !$specializationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters'], 400);
        }
        
        if ($this->doctorRepository->addSpecialization($doctorId, $specializationId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Specialization added']);
        }
        
        $this->jsonResponse(['success' => false, 'message' => 'Failed to add specialization'], 400);
    }

    public function removeSpecialization() {
        $this->requireRole('admin');
        
        if (!$this->isPost()) {
            $this->redirect('doctors-list');
        }
        
        $doctorId = ValidationService::sanitizeInt($_POST['doctor_id'] ?? 0);
        $specializationId = ValidationService::sanitizeInt($_POST['specialization_id'] ?? 0);
        
        if (!$doctorId || !$specializationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters'], 400);
        }
        
        if ($this->doctorRepository->removeSpecialization($doctorId, $specializationId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Specialization removed']);
        }
        
        $this->jsonResponse(['success' => false, 'message' => 'Failed to remove specialization'], 400);
    }
}