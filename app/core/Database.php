<?php
// app/core/Database.php

// Nạp file cấu hình


// NẠP FILE CONFIG Ở ĐÂY ĐỂ CÁC HẰNG SỐ CÓ SẴN
require_once __DIR__ . '/../../config/database.php';

class Database {
    private static $instance = null;
    private $pdo;
    private $stmt;

    private function __construct() {
        // KHÔNG DÙNG global $dsn, $options; nữa

        // TỰ TẠO $dsn và $options Ở ĐÂY DỰA TRÊN CÁC HẰNG SỐ
        // Đảm bảo các hằng số DB_HOST, DB_NAME, DB_CHARSET đã được định nghĩa trong config/database.php
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_CHARSET') || !defined('DB_USER') || !defined('DB_PASS')) {
            die("Lỗi: Một hoặc nhiều hằng số DB (DB_HOST, DB_NAME, etc.) chưa được định nghĩa trong file config.");
        }

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Sử dụng các biến cục bộ $dsn, $options và các hằng số DB_USER, DB_PASS
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // echo "<p style='color:green;'>Kết nối CSDL thành công!</p>"; // Bỏ comment nếu muốn thấy
        } catch (PDOException $e) {
            die("Kết nối CSDL thất bại: " . $e->getMessage());
        }
    }


    // Singleton pattern để đảm bảo chỉ có một instance của Database
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Chuẩn bị câu lệnh SQL
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }

    // Bind giá trị
    // Ví dụ: $db->bind(':email', $email);
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Thực thi câu lệnh đã chuẩn bị
    public function execute() {
        return $this->stmt->execute();
    }

    // Lấy tất cả các dòng kết quả (mảng các mảng)
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Lấy một dòng kết quả
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Lấy số dòng bị ảnh hưởng bởi câu lệnh (INSERT, UPDATE, DELETE)
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Lấy ID của dòng cuối cùng được chèn
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // Bắt đầu transaction
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    // Commit transaction
    public function commit() {
        return $this->pdo->commit();
    }

    // Rollback transaction
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    // app/core/Database.php
public function inTransaction() {
    return $this->pdo->inTransaction();
}

    // Đóng kết nối (không bắt buộc, PDO sẽ tự động đóng khi script kết thúc)
    public function close() {
        $this->pdo = null;
    }
}
?>