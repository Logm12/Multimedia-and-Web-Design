<?php
// app/controllers/ReportController.php

class ReportController {
    private $reportModel;
    private $specializationModel; // For specialization filter if needed

    public function __construct() {
        $this->reportModel = new ReportModel();
        $this->specializationModel = new SpecializationModel(); // For filter options

        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $_SESSION['error_message'] = "Access denied. Admin login required.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
    }

    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    public function overview() {
        $currentDate = date('Y-m-d');
        $defaultStartDate = date('Y-m-01'); // Default to start of current month

        $filterStartDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $filterEndDate = trim($_GET['end_date'] ?? $currentDate);
        // Basic validation for dates
        if (!DateTime::createFromFormat('Y-m-d', $filterStartDate)) $filterStartDate = $defaultStartDate;
        if (!DateTime::createFromFormat('Y-m-d', $filterEndDate)) $filterEndDate = $currentDate;
        if (strtotime($filterStartDate) > strtotime($filterEndDate)) $filterStartDate = $filterEndDate; // Ensure start is not after end

        // Data for overview boxes
        $newPatientsCount = $this->reportModel->getNewPatientsCount($filterStartDate, $filterEndDate);
        $completedAppointmentsCount = $this->reportModel->getCompletedAppointmentsCount($filterStartDate, $filterEndDate);

        // Data for Appointment Status Chart (Pie Chart)
        $statusCountsRaw = $this->reportModel->getAppointmentCountsByStatus($filterStartDate, $filterEndDate);
        $statusLabels = [];
        $statusData = [];
        foreach ($statusCountsRaw as $row) {
            $statusLabels[] = ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $row['Status']));
            $statusData[] = (int)$row['count'];
        }
        $appointmentStatusChartData = ['labels' => $statusLabels, 'data' => $statusData];

        // Data for Completed Appointments Trend (Line Chart)
        // For trend, let's use a fixed range like last 30 days from the filterEndDate for better visualization
        $trendStartDate = date('Y-m-d', strtotime($filterEndDate . ' -29 days')); // 30 days including end date
        $trendDataRaw = $this->reportModel->getCompletedAppointmentsTrendByDay($trendStartDate, $filterEndDate);
        $trendLabels = [];
        $trendCounts = [];
        // Create a date range for the labels to ensure all days are present
        $period = new DatePeriod(new DateTime($trendStartDate), new DateInterval('P1D'), (new DateTime($filterEndDate))->modify('+1 day'));
        $trendDataMap = [];
        foreach ($trendDataRaw as $row) { $trendDataMap[$row['visit_date']] = (int)$row['completed_count']; }
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $trendLabels[] = $date->format('M j'); // e.g., Aug 15
            $trendCounts[] = $trendDataMap[$formattedDate] ?? 0;
        }
        $appointmentTrendChartData = ['labels' => $trendLabels, 'data' => $trendCounts];

        // Data for tables
        $appointmentsByDoctor = $this->reportModel->getCompletedAppointmentsByDoctor($filterStartDate, $filterEndDate);
        $appointmentsBySpecialization = $this->reportModel->getCompletedAppointmentsBySpecialization($filterStartDate, $filterEndDate);
        // $allSpecializations = $this->specializationModel->getAllSpecializations(); // For filter dropdown if needed

        $data = [
            'title' => 'Reports & Statistics Overview',
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'newPatientsCount' => $newPatientsCount,
            'completedAppointmentsCount' => $completedAppointmentsCount,
            'appointmentStatusChartData' => $appointmentStatusChartData,
            'appointmentTrendChartData' => $appointmentTrendChartData,
            'appointmentsByDoctor' => $appointmentsByDoctor,
            'appointmentsBySpecialization' => $appointmentsBySpecialization,
            // 'allSpecializationsForFilter' => $allSpecializations 
        ];
        $this->view('admin/reports/overview', $data);
    }
}
?>