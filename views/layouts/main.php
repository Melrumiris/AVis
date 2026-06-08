<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'AVis') ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <script src="/js/api/ApiHandler.js"></script>
</head>
<body>
    <?php require ROOT . '/views/components/navbar.php'; ?>
    <main class="content-zone">
        <?php require $pageTemplate ?? ROOT . '/views/pages/error.php'; ?>
    </main>
</body>
</html>