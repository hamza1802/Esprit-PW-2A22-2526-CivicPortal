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
            
            <!-- Professional Security Verification (Canvas API) -->
            <div class="form-group" style="display: flex; flex-direction: column; align-items: center; margin: 1.5rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                <p style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 1rem; color: #1D2A44; letter-spacing: 1px;">Security Verification</p>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; width: 100%;">
                    <canvas id="captcha-canvas" width="160" height="50" style="border-radius: 8px; flex: 1; border: 1px solid #dee2e6; background: #fff;"></canvas>
                    <button type="button" id="refresh-captcha" style="background: none; border: none; cursor: pointer; color: #1D2A44; font-size: 1.2rem;" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <input type="text" name="captcha_code" id="captcha_code" placeholder="ENTER THE CODE ABOVE" style="text-align: center; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;">
                <span class="inline-error" id="error-captcha" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= isset($errors['captcha']) ? htmlspecialchars($errors['captcha']) : '' ?>
                </span>
            </div>

            <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem;">Sign In</button>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="index.php?page=front_forgot_password" style="font-size: 0.85rem; color: #1D2A44; font-weight: 700; text-decoration: none; text-transform: uppercase;">Forgot Password?</a>
            </div>
        </form>

        <div style="margin-top: 1.5rem; font-size: 0.75rem; text-align: center; color: #666; font-weight: 600; line-height: 1.4;">
            This site is protected by reCAPTCHA and the Google <br>
            <a href="https://policies.google.com/privacy" style="color: var(--primary-navy);">Privacy Policy</a> and
            <a href="https://policies.google.com/terms" style="color: var(--primary-navy);">Terms of Service</a> apply.
        </div>

        <style>
            /* Resetting visibility for v2 checkbox */
            .grecaptcha-badge { visibility: visible !important; }
        </style>

        <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
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
            
            const captcha = document.getElementById('captcha_code').value.trim();
            if (captcha === '') {
                clientErrors.captcha = 'SECURITY CODE IS REQUIRED';
            }

            if (Object.keys(clientErrors).length > 0) {
                for (const field in clientErrors) {
                    document.getElementById(`error-${field}`).textContent = clientErrors[field];
                }
                card.classList.remove('shake');
                void card.offsetWidth;
                card.classList.add('shake');
                return;
            }

            form.submit();
        });

        // Professional Canvas CAPTCHA Logic
        function initCaptcha() {
            const canvas = document.getElementById('captcha-canvas');
            const ctx = canvas.getContext('2d');
            const refreshBtn = document.getElementById('refresh-captcha');

            function generate() {
                fetch('captcha_api.php?get_code=1')
                    .then(res => res.json())
                    .then(data => {
                        const code = data.code;
                        draw(code);
                    });
            }

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
            generate();
        }
        initCaptcha();
        </script>

        <div style="text-align: center; margin-top: 2rem; font-weight: 700; text-transform: uppercase; font-size: 0.9rem;">
            No account? <a href="index.php?page=front_register" style="color: var(--primary-navy); margin-left: 0.5rem;">Register</a>
        </div>
    </div>
</section>

</body>
</html>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
