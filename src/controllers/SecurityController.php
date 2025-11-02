<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

        if ($this->isGet()) {
            $message = $this->getFlash('message');
            return $this->render('login', ['message' => $message]);
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->render('login', ['message' => 'Wypełnij wszystkie pola']);
        }

        $result = $this->authService->login($email, $password);

        if ($result === null) {
            return $this->render('login', ['message' => 'Nieprawidłowy email lub hasło']);
        }

        if (isset($result['error'])) {
            return $this->render('login', ['message' => $result['error']]);
        }

        $this->redirectToDashboard();
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

        if ($this->isGet()) {
            return $this->render('register');
        }

        $email = $_POST['email'] ?? '';
        $password1 = $_POST['password1'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname = $_POST['lastname'] ?? '';

        if (empty($email) || empty($password1) || empty($password2) || empty($firstname) || empty($lastname)) {
            return $this->render('register', ['message' => 'Wypełnij wszystkie pola']);
        }

        if ($password1 !== $password2) {
            return $this->render('register', ['message' => 'Hasła nie są identyczne']);
        }

        if (strlen($password1) < 6) {
            return $this->render('register', ['message' => 'Hasło musi mieć minimum 6 znaków']);
        }

        $result = $this->authService->register($email, $password1, 'patient');

        if (isset($result['error'])) {
            return $this->render('register', ['message' => $result['error']]);
        }

        $this->setFlash('message', 'Rejestracja udana. Możesz się zalogować.');
        $this->redirect('login');
    }

    public function logout() {
        $this->authService->logout();
        $this->redirect('login');
    }

    private function redirectToDashboard(): void {
        $role = $this->getUserRole();
        
        switch ($role) {
            case 'admin':
            case 'doctor':
            case 'receptionist':
                $this->redirect('dashboard');
                break;
            case 'patient':
                $this->redirect('patient-dashboard');
                break;
            default:
                $this->redirect('login');
        }
    }
}