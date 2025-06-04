<?php
// app/views/admin/medicines/list.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung
?>

<h2><?php echo htmlspecialchars($data['title']); ?></h2>

<?php if (isset($_SESSION['admin_medicine_message_success'])): ?>
    <p class="success-message" style="margin-bottom: 15px; padding:10px; border:1px solid green; background-color:#e6ffe6;">
        <?php echo $_SESSION['admin_medicine_message_success']; unset($_SESSION['admin_medicine_message_success']); ?>
    </p>
<?php endif; ?>
<?php if (isset($_SESSION['admin_medicine_message_error'])): ?>
    <p class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <?php echo $_SESSION['admin_medicine_message_error']; unset($_SESSION['admin_medicine_message_error']); ?>
    </p>
<?php endif; ?>

<div class="controls" style="margin-bottom: 20px; padding:10px; border:1px solid #eee; background-color:#f9f9f9; display:flex; justify-content:space-between; align-items:center;">
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/listMedicines" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($data['currentSearchTerm'] ?? ''); ?>" placeholder="Search by Name, Manufacturer...">
        <button type="submit" class="btn btn-sm">Search</button>
        <?php if (!empty($data['currentSearchTerm'])): ?>
            <a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="btn btn-sm btn-outline-secondary" style="text-decoration:none;">Clear Search</a>
        <?php endif; ?>
    </form>
    <a href="<?php echo BASE_URL; ?>/admin/createMedicine" class="btn btn-success btn-sm" style="background-color:#28a745; text-decoration:none; color:white;">+ Add New Medicine</a>
</div>

<?php if (!empty($data['medicines'])): ?>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">#</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Name</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Unit</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Manufacturer</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Description</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:right;">Stock</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 1; // Khởi tạo số thứ tự ảo ?>
            <?php foreach ($data['medicines'] as $medicine): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $stt++; ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($medicine['Name']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($medicine['Unit']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($medicine['Manufacturer'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo nl2br(htmlspecialchars($medicine['Description'] ?? 'N/A')); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align:right;"><?php echo htmlspecialchars($medicine['StockQuantity']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <a href="<?php echo BASE_URL . '/admin/editMedicine/' . $medicine['MedicineID']; ?>" class="btn btn-sm btn-info" style="background-color:#17a2b8; text-decoration:none; color:white; padding:3px 6px; margin-right:5px;">Edit</a>
                        <form action="<?php echo BASE_URL; ?>/admin/deleteMedicine" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this medicine? This action cannot be undone if the medicine is not in use.');">
                            <input type="hidden" name="medicine_id_to_delete" value="<?php echo $medicine['MedicineID']; ?>">
                            <?php echo generateCsrfInput(); // CSRF Token ?>
                            <button type="submit" class="btn btn-sm btn-danger" style="background-color:#dc3545; padding:3px 6px;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No medicines found<?php echo !empty($data['currentSearchTerm']) ? ' matching your search "' . htmlspecialchars($data['currentSearchTerm']) . '"' : ''; ?>. <a href="<?php echo BASE_URL; ?>/admin/createMedicine">Add a new one?</a></p>
<?php endif; ?>

<div style="margin-top:20px;">
    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-secondary" style="background-color:#6c757d; text-decoration:none; color:white;">« Back to Admin Dashboard</a>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung
?>