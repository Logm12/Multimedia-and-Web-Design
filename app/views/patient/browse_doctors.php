<?php
// app/views/patient/browse_doctors.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Browse Available Doctors</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<?php if (!empty($data['doctors'])): ?>
    <div class="doctor-list">
        <?php foreach ($data['doctors'] as $doctor): ?>
            <div class="doctor-card" style="border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <?php // SỬA Ở ĐÂY: Dùng cú pháp mảng ?>
                <h3><?php echo htmlspecialchars($doctor['DoctorName'] ?? 'N/A'); ?></h3>
                <p>
                    <strong>Specialization:</strong>
                    <?php echo htmlspecialchars($doctor['SpecializationName'] ?? 'N/A'); ?>
                </p>
                <p>
                    <strong>Experience:</strong>
                    <?php echo htmlspecialchars($doctor['ExperienceYears'] ?? '0'); ?> years
                </p>
                <?php if (!empty($doctor['DoctorBio'])): ?>
                    <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($doctor['DoctorBio'])); ?></p>
                <?php endif; ?>
                <p>
                    <strong>Consultation Fee:</strong>
                    <?php echo htmlspecialchars(number_format($doctor['ConsultationFee'] ?? 0, 2)); ?>
                </p>
                <button class="btn view-availability-btn" data-doctor-id="<?php echo $doctor['DoctorID']; // SỬA Ở ĐÂY ?>">
                    View Availability & Book
                </button>
                <div class="availability-slots" id="slots-for-doctor-<?php echo $doctor['DoctorID']; // SỬA Ở ĐÂY ?>" style="margin-top:10px;">
                    <!-- Lịch trống sẽ được load vào đây bằng AJAX -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No doctors are currently available.</p>
<?php endif; ?>

<!-- app/views/patient/browse_doctors.php -->
<!-- ... (phần HTML ở trên) ... -->

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewAvailabilityButtons = document.querySelectorAll('.view-availability-btn');

    viewAvailabilityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const doctorId = this.dataset.doctorId;
            const slotsContainer = document.getElementById('slots-for-doctor-' + doctorId);
            const currentButton = this;

            slotsContainer.innerHTML = '<p><em>Loading availability...</em></p>';
            currentButton.disabled = true;
            currentButton.textContent = 'Loading...';

            fetch('<?php echo BASE_URL; ?>/patient/getDoctorAvailability/' + doctorId)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.message || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                currentButton.disabled = false;
                currentButton.textContent = 'View Availability & Book';

                if (data.success && data.slots && data.slots.length > 0) {
                    let html = '<h4>Available Slots:</h4><ul>';
                    data.slots.forEach(slot => {
                        const startTime = new Date(`1970-01-01T${slot.StartTime}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                        const endTime = new Date(`1970-01-01T${slot.EndTime}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                        html += `<li style="margin-bottom: 5px; padding: 5px; background-color: #f9f9f9; border-radius: 3px;" id="slot-item-${slot.AvailabilityID}">
                                    <strong>${slot.AvailableDate}</strong> (${startTime} - ${endTime})
                                    <button class="btn book-slot-btn"
                                            data-availability-id="${slot.AvailabilityID}"
                                            data-doctor-id="${slot.DoctorID}"
                                            style="margin-left: 10px; padding: 5px 10px; font-size: 0.9em; cursor:pointer;">
                                        Book This Slot
                                    </button>
                                 </li>`;
                    });
                    html += '</ul>';
                    slotsContainer.innerHTML = html;
                    attachBookSlotListeners();
                } else if (data.success && data.slots && data.slots.length === 0) {
                    slotsContainer.innerHTML = '<p>No available slots found for the selected period.</p>';
                } else {
                    slotsContainer.innerHTML = `<p>${data.message || 'Could not retrieve availability.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching availability:', error);
                slotsContainer.innerHTML = `<p>Error loading availability: ${error.message}. Please try again.</p>`; // <--- SỬA THÀNH DẤU NHÁY NGƯỢC
                currentButton.disabled = false;
                currentButton.textContent = 'View Availability & Book';
            });
        });
    });

    function attachBookSlotListeners() {
        const bookSlotButtons = document.querySelectorAll('.book-slot-btn');
        bookSlotButtons.forEach(button => {
            const newButton = button.cloneNode(true); // Clone để tránh multiple listeners
            button.parentNode.replaceChild(newButton, button); // Thay thế nút cũ bằng nút mới

            newButton.addEventListener('click', function() {
                const availabilityId = this.dataset.availabilityId;
                const doctorId = this.dataset.doctorId;
                const bookingButton = this; // Lưu lại nút đặt lịch

                const reasonForVisit = prompt("Please enter the reason for your visit (optional):", "");

                if (reasonForVisit !== null) { // User không nhấn cancel
                    bookingButton.disabled = true;
                    bookingButton.textContent = 'Booking...';

                    // --- HOÀN THIỆN PHẦN FETCH POST Ở ĐÂY ---
                    fetch('<?php echo BASE_URL; ?>/appointment/bookSlot', { // Thay đổi URL nếu controller/action khác
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            // 'X-CSRF-TOKEN': 'your_csrf_token_here' // Nếu dùng CSRF
                        },
                        body: JSON.stringify({
                            availability_id: parseInt(availabilityId), // Đảm bảo là số
                            doctor_id: parseInt(doctorId),           // Đảm bảo là số
                            reason_for_visit: reasonForVisit
                        })
                    })
                    .then(response => {
                        // Kiểm tra xem server có trả về lỗi không, ngay cả khi response.ok là true
                        // (ví dụ: server trả về 200 OK nhưng body là JSON lỗi)
                        return response.json().then(data => {
                            if (!response.ok) {
                                // Nếu HTTP status không phải 2xx, throw error với message từ server
                                throw new Error(data.message || `HTTP error! status: ${response.status}`);
                            }
                            return data; // Trả về data nếu response OK
                        });
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Appointment booked successfully!');
                            // Xóa slot đã đặt khỏi danh sách hoặc đánh dấu là đã đặt
                            const slotItem = document.getElementById('slot-item-' + availabilityId);
                            if (slotItem) {
                                // slotItem.remove(); // Xóa hoàn toàn
                                slotItem.innerHTML = `<strong>${slotItem.querySelector('strong').textContent}</strong> - <span style="color:green;font-weight:bold;">Booked! (ID: ${data.appointment_id})</span>`;
                            }
                        } else {
                            // data.success là false, hiển thị lỗi từ server
                            alert(data.message || 'Failed to book appointment. Please try again.');
                            bookingButton.disabled = false;
                            bookingButton.textContent = 'Book This Slot';
                        }
                    })
                    .catch(error => {
                        console.error('Error booking slot:', error);
                        alert('Error booking slot: ' + error.message + '. Please try again.');
                        bookingButton.disabled = false;
                        bookingButton.textContent = 'Book This Slot';
                    });
                } else {
                    console.log("Booking cancelled by user.");
                }
            });
        });
    }
});
</script>
