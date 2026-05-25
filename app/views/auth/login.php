<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login – USeP Vehicle Reservation System</title>
    <meta name="description" content="Sign in to the USeP Vehicle Reservation System"/>
    <meta name="csrf-token" content="<?= Controller::generateCsrfToken() ?>"/>
    <meta name="base-url" content="<?= BASE_URL ?>"/>
    <link rel="icon" href="<?= BASE_URL ?>images/logo.png"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/login.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/notifications.css"/>
</head>
<body class="login-page">

<header class="site-header">
    <img src="<?= BASE_URL ?>images/logo.png" class="logo" alt="USeP Logo"/>
    <span class="site-title">USeP Vehicle Reservation System</span>
</header>

<main class="login-main">
    <div class="login-card">

        <div class="card-header">
            <img src="<?= BASE_URL ?>images/logo.png" alt="USeP Logo"/>
            <h1>WELCOME!</h1>
            <p>Log in to your account to continue.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" id="login-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>auth/do_login" method="POST" novalidate id="login-form"
              data-ajax-url="<?= BASE_URL ?>api/auth/login">
            <?= Controller::csrfField() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username"
                       autocomplete="username" required/>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password" required/>
                    <button type="button" class="toggle-password" id="togglePwd"
                            aria-label="Show/hide password">
                        <img src="<?= BASE_URL ?>images/visible.png" id="toggle-icon" alt="Show"/>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary login-btn" id="login-submit">Log In</button>
        </form>
    </div>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> University of Southeastern Philippines. All rights reserved.
</footer>

<script src="<?= BASE_URL ?>public/js/notifications.js"></script>
<script src="<?= BASE_URL ?>public/js/ajax.js"></script>
<script src="<?= BASE_URL ?>public/js/login.js"></script>
</body>
</html>