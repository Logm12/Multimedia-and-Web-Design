<?php
// app/controllers/NotificationController.php

class NotificationController {
    private $notificationModel;
    private $userModel; // To get user info for the view

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "You must be logged in to view notifications.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    protected function view($view, $data = []) {
        // Add user info to data for the layout
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = $this->userModel->findUserById($_SESSION['user_id']);
        }
        $data['userRole'] = $_SESSION['user_role'];

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist, my dear.");
        }
    }

    /**
     * Display the list of notifications for the logged-in user.
     */
    public function list() {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getNotificationsByUserId($userId);
        
        $data = [
            'title' => 'My Notifications',
            'notifications' => $notifications
        ];

        // The view path depends on the user's role
        $roleFolder = strtolower($_SESSION['user_role']); // patient, doctor, etc.
        $this->view($roleFolder . '/notifications/list', $data);
    }

    /**
     * Mark a notification as read and redirect to its link.
     */
    public function read($notificationId = 0) {
        $userId = $_SESSION['user_id'];
        $notificationId = (int)$notificationId;

        if ($notificationId > 0) {
            $notification = $this->notificationModel->getNotificationById($notificationId);
            
            // Ensure the notification belongs to the current user before marking as read
            if ($notification && $notification['UserID'] == $userId) {
                $this->notificationModel->markAsRead($notificationId, $userId);
                
                // Redirect to the notification's link if it exists, otherwise back to the list
                if (!empty($notification['Link'])) {
                    header('Location: ' . BASE_URL . $notification['Link']);
                    exit();
                }
            }
        }
        
        // Default redirect if something goes wrong or no link
        header('Location: ' . BASE_URL . '/notification/list');
        exit();
    }

    /**
     * Mark all notifications as read for the logged-in user.
     */
    public function markAllAsRead() {
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAllAsRead($userId);
        
        $_SESSION['success_message'] = "All notifications have been marked as read.";
        header('Location: ' . BASE_URL . '/notification/list');
        exit();
    }
}
?>