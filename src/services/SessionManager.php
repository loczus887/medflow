<?php

class SessionManager {
    
    private static bool $started = false;
    
    public static function start(): void {
        if (self::$started) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_secure', '0');
            ini_set('session.cookie_samesite', 'Lax');
            
            session_start();
            self::$started = true;
        }
    }
    
    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        self::$started = false;
    }
    
    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }
    
    public static function setFlash(string $key, $value): void {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }
    
    public static function getFlash(string $key, $default = null) {
        self::start();
        
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        
        return $default;
    }
    
    public static function hasFlash(string $key): bool {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
}