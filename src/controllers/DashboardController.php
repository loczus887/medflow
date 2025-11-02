<?php

require_once 'AppController.php';

class DashboardController extends AppController {

    public function index() {
        $this->requireAnyRole(['admin', 'doctor', 'receptionist']);
        
        echo "<h1>Dashboard for staff</h1>";
        echo "<p>Role: " . htmlspecialchars($this->getUserRole()) . "</p>";
        echo "<p>User ID: " . $this->getCurrentUserId() . "</p>";
        echo '<a href="/logout">Wyloguj się</a>';
    }

    public function patientIndex() {
        $this->requireRole('patient');
        
        echo "<h1>Dashboard for patients</h1>";
        echo "<p>User ID: " . $this->getCurrentUserId() . "</p>";
        echo '<a href="/logout">Wyloguj się</a>';
    }
}