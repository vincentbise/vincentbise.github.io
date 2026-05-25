<?php
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($pageTitle) ?> – USeP VRS</title>
    <meta name="description" content="University of Southeastern Philippines Vehicle Reservation System"/>
    <meta name="csrf-token" content="<?= Controller::generateCsrfToken() ?>"/>
    <meta name="base-url" content="<?= BASE_URL ?>"/>
    <link rel="icon" href="<?= BASE_URL ?>images/logo.png"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=<?= time() ?>"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/tables.css?v=<?= time() ?>"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/notifications.css?v=<?= time() ?>"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/responsive.css?v=<?= time() ?>"/>
</head>
<body>

<header class="site-header">
    <a href="<?= BASE_URL ?>" class="header-brand">
        <img src="<?= BASE_URL ?>images/logo.png" class="logo" alt="USeP Logo"/>
        <span class="site-title">USeP Vehicle Reservation System</span>
    </a>
    <div class="header-right">
        <span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
        <button class="nav-toggle" id="toggleSideNav" aria-label="Toggle Navigation">
            <img src="<?= BASE_URL ?>images/navigation.png" alt="Menu"/>
        </button>
    </div>
</header>


<?php include VIEW_PATH . '/layouts/sidebar.php'; ?>
