<?php
// app/models/AppointmentModel.php

class AppointmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Tạo một lịch hẹn mới
     * @param int $patientId
     * @param int $doctorId
     * @param int $availabilityId ID của slot trong DoctorAvailability
     * @param string $appointmentDateTime Thời gian hẹn chính xác (Y-m-d H:i:s)
     * @param string|null $reasonForVisit Lý do khám bệnh
     * @param string $status Trạng thái ban đầu của lịch hẹn (ví dụ: 'Scheduled', 'Confirmed')
     * @return int|false Trả về AppointmentID mới nếu thành công, false nếu thất bại
     */
    public function createAppointment($patientId, $doctorId, $availabilityId, $appointmentDateTime, $reasonForVisit = null, $status = 'Scheduled') {
        $this->db->query('INSERT INTO Appointments (PatientID, DoctorID, AvailabilityID, AppointmentDateTime, ReasonForVisit, Status)
                          VALUES (:patientId, :doctorId, :availabilityId, :appointmentDateTime, :reasonForVisit, :status)');

        $this->db->bind(':patientId', $patientId);
        $this->db->bind(':doctorId', $doctorId);
        $this->db->bind(':availabilityId', $availabilityId);
        $this->db->bind(':appointmentDateTime', $appointmentDateTime);
        $this->db->bind(':reasonForVisit', $reasonForVisit);
        $this->db->bind(':status', $status);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Lấy thông tin một lịch hẹn bằng AppointmentID
     * @param int $appointmentId
     * @return object|false // Đổi thành object vì single() trả về object hoặc false
     */
    public function getAppointmentById($appointmentId) {
        $this->db->query('SELECT * FROM Appointments WHERE AppointmentID = :appointmentId');
        $this->db->bind(':appointmentId', $appointmentId);
        return $this->db->single();
    }

    /**
     * Lấy các lịch hẹn của một bệnh nhân (ĐÃ CẬP NHẬT)
     * @param int $patientId
     * @param string $statusFilter Lọc theo trạng thái (ví dụ: 'Scheduled', 'Completed', 'All')
     * @param string $orderBy Sắp xếp (ví dụ: 'a.AppointmentDateTime DESC')
     * @return array
     */
    public function getAppointmentsByPatientId($patientId, $statusFilter = 'All', $orderBy = 'a.AppointmentDateTime DESC') {
        $sql = "SELECT
                    a.AppointmentID,
                    a.AppointmentDateTime,
                    a.ReasonForVisit,
                    a.Status,
                    a.AvailabilityID, -- Thêm AvailabilityID
                    u_doc.FullName AS DoctorName,
                    s.Name AS SpecializationName
                FROM Appointments a
                JOIN Doctors d ON a.DoctorID = d.DoctorID
                JOIN Users u_doc ON d.UserID = u_doc.UserID
                LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
                WHERE a.PatientID = :patient_id";

        if ($statusFilter !== 'All' && !empty($statusFilter)) {
            $sql .= " AND a.Status = :status_filter";
        }
        $sql .= " ORDER BY " . $orderBy;

        $this->db->query($sql);
        $this->db->bind(':patient_id', $patientId);

        if ($statusFilter !== 'All' && !empty($statusFilter)) {
            $this->db->bind(':status_filter', $statusFilter);
        }
        return $this->db->resultSet();
    }

    /**
 * Lấy các lịch hẹn của một bác sĩ (CẬP NHẬT)
 * @param int $doctorId
 * @param string $statusFilter Lọc theo trạng thái
 * @param array $dateRangeFilter Mảng chứa 'start_date' và 'end_date' để lọc theo ngày (tùy chọn)
 * @param string $orderBy
 * @return array
 */
public function getAppointmentsByDoctorId($doctorId, $statusFilter = 'All', $dateRangeFilter = [], $orderBy = 'a.AppointmentDateTime ASC') {
    $sql = "SELECT
                a.AppointmentID,
                a.AppointmentDateTime,
                a.ReasonForVisit,
                a.Status,
                u_pat.FullName AS PatientName,
                u_pat.PhoneNumber AS PatientPhoneNumber
            FROM Appointments a
            JOIN Patients pat_info ON a.PatientID = pat_info.PatientID
            JOIN Users u_pat ON pat_info.UserID = u_pat.UserID
            WHERE a.DoctorID = :doctor_id";

    // Xử lý bộ lọc trạng thái
    if ($statusFilter !== 'All' && !empty($statusFilter)) {
        $sql .= " AND a.Status = :status_filter";
    }

    // Xử lý bộ lọc ngày từ $dateRangeFilter
    $params = [':doctor_id' => $doctorId];
    if ($statusFilter !== 'All' && !empty($statusFilter)) {
        $params[':status_filter'] = $statusFilter;
    }

    // Nếu không có dateRangeFilter cụ thể và dateFilterInput là 'all_upcoming' (hoặc một giá trị đặc biệt)
    // chúng ta có thể thêm điều kiện trực tiếp vào SQL
    // Tuy nhiên, để linh hoạt, chúng ta sẽ xử lý các trường hợp cụ thể của $dateRangeFilter
    if (!empty($dateRangeFilter['start_date']) && !empty($dateRangeFilter['end_date'])) {
        $sql .= " AND DATE(a.AppointmentDateTime) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $dateRangeFilter['start_date'];
        $params[':end_date'] = $dateRangeFilter['end_date'];
    } elseif (!empty($dateRangeFilter['specific_date'])) {
        $sql .= " AND DATE(a.AppointmentDateTime) = :specific_date";
        $params[':specific_date'] = $dateRangeFilter['specific_date'];
    }
    // Nếu bạn muốn 'all_upcoming' làm mặc định và không có dateRangeFilter nào khác,
    // bạn có thể thêm điều kiện này nếu $dateRangeFilter rỗng và một biến cờ nào đó được đặt.
    // Ví dụ, nếu controller truyền một giá trị đặc biệt cho $dateFilterInput mà không tạo $dateRangeFilter:
    // (Giả sử $dateFilterInput được truyền vào hàm này hoặc bạn có một cách khác để biết)
    // if (empty($dateRangeFilter) && $someFlagIndicatingAllUpcoming) {
    //    $sql .= " AND a.AppointmentDateTime >= CURDATE()";
    //    // Và có thể bạn chỉ muốn các status 'Scheduled' hoặc 'Confirmed' cho 'all_upcoming'
    //    if ($statusFilter === 'All') { // Nếu status filter đang là all, thì cho all_upcoming chỉ lấy scheduled/confirmed
    //        $sql .= " AND a.Status IN ('Scheduled', 'Confirmed')";
    //    }
    // }
    // Cách đơn giản hơn cho 'all_upcoming' là controller sẽ KHÔNG truyền $dateRangeFilter,
    // và nếu không có $dateRangeFilter, thì mặc định là lấy từ ngày hiện tại trở đi.
    // HOẶC, nếu bạn truyền $dateFilterInput = 'all_upcoming' vào model:
    elseif (isset($dateRangeFilter['type']) && $dateRangeFilter['type'] === 'all_upcoming') {
         $sql .= " AND a.AppointmentDateTime >= CURDATE()";
         // Nếu status filter là 'All', mặc định chỉ lấy các lịch sắp tới có thể diễn ra
         if ($statusFilter === 'All') {
            $sql .= " AND a.Status IN ('Scheduled', 'Confirmed')";
         }
    }
    // Nếu không có $dateRangeFilter và không phải 'all_upcoming', thì là 'all_time' (không thêm điều kiện ngày)


    $sql .= " ORDER BY " . $orderBy; // Đảm bảo $orderBy an toàn

    $this->db->query($sql);
    // Bind tất cả các params đã thu thập
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }

    return $this->db->resultSet();
}

    /**
     * Cập nhật trạng thái của một lịch hẹn
     * @param int $appointmentId
     * @param string $status
     * @return bool
     */
    public function updateAppointmentStatus($appointmentId, $status) {
        $this->db->query('UPDATE Appointments SET Status = :status WHERE AppointmentID = :appointmentId');
        $this->db->bind(':status', $status);
        $this->db->bind(':appointmentId', $appointmentId);
        return $this->db->execute();
    }

    /**
     * Lấy thông tin chi tiết của một lịch hẹn cho việc hủy (bao gồm AvailabilityID) (MỚI THÊM)
     * @param int $appointmentId
     * @param int $patientId (Để kiểm tra quyền sở hữu)
     * @return object|false Thông tin lịch hẹn nếu hợp lệ, false nếu không
     */
    public function getAppointmentDetailsForCancellation($appointmentId, $patientId) {
        $this->db->query(
            "SELECT AppointmentID, PatientID, AvailabilityID, Status, AppointmentDateTime
             FROM Appointments
             WHERE AppointmentID = :appointment_id AND PatientID = :patient_id"
        );
        $this->db->bind(':appointment_id', $appointmentId);
        $this->db->bind(':patient_id', $patientId);
        return $this->db->single();
    }

    /**
     * Đánh dấu một slot trong DoctorAvailability là trống (IsBooked = FALSE) (MỚI THÊM)
     * @param int $availabilityId
     * @return bool
     */
    public function markSlotAsAvailableAgain($availabilityId) {
        if (empty($availabilityId)) {
            return true;
        }
        $this->db->query("UPDATE DoctorAvailability SET IsBooked = FALSE WHERE AvailabilityID = :availability_id");
        $this->db->bind(':availability_id', $availabilityId);
        return $this->db->execute();
    }
    public function getAppointmentByIdWithDoctorInfo($appointmentId) {
    $this->db->query("
        SELECT
            a.*, -- Tất cả các cột từ Appointments
            u_doc.FullName AS DoctorName,
            s.Name AS SpecializationName
        FROM Appointments a
        JOIN Doctors d ON a.DoctorID = d.DoctorID
        JOIN Users u_doc ON d.UserID = u_doc.UserID
        LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
        WHERE a.AppointmentID = :appointment_id
    ");
    $this->db->bind(':appointment_id', $appointmentId);
    return $this->db->single();
}
/**
 * Lấy tất cả lịch hẹn cho Admin xem, với các tùy chọn lọc
 * @param array $filters Mảng chứa các bộ lọc: 'date_from', 'date_to', 'doctor_id', 'patient_name_phone', 'status'
 * @param string $orderBy
 * @return array
 */
public function getAllAppointmentsForAdmin($filters = [], $orderBy = 'a.AppointmentDateTime DESC') {
    $sql = "SELECT
                a.AppointmentID,
                a.AppointmentDateTime,
                a.ReasonForVisit,
                a.Status,
                u_doc.FullName AS DoctorName,
                s.Name AS SpecializationName,
                u_pat.FullName AS PatientName,
                u_pat.PhoneNumber AS PatientPhoneNumber,
                -- Thêm các cột khác nếu cần
                mr.RecordID -- Để kiểm tra xem đã có EMR chưa
            FROM Appointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            JOIN Patients pat_info ON a.PatientID = pat_info.PatientID
            JOIN Users u_pat ON pat_info.UserID = u_pat.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            LEFT JOIN MedicalRecords mr ON a.AppointmentID = mr.AppointmentID -- Join để xem có EMR không
            WHERE 1=1"; // Điều kiện luôn đúng để dễ nối AND

    $params = [];

    // Lọc theo khoảng ngày
    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(a.AppointmentDateTime) >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND DATE(a.AppointmentDateTime) <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    // Lọc theo DoctorID
    if (!empty($filters['doctor_id']) && filter_var($filters['doctor_id'], FILTER_VALIDATE_INT)) {
        $sql .= " AND a.DoctorID = :doctor_id";
        $params[':doctor_id'] = (int)$filters['doctor_id'];
    }

    // Lọc theo tên hoặc SĐT bệnh nhân
    if (!empty($filters['patient_search'])) {
        $sql .= " AND (u_pat.FullName LIKE :patient_search OR u_pat.PhoneNumber LIKE :patient_search)";
        $params[':patient_search'] = '%' . $filters['patient_search'] . '%';
    }

    // Lọc theo trạng thái
    if (!empty($filters['status']) && $filters['status'] !== 'All') {
        $sql .= " AND a.Status = :status";
        $params[':status'] = $filters['status'];
    }

    $sql .= " ORDER BY " . $orderBy; // Đảm bảo $orderBy an toàn

    $this->db->query($sql);
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }
    return $this->db->resultSet();
}
/**
     * Lấy danh sách các lịch hẹn sắp tới cho dashboard của Nurse.
     * Ban đầu có thể lấy tất cả, sau này có thể lọc theo NurseID nếu có phân công.
     * @param string $startDate 'YYYY-MM-DD' (Thường là ngày hiện tại)
     * @param int|null $nurseUserId ID của Nurse (nếu cần lọc theo phân công cho Nurse cụ thể)
     * @param int $limit Số lượng lịch hẹn tối đa muốn lấy
     * @return array
     */
    public function getUpcomingAppointmentsForNurseDashboard($startDate, $nurseUserId = null, $limit = 10) {
        // Câu SQL cơ bản lấy lịch hẹn sắp tới
        $sql = "SELECT A.AppointmentID, A.AppointmentDateTime, A.Status,
                       PUser.FullName as PatientName, DocUser.FullName as DoctorName, S.Name as SpecializationName
                FROM Appointments A
                JOIN Patients Pat ON A.PatientID = Pat.PatientID
                JOIN Users PUser ON Pat.UserID = PUser.UserID
                JOIN Doctors Doc ON A.DoctorID = Doc.DoctorID
                JOIN Users DocUser ON Doc.UserID = DocUser.UserID
                LEFT JOIN Specializations S ON Doc.SpecializationID = S.SpecializationID
                WHERE DATE(A.AppointmentDateTime) >= :start_date
                AND A.Status IN ('Scheduled', 'Confirmed') "; // Chỉ lấy các lịch chưa hoàn thành/hủy

        $params = [':start_date' => $startDate];

        // PHẦN NÀY SẼ CẦN NÂNG CẤP SAU KHI CÓ LOGIC PHÂN CÔNG NURSE CHO DOCTOR
        if ($nurseUserId) {
            // Nếu có logic Nurse được gán cho Doctor cụ thể, bạn cần join với bảng DoctorNurseAssignments
            // và lọc theo $nurseUserId
            // Ví dụ (cần điều chỉnh cho đúng CSDL của bạn):
            // $sql .= " AND A.DoctorID IN (SELECT dna.DoctorID FROM DoctorNurseAssignments dna WHERE dna.NurseID = (SELECT NurseID FROM Nurses WHERE UserID = :nurse_user_id)) ";
            // $params[':nurse_user_id'] = $nurseUserId;
            // HOẶC nếu NurseID được gán trực tiếp trong bảng Appointments
            // $sql .= " AND A.NurseID = (SELECT NurseID FROM Nurses WHERE UserID = :nurse_user_id) ";
            // $params[':nurse_user_id'] = $nurseUserId;
        }

        $sql .= " ORDER BY A.AppointmentDateTime ASC LIMIT :limit";
        $params[':limit'] = $limit;

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        return $this->db->resultSet();
    }
    /**
     * Lấy danh sách lịch hẹn cho Nurse xem, có thể lọc.
     * @param string $filterDate 'YYYY-MM-DD'
     * @param string $filterStatus Trạng thái lịch hẹn ('All', 'Scheduled', ...)
     * @param int|null $nurseUserId ID của Nurse (nếu cần lọc theo phân công)
     * @param int|null $filterDoctorId ID của bác sĩ (nếu Nurse lọc theo bác sĩ cụ thể mà họ hỗ trợ)
     * @return array
     */
    public function getAppointmentsForNurseView($filterDate, $filterStatus = 'All', $nurseUserId = null, $filterDoctorId = null) {
        $sql = "SELECT A.AppointmentID, A.AppointmentDateTime, A.Status, A.ReasonForVisit,
                       PUser.FullName as PatientName, Pat.PatientID,
                       DocUser.FullName as DoctorName, Doc.DoctorID as DoctorProfileID,
                       S.Name as SpecializationName
                FROM Appointments A
                JOIN Patients Pat ON A.PatientID = Pat.PatientID
                JOIN Users PUser ON Pat.UserID = PUser.UserID
                JOIN Doctors Doc ON A.DoctorID = Doc.DoctorID
                JOIN Users DocUser ON Doc.UserID = DocUser.UserID
                LEFT JOIN Specializations S ON Doc.SpecializationID = S.SpecializationID
                WHERE DATE(A.AppointmentDateTime) = :filter_date";

        $params = [':filter_date' => $filterDate];

        if ($filterStatus !== 'All') {
            $sql .= " AND A.Status = :filter_status";
            $params[':filter_status'] = $filterStatus;
        }

        // PHẦN LỌC THEO PHÂN CÔNG NURSE (SẼ HOÀN THIỆN SAU)
        if ($nurseUserId) {
            // Giả sử Nurse được phân công cho nhiều bác sĩ qua bảng DoctorNurseAssignments
            // Và NurseID trong DoctorNurseAssignments là UserID của Nurse
            // $sql .= " AND A.DoctorID IN (
            //             SELECT dna.DoctorID
            //             FROM DoctorNurseAssignments dna
            //             JOIN Nurses n ON dna.NurseID = n.NurseID
            //             WHERE n.UserID = :nurse_user_id
            //           )";
            // $params[':nurse_user_id'] = $nurseUserId;

            // Hoặc nếu bạn lưu NurseID trực tiếp trong bảng Appointments
            // $sql .= " AND A.NurseID = (SELECT n.NurseID FROM Nurses n WHERE n.UserID = :nurse_user_id)";
            // $params[':nurse_user_id'] = $nurseUserId;
        }

        if ($filterDoctorId) {
            $sql .= " AND A.DoctorID = :filter_doctor_id";
            $params[':filter_doctor_id'] = $filterDoctorId;
        }

        $sql .= " ORDER BY A.AppointmentDateTime ASC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        return $this->db->resultSet();
    }
      /**
     * Lấy chi tiết một lịch hẹn bằng ID (có thể dùng chung cho nhiều vai trò)
     * @param int $appointmentId
     * @return array|false
     */
      public function getAppointmentDetailsById($appointmentId) {
        $sql = "SELECT A.*,
                       PUser.FullName as PatientFullName, PUser.Email as PatientEmail, PUser.PhoneNumber as PatientPhoneNumber,
                       Pat.PatientID as PatientProfileID, -- LẤY PatientID TỪ BẢNG Patients
                       Pat.DateOfBirth as PatientDOB, Pat.Gender as PatientGender,
                       DocUser.FullName as DoctorFullName, DocUser.Email as DoctorEmail,
                       S.Name as SpecializationName,
                       NUser.FullName as NurseFullName
                FROM Appointments A
                JOIN Patients Pat ON A.PatientID = Pat.PatientID
                JOIN Users PUser ON Pat.UserID = PUser.UserID
                JOIN Doctors Doc ON A.DoctorID = Doc.DoctorID
                JOIN Users DocUser ON Doc.UserID = DocUser.UserID
                LEFT JOIN Specializations S ON Doc.SpecializationID = S.SpecializationID
                LEFT JOIN Nurses N ON A.NurseID = N.NurseID
                LEFT JOIN Users NUser ON N.UserID = NUser.UserID
                WHERE A.AppointmentID = :appointment_id";

        $this->db->query($sql);
        $this->db->bind(':appointment_id', $appointmentId, PDO::PARAM_INT);
        return $this->db->single();
    }
}

?>