<?php
// app/services/MailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Ensure Composer autoloader is loaded (usually in public/index.php)
// require_once __DIR__ . '/../../vendor/autoload.php'; 

class MailService {
    private $mail;
    private $senderEmail = 'no-reply@yourclinicdomain.com'; // Define as property
    private $senderName = 'Healthcare System Support';   // Define as property

    public function __construct() {
        $this->mail = new PHPMailer(true); 

        try {
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER; 
            $this->mail->isSMTP();
            $this->mail->Host       = 'smtp.gmail.com'; 
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'duykhoadd1@gmail.com'; 
            $this->mail->Password   = 'kpambwxhyjfmodyg';      
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    
            $this->mail->Port       = 465;                          

            $this->mail->CharSet = 'UTF-8';

            // Set sender from properties
            $this->mail->setFrom($this->senderEmail, $this->senderName);


        } catch (Exception $e) {
            error_log("MailService PHPMailer could not be configured. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    /**
     * Sends a welcome email with account details.
     * @param string $toEmail Recipient's email address.
     * @param string $fullName Recipient's full name.
     * @param string $username Login username.
     * @param string $tempPassword Temporary password.
     * @param string $role User's role.
     * @return bool True if sent successfully, false otherwise.
     */
    public function sendWelcomeEmail($toEmail, $fullName, $username, $tempPassword, $role) {
        try {
            $this->mail->clearAddresses(); // Clear previous recipients
            $this->mail->addAddress($toEmail, $fullName);

            $this->mail->isHTML(true); 
            $this->mail->Subject = 'Welcome to Our Healthcare System - Your Account Details';

            $loginUrl = BASE_URL . '/auth/login'; 

            $this->mail->Body    = "<p>Dear {$role} {$fullName},</p>" .
                                 "<p>An account has been created for you on our Healthcare System.</p>" .
                                 "<p>Here are your login details:<br>" .
                                 "<strong>Username:</strong> {$username}<br>" .
                                 "<strong>Temporary Password:</strong> {$tempPassword}</p>" .
                                 "<p>Please log in as soon as possible using the link below and change your password immediately for security reasons.<br>" .
                                 "Login here: <a href='{$loginUrl}'>{$loginUrl}</a></p>" .
                                 "<p>If you have any questions, please contact our support team.</p>" .
                                 "<p>Thank you,<br>Healthcare System Administration</p>";

            $this->mail->AltBody = "Dear {$role} {$fullName},\n\nAn account has been created for you on our Healthcare System.\n\n" .
                                 "Here are your login details:\n" .
                                 "Username: {$username}\n" .
                                 "Temporary Password: {$tempPassword}\n\n" .
                                 "Please log in as soon as possible using the link below and change your password immediately for security reasons.\n" .
                                 "Login here: {$loginUrl}\n\n" .
                                 "If you have any questions, please contact our support team.\n\n" .
                                 "Thank you,\nHealthcare System Administration";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Welcome email could not be sent to {$toEmail}. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Sends a password reset email.
     * @param string $toEmail Recipient's email address.
     * @param string $fullName Recipient's full name.
     * @param string $resetLink The unique link to reset the password.
     * @return bool True if sent successfully, false otherwise.
     */
    public function sendPasswordResetEmail($toEmail, $fullName, $resetLink) {
        try {
            $this->mail->clearAddresses(); // Clear previous recipients before adding new one
            $this->mail->addAddress($toEmail, $fullName);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request - Healthcare System';

            $this->mail->Body    = "<p>Dear {$fullName},</p>" .
                                 "<p>We received a request to reset your password for your Healthcare System account.</p>" .
                                 "<p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>" .
                                 "<p>To reset your password, please click on the link below (or copy and paste it into your browser). This link is valid for 1 hour:</p>" .
                                 "<p><a href='{$resetLink}'>{$resetLink}</a></p>" .
                                 "<p>Thank you,<br>Healthcare System Support</p>";
            
            $this->mail->AltBody = "Dear {$fullName},\n\nWe received a request to reset your password for your Healthcare System account.\n\n" .
                                 "If you did not request a password reset, please ignore this email or contact support if you have concerns.\n\n" .
                                 "To reset your password, please copy and paste the following link into your browser. This link is valid for 1 hour:\n" .
                                 "{$resetLink}\n\n" .
                                 "Thank you,\nHealthcare System Support";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Password reset email could not be sent to {$toEmail}. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>