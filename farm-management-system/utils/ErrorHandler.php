<?php
class ErrorHandler {
    public static function handleException($exception) {
        error_log("Exception: " . $exception->getMessage());
        
        if (getenv('DEBUG_MODE') === 'true') {
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'An error occurred'
            ];
        }
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline) {
        error_log("Error: $errstr in $errfile on line $errline");
        
        if (getenv('DEBUG_MODE') === 'true') {
            $response = [
                'success' => false,
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'An error occurred'
            ];
        }
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Set error handlers
set_exception_handler(['ErrorHandler', 'handleException']);
set_error_handler(['ErrorHandler', 'handleError']);
?>