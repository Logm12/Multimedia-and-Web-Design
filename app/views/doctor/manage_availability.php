<?php
// app/views/doctor/manage_availability.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Manage My Availability</h2>
<form method="GET" action="<?php echo BASE_URL; ?>/doctor/manageAvailability" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; background-color:#f9f9f9;">
    <label for="start_date_filter">From:</label>
    <input type="date" id="start_date_filter" name="start_date" value="<?php echo htmlspecialchars($data['currentStartDate']); ?>">

    <label for="end_date_filter" style="margin-left: 10px;">To:</label>
    <input type="date" id="end_date_filter" name="end_date" value="<?php echo htmlspecialchars($data['currentEndDate']); ?>">

    <button type="submit" class="btn btn-sm" style="margin-left: 10px;">Filter Dates</button>
</form>

<p>Showing availability from <?php echo htmlspecialchars(date('M j, Y', strtotime($data['currentStartDate']))); ?> to <?php echo htmlspecialchars(date('M j, Y', strtotime($data['currentEndDate']))); ?>.</p>

<!-- TODO: Thêm form để lọc theo khoảng ngày khác -->
<!-- TODO: Thêm form/nút để "Add New Availability Slot" -->

<?php if (isset($_SESSION['availability_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['availability_message_success']; unset($_SESSION['availability_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['availability_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['availability_message_error']; unset($_SESSION['availability_message_error']); ?></p>
<?php endif; ?>

<?php
$groupedSlots = [];
if (!empty($data['slots'])) {
    foreach ($data['slots'] as $slot) {
        $groupedSlots[$slot['AvailableDate']][] = $slot;
    }
    ksort($groupedSlots); // Sắp xếp theo ngày
}
?>

<?php if (!empty($groupedSlots)): ?>
    <?php foreach ($groupedSlots as $date => $slotsOnDate): ?>
        <h3 style="margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px;"><?php echo htmlspecialchars(date('D, M j, Y', strtotime($date))); ?></h3>
        <table style="width:100%; border-collapse: collapse; margin-bottom:15px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Time Slot</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Status</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Booked By</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slotsOnDate as $slot): ?>
                    <?php
                        $startTime = date('g:i A', strtotime($slot['StartTime']));
                        $endTime = date('g:i A', strtotime($slot['EndTime']));
                    ?>
                    <tr style="<?php echo $slot['IsBooked'] ? 'background-color: #ffeeba;' : ($slot['SlotType'] === 'Blocked' ? 'background-color: #f5c6cb;' : ''); ?>">
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $startTime . ' - ' . $endTime; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php
                            if ($slot['IsBooked']) {
                                echo '<span style="color:orange; font-weight:bold;">Booked</span>';
                            } elseif ($slot['SlotType'] === 'Blocked') {
                                echo '<span style="color:red;">Blocked</span>';
                            } elseif ($slot['SlotType'] === 'Working') {
                                echo '<span style="color:green;">Available</span>';
                            } else {
                                echo htmlspecialchars($slot['SlotType']);
                            }
                            ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $slot['IsBooked'] ? htmlspecialchars($slot['PatientName'] ?? 'N/A') : '-'; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php if (!$slot['IsBooked'] && $slot['SlotType'] === 'Working'): ?>
                                <form action="<?php echo BASE_URL; ?>/doctor/deleteAvailabilitySlot" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this available slot?');">
                                    <input type="hidden" name="availability_id" value="<?php echo $slot['AvailabilityID']; ?>">
                                    <?php // echo generateCsrfInput(); // Hàm tạo CSRF input của bạn ?>
                                    <button type="submit" class="btn btn-danger btn-sm" ...>Delete</button>
                                </form>
                                <form action="<?php echo BASE_URL; ?>/doctor/updateSlotType" method="POST" style="display:inline; margin-left:5px;" onsubmit="return confirm('Are you sure you want to block this slot?');">
                                    <input type="hidden" name="availability_id" value="<?php echo $slot['AvailabilityID']; ?>">
                                    <input type="hidden" name="new_type" value="Blocked"> <!-- Gửi new_type là Blocked -->
                                    <?php // echo generateCsrfInput(); ?>
                                    <button type="submit" class="btn btn-warning btn-sm" style="background-color: #ffc107; padding: 3px 6px; font-size:0.8em;">Block</button>
                                </form>
                            <?php elseif (!$slot['IsBooked'] && $slot['SlotType'] === 'Blocked'): ?>
                                <form action="<?php echo BASE_URL; ?>/doctor/updateSlotType" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to unblock this slot and make it available?');">
                                <input type="hidden" name="availability_id" value="<?php echo $slot['AvailabilityID']; ?>">
                                <input type="hidden" name="new_type" value="Working"> <!-- Gửi new_type là Working -->
                                <?php // echo generateCsrfInput(); ?>
                                <button type="submit" class="btn btn-info btn-sm" style="background-color: #17a2b8; padding: 3px 6px; font-size:0.8em;">Unblock</button>
                            </form>
                                                        <?php else: ?>
 -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <p>No availability slots found for the selected period.</p>
<?php endif; ?>

<hr>
<h3>Add New Availability Slot(s)</h3>
<form action="<?php echo BASE_URL; ?>/doctor/addAvailabilitySlot" method="POST" id="addSlotForm">
    <!-- Thêm CSRF token -->
    <div style="margin-bottom: 10px;">
        <label for="slot_date">Date:</label>
        <input type="date" id="slot_date" name="slot_date" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label for="slot_duration">Slot Duration (minutes):</label>
        <select id="slot_duration" name="slot_duration">
            <option value="30">30 minutes</option>
            <option value="45">45 minutes</option>
            <option value="60">60 minutes (1 hour)</option>
        </select>
    </div>
    <!-- TODO: Thêm tùy chọn lặp lại (nếu muốn) -->
    <button type="submit" class="btn">Add Slot(s)</button>
</form>
<div id="addSlotResult" style="margin-top:10px;"></div>


<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
<!-- =============================================== -->
<!-- THÊM ĐOẠN SCRIPT SAU VÀO ĐÂY -->
<!-- =============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addSlotForm = document.getElementById('addSlotForm');
    const addSlotResultDiv = document.getElementById('addSlotResult');

    // Kiểm tra xem các element có tồn tại không trước khi thêm event listener
    if (addSlotForm && addSlotResultDiv) {
        addSlotForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Ngăn chặn hành vi submit mặc định của form
            console.log('AJAX Add Slot Form submission initiated.'); // Debug

            addSlotResultDiv.innerHTML = '<p><em>Adding slots, please wait...</em></p>';
            const formData = new FormData(this); // 'this' ở đây là form element

            // Nếu bạn đã có hàm generateCsrfInput() và nó thêm input csrf_token
            // thì FormData sẽ tự động lấy. Nếu không, bạn cần thêm thủ công:
            // const csrfToken = document.querySelector('input[name="csrf_token"]'); // Hoặc cách bạn lấy token
            // if (csrfToken) {
            //     formData.append('csrf_token', csrfToken.value);
            // }

            fetch('<?php echo BASE_URL; ?>/doctor/addAvailabilitySlot', {
                method: 'POST',
                body: formData
                // Không cần set 'Content-Type' khi dùng FormData, trình duyệt sẽ tự làm
            })
            .then(response => {
                console.log('Fetch response status:', response.status); // Debug
                // Kiểm tra nếu server không trả về JSON (ví dụ lỗi 500 không có body JSON)
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        throw new Error("Server did not return JSON. Response: " + text);
                    });
                }
            })
            .then(data => {
                console.log('Data received from server:', data); // Debug
                if (data.success) {
                    addSlotResultDiv.innerHTML = `<p class="success-message">${data.message}</p>`;
                    // Tải lại trang sau khi thêm thành công để cập nhật danh sách
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000); // Chờ 2 giây rồi tải lại
                } else {
                    addSlotResultDiv.innerHTML = `<p class="error-message">${data.message || 'An error occurred while adding slots.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error during fetch operation:', error);
                addSlotResultDiv.innerHTML = `<p class="error-message">Request failed: ${error.message}. Please check the console for more details.</p>`;
            });
        });
    } else {
        if (!addSlotForm) console.error('Form with ID "addSlotForm" not found.');
        if (!addSlotResultDiv) console.error('Element with ID "addSlotResult" not found.');
    }

    // TODO: Thêm JavaScript cho các form delete/block/unblock slot sau này
});
</script>