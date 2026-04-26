<?php
/**
 * FrontOffice/register.php
 * Registration form — editorial design from user branch, logic via Verification.php API
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal — Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            max-width: 480px;
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
            font-size: clamp(1.8rem, 5vw, 2.2rem);
            font-weight: 900;
            text-transform: uppercase;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
            color: var(--primary-navy, #1D2A44);
        }
        .login-header p {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
        }
        .form-group { margin-bottom: 1.2rem; }
        .form-group.full-width { grid-column: span 2; }
        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid var(--primary-navy, #1D2A44);
            border-radius: var(--radius-sm, 12px);
            background: transparent;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary-navy, #1D2A44);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
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
            grid-column: span 2;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(29, 42, 68, 0.25);
        }
        .message.error {
            color: #A4161A; background: #FFB3B3;
            padding: 1rem; border: 2px solid currentColor;
            border-radius: var(--radius-sm, 12px);
            margin-bottom: 1.5rem; grid-column: span 2;
            font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        }
        .signup-link {
            text-align: center;
            margin-top: 2rem; padding-top: 1.5rem;
            border-top: 2px solid var(--bg-neutral, #F0EADC);
            font-size: 0.85rem; font-weight: 700;
            text-transform: uppercase; grid-column: span 2;
        }
        .signup-link a {
            color: var(--primary-navy, #1D2A44);
            text-decoration: underline; text-decoration-thickness: 2px;
            margin-left: 0.5rem;
        }
        .inline-error {
            color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem;
            display: block; font-weight: 700; text-transform: uppercase;
        }
        @media (max-width: 500px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full-width { grid-column: auto; }
            .submit-btn { grid-column: auto; }
            .signup-link { grid-column: auto; }
            .message.error { grid-column: auto; }
            .login-container { padding: 1.5rem; border-radius: var(--radius-md, 16px); }
            .login-wrapper { padding: 1rem; }
            .top-logo { padding: 1.5rem; }
            .top-logo img { height: 60px; }
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
            <h1>Join the portal</h1>
            <p>Enter your details</p>
        </div>

        <form id="register-form" class="form-grid" novalidate>

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

            <div class="form-group full-width">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" placeholder="YOUR NAME" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
            </div>

            <div class="form-group full-width">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" placeholder="YOUR@EMAIL.COM" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>

            <div class="form-group full-width">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <?php $roleValue = $old['role'] ?? 'citizen'; ?>
                    <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                    <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="••••••••">
            </div>

            <button class="submit-btn" type="submit">Get Started</button>

            <div class="signup-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorMsg = document.getElementById('error-message');
        errorMsg.style.display = 'none';

        const data = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            role: document.getElementById('role').value,
            password: document.getElementById('password').value,
            confirm_password: document.getElementById('confirm_password').value
        };

        try {
            const res = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'register', data })
            });
            const result = await res.json();

            if (result.success && !result.data.errors) {
                window.location.href = 'login.php';
            } else {
                errorMsg.style.display = 'block';
                errorMsg.innerHTML = result.data.errors
                    ? Object.values(result.data.errors).join('<br>')
                    : 'Registration failed';
            }
        } catch (err) {
            errorMsg.style.display = 'block';
            errorMsg.textContent = 'A network error occurred';
        }
    });
</script>

</body>
</html>
