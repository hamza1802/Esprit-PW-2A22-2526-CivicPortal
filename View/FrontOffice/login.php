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
    <title>CivicPortal - Login</title>
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
            margin-top: -60px;
        }

        .login-container {
            background: var(--white);
            border: var(--border-main);
            box-shadow: var(--shadow-editorial);
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
            border-bottom: var(--border-main);
            padding-bottom: 1.5rem;
        }

        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 0.9;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

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
            border: 2px solid var(--primary-navy);
            background: transparent;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-navy);
            transition: all 0.2s ease;
        }

        .form-group input:focus {
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

        .message.error {
            color: #A4161A;
            background: #FFB3B3;
        }

        .message.success {
            color: #2D6A4F;
            background: #D8F3DC;
        }

        .signup-link {
            text-align: center;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--bg-neutral);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .signup-link a {
            color: var(--primary-navy);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            margin-left: 0.5rem;
        }

        @media (max-width: 500px) {
            .top-logo { padding: 2rem; }
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
            <h1>Welcome back</h1>
            <p>Enter your details</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul style="list-style:none">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?page=front_login">
            <input type="hidden" name="action" value="login">

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
            No account? <a href="index.php?page=front_register">Register</a>
        </div>
    </div>
</div>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
