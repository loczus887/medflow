<?php

class ErrorHandler {
    
    public static function register(): void {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = self::getErrorType($errno);
        
        error_log("[$errorType] $errstr in $errfile on line $errline");
        
        if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::showErrorPage(500, "Internal Server Error");
        }
        
        return true;
    }
    
    public static function handleException(Throwable $exception): void {
        error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
        error_log("Stack trace: " . $exception->getTraceAsString());
        
        self::showErrorPage(500, "Internal Server Error");
    }
    
    public static function handleShutdown(): void {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
            self::showErrorPage(500, "Internal Server Error");
        }
    }
    
    public static function show400(): void {
        self::showErrorPage(400, "Bad Request");
    }
    
    public static function show403(): void {
        self::showErrorPage(403, "Forbidden");
    }
    
    public static function show404(): void {
        self::showErrorPage(404, "Not Found");
    }
    
    public static function show500(): void {
        self::showErrorPage(500, "Internal Server Error");
    }
    
    private static function showErrorPage(int $code, string $message): void {
        http_response_code($code);
        
        $errorPages = [
            400 => 'public/views/errors/400.html',
            403 => 'public/views/errors/403.html',
            404 => 'public/views/errors/404.html',
            500 => 'public/views/errors/500.html'
        ];
        
        $errorPage = $errorPages[$code] ?? 'public/views/errors/500.html';
        
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo self::getDefaultErrorPage($code, $message);
        }
        
        exit();
    }
    
    private static function getDefaultErrorPage(int $code, string $message): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error $code - MedFlow</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
        }
        h1 {
            font-size: 72px;
            color: #667eea;
            margin: 0 0 1rem 0;
        }
        h2 {
            font-size: 24px;
            color: #333;
            margin: 0 0 1rem 0;
        }
        p {
            color: #666;
            margin: 0 0 2rem 0;
        }
        a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: transform 0.2s;
        }
        a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>$code</h1>
        <h2>$message</h2>
        <p>Przepraszamy, ale wystąpił błąd podczas przetwarzania Twojego żądania.</p>
        <a href="/">Powrót do strony głównej</a>
    </div>
</body>
</html>
HTML;
    }
    
    private static function getErrorType(int $errno): string {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
        
        return $errorTypes[$errno] ?? 'UNKNOWN';
    }
}