<?php
require_once __DIR__ . '/../../includes/header.php';

// Initial data for rendering
$user = $currentUser;
$profile = $currentProfile;
$initials = '';
if ($user) {
    $names = explode(' ', $user->getDisplayName());
    foreach ($names as $n) {
        $initials .= strtoupper(substr($n, 0, 1));
    }
}
$initials = substr($initials, 0, 2);
?>


<style>
    /* Profile Premium Styles */
    .profile-hero {
        position: relative;
        height: 300px;
        background: url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?q=80&w=2144&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        border-bottom: 4px solid var(--primary-navy);
        margin-top: -2rem; /* Pull up to touch header if needed */
    }

    .profile-avatar-container {
        position: absolute;
        bottom: -50px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 120px;
        background: var(--primary-navy);
        border: 4px solid var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-lg);
        z-index: 10;
    }

    .profile-avatar-initials {
        font-size: 3rem;
        font-weight: 900;
        color: var(--white);
        letter-spacing: -2px;
    }

    .profile-info-header {
        text-align: center;
        margin-top: 60px;
        padding-bottom: 2rem;
        border-bottom: 2px solid #ebebeb;
    }

    .profile-name {
        font-size: 2.5rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        letter-spacing: -1px;
    }

    .profile-role-badge {
        display: inline-block;
        background: var(--primary-navy);
        color: var(--white);
        padding: 0.4rem 1.5rem;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        border-radius: 20px;
        letter-spacing: 1px;
    }

    .profile-content {
        background: #f4f1e9; /* Light beige from screenshot */
        padding: 3rem 5%;
        min-height: 400px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        max-width: 1000px;
        margin: 0 auto;
    }

    .info-card {
        background: var(--white);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid #d5d9e0;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: var(--transition-fast);
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .info-icon {
        width: 50px;
        height: 50px;
        background: #f0f4f8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--primary-navy);
    }

    .info-details label {
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 0.2rem;
    }

    .info-details span {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary-navy);
    }

    .edit-profile-btn-container {
        text-align: center;
        margin-top: 3rem;
    }

    .btn-dark {
        background: var(--primary-navy);
        color: var(--white);
        padding: 1rem 3rem;
        font-weight: 900;
        text-transform: uppercase;
        border-radius: 30px;
        border: none;
        cursor: pointer;
        transition: var(--transition-fast);
        box-shadow: 0 4px 15px rgba(29, 42, 68, 0.3);
    }

    .btn-dark:hover {
        transform: scale(1.05);
        background: #2a3b5a;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(29, 42, 68, 0.8);
        backdrop-filter: blur(10px);
        z-index: 2000;
        display: none; /* Controlled by JS */
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modal-content {
        background: var(--white);
        width: 100%;
        max-width: 800px;
        border-radius: 24px;
        padding: 3rem;
        position: relative;
        box-shadow: var(--shadow-lg);
        animation: modalFadeIn 0.4s var(--ease-smooth);
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }

    .modal-header h2 {
        font-size: 2rem;
        margin: 0;
        border: none;
        padding: 0;
    }

    .btn-cancel {
        background: var(--white);
        border: 1px solid #ddd;
        padding: 0.6rem 1.5rem;
        border-radius: 20px;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.8rem;
        cursor: pointer;
    }

    .edit-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .form-group label {
        display: block;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 0.8rem;
        margin-bottom: 0.8rem;
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 12px;
        font-family: inherit;
        font-weight: 600;
        font-size: 1rem;
    }

    .bio-wrapper {
        position: relative;
        grid-column: span 2;
    }

    .bio-icon {
        position: absolute;
        right: 15px;
        bottom: 15px;
        color: #aaa;
        font-size: 1.2rem;
    }

    .two-fa-card {
        grid-column: span 2;
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .two-fa-info h4 {
        margin: 0;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .two-fa-info p {
        margin: 0;
        font-size: 0.8rem;
        opacity: 0.6;
    }

    /* Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: var(--primary-navy);
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .profile-photo-group {
        grid-column: span 2;
        margin-top: 1rem;
    }

    .save-changes-btn {
        grid-column: span 2;
        width: 100%;
        margin-top: 2rem;
        padding: 1.5rem;
        background: var(--primary-navy);
        color: var(--white);
        border: none;
        border-radius: 16px;
        font-size: 1.2rem;
        font-weight: 900;
        text-transform: uppercase;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(29, 42, 68, 0.2);
    }
    
    .face-enroll-section {
        max-width: 1000px;
        margin: 4rem auto;
        padding: 0 5%;
    }
</style>

<div class="profile-hero">
    <div class="profile-avatar-container">
        <div class="profile-avatar-initials"><?= $initials ?></div>
    </div>
</div>

<div class="profile-info-header">
    <h1 class="profile-name"><?= htmlspecialchars($user->getDisplayName()) ?></h1>
    <div class="profile-role-badge"><?= htmlspecialchars($user->getRole()) ?></div>
</div>

<div class="profile-content">
    <div class="info-grid">
        <div class="info-card">
            <div class="info-icon"><i class="bi bi-envelope"></i></div>
            <div class="info-details">
                <label>Email</label>
                <span><?= htmlspecialchars($user->getEmail()) ?></span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon"><i class="bi bi-telephone"></i></div>
            <div class="info-details">
                <label>Phone</label>
                <span><?= htmlspecialchars($profile->getPhoneNumber() ?: '—') ?></span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="info-details">
                <label>Date of Birth</label>
                <span><?= htmlspecialchars($profile->getDateOfBirth() ?: '—') ?></span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon"><i class="bi bi-person-badge"></i></div>
            <div class="info-details">
                <label>Role</label>
                <span><?= htmlspecialchars(ucfirst($user->getRole())) ?></span>
            </div>
        </div>
        <div class="info-card" style="grid-column: span 1;">
            <div class="info-icon"><i class="bi bi-shield-check"></i></div>
            <div class="info-details">
                <label>Double Verification</label>
                <span style="color: <?= $user->isTwoFaEnabled() ? 'var(--success)' : '#888' ?>">
                    <?= $user->isTwoFaEnabled() ? 'ENABLED' : 'DISABLED' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="edit-profile-btn-container">
        <button class="btn-dark" onclick="openEditModal()">EDIT PROFILE</button>
    </div>
</div>

<!-- Face ID Enrollment Section -->
<div class="face-enroll-section">
    <div class="face-enroll-card">
        <h2>SECURITY: FACE ID ENROLLMENT</h2>
        <p>Register your face to enable quick login. Please ensure you are in a well-lit area.</p>
        
        <div id="enroll-status" class="face-id-status status-scanning">Loading Face ID...</div>
        
        <div class="webcam-container" style="max-width: 600px; margin: 1.5rem auto; border: 4px solid var(--primary-navy); border-radius: 20px; overflow: hidden; background: #000;">
            <video id="enroll-video" width="100%" height="auto" autoplay muted></video>
            <canvas id="enroll-canvas"></canvas>
        </div>

        <div id="enroll-feedback" class="face-id-feedback"></div>
        
        <button id="enroll-save" class="btn btn-primary" disabled style="width: 100%; padding: 1.5rem; font-size: 1.1rem;">
            <i class="bi bi-person-bounding-box" style="margin-right: 10px;"></i>
            SAVE MY FACE DATA
        </button>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>EDIT PROFILE</h2>
            <button class="btn-cancel" onclick="closeEditModal()">CANCEL</button>
        </div>

        <form id="profileUpdateForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="edit-form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user->getDisplayName()) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" value="<?= htmlspecialchars($profile->getPhoneNumber()) ?>" placeholder="+1 234 567 890">
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="text" name="date_of_birth" value="<?= htmlspecialchars($profile->getDateOfBirth()) ?>" placeholder="YYYY-MM-DD">
                </div>
                
                <div class="bio-wrapper">
                    <label>Biography</label>
                    <textarea name="bio" rows="4" placeholder="Write something about yourself..."><?= htmlspecialchars($profile->getBio()) ?></textarea>
                    <i class="bi bi-pencil-fill bio-icon"></i>
                </div>

                <div class="two-fa-card">
                    <div class="two-fa-info">
                        <h4>DOUBLE VERIFICATION (2FA)</h4>
                        <p>Protect your account with an email code</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="two_fa_enabled" id="two_fa_toggle" <?= $user->isTwoFaEnabled() ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="profile-photo-group">
                    <label>Profile Photo</label>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="file" name="avatar" id="avatarInput" style="border: none; padding: 0;">
                    </div>
                </div>

                <button type="submit" class="save-changes-btn">SAVE CHANGES</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.getElementById('profileUpdateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.save-changes-btn');
    const originalText = btn.innerText;
    btn.innerText = 'SAVING...';
    btn.disabled = true;

    const formData = new FormData(this);
    
    // Explicitly handle 2FA checkbox for API consistency
    const twoFaCheckbox = document.getElementById('two_fa_toggle');
    formData.set('two_fa_enabled', twoFaCheckbox.checked ? '1' : '0');

    try {
        const response = await fetch('Verification.php', {
            method: 'POST',
            body: formData // Use FormData for file upload support
        });
        
        const result = await response.json();
        
        if (result.success && result.data?.success) {
            // Show toast or alert
            alert('Profile updated successfully!');
            location.reload(); // Refresh to show changes
        } else {
            const errorMsg = result.error || (result.data?.errors ? Object.values(result.data.errors).join('\n') : 'Update failed');
            alert(errorMsg);
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('An error occurred during update.');
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
});

// Close modal when clicking outside content
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
