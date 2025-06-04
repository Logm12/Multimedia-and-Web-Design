<?php
// app/views/admin/medicines/form.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung

// Xác định xem đây là form Create hay Edit
$isEditMode = isset($data['medicineId']) && $data['medicineId'] > 0;
$formAction = $isEditMode ? (BASE_URL . '/admin/editMedicine/' . $data['medicineId']) : (BASE_URL . '/admin/createMedicine');
?>

<h2><?php echo htmlspecialchars($data['title']); ?></h2>

<?php if (!empty($data['errors'])): ?>
    <div class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($data['errors'] as $field => $errorMsg): // Giả sử errors là mảng key => value ?>
                <li><?php echo htmlspecialchars(is_int($field) ? $errorMsg : ($field .": ". $errorMsg) ); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php // Thông báo session có thể không cần ở đây nếu luôn redirect về list sau khi thành công/lỗi từ controller ?>


<form action="<?php echo $formAction; ?>" method="POST">
    <?php echo generateCsrfInput(); // CSRF Token ?>
    <?php if ($isEditMode): ?>
        <input type="hidden" name="medicineId" value="<?php echo $data['medicineId']; ?>">
    <?php endif; ?>

    <fieldset>
        <legend>Medicine Details</legend>
        <div class="form-group">
            <label for="Name">Medicine Name: *</label>
            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($data['input']['Name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="Unit">Unit: * (e.g., tablet, capsule, bottle, ml, mg)</label>
            <input type="text" id="Unit" name="Unit" value="<?php echo htmlspecialchars($data['input']['Unit'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="Description">Description:</label>
            <textarea name="Description" id="Description" rows="3"><?php echo htmlspecialchars($data['input']['Description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="Manufacturer">Manufacturer:</label>
            <input type="text" id="Manufacturer" name="Manufacturer" value="<?php echo htmlspecialchars($data['input']['Manufacturer'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="StockQuantity">Stock Quantity: *</label>
            <input type="number" id="StockQuantity" name="StockQuantity" value="<?php echo htmlspecialchars($data['input']['StockQuantity'] ?? '0'); ?>" min="0" required>
        </div>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="btn"><?php echo $isEditMode ? 'Update Medicine' : 'Add Medicine'; ?></button>
        <a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Cancel</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung
?>