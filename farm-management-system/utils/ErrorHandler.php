<?php
// utils/ErrorHandler.php
class ErrorHandler {
    public static function handleException($e) {
        error_log($e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'message'=>'Server error']);
        exit;
    }
}
set_exception_handler(['ErrorHandler','handleException']);
