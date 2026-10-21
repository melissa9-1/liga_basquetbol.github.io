<?php if ($successMessage): ?>
    <div class="alert success" role="status"><?= h($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert error" role="alert"><?= h($errorMessage) ?></div>
<?php endif; ?>
