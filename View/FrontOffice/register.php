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
    <link rel="stylesheet" href="View/assets/css/style.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        @media (max-width: 500px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full-width { grid-column: auto; }
        }
    </style>
</head>
<body>

<section class="page-container" style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="form-card" style="width: 100%; max-width: 600px; padding: 4rem 3rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="View/assets/images/logo.png" alt="CivicPortal" style="height: 60px;">
        </div>
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.5rem; text-transform: uppercase; margin-bottom: 0.5rem;">Join the portal</h1>
            <p style="font-weight: 700; text-transform: uppercase; opacity: 0.7;">Enter your details</p>
        </div>

        <form method="post" action="index.php?page=front_register" class="form-grid" novalidate>
            <input type="hidden" name="action" value="register">



            <div class="form-group full-width">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" placeholder="YOUR NAME " value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                <?php if (isset($errors['name'])): ?>
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" placeholder="YOUR@EMAIL.COM" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <?php $roleValue = $old['role'] ?? 'citizen'; ?>
                    <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                    <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($errors['role']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••">
                <?php if (isset($errors['password'])): ?>
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="••••••••">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="inline-error" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($errors['confirm_password']) ?></span>
                <?php endif; ?>
            </div>

            <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem; grid-column: span 2;">Get Started</button>

            <div style="text-align: center; margin-top: 2rem; font-weight: 700; text-transform: uppercase; font-size: 0.9rem; grid-column: span 2;">
                Already have an account? <a href="index.php?page=front_login" style="color: var(--primary-navy); margin-left: 0.5rem;">Sign in</a>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Current registration is limited to Citizen and Agent roles.
});
</script>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
