<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - Register</title>
    <style>
        :root {
            --primary-navy: #1D2A44;
            --bg-neutral: #F0EADC;
            --white: #ffffff;
            --border-main: 3px solid #1D2A44;
            --shadow-editorial: 15px 15px 0px 0px rgba(29, 42, 68, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
            background-color: var(--bg-neutral);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--primary-navy);
        }

        .top-logo {
            padding: 2.5rem 4rem;
        }

        .top-logo img {
            height: 100px;
            width: auto;
        }

        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: var(--white);
            border: var(--border-main);
            box-shadow: var(--shadow-editorial);
            width: 100%;
            max-width: 440px;
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
            border-bottom: var(--border-main);
            padding-bottom: 1.5rem;
        }

        .login-header h1 {
            font-size: 2.22rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
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

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

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
            border: 2px solid var(--primary-navy);
            background: transparent;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary-navy);
            transition: all 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            background-color: #f8f6f0;
            box-shadow: 6px 6px 0px 0px var(--primary-navy);
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background-color: var(--primary-navy);
            color: var(--white);
            border: none;
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
            transform: translate(-4px, -4px);
            box-shadow: 6px 6px 0px 0px rgba(29, 42, 68, 0.3);
        }

        .message.error {
            color: #A4161A;
            background: #FFB3B3;
            padding: 1rem;
            border: 2px solid currentColor;
            margin-bottom: 1.5rem;
            grid-column: span 2;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .signup-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--bg-neutral);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            grid-column: span 2;
        }

        .signup-link a {
            color: var(--primary-navy);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            margin-left: 0.5rem;
        }

        @media (max-width: 500px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full-width { grid-column: auto; }
            .submit-btn { grid-column: auto; }
            .signup-link { grid-column: auto; }
            .login-container { padding: 2rem; }
        }
    </style>
</head>
<body>

<div class="top-logo">
    <img src="View/assets/images/logo.png" alt="CivicPortal">
</div>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h1>Join the portal</h1>
            <p>Enter your details</p>
        </div>

        <form method="post" action="index.php?page=front_register" class="form-grid" novalidate>
            <input type="hidden" name="action" value="register">

            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <ul style="list-style:none">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-group full-width">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" placeholder="YOUR NAME " value="<?= htmlspecialchars($old['name'] ?? '') ?>">
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
                    <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Administrator</option>
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
                Already have an account? <a href="index.php?page=front_login">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = document.getElementById('name');
    const roleSelect = document.getElementById('role');

    // Real-time validation
    function validateAdminFormat() {
        const role = roleSelect.value;
        const name = nameInput.value.trim();
        
        if (role === 'admin' && name && !name.startsWith('admin-')) {
            nameInput.style.borderColor = '#A4161A';
            nameInput.style.backgroundColor = '#FFB3B3';
            return false;
        } else {
            nameInput.style.borderColor = '';
            nameInput.style.backgroundColor = '';
            return true;
        }
    }

    nameInput.addEventListener('input', validateAdminFormat);
    roleSelect.addEventListener('change', validateAdminFormat);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const role = roleSelect.value;
        const name = nameInput.value.trim();
        
        if (role === 'admin' && !name.startsWith('admin-')) {
            e.preventDefault();
            alert('⚠️cant register ');
            nameInput.focus();
            return false;
        }
    });
});
</script>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
