<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>404 – Page Not Found | USeP VRS</title>
    <link rel="icon" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>images/logo.png"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap');
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #800000 0%, #3a0000 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            padding: 30px;
        }
        .error-logo { width: 80px; margin: 0 auto 24px; opacity: .9; }
        .error-code {
            font-size: clamp(80px, 15vw, 140px);
            font-weight: 800;
            line-height: 1;
            opacity: .15;
            position: absolute;
            pointer-events: none;
            user-select: none;
        }
        .error-box { position: relative; z-index: 1; }
        h1 { font-size: clamp(22px, 4vw, 32px); margin-bottom: 12px; font-weight: 800; }
        p  { font-size: 15px; opacity: .8; margin-bottom: 30px; }
        .btn {
            display: inline-block;
            background: rgba(255,255,255,.15);
            border: 2px solid rgba(255,255,255,.4);
            color: #fff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            transition: background .2s;
        }
        .btn:hover { background: rgba(255,255,255,.28); }
        .logo-label {
            margin-top: 48px;
            opacity: .45;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="error-code">404</div>
    <div class="error-box">
        <img src="<?= defined('BASE_URL') ? BASE_URL : '/' ?>images/logo.png"
             class="error-logo" alt="USeP Logo"/>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn">← Back to Home</a>
    </div>
    <p class="logo-label">USeP Vehicle Reservation System &copy; <?= date('Y') ?></p>
</body>
</html>