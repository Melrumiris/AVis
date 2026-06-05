<?php global $pageTemplate; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Offline') ?></title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php require ROOT . '/views/components/navbar.php'; ?>
<main class="content-zone">
    <?php require $pageTemplate ?? ROOT . '/views/pages/error.php'; ?>
</main>
</body>
</html>