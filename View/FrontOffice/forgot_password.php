<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/a1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - Forgot Password</title>
    <link rel="stylesheet" href="View/assets/css/style.css">
</head>
<body>

<div class="login-container">
    <div class="form-card" style="max-width: 450px; padding: 3rem;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <img src="View/assets/images/logo.png" alt="Logo" style="height: 60px; margin-bottom: 1.5rem;">
            <h1 style="font-size: 2rem; font-weight: 900; color: #1D2A44; letter-spacing: -1px; text-transform: uppercase;">Reset Access</h1>
            <p style="color: #64748b; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Enter your email to receive a reset link</p>
        </div>

        <form action="index.php" method="POST" id="forgot-form">
            <input type="hidden" name="action" value="request_reset">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email" style="font-size: 0.8rem; font-weight: 800; color: #1D2A44; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Email Address</label>
                <input type="email" id="email" name="email" placeholder="YOUR@EMAIL.COM" required style="padding: 1.2rem; font-size: 1rem; border-radius: 12px; background: #f0f4f8; border: none; width: 100%; font-weight: 600;">
                <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                </span>
            </div>

            <button class="btn btn-primary" type="submit" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border-radius: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Send Reset Link</button>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php?page=front_login" style="font-size: 0.85rem; color: #64748b; font-weight: 700; text-decoration: none; text-transform: uppercase;">Back to Login</a>
            </div>
        </form>

        <?php if ($success): ?>
            <div style="margin-top: 2rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 12px; font-weight: 700; text-align: center; font-size: 0.85rem; text-transform: uppercase;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
