<?php
function convertToFetch($filePath, $actionName, $redirectUrl) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }
    $content = file_get_contents($filePath);
    
    // Remove action="index.php..." and method="POST"
    $content = preg_replace('/action="[^"]+"/i', '', $content);
    $content = preg_replace('/method="post"/i', '', $content);
    
    // Add an ID to the form if it doesn't have one
    if (strpos($content, 'id="main-form"') === false) {
        $content = str_replace('<form ', '<form id="main-form" ', $content);
    }
    
    $js = "
<script>
document.getElementById('main-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await fetch('../../Verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: '$actionName', data: data })
        });
        const result = await res.json();
        
        if (result.success && !result.data?.errors) {
            window.location.href = '$redirectUrl';
        } else {
            alert(result.data?.errors ? Object.values(result.data.errors)[0] : (result.error || 'Action failed'));
        }
    } catch (err) {
        alert('Network error occurred.');
    }
});
</script>
</body>
    ";
    
    // only replace once
    if (strpos($content, "document.getElementById('main-form').addEventListener") === false) {
        $content = str_replace('</body>', $js, $content);
        file_put_contents($filePath, $content);
        echo "Updated $filePath\n";
    } else {
        echo "Already updated $filePath\n";
    }
}

// Copy the files from a1 if they don't exist in integ
$filesToCopy = ['forgot_password.php', 'reset_password.php', 'verify_otp.php', 'register.php'];
foreach ($filesToCopy as $f) {
    $src = __DIR__ . '/../a1/View/FrontOffice/' . $f;
    $dst = __DIR__ . '/View/FrontOffice/' . $f;
    copy($src, $dst);
    echo "Copied $f\n";
}

convertToFetch(__DIR__ . "/View/FrontOffice/forgot_password.php", "request_reset", "forgot_password.php?success=1");
convertToFetch(__DIR__ . "/View/FrontOffice/reset_password.php", "reset_password", "login.php");
convertToFetch(__DIR__ . "/View/FrontOffice/verify_otp.php", "verify_otp", "index.php");
// For register, it needs CAPTCHA exactly like a1 login!

echo "Done.";
?>
