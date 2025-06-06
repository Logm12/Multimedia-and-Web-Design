<?php
// app/models/NotificationModel.php

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createNotification($data) {
        $this->db->query("INSERT INTO notifications (UserID, RelatedEntityID, EntityType, Type, Message, ShortMessage, Link, IsRead, CreatedAt) 
                          VALUES (:user_id, :related_entity_id, :entity_type, :type, :message, :short_message, :link, FALSE, NOW())");
        $this->db->bind(':user_id', $data['UserID']);
        $this->db->bind(':related_entity_id', $data['RelatedEntityID'] ?? null);
        $this->db->bind(':entity_type', $data['EntityType'] ?? null);
        $this->db->bind(':type', $data['Type']);
        $this->db->bind(':message', $data['Message']);
        $this->db->bind(':short_message', $data['ShortMessage'] ?? substr($data['Message'], 0, 100) . '...');
        $this->db->bind(':link', $data['Link'] ?? null);
        return $this->db->execute();
    }

    public function getNotificationsByUserId($userId, $limit = 20, $offset = 0) {
        $this->db->query("SELECT * FROM notifications WHERE UserID = :user_id ORDER BY CreatedAt DESC LIMIT :limit OFFSET :offset");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function markAsRead($notificationId, $userId) {
        $this->db->query("UPDATE notifications SET IsRead = TRUE WHERE NotificationID = :notification_id AND UserID = :user_id");
        $this->db->bind(':notification_id', $notificationId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function markAllAsRead($userId) {
        $this->db->query("UPDATE notifications SET IsRead = TRUE WHERE UserID = :user_id AND IsRead = FALSE");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function getUnreadNotificationCount($userId) {
        $this->db->query("SELECT COUNT(NotificationID) as unread_count FROM notifications WHERE UserID = :user_id AND IsRead = FALSE");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? (int)$row['unread_count'] : 0;
    }
    
    public function getNotificationById($notificationId) {
        $this->db->query("SELECT * FROM notifications WHERE NotificationID = :notification_id");
        $this->db->bind(':notification_id', $notificationId);
        return $this->db->single();
    }
}
?>