<?php
// app/views/nurse/appointments/list.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header của nurse
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filters</h3>
                    </div>
                    <div class="box-body">
                        <form method="GET" action="<?php echo BASE_URL; ?>/nurse/listAppointments" class="form-inline">
                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($data['filterDate']); ?>">
                            </div>
                            <div class="form-group" style="margin-left: 10px;">
                                <label for="status">Status:</label>
                                <select id="status" name="status" class="form-control">
                                    <?php foreach ($data['allStatuses'] as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($data['filterStatus'] == $status) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' (Patient)', ' (Clinic)'], $status)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- TODO: Thêm bộ lọc bác sĩ nếu Nurse hỗ trợ nhiều bác sĩ -->
                            <!--
                            <div class="form-group" style="margin-left: 10px;">
                                <label for="doctor_id">Doctor:</label>
                                <select id="doctor_id" name="doctor_id" class="form-control">
                                    <option value="">All Doctors</option>
                                    <?php // foreach ($data['doctorsForFilter'] as $doctor): ?>
                                        <option value="<?php // echo $doctor['DoctorProfileID']; ?>" <?php // echo (isset($_GET['doctor_id']) && $_GET['doctor_id'] == $doctor['DoctorProfileID']) ? 'selected' : ''; ?>>
                                            <?php // echo htmlspecialchars($doctor['FullName']); ?>
                                        </option>
                                    <?php // endforeach; ?>
                                </select>
                            </div>
                            -->
                            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Apply Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Appointments List</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['appointments'])): ?>
                                    <?php foreach ($data['appointments'] as $appointment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('H:i', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($appointment['ReasonForVisit'] ?? '', 0, 50))) . (strlen($appointment['ReasonForVisit'] ?? '') > 50 ? '...' : ''); ?></td>
                                            <td><span class="label label-<?php echo strtolower($appointment['Status']); ?>"><?php echo htmlspecialchars($appointment['Status']); ?></span></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="btn btn-xs btn-info" title="View Details"><i class="fa fa-eye">View Details</i></a>
                                                <!-- Nút "Record Vitals" sẽ được thêm ở đây -->
                                                 <!-- THÊM LINK/NÚT GHI SINH HIỆU Ở ĐÂY -->
                                        <?php
                                        // Chỉ hiển thị nút ghi sinh hiệu cho các trạng thái phù hợp, ví dụ: Scheduled, Confirmed
                                        // Hoặc có thể luôn hiển thị để cho phép cập nhật
                                        // $allowedStatusesForVitals = ['Scheduled', 'Confirmed'];
                                        // if (in_array($appointment['Status'], $allowedStatusesForVitals)):
                                        ?>
                                <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="btn btn-xs btn-success" title="Record Vitals">
                                    <i class="fa fa-heartbeat"></i> Vitals
                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No appointments found for the selected criteria.</td>
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
require_once __DIR__ . '/../../layouts/footer.php';
?>