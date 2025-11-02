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