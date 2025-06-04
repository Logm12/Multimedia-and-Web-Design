<?php
// app/controllers/ReportController.php

class ReportController {
    private $reportModel;
    // Bạn có thể cần UserModel hoặc SpecializationModel để lấy danh sách cho bộ lọc
    // private $userModel;
    // private $specializationModel;

    public function __construct() {
        // Xác thực Admin ở đây hoặc trong từng phương thức
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $_SESSION['error_message'] = "Unauthorized access to reports.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        $this->reportModel = new ReportModel();
        // $this->userModel = new UserModel();
        // $this->specializationModel = new SpecializationModel();
    }

    // Hàm để load view chung (có thể kế thừa từ BaseController nếu có)
    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    /**
     * Trang tổng quan báo cáo
     */
    public function overview() {
        $data = [
            'title' => 'Reports Overview'
        ];

        // Mặc định lấy dữ liệu cho tháng hiện tại
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t'); // Ngày cuối cùng của tháng hiện tại

        // Lấy bộ lọc từ GET request nếu có
        $filterStartDate = $_GET['start_date'] ?? $currentMonthStart;
        $filterEndDate = $_GET['end_date'] ?? $currentMonthEnd;

        // Validate date format (cơ bản)
        // Bạn nên có hàm validate ngày tháng tốt hơn
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $filterStartDate)) {
            $filterStartDate = $currentMonthStart;
        }
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $filterEndDate)) {
            $filterEndDate = $currentMonthEnd;
        }


        $data['filterStartDate'] = $filterStartDate;
        $data['filterEndDate'] = $filterEndDate;

        // 1. Số lượng bệnh nhân mới
        $data['newPatientsCount'] = $this->reportModel->getNewPatientsCount($filterStartDate, $filterEndDate);

        // 2. Tổng số lượt khám hoàn thành
        $data['completedAppointmentsCount'] = $this->reportModel->getCompletedAppointmentsCount($filterStartDate, $filterEndDate);

        // 3. Thống kê lịch hẹn theo trạng thái (cho biểu đồ tròn)
        $rawStatusCounts = $this->reportModel->getAppointmentCountsByStatus($filterStartDate, $filterEndDate);
        $appointmentStatusChartData = ['labels' => [], 'data' => []];
        foreach ($rawStatusCounts as $row) {
            $appointmentStatusChartData['labels'][] = ucfirst(str_replace(['ByPatient', 'ByClinic'], [' (Patient)', ' (Clinic)'], $row['Status']));
            $appointmentStatusChartData['data'][] = (int)$row['count'];
        }
        $data['appointmentStatusChartData'] = $appointmentStatusChartData;

        // 4. Xu hướng lượt khám hoàn thành theo ngày (cho biểu đồ đường)
        // Lấy dữ liệu cho 30 ngày gần nhất làm ví dụ
        $trendStartDate = date('Y-m-d', strtotime('-29 days', strtotime($filterEndDate))); // 30 ngày tính từ endDate
        $rawTrendData = $this->reportModel->getCompletedAppointmentsTrendByDay($trendStartDate, $filterEndDate);
        $appointmentTrendChartData = ['labels' => [], 'data' => []];
        // Tạo một mảng ngày đầy đủ trong khoảng để đảm bảo không thiếu ngày nào trên biểu đồ
        $period = new DatePeriod(
             new DateTime($trendStartDate),
             new DateInterval('P1D'),
             (new DateTime($filterEndDate))->modify('+1 day') // Bao gồm cả ngày cuối
        );
        $dateMap = [];
        foreach ($period as $date) {
            $dateMap[$date->format('Y-m-d')] = 0;
        }
        foreach ($rawTrendData as $row) {
            $dateMap[$row['visit_date']] = (int)$row['completed_count'];
        }
        $appointmentTrendChartData['labels'] = array_keys($dateMap);
        $appointmentTrendChartData['data'] = array_values($dateMap);
        $data['appointmentTrendChartData'] = $appointmentTrendChartData;


        // Thêm các báo cáo chi tiết khác nếu muốn hiển thị trên cùng trang
        // $data['appointmentsByDoctor'] = $this->reportModel->getCompletedAppointmentsByDoctor($filterStartDate, $filterEndDate);
        // $data['appointmentsBySpecialization'] = $this->reportModel->getCompletedAppointmentsBySpecialization($filterStartDate, $filterEndDate);


        $this->view('admin/reports/overview', $data);
    }

    // Bạn có thể tạo các action riêng cho từng báo cáo chi tiết nếu cần trang riêng
    // Ví dụ:
    // public function appointmentsByDoctorReport() { /* ... */ }
    // public function appointmentsBySpecializationReport() { /* ... */ }
}
?>