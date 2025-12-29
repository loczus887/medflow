<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/DoctorRepository.php';
require_once __DIR__ . '/../repository/PatientRepository.php';
require_once __DIR__ . '/../repository/AppointmentRepository.php';
require_once __DIR__ . '/../services/AppointmentService.php';

class DashboardController extends AppController {

    private DoctorRepository $doctorRepository;
    private PatientRepository $patientRepository;
    private AppointmentRepository $appointmentRepository;
    private AppointmentService $appointmentService;

    public function __construct() {
        parent::__construct();
        $this->doctorRepository = new DoctorRepository();
        $this->patientRepository = new PatientRepository();
        $this->appointmentRepository = new AppointmentRepository();
        $this->appointmentService = new AppointmentService();
    }

    public function index() {
        $this->requireLogin();
        
        $role = $this->getUserRole();
        
        switch ($role) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'doctor':
                $this->doctorDashboard();
                break;
            case 'receptionist':
                $this->receptionistDashboard();
                break;
            default:
                $this->redirect('login');
        }
    }

    public function patientIndex() {
        $this->requireRole('patient');
        
        $userId = $this->getCurrentUserId();
        $patient = $this->patientRepository->getPatientByUserId($userId);
        
        if (!$patient) {
            $this->setFlash('error', 'Patient profile not found');
            $this->redirect('login');
        }
        
        $upcomingAppointments = $this->appointmentRepository->getUpcomingAppointments($patient['id']);
        $pastAppointments = $this->appointmentRepository->getPastAppointments($patient['id']);
        
        $this->render('patient-dashboard', [
            'patient' => $patient,
            'upcomingAppointments' => $upcomingAppointments,
            'pastAppointments' => $pastAppointments
        ]);
    }

    private function adminDashboard() {
        $doctors = $this->doctorRepository->getDoctorsWithSpecializations();
        $patients = $this->patientRepository->getAllPatients();
        
        $today = date('Y-m-d');
        $todayAppointments = $this->appointmentRepository->getAppointmentsByDateRange($today, $today);
        
        $stats = [
            'total_doctors' => count($doctors),
            'total_patients' => count($patients),
            'appointments_today' => count($todayAppointments)
        ];
        
        $this->render('admin-dashboard', [
            'stats' => $stats,
            'doctors' => $doctors,
            'patients' => $patients,
            'todayAppointments' => $todayAppointments
        ]);
    }

    private function doctorDashboard() {
        $userId = $this->getCurrentUserId();
        $doctor = $this->doctorRepository->getDoctorByUserId($userId);
        
        if (!$doctor) {
            $this->setFlash('error', 'Doctor profile not found');
            $this->redirect('login');
        }
        
        $todayAppointments = $this->appointmentRepository->getTodayAppointments($doctor['id']);
        $upcomingAppointments = $this->appointmentRepository->getUpcomingAppointmentsByDoctor($doctor['id']);
        $stats = $this->appointmentRepository->getAppointmentStatsByDoctor($doctor['id']);
        
        $this->render('doctor-dashboard', [
            'doctor' => $doctor,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'stats' => $stats
        ]);
    }

    private function receptionistDashboard() {
        $today = date('Y-m-d');
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        
        $todayAppointments = $this->appointmentRepository->getAppointmentsByDateRange($today, $today);
        $weekAppointments = $this->appointmentRepository->getAppointmentsByDateRange($startOfWeek, $endOfWeek);
        $doctors = $this->doctorRepository->getAllDoctors();
        
        $this->render('receptionist-dashboard', [
            'todayAppointments' => $todayAppointments,
            'weekAppointments' => $weekAppointments,
            'doctors' => $doctors,
            'currentDate' => $today
        ]);
    }
}