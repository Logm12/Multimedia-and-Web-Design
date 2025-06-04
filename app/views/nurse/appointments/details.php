<?php
// app/views/nurse/appointments/details.php
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
            <li class="active">Details</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Appointment #<?php echo htmlspecialchars($data['appointment']['AppointmentID']); ?></h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Date & Time</dt>
                    <dd><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($data['appointment']['AppointmentDateTime']))); ?></dd>

                    <dt>Status</dt>
                    <dd><span class="label label-<?php echo strtolower($data['appointment']['Status']); ?>"><?php echo htmlspecialchars($data['appointment']['Status']); ?></span></dd>

                    <hr>
                    <dt>Patient Name</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['PatientFullName']); ?></dd>

                    <dt>Patient Email</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['PatientEmail'] ?? 'N/A'); ?></dd>

                    <dt>Patient Phone</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['PatientPhoneNumber'] ?? 'N/A'); ?></dd>

                    <dt>Patient DOB</dt>
                    <dd><?php echo $data['appointment']['PatientDOB'] ? htmlspecialchars(date('d/m/Y', strtotime($data['appointment']['PatientDOB']))) : 'N/A'; ?></dd>

                    <dt>Patient Gender</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['PatientGender'] ?? 'N/A'); ?></dd>

                    <hr>
                    <dt>Doctor Name</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['DoctorFullName']); ?></dd>

                    <dt>Specialization</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['SpecializationName'] ?? 'N/A'); ?></dd>

                    <?php if(!empty($data['appointment']['NurseFullName'])): ?>
                    <dt>Assisting Nurse</dt>
                    <dd><?php echo htmlspecialchars($data['appointment']['NurseFullName']); ?></dd>
                    <?php endif; ?>

                    <hr>
                    <dt>Reason For Visit</dt>
                    <dd><?php echo nl2br(htmlspecialchars($data['appointment']['ReasonForVisit'] ?? 'N/A')); ?></dd>

                    <dt>Clinic Notes</dt>
                    <dd><?php echo nl2br(htmlspecialchars($data['appointment']['Notes'] ?? 'N/A')); ?></dd>

                    <dt>Booked At</dt>
                    <dd><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($data['appointment']['CreatedAt']))); ?></dd>
                </dl>
            </div>
            <div class="box-footer">
                <a href="<?php echo BASE_URL; ?>/nurse/listAppointments?date=<?php echo date('Y-m-d', strtotime($data['appointment']['AppointmentDateTime'])); ?>" class="btn btn-default">Back to List</a>
                <!-- Nút "Record Vitals" có thể đặt ở đây hoặc trên trang list -->
                   <!-- THÊM LINK/NÚT GHI SINH HIỆU Ở ĐÂY -->
    <?php
    // Tương tự, bạn có thể có điều kiện hiển thị nút này
    // $allowedStatusesForVitals = ['Scheduled', 'Confirmed'];
    // if (in_array($data['appointment']['Status'], $allowedStatusesForVitals)):
    ?>
        <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $data['appointment']['AppointmentID']; ?>" class="btn btn-success pull-right">
            <i class="fa fa-heartbeat"></i> Record/Edit Vitals
        </a>      
            </div>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>