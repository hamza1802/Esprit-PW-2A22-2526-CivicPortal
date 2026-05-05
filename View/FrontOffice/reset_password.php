<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/CivicPortal/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - Reset Password</title>
    <link rel="stylesheet" href="View/assets/css/style.css">
</head>
<body>

<div class="login-container">
    <div class="form-card" style="max-width: 450px; padding: 3rem;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <img src="View/assets/images/logo.png" alt="Logo" style="height: 60px; margin-bottom: 1.5rem;">
            <h1 style="font-size: 2rem; font-weight: 900; color: #1D2A44; letter-spacing: -1px; text-transform: uppercase;">New Password</h1>
            <p style="color: #64748b; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Secure your account with a new password</p>
        </div>

        <?php if (empty($_GET['token'])): ?>
            <div style="padding: 1.5rem; background: #fee2e2; color: #b91c1c; border-radius: 12px; font-weight: 700; text-align: center; font-size: 0.85rem; text-transform: uppercase;">
                INVALID OR MISSING RESET TOKEN.
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php?page=front_login" class="btn btn-primary">Go to Login</a>
            </div>
        <?php else: ?>
            <form action="index.php" method="POST" id="reset-form">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="password" style="font-size: 0.8rem; font-weight: 800; color: #1D2A44; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">New Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required style="padding: 1.2rem; font-size: 1rem; border-radius: 12px; background: #f0f4f8; border: none; width: 100%; font-weight: 600;">
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
                    </span>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="confirm_password" style="font-size: 0.8rem; font-weight: 800; color: #1D2A44; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required style="padding: 1.2rem; font-size: 1rem; border-radius: 12px; background: #f0f4f8; border: none; width: 100%; font-weight: 600;">
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= isset($errors['confirm_password']) ? htmlspecialchars($errors['confirm_password']) : '' ?>
                    </span>
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border-radius: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
