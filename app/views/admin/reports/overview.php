<?php
// app/views/admin/reports/overview.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung của admin
// Giả sử bạn có sidebar cho admin
// require_once __DIR__ . '/../layouts/admin_sidebar.php';
?>

<div class="content-wrapper"> <!-- Hoặc class bao ngoài nội dung của bạn -->
    <section class="content-header">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
        <!-- Breadcrumbs nếu có -->
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filters</h3>
                    </div>
                    <div class="box-body">
                        <form method="GET" action="<?php echo BASE_URL; ?>/report/overview">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($data['filterStartDate']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($data['filterEndDate']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label> </label><br>
                                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hàng cho các số liệu tổng quan -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $data['newPatientsCount']; ?></h3>
                        <p>New Patients</p>
                    </div>
                    <div class="icon"><i class="ion ion-person-add"></i></div>
                    <!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $data['completedAppointmentsCount']; ?></h3>
                        <p>Completed Appointments</p>
                    </div>
                    <div class="icon"><i class="ion ion-checkmark-circled"></i></div>
                </div>
            </div>
            <!-- Thêm các box khác nếu cần -->
        </div>

        <!-- Hàng cho các biểu đồ -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Appointment Statuses</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="appointmentStatusChart" style="height:250px"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Completed Appointments Trend (Last 30 Days from End Date)</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="appointmentTrendChart" style="height:250px"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu chi tiết (ví dụ) -->
        <div class="row" style="margin-top: 20px;">
             <div class="col-md-6">
                <div class="box">
                    <div class="box-header"><h3 class="box-title">Appointments by Doctor (Completed)</h3></div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead><tr><th>Doctor Name</th><th>Completed Count</th></tr></thead>
                            <tbody>
                                <?php
                                $appByDocData = $this->reportModel->getCompletedAppointmentsByDoctor($data['filterStartDate'], $data['filterEndDate']);
                                if (!empty($appByDocData)):
                                    foreach ($appByDocData as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                        <td><?php echo $row['completed_count']; ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="2">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
             <div class="col-md-6">
                <div class="box">
                    <div class="box-header"><h3 class="box-title">Appointments by Specialization (Completed)</h3></div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead><tr><th>Specialization</th><th>Completed Count</th></tr></thead>
                            <tbody>
                                 <?php
                                $appBySpecData = $this->reportModel->getCompletedAppointmentsBySpecialization($data['filterStartDate'], $data['filterEndDate']);
                                if (!empty($appBySpecData)):
                                    foreach ($appBySpecData as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['specialization_name']); ?></td>
                                        <td><?php echo $row['completed_count']; ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="2">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung // Hoặc footer chung của admin ?>

<!-- Nhúng Chart.js (ví dụ: từ CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Biểu đồ Trạng thái Lịch hẹn
    const statusCtx = document.getElementById('appointmentStatusChart');
    if (statusCtx) {
        const statusChartData = <?php echo json_encode($data['appointmentStatusChartData']); ?>;
        new Chart(statusCtx, {
            type: 'pie', // Hoặc 'doughnut'
            data: {
                labels: statusChartData.labels,
                datasets: [{
                    label: 'Appointments',
                    data: statusChartData.data,
                    backgroundColor: [ // Thêm màu cho từng trạng thái
                        'rgba(75, 192, 192, 0.7)', // Completed
                        'rgba(255, 99, 132, 0.7)', // Cancelled
                        'rgba(255, 206, 86, 0.7)', // Scheduled
                        'rgba(54, 162, 235, 0.7)', // Confirmed
                        'rgba(153, 102, 255, 0.7)', // NoShow
                        'rgba(255, 159, 64, 0.7)'  // Rescheduled
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    // Biểu đồ Xu hướng Lịch hẹn
    const trendCtx = document.getElementById('appointmentTrendChart');
    if (trendCtx) {
        const trendChartData = <?php echo json_encode($data['appointmentTrendChartData']); ?>;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendChartData.labels, // Mảng các ngày
                datasets: [{
                    label: 'Completed Appointments',
                    data: trendChartData.data, // Mảng số lượng tương ứng
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>