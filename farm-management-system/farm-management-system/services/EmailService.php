<?php
class EmailService {
    private $smtp_host;
    private $smtp_user;
    private $smtp_pass;
    private $smtp_port;
    
    public function __construct() {
        $this->smtp_host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $this->smtp_user = getenv('SMTP_USER') ?: 'your-email@gmail.com';
        $this->smtp_pass = getenv('SMTP_PASS') ?: 'your-app-password';
        $this->smtp_port = getenv('SMTP_PORT') ?: 587;
    }
    
    public function send2FACode($email, $code) {
        $subject = "Your Farm Management System 2FA Code";
        $message = "
            <h2>Two-Factor Authentication Code</h2>
            <p>Your verification code is: <strong>{$code}</strong></p>
            <p>This code will expire in 10 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    public function sendVerificationEmail($email, $token) {
        $subject = "Verify Your Farm Management System Account";
        $message = "
            <h2>Welcome to Farm Management System!</h2>
            <p>Please verify your email address by clicking the link below:</p>
            <a href='" . getenv('APP_URL') . "/verify-email?token={$token}'>Verify Email</a>
            <p>Or use this code: {$token}</p>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    public function sendPasswordResetEmail($email, $token) {
        $subject = "Reset Your Farm Management System Password";
        $message = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <a href='" . getenv('APP_URL') . "/reset-password?token={$token}'>Reset Password</a>
            <p>This link will expire in 1 hour.</p>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    private function sendEmail($to, $subject, $body) {
        // For production, use PHPMailer or SwiftMailer
        // This is a simplified version using mail() function
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Farm Management System <noreply@farmmanagement.com>" . "\r\n";
        
        return mail($to, $subject, $body, $headers);
    }
}
?>