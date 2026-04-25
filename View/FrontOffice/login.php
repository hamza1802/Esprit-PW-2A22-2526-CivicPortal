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
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-neutral, #F0EADC);
            margin: 0;
        }
        .top-logo { padding: 2.5rem 4rem; }
        .top-logo img { height: 100px; width: auto; }
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            margin-top: -60px;
        }
        .login-container {
            background: var(--white, #fff);
            border: var(--border-main, 3px solid #1D2A44);
            box-shadow: var(--shadow-editorial, 15px 15px 0px 0px rgba(29, 42, 68, 0.1));
            width: 100%;
            max-width: 360px;
            padding: 2.22rem;
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
            font-size: 2.5rem;
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
            box-shadow: 6px 6px 0px 0px var(--primary-navy, #1D2A44);
        }
        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background-color: var(--primary-navy, #1D2A44);
            color: var(--white, #fff);
            border: none;
            font-size: 1.1rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }
        .submit-btn:hover {
            transform: translate(-4px, -4px);
            box-shadow: 6px 6px 0px 0px rgba(29, 42, 68, 0.3);
        }
        .message {
            padding: 1rem;
            border: 2px solid currentColor;
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
            .top-logo { padding: 2rem; }
            .login-container { padding: 2rem; }
        }
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

            <button class="submit-btn" type="submit">Sign In</button>
        </form>

        <div class="signup-link">
            No account? <a href="register.php">Register</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorMsg = document.getElementById('error-message');
        errorMsg.style.display = 'none';

        try {
            const res = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', data: { email, password } })
            });
            const result = await res.json();

            if (result.success && !result.data.errors) {
                const userRole = result.data.user.role;
                if (userRole === 'admin' || userRole === 'agent') {
                    window.location.href = '../BackOffice/index.php';
                } else {
                    window.location.href = 'index.php';
                }
            } else {
                errorMsg.style.display = 'block';
                errorMsg.textContent = result.data.errors
                    ? Object.values(result.data.errors)[0]
                    : 'Login failed';
            }
        } catch (err) {
            errorMsg.style.display = 'block';
            errorMsg.textContent = 'A network error occurred';
        }
    });
</script>

</body>
</html>
