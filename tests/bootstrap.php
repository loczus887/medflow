<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/src/services/SessionManager.php';
require_once BASE_PATH . '/src/services/ValidationService.php';
require_once BASE_PATH . '/src/services/AuthService.php';
require_once BASE_PATH . '/src/services/AppointmentService.php';
require_once BASE_PATH . '/src/repository/Database.php';
require_once BASE_PATH . '/src/repository/Repository.php';
require_once BASE_PATH . '/src/repository/UserRepository.php';
require_once BASE_PATH . '/src/repository/RoleRepository.php';
require_once BASE_PATH . '/src/repository/PatientRepository.php';
require_once BASE_PATH . '/src/repository/DoctorRepository.php';
require_once BASE_PATH . '/src/repository/AppointmentRepository.php';
require_once BASE_PATH . '/src/models/User.php';
require_once BASE_PATH . '/src/models/Patient.php';
require_once BASE_PATH . '/src/models/Doctor.php';
require_once BASE_PATH . '/src/models/Appointment.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "Test environment initialized\n";