<?php
// app/controllers/HomeController.php

class HomeController {

    // Tạo một phương thức view đơn giản trong HomeController
    // Hoặc nếu bạn có BaseController, hãy kế thừa và sử dụng phương thức view từ đó
    protected function view($viewName, $data = []) {
        // Tạo đường dẫn đầy đủ đến file view
        // Giả sử BASE_URL đã được định nghĩa toàn cục trong public/index.php
        // và các view nằm trong app/views/
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';

        if (file_exists($viewFile)) {
            // Truyền BASE_URL vào view để tiện sử dụng cho các link, src ảnh, css, js
            if (!isset($data['BASE_URL'])) {
                // Kiểm tra xem BASE_URL đã được define chưa để tránh lỗi
                if (defined('BASE_URL')) {
                    $data['BASE_URL'] = BASE_URL;
                } else {
                    // Xử lý trường hợp BASE_URL chưa được define (ví dụ, gán giá trị mặc định hoặc báo lỗi)
                    // Điều này không nên xảy ra nếu public/index.php của bạn chạy đúng
                    $data['BASE_URL'] = ''; // Hoặc một giá trị fallback
                    error_log("BASE_URL is not defined when trying to load view: " . $viewName);
                }
            }

            // Giải nén mảng $data thành các biến riêng lẻ để view có thể truy cập trực tiếp
            // Ví dụ: $data['title'] sẽ trở thành biến $title trong view
            extract($data);

            require_once $viewFile;
        } else {
            // Xử lý lỗi nếu file view không tồn tại
            die("Error: View '{$viewName}' not found at '{$viewFile}'.");
        }
    }

    public function index() {
        // Dữ liệu cần thiết cho trang giới thiệu
        $data = [
            'pageTitle' => 'Welcome to Our Healthcare Management System', // Tiêu đề cho thẻ <title>
            'heroHeadline' => 'Chăm Sóc Sức Khỏe Thông Minh, Trong Tầm Tay Bạn',
            'heroSubheadline' => 'Đặt lịch khám dễ dàng, quản lý hồ sơ y tế an toàn và hiệu quả.',
            // Bạn có thể thêm các mảng dữ liệu cho section "Features" ở đây
            'features' => [
                [
                    'icon' => 'fa-calendar-check', // Ví dụ class icon Font Awesome
                    'title' => 'Đặt Lịch Trực Tuyến 24/7',
                    'description' => 'Tìm kiếm bác sĩ và đặt lịch hẹn phù hợp mọi lúc, mọi nơi.'
                ],
                [
                    'icon' => 'fa-file-medical-alt',
                    'title' => 'Quản Lý Bệnh Án Điện Tử',
                    'description' => 'Truy cập và theo dõi hồ sơ sức khỏe cá nhân một cách an toàn.'
                ],
                [
                    'icon' => 'fa-user-md',
                    'title' => 'Kết Nối Chuyên Gia Y Tế',
                    'description' => 'Hệ thống giúp bạn dễ dàng tương tác với đội ngũ y bác sĩ.'
                ]
            ]
        ];

        // Load view của trang giới thiệu
        // Giả sử bạn tạo file app/views/home/landing.php
        $this->view('home/landing', $data);
    }
}
?>