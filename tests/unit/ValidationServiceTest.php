<?php

/**
 * Unit tests for ValidationService
 */

class ValidationServiceTest {
    
    public function testValidateEmail() {
        echo "Testing email validation...\n";
        
        // Valid emails
        $result = ValidationService::validateEmail('test@example.com');
        assert($result['valid'] === true, "Valid email should pass");
        
        $result = ValidationService::validateEmail('user.name+tag@example.co.uk');
        assert($result['valid'] === true, "Complex valid email should pass");
        
        // Invalid emails
        $result = ValidationService::validateEmail('invalid.email');
        assert($result['valid'] === false, "Email without @ should fail");
        
        $result = ValidationService::validateEmail('');
        assert($result['valid'] === false, "Empty email should fail");
        
        $result = ValidationService::validateEmail('@example.com');
        assert($result['valid'] === false, "Email without local part should fail");
        
        echo "Email validation tests passed\n";
    }
    
    public function testValidatePassword() {
        echo "Testing password validation...\n";
        
        //Valid passwords
        $result = ValidationService::validatePassword('password123');
        assert($result['valid'] === true, "Valid password should pass");
        
        $result = ValidationService::validatePassword('123456');
        assert($result['valid'] === true, "6+ character password should pass");
        
        //Invalid passwords
        $result = ValidationService::validatePassword('12345');
        assert($result['valid'] === false, "Password < 6 chars should fail");
        
        $result = ValidationService::validatePassword('');
        assert($result['valid'] === false, "Empty password should fail");
        
        echo "Password validation tests passed\n";
    }
    
    public function testValidatePesel() {
        echo "Testing PESEL validation...\n";
        
        //Valid PESEL (with correct checksum)
        $result = ValidationService::validatePesel('44051401458');
        assert($result['valid'] === true, "Valid PESEL should pass");
        
        //Invalid PESELs
        $result = ValidationService::validatePesel('12345678901');
        assert($result['valid'] === false, "PESEL with wrong checksum should fail");
        
        $result = ValidationService::validatePesel('123456789');
        assert($result['valid'] === false, "PESEL with < 11 digits should fail");
        
        $result = ValidationService::validatePesel('1234567890a');
        assert($result['valid'] === false, "PESEL with letters should fail");
        
        $result = ValidationService::validatePesel('');
        assert($result['valid'] === false, "Empty PESEL should fail");
        
        echo "PESEL validation tests passed\n";
    }
    
    public function testValidatePhone() {
        echo "Testing phone validation...\n";
        
        //Valid phones
        $result = ValidationService::validatePhone('123456789');
        assert($result['valid'] === true, "9-digit phone should pass");
        
        $result = ValidationService::validatePhone('+48123456789');
        assert($result['valid'] === true, "Phone with country code should pass");
        
        $result = ValidationService::validatePhone('');
        assert($result['valid'] === true, "Empty phone should pass (optional)");
        
        //Invalid phones
        $result = ValidationService::validatePhone('12345');
        assert($result['valid'] === false, "Phone < 9 digits should fail");
        
        echo "Phone validation tests passed\n";
    }
    
    public function testValidateDate() {
        echo "Testing date validation...\n";
        
        //Valid dates
        $result = ValidationService::validateDate('2024-12-29');
        assert($result['valid'] === true, "Valid date should pass");
        
        $result = ValidationService::validateDate('2000-01-01');
        assert($result['valid'] === true, "Valid date should pass");
        
        //Invalid dates
        $result = ValidationService::validateDate('2024-13-01');
        assert($result['valid'] === false, "Invalid month should fail");
        
        $result = ValidationService::validateDate('2024-12-32');
        assert($result['valid'] === false, "Invalid day should fail");
        
        $result = ValidationService::validateDate('invalid-date');
        assert($result['valid'] === false, "Invalid format should fail");
        
        $result = ValidationService::validateDate('');
        assert($result['valid'] === false, "Empty date should fail");
        
        echo "Date validation tests passed\n";
    }
    
    public function testSanitizeString() {
        echo "Testing string sanitization...\n";
        
        $result = ValidationService::sanitizeString('<script>alert("XSS")</script>');
        assert(strpos($result, '<script>') === false, "HTML tags should be removed");
        
        $result = ValidationService::sanitizeString('  test  ');
        assert($result === 'test', "Whitespace should be trimmed");
        
        echo "String sanitization tests passed\n";
    }
    
    public function testSanitizeEmail() {
        echo "Testing email sanitization...\n";
        
        $result = ValidationService::sanitizeEmail('  Test@Example.com  ');
        assert($result === 'Test@Example.com', "Email should be trimmed");
        
        echo "Email sanitization tests passed\n";
    }
    
    public function runAllTests() {
        echo "\nRunning ValidationService Tests\n\n";
        
        try {
            $this->testValidateEmail();
            $this->testValidatePassword();
            $this->testValidatePesel();
            $this->testValidatePhone();
            $this->testValidateDate();
            $this->testSanitizeString();
            $this->testSanitizeEmail();
            
            echo "\nAll ValidationService tests passed!\n\n";
            return true;
        } catch (AssertionError $e) {
            echo "\nTest failed: " . $e->getMessage() . "\n\n";
            return false;
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../bootstrap.php';
    
    $test = new ValidationServiceTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}