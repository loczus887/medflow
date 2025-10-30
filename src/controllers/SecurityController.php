<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        if ($this->isGet()) {
            return $this->render('login');
        }

        //POST logic will be implemented later
        $this->render('login');
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        if ($this->isGet()) {
            return $this->render('register');
        }

        //POST logic will be implemented later
        $this->render('register');
    }

    public function logout() {
        session_destroy();
        $this->redirect('login');
    }
}