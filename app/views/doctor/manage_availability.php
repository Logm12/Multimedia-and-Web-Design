<?php
// app/views/doctor/manage_availability.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Doctor';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_avatar.png';

// $data = $data ?? [ /* ... existing dummy data ... */ ];
// $currentMonthYear = $data['currentMonthYear'] ?? date('F Y');
// $slotsForCalendar = $data['slotsForCalendar'] ?? []; // Data formatted for FullCalendar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Manage Availability'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FullCalendar CSS (You'll need to download/host this or use a CDN) -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; 
            /* MÀU GRADIENT MỚI CỦA CẬU ĐÂY NÈ */
            background: linear-gradient(90deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); 
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
        }
        .sidebar-header-cutie { 
            text-align: center; 
            margin-bottom: 30px; 
            padding: 0 20px; 
        }
        .sidebar-logo-cutie { 
            font-size: 24px; 
            font-weight: 700; 
            color: #fff; 
            text-decoration: none; 
        }
        .sidebar-nav-cutie ul { 
            list-style: none; 
            padding: 0;
            margin: 0; 
        }
        .sidebar-nav-cutie li a { 
            display: flex; 
            align-items: center; 
            padding: 15px 25px; 
            color: #dfe6e9; /* Màu chữ hơi sáng hơn cho dễ đọc trên gradient */
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500; 
            border-left: 4px solid transparent; 
            transition: all 0.2s ease; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.15); /* Nền hơi sáng hơn khi hover/active */
            color: #fff; 
            border-left-color: #55efc4; /* Màu nhấn xanh mint sáng cho active (tương phản) */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 12px; 
            font-size: 18px; 
            width: 20px; 
            text-align: center; 
        }
        .sidebar-footer-cutie { 
            margin-top: auto; 
            padding: 20px 25px; 
            text-align: center; 
            font-size: 13px; 
            color: #bdc3c7; /* Màu chữ cho footer */
        }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #34495e; }

        .availability-layout-cutie { display: flex; gap: 25px; flex-grow: 1; }
        .availability-sidebar-panel-cutie { flex: 0 0 280px; display: flex; flex-direction: column; gap: 20px; }
        .panel-card-cutie { background-color: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .panel-card-cutie h3 { font-size: 16px; font-weight: 600; color: #34495e; margin-bottom: 15px; }
        /* Placeholder for mini-calendar and search */
        .mini-calendar-placeholder-cutie, .search-appointment-placeholder-cutie { height: 200px; display: flex; justify-content: center; align-items: center; border: 1px dashed #bdc3c7; color: #7f8c8d; font-style: italic; }

        .availability-calendar-panel-cutie { flex-grow: 1; background-color: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; flex-direction: column; }
        .calendar-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-view-buttons-cutie button { background-color: #ecf0f1; border: 1px solid #bdc3c7; color: #34495e; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 5px; }
        .calendar-view-buttons-cutie button.active-view-btn-cutie { background-color: #3498db; color: white; border-color: #3498db; }
        .btn-add-availability-cutie { background-color: #2ecc71; color: white; padding: 9px 15px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .btn-add-availability-cutie:hover { background-color: #27ae60; }
        #availabilityCalendar { flex-grow: 1; min-height: 500px; /* Ensure calendar has enough height */ }

        /* Modal for Add Availability */
        .modal-overlay-cutie { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-overlay-cutie.visible-modal-cutie { display: flex; }
        .modal-content-cutie { background-color: #fff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); width: 100%; max-width: 550px; position: relative; }
        .modal-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header-cutie h3 { font-size: 20px; color: #2c3e50; }
        .modal-close-btn-cutie { background: none; border: none; font-size: 24px; cursor: pointer; color: #7f8c8d; }
        .modal-form-grid-cutie { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 20px; }
        .modal-form-grid-cutie .form-group-cutie { margin-bottom: 0; } /* Reset margin from global */
        .modal-form-grid-cutie .full-width-modal-cutie { grid-column: 1 / -1; }
        .modal-form-grid-cutie label { font-size: 13px; color: #495057; margin-bottom: 6px; display: block; }
        .modal-form-grid-cutie input, .modal-form-grid-cutie select { width: 100%; padding: 9px 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px; }
        .modal-form-grid-cutie input:focus, .modal-form-grid-cutie select:focus { border-color: #3498db; box-shadow: 0 0 0 0.15rem rgba(52,152,219,.25); outline: none; }
        .modal-actions-cutie { margin-top: 25px; text-align: right; }
        .btn-save-slot-cutie { background-color: #2ecc71; color: white; padding: 10px 20px; border:none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        .btn-save-slot-cutie:hover { background-color: #27ae60; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* FullCalendar specific overrides if needed */
        .fc .fc-button-primary { background-color: #3498db; border-color: #3498db; }
        .fc .fc-button-primary:hover { background-color: #2980b9; border-color: #2980b9; }
        .fc .fc-daygrid-day.fc-day-today { background-color: rgba(52, 152, 219, 0.1); }
        .fc-event { border-radius: 4px; padding: 3px 5px; font-size: 0.85em; }
        .fc-event-main { cursor: pointer; }
        .fc-event.status-available-fc { background-color: #2ecc71; border-color: #2ecc71; }
        .fc-event.status-booked-fc { background-color: #e67e22; border-color: #e67e22; }
        .fc-event.status-blocked-fc { background-color: #95a5a6; border-color: #95a5a6; }


        @media (max-width: 1200px) { .availability-layout-cutie { flex-direction: column; } .availability-sidebar-panel-cutie { flex: 0 0 auto; flex-direction: row; justify-content: space-around; } .availability-sidebar-panel-cutie .panel-card-cutie { flex: 1; } }
        @media (max-width: 768px) { /* Sidebar responsive */ .availability-sidebar-panel-cutie { flex-direction: column;} }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/mySchedule" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/mySchedule') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>View My Schedule</a></li>
                <li><a href="<?php echo BASE_URL; ?>/medicalrecord/viewConsultationDetails" class="<?php echo (strpos($_GET['url'] ?? '', 'medicalrecord/viewConsultationDetails') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📝</span>EMR</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/manageAvailability" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/manageAvailability') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⏱️</span>Manage Availability</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/patientList" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/patientList') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Patient List</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/notifications" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/notifications') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔔</span>Notifications</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/requestTimeOff" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/requestTimeOff') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Request Time Off</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Update Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Manage Availability'); ?></h2></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar">
                    <span>Dr. <?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (isset($_SESSION['availability_message_success'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['availability_message_success']; unset($_SESSION['availability_message_success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['availability_message_error'])): ?>
            <p class="message-cutie error-message"><?php echo $_SESSION['availability_message_error']; unset($_SESSION['availability_message_error']); ?></p>
        <?php endif; ?>

        <div class="availability-layout-cutie">
            <div class="availability-sidebar-panel-cutie">
                <div class="panel-card-cutie">
                    <h3>Mini Calendar</h3>
                    <div class="mini-calendar-placeholder-cutie" id="miniCalendar">April 2025 (Placeholder)</div>
                </div>
                <div class="panel-card-cutie">
                    <h3>Search Availability</h3>
                    <div class="search-appointment-placeholder-cutie">Search (Placeholder)</div>
                </div>
            </div>
            <div class="availability-calendar-panel-cutie">
                <div class="calendar-header-cutie">
                    <div class="calendar-view-buttons-cutie">
                        <button type="button" id="btnDayView">Day</button>
                        <button type="button" id="btnWeekView" class="active-view-btn-cutie">Week</button>
                        <button type="button" id="btnMonthView">Month</button>
                    </div>
                    <button type="button" class="btn-add-availability-cutie" id="openAddAvailabilityModalBtn">+ Add Availability</button>
                </div>
                <div id='availabilityCalendar'></div>
            </div>
        </div>
    </main>

    <!-- Add Availability Modal -->
    <div class="modal-overlay-cutie" id="addAvailabilityModal">
        <div class="modal-content-cutie">
            <div class="modal-header-cutie">
                <h3>Add New Availability Slot(s)</h3>
                <button type="button" class="modal-close-btn-cutie" id="closeAddAvailabilityModalBtn">×</button>
            </div>
            <form id="addSlotFormModal">
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                <div class="modal-form-grid-cutie">
                    <div class="form-group-cutie">
                        <label for="modal_slot_date">Date:</label>
                        <input type="date" id="modal_slot_date" name="slot_date" required>
                    </div>
                    <div class="form-group-cutie">
                        <label for="modal_slot_duration">Slot Duration:</label>
                        <select id="modal_slot_duration" name="slot_duration">
                            <option value="30">30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">60 minutes</option>
                        </select>
                    </div>
                    <div class="form-group-cutie">
                        <label for="modal_start_time">Start Time:</label>
                        <input type="time" id="modal_start_time" name="start_time" required>
                    </div>
                    <div class="form-group-cutie">
                        <label for="modal_end_time">End Time:</label>
                        <input type="time" id="modal_end_time" name="end_time" required>
                    </div>
                    <div class="form-group-cutie full-width-modal-cutie">
                        <label for="modal_repeat_option">Repeat:</label>
                        <select id="modal_repeat_option" name="repeat_option">
                            <option value="none" selected>Does not repeat</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly on this day</option>
                            <option value="monthly">Monthly on this day</option>
                            <!-- Add more complex options if needed -->
                        </select>
                    </div>
                     <div class="form-group-cutie full-width-modal-cutie" id="repeat_until_group" style="display:none;">
                        <label for="modal_repeat_until">Repeat Until:</label>
                        <input type="date" id="modal_repeat_until" name="repeat_until">
                    </div>
                </div>
                <div class="modal-actions-cutie">
                    <button type="submit" class="btn-save-slot-cutie">Add Slot(s)</button>
                </div>
            </form>
            <div id="addSlotModalResult" style="margin-top:10px;"></div>
        </div>
    </div>
    
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('availabilityCalendar');
        const addAvailabilityModal = document.getElementById('addAvailabilityModal');
        const openModalBtn = document.getElementById('openAddAvailabilityModalBtn');
        const closeModalBtn = document.getElementById('closeAddAvailabilityModalBtn');
        const addSlotFormModal = document.getElementById('addSlotFormModal');
        const addSlotModalResultDiv = document.getElementById('addSlotModalResult');
        const repeatOptionSelect = document.getElementById('modal_repeat_option');
        const repeatUntilGroup = document.getElementById('repeat_until_group');

        // --- FullCalendar Initialization ---
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek', // Default view
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '' // View buttons are handled separately
            },
            slotMinTime: '08:00:00', // Example: working hours
            slotMaxTime: '20:00:00',
            editable: true, // Allows dragging and resizing events (slots)
            selectable: true, // Allows clicking and dragging to select time ranges
            events: <?php echo json_encode($data['slotsForCalendar'] ?? []); ?>, // Load initial slots
            
            // Event click: to edit or delete a slot
            eventClick: function(info) {
                // info.event.id, info.event.title, info.event.start, info.event.end
                // info.event.extendedProps.isBooked, info.event.extendedProps.slotType
                console.log('Event clicked:', info.event);
                if (info.event.extendedProps.isBooked) {
                    alert('This slot is already booked and cannot be modified directly here.');
                    return;
                }
                // Open a modal to edit/delete/block/unblock this slot
                // For now, just a confirm to delete as an example
                if (confirm(`Slot: ${info.event.title}\nDo you want to delete this available slot?`)) {
                    // AJAX call to delete slot (similar to existing delete logic)
                    // Example: deleteSlotOnCalendar(info.event.id);
                    // info.event.remove(); // Remove from calendar UI
                }
            },

            // Date/time range select: to quickly add a new slot
            select: function(info) {
                // info.startStr, info.endStr, info.allDay
                // Pre-fill the add availability modal with these dates/times
                document.getElementById('modal_slot_date').value = info.startStr.substring(0,10);
                document.getElementById('modal_start_time').value = info.startStr.substring(11,16);
                document.getElementById('modal_end_time').value = info.endStr.substring(11,16);
                addAvailabilityModal.classList.add('visible-modal-cutie');
            },
            eventDrop: function(info) { // When a slot is dragged and dropped
                // info.event.id, info.newStart, info.newEnd
                // AJAX call to update slot time
                console.log('Event dropped:', info.event.id, info.event.startStr, info.event.endStr);
                // updateSlotTimeOnCalendar(info.event.id, info.event.startStr, info.event.endStr);
            },
            eventResize: function(info) { // When a slot is resized
                // info.event.id, info.newEnd
                // AJAX call to update slot end time
                console.log('Event resized:', info.event.id, info.event.startStr, info.event.endStr);
                // updateSlotTimeOnCalendar(info.event.id, info.event.startStr, info.event.endStr);
            },
            eventDidMount: function(info) { // Customize event rendering
                if (info.event.extendedProps.isBooked) {
                    info.el.classList.add('status-booked-fc');
                } else if (info.event.extendedProps.slotType === 'Blocked') {
                    info.el.classList.add('status-blocked-fc');
                } else {
                    info.el.classList.add('status-available-fc');
                }
            }
        });
        calendar.render();

        // --- Calendar View Buttons ---
        document.getElementById('btnDayView')?.addEventListener('click', () => calendar.changeView('timeGridDay'));
        document.getElementById('btnWeekView')?.addEventListener('click', () => calendar.changeView('timeGridWeek'));
        document.getElementById('btnMonthView')?.addEventListener('click', () => calendar.changeView('dayGridMonth'));


        // --- Modal Handling ---
        openModalBtn?.addEventListener('click', () => addAvailabilityModal.classList.add('visible-modal-cutie'));
        closeModalBtn?.addEventListener('click', () => addAvailabilityModal.classList.remove('visible-modal-cutie'));
        window.addEventListener('click', (event) => { // Close if clicked outside
            if (event.target == addAvailabilityModal) addAvailabilityModal.classList.remove('visible-modal-cutie');
        });

        repeatOptionSelect?.addEventListener('change', function() {
            if (this.value === 'none') {
                repeatUntilGroup.style.display = 'none';
            } else {
                repeatUntilGroup.style.display = 'block';
            }
        });

        // --- Add Slot Form Submission (Modal) ---
        addSlotFormModal?.addEventListener('submit', function(e) {
            e.preventDefault();
            addSlotModalResultDiv.innerHTML = '<p><em>Adding slots...</em></p>';
            const formData = new FormData(this);
            // Add CSRF token if your backend expects it via FormData
            // const csrfTokenInput = this.querySelector('input[name="csrf_token"]');
            // if (csrfTokenInput) formData.append('csrf_token', csrfTokenInput.value);

            fetch('<?php echo BASE_URL; ?>/doctor/addAvailabilitySlot', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().then(data => ({status: response.status, body: data })))
            .then(res => {
                if (res.body.success) {
                    addSlotModalResultDiv.innerHTML = `<p class="success-message">${res.body.message}</p>`;
                    calendar.refetchEvents(); // << --- ĐÂY LÀ PHÉP MÀU!
                    setTimeout(() => {
                        addAvailabilityModal.classList.remove('visible-modal-cutie');
                        addSlotModalResultDiv.innerHTML = ''; 
                        this.reset(); 
                        repeatUntilGroup.style.display = 'none';
                    }, 1500);
                } else {
                    addSlotModalResultDiv.innerHTML = `<p class="error-message">${res.body.message || 'Error adding slots.'}</p>`;
                }
            })
        });
    });
    </script>
</body>
</html>