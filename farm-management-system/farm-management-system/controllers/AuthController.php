<?php
// Simple placeholder — you likely have session/auth already
class AuthController {
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        return ["status" => "ok"];
    }
}
?>
