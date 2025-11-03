<?php

class ValidationService {
    
    public static function validateEmail(string $email): array {
        if (empty($email)) {
            return ['valid' => false, 'error' => 'Email is required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        return ['valid' => true];
    }
    
    public static function validatePassword(string $password): array {
        if (empty($password)) {
            return ['valid' => false, 'error' => 'Password is required'];
        }
        
        if (strlen($password) < 6) {
            return ['valid' => false, 'error' => 'Password must be at least 6 characters'];
        }
        
        return ['valid' => true];
    }
    
    public static function validatePesel(string $pesel): array {
        if (empty($pesel)) {
            return ['valid' => false, 'error' => 'PESEL is required'];
        }
        
        if (strlen($pesel) !== 11) {
            return ['valid' => false, 'error' => 'PESEL must be 11 digits'];
        }
        
        if (!ctype_digit($pesel)) {
            return ['valid' => false, 'error' => 'PESEL must contain only digits'];
        }
        
        if (!self::validatePeselChecksum($pesel)) {
            return ['valid' => false, 'error' => 'Invalid PESEL checksum'];
        }
        
        return ['valid' => true];
    }
    
    public static function validatePhone(string $phone): array {
        if (empty($phone)) {
            return ['valid' => true];
        }
        
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (strlen($cleanPhone) < 9 || strlen($cleanPhone) > 15) {
            return ['valid' => false, 'error' => 'Invalid phone number length'];
        }
        
        return ['valid' => true];
    }
    
    public static function validateDate(string $date): array {
        if (empty($date)) {
            return ['valid' => false, 'error' => 'Date is required'];
        }
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return ['valid' => false, 'error' => 'Invalid date format (use YYYY-MM-DD)'];
        }
        
        return ['valid' => true];
    }
    
    public static function validateDateOfBirth(string $date): array {
        $result = self::validateDate($date);
        if (!$result['valid']) {
            return $result;
        }
        
        $birthDate = new DateTime($date);
        $today = new DateTime();
        
        if ($birthDate > $today) {
            return ['valid' => false, 'error' => 'Date of birth cannot be in the future'];
        }
        
        $age = $today->diff($birthDate)->y;
        if ($age > 150) {
            return ['valid' => false, 'error' => 'Invalid date of birth'];
        }
        
        return ['valid' => true];
    }
    
    public static function validateTime(string $time): array {
        if (empty($time)) {
            return ['valid' => false, 'error' => 'Time is required'];
        }
        
        $t = DateTime::createFromFormat('H:i', $time);
        if (!$t) {
            $t = DateTime::createFromFormat('H:i:s', $time);
        }
        
        if (!$t) {
            return ['valid' => false, 'error' => 'Invalid time format (use HH:MM)'];
        }
        
        return ['valid' => true];
    }
    
    public static function validateRequiredFields(array $data, array $requiredFields): array {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        return ['valid' => true];
    }
    
    public static function sanitizeString(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeEmail(string $email): string {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    public static function sanitizeInt($value): int {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    
    private static function validatePeselChecksum(string $pesel): bool {
        $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        $sum = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$pesel[$i] * $weights[$i];
        }
        
        $checksum = (10 - ($sum % 10)) % 10;
        
        return $checksum == (int)$pesel[10];
    }
}