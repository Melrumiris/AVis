<div class="http-error">
    <h2>System Failure: <?= htmlspecialchars($errorCode ?? '500') ?></h2>
    <p><?= htmlspecialchars($errorMessage ?? 'Internal Server Error') ?></p>
</div>