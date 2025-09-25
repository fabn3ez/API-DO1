<?php
// services/EmailService.php
class EmailService {
    public function send2FACode(string $email, string $code) : bool {
        $subject = "Your Farm System 2FA Code";
        $body = "<p>Your verification code is <strong>{$code}</strong>. It expires in 10 minutes.</p>";
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: noreply@farm.app\r\n";
        return mail($email, $subject, $body, $headers);
    }
}
