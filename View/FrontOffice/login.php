<?php
/**
 * FrontOffice/login.php
 * Login form — editorial design from user branch, logic via Verification.php API
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Flash messages from session (e.g. after registration redirect)
$success = $_SESSION['success'] ?? '';
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal — Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/face-id.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-neutral, #F0EADC);
            margin: 0;
        }
        .top-logo { padding: 2rem 4%; }
        .top-logo img { height: 80px; width: auto; }
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .login-container {
            background: var(--white, #fff);
            border: var(--border-main, 3px solid #1D2A44);
            border-radius: var(--radius-lg, 24px);
            box-shadow: 0 20px 60px rgba(29, 42, 68, 0.12);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            text-align: left;
            margin-bottom: 2.5rem;
            border-bottom: var(--border-main, 3px solid #1D2A44);
            padding-bottom: 1.5rem;
        }
        .login-header h1 {
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            font-weight: 900;
            text-transform: uppercase;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
            color: var(--primary-navy, #1D2A44);
        }
        .login-header p {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--primary-navy, #1D2A44);
            border-radius: var(--radius-sm, 12px);
            background: transparent;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-navy, #1D2A44);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            background-color: #f8f6f0;
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.2);
        }
        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background-color: var(--primary-navy, #1D2A44);
            color: var(--white, #fff);
            border: none;
            border-radius: var(--radius-sm, 12px);
            font-size: 1.1rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(29, 42, 68, 0.25);
        }
        .message {
            padding: 1rem;
            border: 2px solid currentColor;
            border-radius: var(--radius-sm, 12px);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .message.error { color: #A4161A; background: #FFB3B3; }
        .message.success { color: #2D6A4F; background: #D8F3DC; }
        .signup-link {
            text-align: center;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--bg-neutral, #F0EADC);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .signup-link a {
            color: var(--primary-navy, #1D2A44);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            margin-left: 0.5rem;
        }
        .inline-error {
            color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem;
            display: block; font-weight: 700; text-transform: uppercase;
        }
        @media (max-width: 500px) {
            .top-logo { padding: 1.5rem; }
            .top-logo img { height: 60px; }
            .login-container { padding: 1.5rem; border-radius: var(--radius-md, 16px); }
            .login-wrapper { padding: 1rem; }
        }

        /* CAPTCHA Styles */
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 1rem;
        }
        #captcha-canvas {
            border: 2px solid var(--primary-navy);
            border-radius: 8px;
            cursor: pointer;
            background: #fff;
        }
        .btn-refresh {
            background: none;
            border: none;
            color: var(--primary-navy);
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .btn-refresh:hover { transform: rotate(180deg); }
    </style>
</head>
<body>

<div class="top-logo">
    <img src="../assets/images/logo.png" alt="CivicPortal" onerror="this.style.display='none'">
</div>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome back</h1>
            <p>Enter your details</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div id="error-message" class="message error" style="display:none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul style="list-style:none; padding:0; margin:0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="login-form" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" placeholder="YOUR@EMAIL.COM" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="captcha_code">Security Code</label>
                <input type="text" name="captcha_code" id="captcha_code" placeholder="ENTER CODE" required>
                <div class="captcha-container">
                    <canvas id="captcha-canvas" width="150" height="50"></canvas>
                    <button type="button" id="refresh-captcha" class="btn-refresh" title="Refresh Code">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <button class="submit-btn" type="submit">Sign In</button>

            <button type="button" id="btn-face-id-login" class="btn-face-id">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9,11.75A1.25,1.25 0 0,0 7.75,13A1.25,1.25 0 0,0 9,14.25A1.25,1.25 0 0,0 10.25,13A1.25,1.25 0 0,0 9,11.75M15,11.75A1.25,1.25 0 0,0 13.75,13A1.25,1.25 0 0,0 15,14.25A1.25,1.25 0 0,0 16.25,13A1.25,1.25 0 0,0 15,11.75M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20C7.59,20 4,16.41 4,12C4,11.71 4,11.42 4.05,11.14C6.41,10.09 8.28,8.16 9.26,5.77C11.07,8.33 14.05,10 17.42,10C18.2,10 18.95,9.91 19.67,9.74C19.88,10.45 20,11.21 20,12C20,16.41 16.41,20 12,20Z" /></svg>
                Login with Face ID
            </button>

            <div style="text-align: center; margin-top: 1rem;">
                <a href="forgot_password.php" style="font-size: 0.85rem; color: #1D2A44; font-weight: 700; text-decoration: none; text-transform: uppercase;">Forgot Password?</a>
            </div>
        </form>

        <div class="signup-link">
            No account? <a href="register.php">Register</a>
        </div>
    </div>
</div>

<!-- Face ID Modal -->
<div id="face-id-modal" class="face-id-modal">
    <div class="face-id-content">
        <button id="close-face-modal" class="face-id-close">&times;</button>
        <h2>Face ID Login</h2>
        <div id="login-status" class="face-id-status status-scanning">Initializing...</div>
        <div class="webcam-container">
            <video id="login-video" width="400" height="300" autoplay muted></video>
            <canvas id="login-canvas"></canvas>
        </div>
        <div id="login-feedback" class="face-id-feedback"></div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script type="module" src="../assets/js/face-login.js"></script>

<script>
    // --- Login Form Submission ---
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const captcha_code = document.getElementById('captcha_code').value;
        const errorMsg = document.getElementById('error-message');
        errorMsg.style.display = 'none';

        try {
            const res = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', data: { email, password, captcha_code } })
            });
            const result = await res.json();

            if (result.success && result.data.requires_2fa) {
                window.location.href = 'verify_otp.php';
                return;
            }

            if (result.success && !result.data.errors) {
                window.location.href = 'index.php';
            } else {
                errorMsg.style.display = 'block';
                errorMsg.textContent = result.data.errors
                    ? Object.values(result.data.errors)[0]
                    : (result.error || 'Login failed');
                
                // Refresh captcha on failure
                if (window.refreshCaptcha) window.refreshCaptcha();
            }
        } catch (err) {
            errorMsg.style.display = 'block';
            errorMsg.textContent = 'A network error occurred';
        }
    });

    // --- Professional Canvas CAPTCHA Logic ---
    function initCaptcha() {
        const canvas = document.getElementById('captcha-canvas');
        const ctx = canvas.getContext('2d');
        const refreshBtn = document.getElementById('refresh-captcha');

        function generate() {
            fetch('../../captcha_api.php?get_code=1')
                .then(res => res.json())
                .then(data => {
                    const code = data.code;
                    draw(code);
                });
        }
        window.refreshCaptcha = generate;

        function draw(code) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Add noise
            for(let i=0; i<30; i++) {
                ctx.strokeStyle = `rgba(29, 42, 68, ${Math.random() * 0.2})`;
                ctx.beginPath();
                ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.stroke();
            }

            ctx.font = "bold 24px 'Courier New', monospace";
            ctx.fillStyle = "#1D2A44";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            
            // Draw code with slight random offsets
            for(let i=0; i<code.length; i++) {
                ctx.save();
                ctx.translate(30 + i * 25, 25);
                ctx.rotate((Math.random() - 0.5) * 0.4);
                ctx.fillText(code[i], 0, 0);
                ctx.restore();
            }
        }

        refreshBtn.addEventListener('click', generate);
        canvas.addEventListener('click', generate);
        generate();
    }
    initCaptcha();
</script>

</body>
</html>
