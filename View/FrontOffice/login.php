<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php?page=front_home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - Login</title>
    <link rel="stylesheet" href="View/assets/css/style.css">
</head>
<body>

<section class="page-container" style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="form-card" style="width: 100%; max-width: 500px; padding: 4rem 3rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="View/assets/images/logo.png" alt="CivicPortal" style="height: 60px;">
        </div>
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.5rem; text-transform: uppercase; margin-bottom: 0.5rem;">Welcome back</h1>
            <p style="font-weight: 700; text-transform: uppercase; opacity: 0.7;">Enter your details</p>
        </div>

        <script>
        // Trap the user on this page if they try to go back after logout
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
        </script>

        <?php if (!empty($success)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toast = document.createElement('div');
                    toast.className = 'custom-backoffice-toast visible';
                    toast.textContent = <?= json_encode(strtoupper($success)) ?>;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.classList.remove('visible');
                        setTimeout(() => toast.remove(), 500);
                    }, 4000);
                });
            </script>
        <?php endif; ?>



        <form id="login-form" method="post" action="index.php?page=front_login" novalidate>
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" placeholder="YOUR@EMAIL.COM" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                <span class="inline-error" id="error-email" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                </span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••">
                <span class="inline-error" id="error-password" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
                </span>
            </div>

            <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem;">Sign In</button>
        </form>

        <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const pass = document.getElementById('password').value;
            const card = this.closest('.form-card');
            
            // Clear previous errors
            document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
            
            let clientErrors = {};
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email === '') {
                clientErrors.email = 'EMAIL IS REQUIRED';
            } else if (!emailRegex.test(email)) {
                clientErrors.email = 'INVALID EMAIL FORMAT';
            }
            
            if (pass === '') {
                clientErrors.password = 'PASSWORD IS REQUIRED';
            }
            
            if (Object.keys(clientErrors).length > 0) {
                e.preventDefault();
                for (const field in clientErrors) {
                    document.getElementById(`error-${field}`).textContent = clientErrors[field];
                }
                card.classList.remove('shake');
                void card.offsetWidth;
                card.classList.add('shake');
            }
        });
        </script>

        <div style="text-align: center; margin-top: 2rem; font-weight: 700; text-transform: uppercase; font-size: 0.9rem;">
            No account? <a href="index.php?page=front_register" style="color: var(--primary-navy); margin-left: 0.5rem;">Register</a>
        </div>
    </div>
</section>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
