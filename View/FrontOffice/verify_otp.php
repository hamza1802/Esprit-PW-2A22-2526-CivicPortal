<?php
/**
 * View/FrontOffice/verify_otp.php
 * OTP Verification page for 2FA.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$errors = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Verify Identity</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="../assets/images/logo.png" alt="CivicPortal" style="height: 60px; width: auto;" onerror="this.style.display='none'">
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-shield-lock-fill" style="font-size: 3rem; color: #1D2A44;"></i>
                <h1>Double Verification</h1>
                <p>Enter the 6-digit code sent to your email.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['mail_error'])): ?>
                <div class="auth-errors" style="background: rgba(255, 0, 0, 0.1); border-color: red; color: red; text-align: left;">
                    <p><i class="bi bi-exclamation-triangle-fill"></i> <strong>Debug Mail Error:</strong></p>
                    <pre style="font-size: 0.7rem; white-space: pre-wrap; margin-top: 0.5rem; max-height: 200px; overflow-y: auto;"><?= htmlspecialchars($_SESSION['mail_error']) ?></pre>
                    <?php unset($_SESSION['mail_error']); ?>
                </div>
            <?php endif; ?>

            <form id="main-form" class="auth-form">
                <input type="hidden" name="action" value="verify_otp">
                
                <div class="form-group">
                    <label for="otp_code">OTP Code</label>
                    <input type="text" name="otp_code" id="otp_code" maxlength="6" placeholder="000000" required autofocus 
                           style="text-align: center; font-size: 2rem; letter-spacing: 0.5rem; font-weight: 900;">
                </div>

                <button type="submit" class="btn btn-primary btn-block">VERIFY CODE</button>
            </form>

            <div class="auth-footer">
                <p>Didn't receive the code? <a href="login.php">Back to login</a></p>
            </div>
        </div>
    </div>

    <!-- Replace script inclusion if missing -->
    
<script>
document.getElementById('main-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await fetch('../../Verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify_otp', data: data })
        });
        const result = await res.json();
        
        if (result.success && !result.data?.errors) {
            window.location.href = 'index.php';
        } else {
            alert(result.data?.errors ? Object.values(result.data.errors)[0] : (result.error || 'Action failed'));
        }
    } catch (err) {
        alert('Network error occurred.');
    }
});
</script>
</body>
</html>
