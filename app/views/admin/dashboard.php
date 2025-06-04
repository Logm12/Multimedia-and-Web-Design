<?php
// app/views/admin/dashboard.php
require_once __DIR__ . '/../layouts/header.php'; // Sử dụng layout chung
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (isset($data['welcome_message'])): ?>
    <p><?php echo htmlspecialchars($data['welcome_message']); ?></p>
<?php endif; ?>

<p>This is the administration panel. From here you can manage various aspects of the healthcare system.</p>

<?php if (!empty($data['links'])): ?>
    <ul class="admin-dashboard-links" style="list-style-type: none; padding: 0;">
        <?php foreach ($data['links'] as $link): ?>
            <li style="margin-bottom: 10px;">
                <a href="<?php echo htmlspecialchars($link['url']); ?>" class="btn" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                    <?php echo htmlspecialchars($link['text']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php'; // Sử dụng layout chung
?>