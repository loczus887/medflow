<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/PatientRepository.php';
require_once __DIR__ . '/../repository/MedicalRecordRepository.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../services/ValidationService.php';

class PatientController extends AppController {

    private PatientRepository $patientRepository;
    private MedicalRecordRepository $medicalRecordRepository;
    private AppointmentRepository $appointmentRepository;

    public function __construct() {
        parent::__construct();
        $this->patientRepository = new PatientRepository();
        $this->medicalRecordRepository = new MedicalRecordRepository();
        $this->appointmentRepository = new AppointmentRepository();
    }

    public function profile() {
        $this->requireRole('patient');
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('patient-dashboard');
        }
        
        $this->render('patient-profile', ['patient' => $patient]);
    }

    public function editProfile() {
        $this->requireRole('patient');
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('patient-dashboard');
        }
        
        if ($this->isGet()) {
            return $this->render('edit-patient-profile', ['patient' => $patient]);
        }
        
        $phone = ValidationService::sanitizeString($_POST['phone'] ?? '');
        $address = ValidationService::sanitizeString($_POST['address'] ?? '');
        $city = ValidationService::sanitizeString($_POST['city'] ?? '');
        $postalCode = ValidationService::sanitizeString($_POST['postal_code'] ?? '');
        
        $phoneValidation = ValidationService::validatePhone($phone);
        if (!$phoneValidation['valid']) {
            $this->setFlash('error', $phoneValidation['error']);
            $this->redirect('edit-profile');
        }
        
        $data = [
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode
        ];
        
        if ($this->patientRepository->updatePatient($patient['id'], $data)) {
            $this->setFlash('success', 'Profil został zaktualizowany');
        } else {
            $this->setFlash('error', 'Nie udało się zaktualizować profilu');
        }
        
        $this->redirect('profile');
    }

    public function medicalHistory() {
        $this->requireRole('patient');
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('patient-dashboard');
        }
        
        $medicalRecords = $this->medicalRecordRepository->getMedicalRecordsByPatient($patient['id']);
        
        $this->render('patient-medical-history', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords
        ]);
    }

    public function viewMedicalRecord() {
        $this->requireRole('patient');
        
        $recordId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
        
        if (!$recordId) {
            $this->redirect('medical-history');
        }
        
        $record = $this->medicalRecordRepository->getMedicalRecordById($recordId);
        
        if (!$record) {
            $this->setFlash('error', 'Medical record not found');
            $this->redirect('medical-history');
        }
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient || $patient['id'] != $record['patient_id']) {
            $this->setFlash('error', 'Unauthorized');
            $this->redirect('medical-history');
        }
        
        $appointment = $this->appointmentRepository->getAppointmentById($record['appointment_id']);
        
        $this->render('view-medical-record', [
            'record' => $record,
            'appointment' => $appointment
        ]);
    }

    public function listPatients() {
        $this->requireAnyRole(['admin', 'receptionist', 'doctor']);
        
        $searchTerm = $_GET['search'] ?? '';
        
        if ($searchTerm) {
            $patients = $this->patientRepository->searchPatients($searchTerm);
        } else {
            $patients = $this->patientRepository->getAllPatients();
        }
        
        $this->render('patients-list', [
            'patients' => $patients,
            'searchTerm' => $searchTerm
        ]);
    }

    public function viewPatient() {
        $this->requireAnyRole(['admin', 'receptionist', 'doctor']);
        
        $patientId = ValidationService::sanitizeInt($_GET['id'] ?? 0);
        
        if (!$patientId) {
            $this->redirect('patients-list');
        }
        
        $patient = $this->patientRepository->getPatientById($patientId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient not found');
            $this->redirect('patients-list');
        }
        
        $appointments = $this->appointmentRepository->getAppointmentsByPatient($patientId);
        $medicalRecords = $this->medicalRecordRepository->getMedicalRecordsByPatient($patientId);
        
        $this->render('view-patient', [
            'patient' => $patient,
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords
        ]);
    }

    public function createPatient() {
        $this->requireAnyRole(['admin', 'receptionist']);
        
        if ($this->isGet()) {
            return $this->render('create-patient');
        }
        
        $email = ValidationService::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = ValidationService::sanitizeString($_POST['first_name'] ?? '');
        $lastName = ValidationService::sanitizeString($_POST['last_name'] ?? '');
        $pesel = ValidationService::sanitizeString($_POST['pesel'] ?? '');
        $dateOfBirth = ValidationService::sanitizeString($_POST['date_of_birth'] ?? '');
        $phone = ValidationService::sanitizeString($_POST['phone'] ?? '');
        $address = ValidationService::sanitizeString($_POST['address'] ?? '');
        $city = ValidationService::sanitizeString($_POST['city'] ?? '');
        $postalCode = ValidationService::sanitizeString($_POST['postal_code'] ?? '');
        
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'pesel', 'date_of_birth'];
        $validation = ValidationService::validateRequiredFields($_POST, $requiredFields);
        
        if (!$validation['valid']) {
            $this->setFlash('error', implode(', ', $validation['errors']));
            $this->redirect('create-patient');
        }
        
        $emailValidation = ValidationService::validateEmail($email);
        if (!$emailValidation['valid']) {
            $this->setFlash('error', $emailValidation['error']);
            $this->redirect('create-patient');
        }
        
        $peselValidation = ValidationService::validatePesel($pesel);
        if (!$peselValidation['valid']) {
            $this->setFlash('error', $peselValidation['error']);
            $this->redirect('create-patient');
        }
        
        $dobValidation = ValidationService::validateDateOfBirth($dateOfBirth);
        if (!$dobValidation['valid']) {
            $this->setFlash('error', $dobValidation['error']);
            $this->redirect('create-patient');
        }
        
        if ($this->patientRepository->peselExists($pesel)) {
            $this->setFlash('error', 'PESEL already exists');
            $this->redirect('create-patient');
        }
        
        require_once __DIR__ . '/../repository/UserRepository.php';
        require_once __DIR__ . '/../repository/RoleRepository.php';
        require_once __DIR__ . '/../models/User.php';
        
        $userRepository = new UserRepository();
        $roleRepository = new RoleRepository();
        
        if ($userRepository->emailExists($email)) {
            $this->setFlash('error', 'Email already exists');
            $this->redirect('create-patient');
        }
        
        $roleId = $roleRepository->getRoleIdByName('patient');
        
        if (!$roleId) {
            $this->setFlash('error', 'Patient role not found');
            $this->redirect('create-patient');
        }
        
        try {
            $this->patientRepository->beginTransaction();
            
            $hashedPassword = User::hashPassword($password);
            $userId = $userRepository->createUser($email, $hashedPassword, $roleId);
            
            if (!$userId) {
                throw new Exception('Failed to create user');
            }
            
            $patientData = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'pesel' => $pesel,
                'date_of_birth' => $dateOfBirth,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode
            ];
            
            $patientId = $this->patientRepository->createPatient($patientData);
            
            if (!$patientId) {
                throw new Exception('Failed to create patient');
            }
            
            $this->patientRepository->commit();
            
            $this->setFlash('success', 'Patient created successfully');
            $this->redirect('view-patient?id=' . $patientId);
            
        } catch (Exception $e) {
            $this->patientRepository->rollback();
            $this->setFlash('error', 'Failed to create patient: ' . $e->getMessage());
            $this->redirect('create-patient');
        }
    }
}