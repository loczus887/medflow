<?php

/**
 * Unit tests for User model
 */

class UserTest {
    
    public function testUserCreation() {
        echo "Testing user creation...\n";
        
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => 1,
            'status' => 'active',
            'last_login' => '2024-12-29 10:00:00',
            'created_at' => '2024-01-01 00:00:00'
        ];
        
        $user = new User($userData);
        
        assert($user->getId() === 1, "User ID should be 1");
        assert($user->getEmail() === 'test@example.com', "Email should match");
        assert($user->getRoleId() === 1, "Role ID should be 1");
        assert($user->getStatus() === 'active', "Status should be active");
        assert($user->isActive() === true, "isActive() should return true");
        
        echo "User creation test passed\n";
    }
    
    public function testPasswordHashing() {
        echo "Testing password hashing...\n";
        
        $password = 'testPassword123';
        $hash = User::hashPassword($password);
        
        assert(!empty($hash), "Hash should not be empty");
        assert(strlen($hash) === 60, "Bcrypt hash should be 60 characters");
        assert(password_verify($password, $hash), "Password should verify against hash");
        
        echo "Password hashing test passed\n";
    }
    
    public function testPasswordVerification() {
        echo "Testing password verification...\n";
        
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password"
            'role_id' => 1,
            'status' => 'active'
        ];
        
        $user = new User($userData);
        
        assert($user->verifyPassword('password') === true, "Correct password should verify");
        assert($user->verifyPassword('wrongpassword') === false, "Wrong password should fail");
        
        echo "Password verification test passed\n";
    }
    
    public function testUserStatus() {
        echo "Testing user status...\n";
        
        $activeUser = new User([
            'id' => 1,
            'email' => 'active@example.com',
            'password' => 'hash',
            'role_id' => 1,
            'status' => 'active'
        ]);
        
        $inactiveUser = new User([
            'id' => 2,
            'email' => 'inactive@example.com',
            'password' => 'hash',
            'role_id' => 1,
            'status' => 'inactive'
        ]);
        
        assert($activeUser->isActive() === true, "Active user should be active");
        assert($inactiveUser->isActive() === false, "Inactive user should not be active");
        
        echo "User status test passed\n";
    }
    
    public function testToArray() {
        echo "Testing toArray method...\n";
        
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'hash',
            'role_id' => 1,
            'status' => 'active'
        ];
        
        $user = new User($userData);
        $array = $user->toArray();
        
        assert(is_array($array), "toArray should return array");
        assert($array['id'] === 1, "Array should contain ID");
        assert($array['email'] === 'test@example.com', "Array should contain email");
        assert(isset($array['password']), "Array should contain password");
        
        echo "toArray test passed\n";
    }
    
    public function testToSessionArray() {
        echo "Testing toSessionArray method...\n";
        
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'hash',
            'role_id' => 1,
            'status' => 'active'
        ];
        
        $user = new User($userData);
        $sessionArray = $user->toSessionArray();
        
        assert(is_array($sessionArray), "toSessionArray should return array");
        assert($sessionArray['user_id'] === 1, "Session array should contain user_id");
        assert($sessionArray['email'] === 'test@example.com', "Session array should contain email");
        assert(!isset($sessionArray['password']), "Session array should NOT contain password");
        
        echo "toSessionArray test passed\n";
    }
    
    public function runAllTests() {
        echo "\nRunning User Model Tests\n\n";
        
        try {
            $this->testUserCreation();
            $this->testPasswordHashing();
            $this->testPasswordVerification();
            $this->testUserStatus();
            $this->testToArray();
            $this->testToSessionArray();
            
            echo "\nAll User model tests passed!\n\n";
            return true;
        } catch (AssertionError $e) {
            echo "\nTest failed: " . $e->getMessage() . "\n\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
            return false;
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../bootstrap.php';
    
    $test = new UserTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}