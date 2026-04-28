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

        <form id="register-form" method="post" action="index.php?page=front_register" class="form-grid" novalidate>
            <input type="hidden" name="action" value="register">

            <div class="form-group full-width">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" placeholder="YOUR NAME " value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                <span class="inline-error" id="error-name" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['name']) ? htmlspecialchars($errors['name']) : '' ?>
                </span>
            </div>

            <div class="form-group full-width">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" placeholder="YOUR@EMAIL.COM" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                <span class="inline-error" id="error-email" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                </span>
            </div>

            <div class="form-group full-width">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <?php $roleValue = $old['role'] ?? 'citizen'; ?>
                    <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                    <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                </select>
                <span class="inline-error" id="error-role" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['role']) ? htmlspecialchars($errors['role']) : '' ?>
                </span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••">
                <span class="inline-error" id="error-password" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
                </span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="••••••••">
                <span class="inline-error" id="error-confirm_password" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['confirm_password']) ? htmlspecialchars($errors['confirm_password']) : '' ?>
                </span>
            </div>

            <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem; grid-column: span 2;">Get Started</button>

            <div style="text-align: center; margin-top: 2rem; font-weight: 700; text-transform: uppercase; font-size: 0.9rem; grid-column: span 2;">
                Already have an account? <a href="index.php?page=front_login" style="color: var(--primary-navy); margin-left: 0.5rem;">Sign in</a>
            </div>
        </form>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const card = this.closest('.form-card');
    
    // Clear previous errors
    document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
    
    let clientErrors = {};
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (name === '') {
        clientErrors.name = 'FULL NAME IS REQUIRED';
    } else if (name.length < 3) {
        clientErrors.name = 'NAME MUST BE AT LEAST 3 CHARACTERS';
    } else if (/\d/.test(name)) {
        clientErrors.name = 'NAME CANNOT CONTAIN NUMBERS';
    }
    
    if (email === '') {
        clientErrors.email = 'EMAIL IS REQUIRED';
    } else if (!emailRegex.test(email)) {
        clientErrors.email = 'INVALID EMAIL FORMAT';
    }
    
    if (pass.length < 8) {
        clientErrors.password = 'PASSWORD MUST BE AT LEAST 8 CHARACTERS';
    }
    
    if (pass !== confirm) {
        clientErrors.confirm_password = 'PASSWORDS DO NOT MATCH';
    }
    
    if (Object.keys(clientErrors).length > 0) {
        e.preventDefault();
        for (const field in clientErrors) {
            const errEl = document.getElementById(`error-${field}`);
            if (errEl) errEl.textContent = clientErrors[field];
        }
        card.classList.remove('shake');
        void card.offsetWidth;
        card.classList.add('shake');
    }
});
</script>and Agent roles.
});
</script>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
