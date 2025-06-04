<?php
// app/services/MailService.php

// Nạp autoload của Composer nếu bạn dùng Composer
// Điều này thường được làm ở file bootstrap chính (public/index.php)
// Nếu chưa, bạn có thể cần: require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true); // true để bật exceptions

        // Cấu hình Server settings - THAY THẾ BẰNG THÔNG TIN CỦA BẠN
        // Đây là ví dụ cấu hình cho Gmail. Bạn cần thay đổi cho phù hợp với mail server của bạn.
        try {
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER; // Bật output debug chi tiết (chỉ dùng khi gỡ lỗi)
            $this->mail->isSMTP();
            $this->mail->Host       = 'smtp.gmail.com'; // Đặt SMTP server
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'duykhoadd1@gmail.com'; // SMTP username (email của bạn)
            $this->mail->Password   = 'kpambwxhyjfmodyg';      // SMTP password (Mật khẩu ứng dụng Gmail nếu dùng Gmail)
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // Bật mã hóa TLS ngầm; `PHPMailer::ENCRYPTION_STARTTLS` cũng được chấp nhận
            $this->mail->Port       = 465;                          // Port TCP để kết nối; dùng 587 nếu bạn đặt `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // Cấu hình UTF-8
            $this->mail->CharSet = 'UTF-8';

        } catch (Exception $e) {
            error_log("MailService PHPMailer could not be configured. Mailer Error: {$this->mail->ErrorInfo}");
            // Bạn có thể ném lại exception hoặc xử lý theo cách khác
        }
    }

    /**
     * Gửi email chào mừng với thông tin tài khoản
     * @param string $toEmail Địa chỉ email người nhận
     * @param string $fullName Tên đầy đủ của người nhận
     * @param string $username Tên đăng nhập
     * @param string $tempPassword Mật khẩu tạm thời
     * @param string $role Vai trò của người dùng
     * @return bool True nếu gửi thành công, false nếu thất bại
     */
    public function sendWelcomeEmail($toEmail, $fullName, $username, $tempPassword, $role) {
        try {
            // Người gửi
            $this->mail->setFrom('no-reply@yourclinicdomain.com', 'Healthcare System Admin'); // Thay thế bằng email và tên người gửi của bạn

            // Người nhận
            $this->mail->addAddress($toEmail, $fullName);

            // Nội dung
            $this->mail->isHTML(true); // Đặt định dạng email là HTML
            $this->mail->Subject = 'Welcome to Our Healthcare System - Your Account Details';

            $loginUrl = BASE_URL . '/auth/login'; // Đảm bảo BASE_URL đã được định nghĩa toàn cục

            $this->mail->Body    = "Dear {$role} {$fullName},<br><br>" .
                                 "An account has been created for you on our Healthcare System.<br><br>" .
                                 "Here are your login details:<br>" .
                                 "<strong>Username:</strong> {$username}<br>" .
                                 "<strong>Temporary Password:</strong> {$tempPassword}<br><br>" .
                                 "Please log in as soon as possible using the link below and change your password immediately for security reasons.<br>" .
                                 "Login here: <a href='{$loginUrl}'>{$loginUrl}</a><br><br>" .
                                 "If you have any questions, please contact our support team.<br><br>" .
                                 "Thank you,<br>Healthcare System Administration";

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
}
?>