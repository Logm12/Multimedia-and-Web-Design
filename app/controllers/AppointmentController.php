<?php
// app/controllers/AppointmentController.php

class AppointmentController {
    private $appointmentModel;
    private $doctorAvailabilityModel;
    private $notificationModel; // For creating notifications
    private $userModel;         // To get user details for notifications
    private $db; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (file_exists(__DIR__ . '/../helpers/csrf_helper.php')) {
            require_once __DIR__ . '/../helpers/csrf_helper.php';
        }

        $this->appointmentModel = new AppointmentModel();
        $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
        $this->notificationModel = new NotificationModel(); 
        $this->userModel = new UserModel();         
        $this->db = Database::getInstance(); 
    }

    protected function view($view, $data = []) {
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = $this->userModel->findUserById($_SESSION['user_id'] ?? 0);
        }
        $data['userRole'] = $_SESSION['user_role'] ?? '';

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist, my dear.");
        }
    }

    public function bookSlot() {
        header('Content-Type: application/json'); 

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            http_response_code(401); 
            echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in as a patient.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE); 

        if (empty($input['availability_id']) || empty($input['doctor_id'])) {
            http_response_code(400); 
            echo json_encode(['success' => false, 'message' => 'Missing required fields (availability_id, doctor_id).']);
            exit;
        }

        $availabilityId = filter_var($input['availability_id'], FILTER_VALIDATE_INT);
        $doctorId = filter_var($input['doctor_id'], FILTER_VALIDATE_INT);
        $reasonForVisit = isset($input['reason_for_visit']) ? trim(htmlspecialchars($input['reason_for_visit'])) : null;
        $patientId = $_SESSION['user_id']; 

        if ($availabilityId === false || $availabilityId <= 0 || $doctorId === false || $doctorId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid availability ID or doctor ID.']);
            exit;
        }

        $this->db->beginTransaction();

        try {
            $slotDetails = $this->doctorAvailabilityModel->getSlotById($availabilityId);

            if (!$slotDetails) {
                throw new Exception("Selected slot not found.");
            }
            if ($slotDetails['IsBooked']) {
                throw new Exception("Selected slot is no longer available.");
            }
            if ($slotDetails['DoctorID'] != $doctorId) { 
                throw new Exception("Doctor ID mismatch for the selected slot.");
            }

            $appointmentDateTime = $slotDetails['AvailableDate'] . ' ' . $slotDetails['StartTime'];

            $newAppointmentId = $this->appointmentModel->createAppointment(
                $patientId,
                $doctorId,
                $availabilityId,
                $appointmentDateTime,
                $reasonForVisit
            );

            if (!$newAppointmentId) {
                throw new Exception("Failed to create appointment record.");
            }

            if (!$this->doctorAvailabilityModel->markSlotAsBooked($availabilityId, $newAppointmentId)) {
                throw new Exception("Failed to mark slot as booked. It might have been booked by someone else.");
            }

            // Create notification for the doctor
            $doctorUser = $this->userModel->findUserByDoctorId($doctorId); 
            if ($doctorUser) {
                $notificationData = [
                    'UserID' => $doctorUser['UserID'],
                    'Type' => 'APPOINTMENT_BOOKED',
                    'Message' => 'New appointment booked by ' . $_SESSION['user_fullname'] . ' on ' . date('M j, Y \a\t g:i A', strtotime($appointmentDateTime)) . '.',
                    'Link' => '/doctor/mySchedule?date=' . $slotDetails['AvailableDate'],
                    'RelatedEntityID' => $newAppointmentId,
                    'EntityType' => 'appointment'
                ];
                $this->notificationModel->createNotification($notificationData);
            }

            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment_id' => $newAppointmentId
            ]);
            exit;

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500); 
            if ($e->getMessage() == "Selected slot is no longer available." || $e->getMessage() == "Failed to mark slot as booked. It might have been booked by someone else.") {
                http_response_code(409); 
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    public function myAppointments() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            header('Location: ' . BASE_URL . '/auth/login?redirect_message=Please log in to view your appointments.');
            exit();
        }

        $patientId = $_SESSION['user_id']; 

        $statusFilter = $_GET['status'] ?? 'All';
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'All';
        }

        $appointments = $this->appointmentModel->getAppointmentsByPatientId($patientId, $statusFilter);

        $data = [
            'title' => 'My Appointments',
            'appointments' => $appointments,
            'currentFilter' => $statusFilter,
            'allStatuses' => $validStatuses 
        ];

        $this->view('patient/my_appointments', $data); 
    }

    public function cancelByPatient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['appointment_message_error'] = 'Invalid request method.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
            exit();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            $_SESSION['appointment_message_error'] = 'Unauthorized action.';
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['appointment_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
            exit();
        }

        $patientId = $_SESSION['user_id'];
        $appointmentId = $_POST['appointment_id'] ?? null;

        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) {
            $_SESSION['appointment_message_error'] = 'Invalid Appointment ID.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
            exit();
        }

        $this->db->beginTransaction();
        try {
            $appointmentDetails = $this->appointmentModel->getAppointmentDetailsForCancellation((int)$appointmentId, $patientId);

            if (!$appointmentDetails) { 
                throw new Exception('Appointment not found or you do not have permission to cancel it.');
            }

            if (!isset($appointmentDetails['Status']) || !in_array($appointmentDetails['Status'], ['Scheduled', 'Confirmed'])) {
                throw new Exception('This appointment cannot be cancelled (Current status: ' . htmlspecialchars($appointmentDetails['Status'] ?? 'Unknown') . ').');
            }

            if (!isset($appointmentDetails['AppointmentDateTime'])) {
                throw new Exception('Appointment date/time information is missing.');
            }
            $appointmentTime = strtotime($appointmentDetails['AppointmentDateTime']);
            $currentTime = time();
            $cancellationCutoffHours = 24; 
            if (($appointmentTime - $currentTime) <= ($cancellationCutoffHours * 3600)) {
                throw new Exception('Cannot cancel appointment. It is too close to the appointment time (less than '.$cancellationCutoffHours.' hours).');
            }

            if (!$this->appointmentModel->updateAppointmentStatus((int)$appointmentId, 'CancelledByPatient')) {
                throw new Exception('Failed to update appointment status.');
            }

            if (!empty($appointmentDetails['AvailabilityID'])) { 
                if (!$this->appointmentModel->markSlotAsAvailableAgain($appointmentDetails['AvailabilityID'])) {
                    error_log("Failed to mark slot {$appointmentDetails['AvailabilityID']} as available for cancelled appointment {$appointmentId}. This might require manual correction.");
                }
            }

            // Create notification for the doctor
            $doctorUser = $this->userModel->findUserByDoctorId($appointmentDetails['DoctorID']); 
            if ($doctorUser) {
                $notificationData = [
                    'UserID' => $doctorUser['UserID'],
                    'Type' => 'APPOINTMENT_CANCELLED',
                    'Message' => 'Appointment #' . $appointmentId . ' with ' . $_SESSION['user_fullname'] . ' for ' . date('M j, Y', $appointmentTime) . ' has been cancelled by the patient.',
                    'Link' => '/doctor/mySchedule?date=' . date('Y-m-d', $appointmentTime),
                    'RelatedEntityID' => $appointmentId,
                    'EntityType' => 'appointment'
                ];
                $this->notificationModel->createNotification($notificationData);
            }

            $this->db->commit();
            $_SESSION['appointment_message_success'] = 'Appointment cancelled successfully.';

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error cancelling appointment by patient (ID: {$appointmentId}): " . $e->getMessage());
            $_SESSION['appointment_message_error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
        exit();
    }
}
?>