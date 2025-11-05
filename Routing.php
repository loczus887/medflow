<?php

require_once 'src/controllers/AppController.php';
require_once 'src/controllers/SecurityController.php';

class Routing {
    
    private static $routes = [
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        'logout' => [
            'controller' => 'SecurityController',
            'action' => 'logout'
        ],
        'dashboard' => [
            'controller' => 'DashboardController',
            'action' => 'index'
        ],
        'patient-dashboard' => [
            'controller' => 'DashboardController',
            'action' => 'patientIndex'
        ],
        'book-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'bookAppointment'
        ],
        'my-appointments' => [
            'controller' => 'AppointmentController',
            'action' => 'myAppointments'
        ],
        'cancel-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'cancelAppointment'
        ],
        'get-available-slots' => [
            'controller' => 'AppointmentController',
            'action' => 'getAvailableSlots'
        ],
        'view-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'viewAppointment'
        ],
        'appointments-calendar' => [
            'controller' => 'AppointmentController',
            'action' => 'calendar'
        ],
        'confirm-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'confirmAppointment'
        ],
        'complete-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'completeAppointment'
        ],
        'reschedule-appointment' => [
            'controller' => 'AppointmentController',
            'action' => 'rescheduleAppointment'
        ],
        'profile' => [
            'controller' => 'PatientController',
            'action' => 'profile'
        ],
        'edit-profile' => [
            'controller' => 'PatientController',
            'action' => 'editProfile'
        ],
        'medical-history' => [
            'controller' => 'PatientController',
            'action' => 'medicalHistory'
        ],
        'view-medical-record' => [
            'controller' => 'PatientController',
            'action' => 'viewMedicalRecord'
        ],
        'patients-list' => [
            'controller' => 'PatientController',
            'action' => 'listPatients'
        ],
        'view-patient' => [
            'controller' => 'PatientController',
            'action' => 'viewPatient'
        ],
        'create-patient' => [
            'controller' => 'PatientController',
            'action' => 'createPatient'
        ]
    ];

    public static function run(string $path) {
        
        if (empty($path)) {
            $path = 'login';
        }

        if (array_key_exists($path, self::$routes)) {
            $controller = self::$routes[$path]['controller'];
            $action = self::$routes[$path]['action'];

            require_once "src/controllers/$controller.php";

            $controllerObj = new $controller;
            $controllerObj->$action();
        } else {
            http_response_code(404);
            include 'public/views/404.html';
        }
    }
}