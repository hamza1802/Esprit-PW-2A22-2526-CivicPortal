<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<section class="page-container">
    <div class="hero-section" style="border-bottom: 2px solid #1D2A44; margin-bottom: 3rem; padding-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size: 3.5rem;">User Management</h1>
            <p style="font-size: 1.2rem; margin-top: 1rem; color: #1D2A44;">Administration panel for managing portal accounts.</p>
        </div>
        <button id="show-register-form" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-size: 1.1rem; box-shadow: 8px 8px 0px #1D2A44;">+ NEW REGISTRATION</button>
    </div>

    <!-- Static notifications removed to prioritize toasts and inline errors -->


    <div class="table-responsive" style="margin-bottom: 4rem; background: #fff; box-shadow: 10px 10px 0px #1D2A44;">
        <table class="data-table" id="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr id="user-row-<?= htmlspecialchars($user->getId()) ?>">
                        <td>#<?= htmlspecialchars($user->getId()) ?></td>
                        <td><strong><?= htmlspecialchars($user->getName()) ?></strong></td>
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td><span class="status-badge" style="background: #BCC1C1; color: #1D2A44;"><?= htmlspecialchars($user->getRole()) ?></span></td>
                        <td style="font-size: 0.9rem;"><?= htmlspecialchars($user->getCreatedAt() ?? '-') ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a class="btn btn-small edit-user-btn" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;" href="#" data-id="<?= htmlspecialchars($user->getId()) ?>">Edit</a>
                                <button type="button" class="btn btn-danger btn-small delete-user-trigger" data-id="<?= htmlspecialchars($user->getId()) ?>" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Registration/Edit Section (Replaces Modal) -->
    <div id="user-registration-section" class="form-card" style="display: <?= !empty($editingUser) ? 'block' : 'none' ?>; margin-top: 4rem; box-shadow: 20px 20px 0px #1D2A44; border: 4px solid #1D2A44;">
        <h2 style="margin-bottom: 2.5rem; border-bottom: 4px solid #1D2A44; padding-bottom: 1rem; text-transform: uppercase;">
            <?= !empty($editingUser) ? 'Update Profile' : 'New Registration' ?>
        </h2>

        <form id="user-form" method="post" action="index.php?page=back_users_list" novalidate>
            <input type="hidden" name="action" id="form-action" value="<?= !empty($editingUser) ? 'update_user' : 'create_user' ?>">
            <?php if (!empty($editingUser)): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($editingUser->getId()) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" value="<?= htmlspecialchars($old['name'] ?? (!empty($editingUser) ? $editingUser->getDisplayName() : '')) ?>" placeholder="EX: JOHN DOE">
                <span class="inline-error" id="error-name" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                    <?= $errors['name'] ?? '' ?>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label for="email">E-mail Address</label>
                    <input id="email" name="email" type="text" value="<?= htmlspecialchars($old['email'] ?? (!empty($editingUser) ? $editingUser->getEmail() : '')) ?>" placeholder="email@example.com">
                    <span class="inline-error" id="error-email" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= $errors['email'] ?? '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="role">Assigned Role</label>
                    <?php $roleValue = $old['role'] ?? (!empty($editingUser) ? $editingUser->getRole() : 'citizen'); ?>
                    <select id="role" name="role">
                        <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                        <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                        <?php if ($roleValue === 'admin'): ?>
                            <option value="admin" selected>Administrator</option>
                        <?php endif; ?>
                    </select>
                    <span class="inline-error" id="error-role" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= $errors['role'] ?? '' ?>
                    </span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label for="password">Password <?= !empty($editingUser) ? '<small>(New only)</small>' : '' ?></label>
                    <input id="password" name="password" type="password">
                    <span class="inline-error" id="error-password" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= $errors['password'] ?? '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Identity</label>
                    <input id="confirm_password" name="confirm_password" type="password">
                    <span class="inline-error" id="error-confirm_password" style="color: #ff4d4d; font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;">
                        <?= $errors['confirm_password'] ?? '' ?>
                    </span>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button id="submit-btn" class="btn btn-primary" type="submit" style="width: 100%; padding: 1.5rem; font-size: 1.2rem;">
                    <?= !empty($editingUser) ? 'SAVE CHANGES' : 'COMPLETE REGISTRATION' ?>
                </button>
                <button type="button" id="cancel-btn" class="btn" style="width: 100%; margin-top: 1rem; border-color: transparent;">CANCEL AND CLOSE</button>
            </div>
        </form>
    </div>
</section>

<!-- Custom Editorial Confirmation Modal -->
<div id="delete-confirm-overlay" style="display:none; position:fixed; inset:0; background:rgba(29, 42, 68, 0.85); backdrop-filter:blur(5px); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:#fff; border:4px solid #1D2A44; padding:3rem; max-width:400px; width:90%; box-shadow:20px 20px 0px rgba(29,42,68,0.2); animation:slideInConfirm 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <h3 style="margin-bottom:1.5rem; text-transform:uppercase; font-size:1.5rem; color:#1D2A44; letter-spacing:-1px;">Confirm Deletion</h3>
        <p style="margin-bottom:2.5rem; font-weight:600; font-size:1rem; opacity:0.8;">Are you sure you want to permanently delete this user? This action cannot be undone.</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <button id="cancel-delete-btn" class="btn" style="border-width:2px;">CANCEL</button>
            <button id="confirm-delete-btn" class="btn btn-danger" style="border-width:2px;">DELETE</button>
        </div>
    </div>
</div>

<style>
@keyframes slideInConfirm {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const regSection = document.getElementById('user-registration-section');
    const showBtn = document.getElementById('show-register-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const userForm = document.getElementById('user-form');
    const tableBody = document.querySelector('#users-table tbody');
    const usersTable = document.getElementById('users-table');

    // Handle auto-scroll for edit mode
    if (window.location.search.includes('edit=')) {
        regSection.style.display = 'block';
        regSection.scrollIntoView({ behavior: 'auto', block: 'start' });
    }

    // Show and scroll down to form
    showBtn.addEventListener('click', () => {
        userForm.reset();
        document.getElementById('form-action').value = 'create_user';
        document.getElementById('submit-btn').textContent = 'COMPLETE REGISTRATION';
        document.querySelector('#user-registration-section h2').textContent = 'New Registration';
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
        
        regSection.style.display = 'block';
        regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Handle AJAX Edit Click
    document.addEventListener('click', e => {
        if (e.target.classList.contains('edit-user-btn')) {
            e.preventDefault();
            const userId = e.target.dataset.id;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_user&user_id=${userId}`
            })
                .then(res => res.json())
                .then(user => {
                    // Populate form
                    document.getElementById('form-action').value = 'update_user';
                    document.getElementById('submit-btn').textContent = 'SAVE CHANGES';
                    document.querySelector('#user-registration-section h2').textContent = 'Update Profile';
                    
                    // Add hidden ID if not already there
                    let idInput = userForm.querySelector('input[name="user_id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'user_id';
                        userForm.appendChild(idInput);
                    }
                    idInput.value = user.id;
                    
                    document.getElementById('name').value = user.name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('role').value = user.role;
                    document.getElementById('password').value = '';
                    document.getElementById('confirm_password').value = '';
                    
                    // Show and scroll
                    regSection.style.display = 'block';
                    regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
        }
    });

    // Close and scroll back to top/table
    cancelBtn.addEventListener('click', () => {
        regSection.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (window.location.search.includes('edit=')) {
            window.location.href = 'index.php?page=back_users_list';
        }
    });

    // Handle Custom Deletion Workflow
    let userIdToDelete = null;
    const confirmOverlay = document.getElementById('delete-confirm-overlay');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

    document.addEventListener('click', e => {
        if (e.target.classList.contains('delete-user-trigger')) {
            userIdToDelete = e.target.dataset.id;
            confirmOverlay.style.display = 'flex';
        }
    });

    cancelDeleteBtn.addEventListener('click', () => {
        confirmOverlay.style.display = 'none';
        userIdToDelete = null;
    });

    confirmDeleteBtn.addEventListener('click', () => {
        if (!userIdToDelete) return;
        
        fetch('index.php?page=back_users_list', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete_user&user_id=${userIdToDelete}`
        })
        .then(res => res.json())
        .then(data => {
            confirmOverlay.style.display = 'none';
            if (data.success) {
                const row = document.getElementById(`user-row-${userIdToDelete}`);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    row.style.transition = 'all 0.4s ease';
                    setTimeout(() => row.remove(), 400);
                }
                showToast(data.success);
            } else {
                showToast(data.errors ? data.errors[0] : 'Delete failed', true);
            }
            userIdToDelete = null;
        });
    });

    // Handle AJAX Submission
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear errors
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
        
        const formData = new FormData(userForm);
        const action = document.getElementById('form-action').value;
        
        fetch('index.php?page=back_users_list', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                for (const field in data.errors) {
                    const errorEl = document.getElementById(`error-${field}`);
                    if (errorEl) {
                        errorEl.textContent = data.errors[field];
                    }
                }
            } else if (data.success) {
                if (action === 'create_user') {
                    // Add to table
                    const user = data.user;
                    const newRow = document.createElement('tr');
                    newRow.id = `user-row-${user.id}`;
                    newRow.style.animation = 'highlight-new 2s ease';
                    newRow.innerHTML = `
                        <td>#${user.id}</td>
                        <td><strong>${user.name}</strong></td>
                        <td>${user.email}</td>
                        <td><span class="status-badge" style="background: #BCC1C1; color: #1D2A44;">${user.role}</span></td>
                        <td style="font-size: 0.9rem;">${user.created_at || 'Just now'}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a class="btn btn-small edit-user-btn" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;" href="#" data-id="${user.id}">Edit</a>
                                <button type="button" class="btn btn-danger btn-small delete-user-trigger" data-id="${user.id}" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;">Delete</button>
                            </div>
                        </td>
                    `;
                    tableBody.prepend(newRow);
                } else {
                    // Update existing row
                    const userId = formData.get('user_id');
                    const row = document.querySelector(`tr:has(input[value="${userId}"])`) || document.getElementById(`user-row-${userId}`);
                    // For simplicity, we can refresh the page or just update the row if we find it.
                    // Since it's an update, a quick reload or row update is good.
                    if (row) {
                        row.querySelector('td:nth-child(2) strong').textContent = formData.get('name');
                        row.querySelector('td:nth-child(3)').textContent = formData.get('email');
                        row.querySelector('td:nth-child(4) .status-badge').textContent = formData.get('role');
                        row.style.animation = 'highlight-new 2s ease';
                    } else {
                        // fallback: reload
                        window.location.href = 'index.php?page=back_users_list';
                        return;
                    }
                }
                
                // Common success flow
                regSection.style.display = 'none';
                usersTable.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showToast(data.success);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showToast('An unexpected error occurred.', true);
        });
    });

    function showToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.className = 'custom-backoffice-toast';
        toast.textContent = message.toUpperCase();
        if (isError) toast.style.backgroundColor = '#A4161A';
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('visible'), 100);
        setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});

// Animation for new rows
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight-new {
        0% { background-color: #3A86FF; color: white; }
        100% { background-color: transparent; }
    }
    .custom-backoffice-toast {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: #1D2A44;
        color: white;
        padding: 1.5rem;
        text-align: center;
        font-weight: 900;
        letter-spacing: 1px;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 9999;
    }
    .custom-backoffice-toast.visible {
        transform: translateY(0);
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
