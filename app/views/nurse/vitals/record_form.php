<?php
// app/views/nurse/vitals/record_form.php
require_once __DIR__ . '/../../layouts/header.php';
$appointment = $data['appointment'];
$vitals = $data['input']; // Dùng input để điền lại form nếu có lỗi
$errors = $data['errors'];
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
            <li><a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>">Details #<?php echo $appointment['AppointmentID']; ?></a></li>
            <li class="active">Record Vitals</li>
        </ol>
    </section>

    <section class="content">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <div class="box box-primary">
            <form action="<?php echo BASE_URL; ?>/nurse/saveVitals/<?php echo $appointment['AppointmentID']; ?>" method="POST">
                <?php // echo generateCsrfInput(); // Nếu bạn có CSRF ?>
                <div class="box-header with-border">
                    <h3 class="box-title">Patient: <?php echo htmlspecialchars($appointment['PatientFullName']); ?> | Appointment: <?php echo date('d/m/Y H:i', strtotime($appointment['AppointmentDateTime'])); ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4 form-group <?php echo isset($errors['HeartRate']) ? 'has-error' : ''; ?>">
                            <label for="HeartRate">Heart Rate (bpm)</label>
                            <input type="number" class="form-control" id="HeartRate" name="HeartRate" value="<?php echo htmlspecialchars($vitals['HeartRate'] ?? ''); ?>">
                            <?php if (isset($errors['HeartRate'])): ?><span class="help-block"><?php echo $errors['HeartRate']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-4 form-group <?php echo isset($errors['Temperature']) ? 'has-error' : ''; ?>">
                            <label for="Temperature">Temperature (°C)</label>
                            <input type="text" class="form-control" id="Temperature" name="Temperature" placeholder="e.g., 37.5" value="<?php echo htmlspecialchars($vitals['Temperature'] ?? ''); ?>">
                            <?php if (isset($errors['Temperature'])): ?><span class="help-block"><?php echo $errors['Temperature']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Blood Pressure (mmHg)</label>
                            <div class="row">
                                <div class="col-xs-6 <?php echo isset($errors['BloodPressureSystolic']) ? 'has-error' : ''; ?>">
                                    <input type="number" class="form-control" name="BloodPressureSystolic" placeholder="Systolic" value="<?php echo htmlspecialchars($vitals['BloodPressureSystolic'] ?? ''); ?>">
                                    <?php if (isset($errors['BloodPressureSystolic'])): ?><span class="help-block"><?php echo $errors['BloodPressureSystolic']; ?></span><?php endif; ?>
                                </div>
                                <div class="col-xs-6 <?php echo isset($errors['BloodPressureDiastolic']) ? 'has-error' : ''; ?>">
                                    <input type="number" class="form-control" name="BloodPressureDiastolic" placeholder="Diastolic" value="<?php echo htmlspecialchars($vitals['BloodPressureDiastolic'] ?? ''); ?>">
                                    <?php if (isset($errors['BloodPressureDiastolic'])): ?><span class="help-block"><?php echo $errors['BloodPressureDiastolic']; ?></span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group <?php echo isset($errors['RespiratoryRate']) ? 'has-error' : ''; ?>">
                            <label for="RespiratoryRate">Respiratory Rate (breaths/min)</label>
                            <input type="number" class="form-control" id="RespiratoryRate" name="RespiratoryRate" value="<?php echo htmlspecialchars($vitals['RespiratoryRate'] ?? ''); ?>">
                            <?php if (isset($errors['RespiratoryRate'])): ?><span class="help-block"><?php echo $errors['RespiratoryRate']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-3 form-group <?php echo isset($errors['Weight']) ? 'has-error' : ''; ?>">
                            <label for="Weight">Weight (kg)</label>
                            <input type="text" class="form-control" id="Weight" name="Weight" placeholder="e.g., 60.5" value="<?php echo htmlspecialchars($vitals['Weight'] ?? ''); ?>">
                            <?php if (isset($errors['Weight'])): ?><span class="help-block"><?php echo $errors['Weight']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-3 form-group <?php echo isset($errors['Height']) ? 'has-error' : ''; ?>">
                            <label for="Height">Height (cm)</label>
                            <input type="text" class="form-control" id="Height" name="Height" placeholder="e.g., 165" value="<?php echo htmlspecialchars($vitals['Height'] ?? ''); ?>">
                            <?php if (isset($errors['Height'])): ?><span class="help-block"><?php echo $errors['Height']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-3 form-group <?php echo isset($errors['OxygenSaturation']) ? 'has-error' : ''; ?>">
                            <label for="OxygenSaturation">Oxygen Saturation (SpO2 %)</label>
                            <input type="number" class="form-control" id="OxygenSaturation" name="OxygenSaturation" min="0" max="100" value="<?php echo htmlspecialchars($vitals['OxygenSaturation'] ?? ''); ?>">
                            <?php if (isset($errors['OxygenSaturation'])): ?><span class="help-block"><?php echo $errors['OxygenSaturation']; ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group <?php echo isset($errors['Notes']) ? 'has-error' : ''; ?>">
                        <label for="Notes">Additional Notes</label>
                        <textarea class="form-control" id="Notes" name="Notes" rows="3"><?php echo htmlspecialchars($vitals['Notes'] ?? ''); ?></textarea>
                         <?php if (isset($errors['Notes'])): ?><span class="help-block"><?php echo $errors['Notes']; ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Vital Signs</button>
                    <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>