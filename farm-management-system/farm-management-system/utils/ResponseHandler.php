<?php
class ResponseHandler {
    public static function success($data = null, $message = 'Success') {
        header('Content-Type: application/json');
        $response = ['success' => true, 'message' => $message];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
    }
    
    public static function error($message = 'Error', $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
    
    public static function validationError($errors) {
        http_response_code(422);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
    }
}
?>