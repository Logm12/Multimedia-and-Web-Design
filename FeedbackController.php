<?php
// app/controllers/FeedbackController.php

class FeedbackController {
    private $feedbackModel;
    private $patientModel; // To get PatientID from UserID if needed

    public function __construct() {
        $this->feedbackModel = new FeedbackModel();
        $this->patientModel = new PatientModel(); // Assuming you have this

        // Ensure user is logged in and is a patient for all methods
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            $_SESSION['error_message'] = "You must be logged in as a patient to access feedback features.";
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

    /**
     * Displays the list of feedbacks submitted by the patient.
     */
    public function list() {
        // IMPORTANT: Determine the correct PatientID to use for querying.
        // If $_SESSION['user_id'] is the UserID from Users table, and patientfeedbacks.PatientID
        // refers to Patients.PatientID, you need to get Patients.PatientID first.
        $patientInfo = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
        if (!$patientInfo || !isset($patientInfo['PatientID'])) {
            $_SESSION['error_message'] = "Could not retrieve your patient profile to show feedbacks.";
            header('Location: ' . BASE_URL . '/patient/dashboard');
            exit();
        }
        $patientId = $patientInfo['PatientID'];

        $feedbacks = $this->feedbackModel->getFeedbacksByPatientId($patientId);
        $data = [
            'title' => 'My Feedback History',
            'feedbacks' => $feedbacks
        ];
        $this->view('feedback/list_feedbacks', $data); // Path to your view
    }

    /**
     * Displays the form to submit new feedback.
     */
    public function submit() {
        $patientInfo = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
        if (!$patientInfo || !isset($patientInfo['PatientID'])) {
            $_SESSION['error_message'] = "Could not retrieve your patient profile to submit feedback.";
            header('Location: ' . BASE_URL . '/patient/dashboard');
            exit();
        }
        $patientId = $patientInfo['PatientID'];

        // Get completed appointments for the dropdown
        $appointmentsForFeedback = $this->feedbackModel->getCompletedAppointmentsForFeedbackOptions($patientId);

        $data = [
            'title' => 'Submit New Feedback',
            'appointmentsForFeedback' => $appointmentsForFeedback,
            'input' => $_SESSION['feedback_form_input'] ?? [],
            'errors' => $_SESSION['feedback_form_errors'] ?? [],
            'success_message' => $_SESSION['feedback_success_message'] ?? null,
            'error_message' => $_SESSION['feedback_error_message'] ?? null,
        ];
        unset($_SESSION['feedback_form_input'], $_SESSION['feedback_form_errors'], $_SESSION['feedback_success_message'], $_SESSION['feedback_error_message']);
        
        $this->view('feedback/submit_feedback_form', $data);
    }

    /**
     * Processes the submission of new feedback.
     */
    public function processSubmit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/feedback/submit');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['feedback_error_message'] = 'Invalid form submission. Please try again.';
            header('Location: ' . BASE_URL . '/feedback/submit');
            exit();
        }
        
        $patientInfo = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
        if (!$patientInfo || !isset($patientInfo['PatientID'])) {
            $_SESSION['feedback_error_message'] = "Could not retrieve your patient profile.";
            header('Location: ' . BASE_URL . '/feedback/submit');
            exit();
        }
        $patientId = $patientInfo['PatientID'];

        $appointmentIdWithDoctorId = $_POST['appointment_doctor'] ?? '';
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $comments = trim(filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_SPECIAL_CHARS));

        $errors = [];
        $doctorId = null;
        $appointmentId = null;

        if (empty($appointmentIdWithDoctorId)) {
            $errors['appointment_doctor'] = 'Please select the appointment you are giving feedback for.';
        } else {
            // Value is expected to be "AppointmentID_DoctorID"
            $parts = explode('_', $appointmentIdWithDoctorId);
            if (count($parts) == 2 && filter_var($parts[0], FILTER_VALIDATE_INT) && filter_var($parts[1], FILTER_VALIDATE_INT)) {
                $appointmentId = (int)$parts[0];
                $doctorId = (int)$parts[1];
            } else {
                $errors['appointment_doctor'] = 'Invalid appointment selection.';
            }
        }

        if ($rating === false) {
            $errors['rating'] = 'Please select a rating between 1 and 5 stars.';
        }
        if (empty($comments)) {
            $errors['comments'] = 'Please provide your comments.';
        } elseif (strlen($comments) > 1000) { // Max length example
            $errors['comments'] = 'Comments are too long (max 1000 characters).';
        }

        if (!empty($errors)) {
            $_SESSION['feedback_form_input'] = $_POST;
            $_SESSION['feedback_form_errors'] = $errors;
            header('Location: ' . BASE_URL . '/feedback/submit');
            exit();
        }

        $feedbackData = [
            'PatientID' => $patientId,
            'DoctorID' => $doctorId,
            'AppointmentID' => $appointmentId,
            'Rating' => $rating,
            'Comments' => $comments
        ];

        if ($this->feedbackModel->addFeedback($feedbackData)) {
            $_SESSION['feedback_success_message'] = 'Thank you for your feedback! It has been submitted.';
            header('Location: ' . BASE_URL . '/feedback/list'); // Redirect to feedback history
        } else {
            $_SESSION['feedback_error_message'] = 'Oops! Something went wrong while submitting your feedback. Please try again.';
            $_SESSION['feedback_form_input'] = $_POST; // Keep input for retry
            header('Location: ' . BASE_URL . '/feedback/submit');
        }
        exit();
    }
}
?>