<?php
// PHPMailer configuration
define('SMTP_HOST', 'smtp.gmail.com');  // Change this to your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'rmsbilling.org@gmail.com');  // Change this
define('SMTP_PASSWORD', 'hwjd ytsv mfoe mrqn');     // Change this
define('SMTP_FROM_EMAIL', 'rmsbilling.org@gmail.com');
define('SMTP_FROM_NAME', 'Auth API System');

// Create mailer function
function createMailer() {
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->isHTML(true);
        
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return null;
    }
} 