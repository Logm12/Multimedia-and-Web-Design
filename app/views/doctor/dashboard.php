<?php
// app/views/doctor/dashboard.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Doctor Dashboard</h2>

<?php if (isset($data['welcome_message'])): ?>
    <p><?php echo htmlspecialchars($data['welcome_message']); ?></p>
<?php endif; ?>

<p>This is your doctor dashboard. Manage your schedule and patient appointments.</p>

<?php if (!empty($data['links'])): ?>
    <ul>
        <?php foreach ($data['links'] as $link): ?>
            <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['text']); ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>