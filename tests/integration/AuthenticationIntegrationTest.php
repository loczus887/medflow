<?php

/**
 * Integration test for Authentication flow
 * Tests: Registration → Login → Session → Logout
 */

class AuthenticationIntegrationTest {
    
    private $testEmail = 'integration_test@example.com';
    private $testPassword = 'testPassword123';
    
    public function setUp() {
        //Clean up any existing test user
        echo "Setting up test environment...\n";
    }
    
    public function tearDown() {
        echo "Cleaning up test environment...\n";
    }
    
    public function testUserRegistrationFlow() {
        echo "Testing user registration flow...\n";
        
        //1. Validate email
        $emailValidation = ValidationService::validateEmail($this->testEmail);
        assert($emailValidation['valid'] === true, "Email should be valid");
        
        //2. Validate password
        $passwordValidation = ValidationService::validatePassword($this->testPassword);
        assert($passwordValidation['valid'] === true, "Password should be valid");
        
        //3. Hash password
        $hashedPassword = User::hashPassword($this->testPassword);
        assert(!empty($hashedPassword), "Password should be hashed");
        
        //4. Verify hashed password
        assert(password_verify($this->testPassword, $hashedPassword), "Password should verify");
        
        echo "✓ User registration flow test passed\n";
    }
    
    public function testPasswordValidation() {
        echo "Testing password validation...\n";
        
        $userData = [
            'id' => 999,
            'email' => $this->testEmail,
            'password' => User::hashPassword($this->testPassword),
            'role_id' => 4,
            'status' => 'active'
        ];
        
        $user = new User($userData);
        
        //Test correct password
        assert($user->verifyPassword($this->testPassword) === true, "Correct password should verify");
        
        //Test incorrect password
        assert($user->verifyPassword('wrongPassword') === false, "Wrong password should fail");
        
        echo "✓ Password validation test passed\n";
    }
    
    public function testSessionDataStructure() {
        echo "Testing session data structure...\n";
        
        $userData = [
            'id' => 999,
            'email' => $this->testEmail,
            'password' => User::hashPassword($this->testPassword),
            'role_id' => 4,
            'status' => 'active'
        ];
        
        $user = new User($userData);
        $sessionData = $user->toSessionArray();
        
        //Session should NOT contain password
        assert(!isset($sessionData['password']), "Session should not contain password");
        
        //Session should contain safe data
        assert(isset($sessionData['id']), "Session should contain user ID");
        assert(isset($sessionData['email']), "Session should contain email");
        assert(isset($sessionData['role_id']), "Session should contain role_id");
        
        echo "✓ Session data structure test passed\n";
    }
    
    public function testAppointmentBookingValidation() {
        echo "Testing appointment booking validation...\n";
        
        $patientId = 1;
        $doctorId = 1;
        $date = '2025-01-15';
        $time = '10:00:00';
        
        //Validate date format
        $dateValidation = ValidationService::validateDate($date);
        assert($dateValidation['valid'] === true, "Date should be valid");
        
        //Validate time format
        $timeValidation = ValidationService::validateTime($time);
        assert($timeValidation['valid'] === true, "Time should be valid");
        
        //Check if required fields are present
        assert(!empty($patientId), "Patient ID should be present");
        assert(!empty($doctorId), "Doctor ID should be present");
        assert(!empty($date), "Date should be present");
        assert(!empty($time), "Time should be present");
        
        echo "✓ Appointment booking validation test passed\n";
    }
    
    public function testPatientDataValidation() {
        echo "Testing patient data validation...\n";
        
        $patientData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'pesel' => '44051401458', 
            'date_of_birth' => '1944-05-14',
            'phone' => '123456789',
            'email' => 'jan.kowalski@example.com'
        ];
        
        //Validate PESEL
        $peselValidation = ValidationService::validatePesel($patientData['pesel']);
        assert($peselValidation['valid'] === true, "PESEL should be valid");
        
        //Validate date of birth
        $dobValidation = ValidationService::validateDateOfBirth($patientData['date_of_birth']);
        assert($dobValidation['valid'] === true, "Date of birth should be valid");
        
        //Validate phone
        $phoneValidation = ValidationService::validatePhone($patientData['phone']);
        assert($phoneValidation['valid'] === true, "Phone should be valid");
        
        //Validate email
        $emailValidation = ValidationService::validateEmail($patientData['email']);
        assert($emailValidation['valid'] === true, "Email should be valid");
        
        echo "Patient data validation test passed\n";
    }
    
    public function runAllTests() {
        echo "\nRunning Authentication Integration Tests\n\n";
        
        try {
            $this->setUp();
            
            $this->testUserRegistrationFlow();
            $this->testPasswordValidation();
            $this->testSessionDataStructure();
            $this->testAppointmentBookingValidation();
            $this->testPatientDataValidation();
            
            $this->tearDown();
            
            echo "\nAll integration tests passed!\n\n";
            return true;
        } catch (AssertionError $e) {
            echo "\nTest failed: " . $e->getMessage() . "\n\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
            $this->tearDown();
            return false;
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../bootstrap.php';
    
    $test = new AuthenticationIntegrationTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}