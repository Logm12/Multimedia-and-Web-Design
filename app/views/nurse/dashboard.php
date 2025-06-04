<?php
// app/views/nurse/dashboard.php
// Giả sử bạn có một layout chung cho các vai trò, hoặc layout riêng cho nurse
// Nếu dùng layout chung, nó cần kiểm tra $data['userRole'] để hiển thị menu phù hợp
require_once __DIR__ . '/../layouts/header.php'; // Hoặc header của nurse
// require_once __DIR__ . '/../layouts/nurse_sidebar.php'; // Hoặc sidebar của nurse
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <?php echo htmlspecialchars($data['title']); ?>
            <small>Welcome, <?php echo htmlspecialchars($data['currentUser']['FullName']); ?>!</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo htmlspecialchars($data['welcome_message']); ?></h3>
                    </div>
                    <div class="box-body">
                        <p>This is your Nurse dashboard.</p>

                        <!-- HIỂN THỊ CÁC LINK CHỨC NĂNG -->
                        <?php if (!empty($data['links'])): ?>
                            <h4>Quick Actions:</h4>
                            <ul class="list-inline"> <?php // Hoặc dùng thẻ ul/li bình thường ?>
                                <?php foreach ($data['links'] as $link): ?>
                                    <li style="margin-right: 10px; margin-bottom: 10px;">
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" class="btn btn-primary">
                                            <?php echo htmlspecialchars($link['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <hr>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách lịch hẹn sắp tới -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Upcoming Appointments (Next <?php echo count($data['upcomingAppointments']); ?>)</h3>
                        <!-- <div class="box-tools">
                            <a href="<?php echo BASE_URL; ?>/nurse/appointments" class="btn btn-sm btn-primary">View All Appointments</a>
                        </div> -->
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['upcomingAppointments'])): ?>
                                    <?php foreach ($data['upcomingAppointments'] as $appointment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                            <td><span class="label label-<?php echo strtolower($appointment['Status']); // Cần CSS cho các label này ?>"><?php echo htmlspecialchars($appointment['Status']); ?></span></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="btn btn-xs btn-info">Details</a>
                                                <!-- Thêm action "Record Vitals" ở đây sau này -->
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No upcoming appointments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php'; // Hoặc footer của nurse
?>