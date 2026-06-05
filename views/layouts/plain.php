<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Offline') ?></title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <main class="content-zone">
        <?php require $pageTemplate ?? ROOT . '/views/pages/error.php'; ?>
    </main>

    <script src="../../public/js/api/auth.js"></script>
</body>
</html>