<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * ExceptionHandler - Global exception handler
 */

class ExceptionHandler
{
    public static function register()
    {
        set_exception_handler([self::class, 'handle']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handle($exception)
    {
        // Production'da detay gösterme
        $isProduction = Sword::getData('environment') === 'production';

        if ($exception instanceof ValidationException) {
            self::handleValidationException($exception);
        } elseif ($exception instanceof DatabaseException) {
            self::handleDatabaseException($exception, $isProduction);
        } else {
            self::handleGenericException($exception, $isProduction);
        }
    }

    private static function handleValidationException($exception)
    {
        http_response_code(422);
        if (self::isAjax()) {
            echo json_encode([
                'error' => $exception->getMessage(),
                'errors' => $exception->getErrors()
            ]);
        } else {
            echo "Validation Error: " . $exception->getMessage();
        }
        exit;
    }

    private static function handleDatabaseException($exception, $isProduction)
    {
        http_response_code(500);
        $message = $isProduction ? 'Database error occurred' : $exception->getMessage();

        if (self::isAjax()) {
            echo json_encode(['error' => $message]);
        } else {
            echo "Database Error: " . $message;
        }
        exit;
    }

    private static function handleGenericException($exception, $isProduction)
    {
        http_response_code(500);
        $message = $isProduction ? 'Internal server error' : $exception->getMessage();

        if (self::isAjax()) {
            echo json_encode(['error' => $message]);
        } else {
            echo "Error: " . $message;
        }
        exit;
    }

    public static function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    private static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
