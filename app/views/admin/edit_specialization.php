<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<h2><?php echo $data['title']; ?></h2>

<?php if (!empty($data['errors'])): ?>
    <div class="error-message" style="border:1px solid red; background-color:#ffe0e0; padding:10px; margin-bottom:15px;">
        <strong>Please correct the following errors:</strong>
        <ul><?php foreach ($data['errors'] as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form action="<?php echo BASE_URL . '/admin/editSpecialization' . ($data['specialization'] ? '/' . $data['specialization']['SpecializationID'] : ''); ?>" method="POST">
    <?php // echo generateCsrfInput(); // CSRF Token ?>
    <?php if ($data['specialization']): ?>
        <input type="hidden" name="id" value="<?php echo $data['specialization']['SpecializationID']; ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="name">Specialization Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['input_name']); ?>" required style="width:100%; padding:8px;">
    </div>
    <div class="form-group">
        <label for="description">Description (Optional):</label>
        <textarea id="description" name="description" rows="4" style="width:100%; padding:8px;"><?php echo htmlspecialchars($data['input_description']); ?></textarea>
    </div>
    <button type="submit" class="btn" style="background-color:#28a745;"><?php echo $data['specialization'] ? 'Update' : 'Add'; ?> Specialization</button>
    <a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Cancel</a>
</form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>