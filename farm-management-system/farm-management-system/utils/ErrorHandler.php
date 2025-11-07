<?php
namespace FarmManagement\Utils;
class ErrorHandler {
    public static function handleException($exception) {
        error_log("Exception: " . $exception->getMessage());
        // Always show detailed error info for development
        $response = [
            'success' => false,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ];
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline) {
        error_log("Error: $errstr in $errfile on line $errline");
        // Always show detailed error info for development
        $response = [
            'success' => false,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Set error handlers
set_exception_handler(['FarmManagement\\Utils\\ErrorHandler', 'handleException']);
set_error_handler(['FarmManagement\\Utils\\ErrorHandler', 'handleError']);
?>