<?php
/**
 * users_list_partial.php
 * Content for the Users tab in BackOffice.
 * Simplified table: ID, Name, Email, Role, Created At.
 */
require_once __DIR__ . '/../../controller/UserController.php';
$usersData = UserController::getAllUsers();
$editingUserId = $_GET['edit'] ?? null;
$editingUser = $editingUserId ? UserController::getUserById((int)$editingUserId) : null;
?>
<section class="page-container" style="padding-top: 2rem;">
    <div class="hero-section" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; border-bottom: 2px solid var(--primary-navy); padding-bottom: 2rem;">
        <div>
            <h1 style="font-size: 3rem; color: var(--primary-navy); font-weight: 900; letter-spacing: -1.5px;">User Management</h1>
            <p style="font-size: 1.1rem; opacity: 0.8; margin-top: 10px; font-weight: 600;">Administration panel for managing portal accounts and staff credentials.</p>
        </div>
        <button id="show-register-form" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-weight: 900; border-radius: 4px; box-shadow: 8px 8px 0px var(--primary-navy);">+ NEW REGISTRATION</button>
    </div>

    <div class="table-responsive" style="margin-bottom: 4rem; background: white; border: 2px solid var(--primary-navy); box-shadow: 12px 12px 0px rgba(29, 42, 68, 0.1);">
        <table class="data-table" id="users-table">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usersData as $item): ?>
                    <?php 
                        $u = $item['user'];
                    ?>
                    <tr id="user-row-<?= htmlspecialchars($u->getId()) ?>" data-id="<?= htmlspecialchars($u->getId()) ?>" class="admin-row">
                        <td><span class="id-tag">#<?= htmlspecialchars($u->getId()) ?></span></td>
                        <td><div class="name-cell"><?= htmlspecialchars($u->getName()) ?></div></td>
                        <td><div class="email-cell"><?= htmlspecialchars($u->getEmail()) ?></div></td>
                        <td><span class="role-badge badge-<?= htmlspecialchars($u->getRole()) ?>"><?= strtoupper(htmlspecialchars($u->getRole())) ?></span></td>
                        <td><span class="date-cell"><?= htmlspecialchars($u->getCreatedAt() ?? '-') ?></span></td>
                        <td style="text-align: right;">
                            <div class="action-flex">
                                <button class="btn btn-small edit-user-btn" data-id="<?= htmlspecialchars($u->getId()) ?>" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase;">Edit</button>
                                <button class="btn btn-small btn-del delete-user-trigger" data-id="<?= htmlspecialchars($u->getId()) ?>" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase; background: #e74c3c; color: white; border: none;">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Registration/Edit Section -->
    <div id="user-registration-section" class="form-card" style="display: <?= !empty($editingUser) ? 'block' : 'none' ?>; margin-top: 4rem; border: 4px solid var(--primary-navy); background: white; padding: 3rem; box-shadow: 20px 20px 0px rgba(29, 42, 68, 0.05);">
        <h2 id="form-title" style="margin-bottom: 2.5rem; color: var(--primary-navy); font-weight: 900; text-transform: uppercase; border-bottom: 4px solid var(--primary-navy); padding-bottom: 1rem;">
            <?= !empty($editingUser) ? 'Update Staff Identity' : 'Portal Registration' ?>
        </h2>

        <form id="user-form" method="post" action="index.php" novalidate>
            <input type="hidden" name="action" id="form-action" value="<?= !empty($editingUser) ? 'update_user' : 'create_user' ?>">
            <input type="hidden" name="redirect_tab" value="users">

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Full Display Name</label>
                <input id="name" name="name" type="text" value="<?= htmlspecialchars($old['name'] ?? (!empty($editingUser) ? $editingUser->getDisplayName() : '')) ?>" placeholder="EX: JOHN SMITH" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border: 2px solid var(--primary-navy);">
                <span class="inline-error" id="error-name" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 900; text-transform: uppercase;"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">E-mail Address</label>
                    <input id="email" name="email" type="text" value="<?= htmlspecialchars($old['email'] ?? (!empty($editingUser) ? $editingUser->getEmail() : '')) ?>" placeholder="email@cityhall.gov" style="width: 100%; border: 2px solid var(--primary-navy);">
                    <span class="inline-error" id="error-email" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 900; text-transform: uppercase;"></span>
                </div>

                <div class="form-group">
                    <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Assigned Access Role</label>
                    <?php $roleValue = $old['role'] ?? (!empty($editingUser) ? $editingUser->getRole() : 'citizen'); ?>
                    <select id="role" name="role" style="width: 100%; border: 2px solid var(--primary-navy); height: 60px;">
                        <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen (Public)</option>
                        <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent (Staff)</option>
                        <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                    <span class="inline-error" id="error-role" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 900; text-transform: uppercase;"></span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 3rem;">
                <div class="form-group">
                    <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Password <?= !empty($editingUser) ? '<small>(New only)</small>' : '' ?></label>
                    <input id="password" name="password" type="password" placeholder="••••••••" style="width: 100%; border: 2px solid var(--primary-navy);" autocomplete="new-password">
                    <span class="inline-error" id="error-password" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 900; text-transform: uppercase;"></span>
                </div>

                <div class="form-group">
                    <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Confirm Credentials</label>
                    <input id="confirm_password" name="confirm_password" type="password" placeholder="••••••••" style="width: 100%; border: 2px solid var(--primary-navy);" autocomplete="new-password">
                    <span class="inline-error" id="error-confirm_password" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 900; text-transform: uppercase;"></span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <button id="submit-btn" class="btn btn-primary" type="submit" style="padding: 1.5rem; font-size: 1.2rem; font-weight: 900; letter-spacing: 1px;">
                    <?= !empty($editingUser) ? 'SAVE IDENTITY CHANGES' : 'COMPLETE PORTAL REGISTRATION' ?>
                </button>
                <button type="button" id="cancel-btn" class="btn" style="padding: 1rem; font-weight: 800; background: transparent; border-color: transparent !important;">CANCEL AND DISCARD</button>
            </div>
        </form>
    </div>
</section>

<!-- Custom Deletion Modal (Sharp Styling) -->
<div id="delete-confirm-overlay" style="display:none; position:fixed; inset:0; background:rgba(29, 42, 68, 0.9); backdrop-filter:blur(8px); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:#fff; border:4px solid var(--primary-navy); padding:3rem; max-width:420px; width:90%; box-shadow: 20px 20px 0px rgba(0,0,0,0.2);">
        <h3 style="margin-bottom:1.5rem; text-transform:uppercase; font-size:1.6rem; color:var(--primary-navy); font-weight: 900; letter-spacing: -1px;">Confirm Elimination</h3>
        <p style="margin-bottom:2.5rem; font-weight:600; font-size: 1.1rem; line-height: 1.4; opacity: 0.8;">Are you sure you want to permanently remove this account? This action is irreversible.</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <button id="cancel-delete-btn" class="btn" style="font-weight: 900;">CANCEL</button>
            <button id="confirm-delete-btn" class="btn btn-danger" style="background: var(--primary-red); color: white; border: none; font-weight: 900;">DELETE</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const regSection = document.getElementById('user-registration-section');
    const showBtn = document.getElementById('show-register-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const userForm = document.getElementById('user-form');
    const tableBody = document.querySelector('#users-table tbody');

    // Show form
    showBtn.addEventListener('click', () => {
        userForm.reset();
        document.getElementById('form-action').value = 'create_user';
        document.getElementById('submit-btn').textContent = 'COMPLETE PORTAL REGISTRATION';
        document.getElementById('form-title').textContent = 'Portal Registration';
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
        regSection.style.display = 'block';
        regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Edit button AJAX
    document.addEventListener('click', e => {
        if (e.target.classList.contains('edit-user-btn')) {
            const userId = e.target.dataset.id;
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_user&user_id=${userId}`
            })
            .then(res => res.json())
            .then(user => {
                document.getElementById('form-action').value = 'update_user';
                document.getElementById('submit-btn').textContent = 'SAVE IDENTITY CHANGES';
                document.getElementById('form-title').textContent = 'Update Staff Identity';
                
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
                
                regSection.style.display = 'block';
                regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    });

    cancelBtn.addEventListener('click', () => {
        regSection.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Delete workflow
    let userIdToDelete = null;
    const confirmOverlay = document.getElementById('delete-confirm-overlay');
    
    document.addEventListener('click', e => {
        if (e.target.classList.contains('delete-user-trigger')) {
            userIdToDelete = e.target.dataset.id;
            confirmOverlay.style.display = 'flex';
        }
    });

    document.getElementById('cancel-delete-btn').addEventListener('click', () => {
        confirmOverlay.style.display = 'none';
        userIdToDelete = null;
    });

    document.getElementById('confirm-delete-btn').addEventListener('click', () => {
        if (!userIdToDelete) return;
        fetch('index.php?page=back_dashboard&tab=users', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
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
                if (window.showToast) window.showToast(data.success);
            }
        });
    });

    // Submit workflow (Controle de Saisir)
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
        
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const role = document.getElementById('role').value;
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const action = document.getElementById('form-action').value;
        
        let clientErrors = {};

        // Name Validation
        if (name === '') {
            clientErrors.name = 'FULL NAME IS REQUIRED';
        } else if (name.length < 3) {
            clientErrors.name = 'NAME MUST BE AT LEAST 3 CHARACTERS';
        } else if (/\d/.test(name)) {
            clientErrors.name = 'NAME CANNOT CONTAIN NUMBERS';
        }

        // Email Validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === '') {
            clientErrors.email = 'EMAIL ADDRESS IS REQUIRED';
        } else if (!emailRegex.test(email)) {
            clientErrors.email = 'INVALID EMAIL FORMAT';
        }

        // Password Validation (Required only for new users or if being changed)
        if (action === 'create_user' || pass !== '') {
            if (pass.length < 8) {
                clientErrors.password = 'PASSWORD MUST BE AT LEAST 8 CHARACTERS';
            }
            if (pass !== confirm) {
                clientErrors.confirm_password = 'PASSWORDS DO NOT MATCH';
            }
        }

        // If client errors exist, display them and stop
        if (Object.keys(clientErrors).length > 0) {
            for (const field in clientErrors) {
                const el = document.getElementById(`error-${field}`);
                if (el) el.textContent = clientErrors[field];
            }
            // Shake the form for feedback
            regSection.style.animation = 'none';
            void regSection.offsetWidth;
            regSection.style.animation = 'shake 0.4s ease-in-out';
            return;
        }

        const formData = new FormData(userForm);
        fetch('index.php?page=back_dashboard&tab=users', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.errors) {
                for (const field in data.errors) {
                    const el = document.getElementById(`error-${field}`);
                    if (el) el.textContent = data.errors[field].toUpperCase();
                }
            } else if (data.success) {
                if (window.view && view.renderToast) {
                    view.renderToast(data.success, 'success');
                } else if (window.showToast) {
                    showToast(data.success);
                }

                if (data.user) {
                    const user = data.user;
                    const tbody = document.querySelector('#users-table tbody');
                    // Reliable ID-based lookup
                    let row = document.getElementById(`user-row-${user.id}`);
                    
                    const rowContent = `
                        <td><strong>#${user.id}</strong></td>
                        <td><span style="font-weight: 800;">${user.name}</span></td>
                        <td>${user.email}</td>
                        <td><span class="status-badge" style="background: #BCC1C1; color: var(--primary-navy); border:none; font-size: 0.65rem;">${user.role.toUpperCase()}</span></td>
                        <td style="font-size: 0.75rem; opacity: 0.7;">${user.created_at}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn btn-small edit-user-btn" data-id="${user.id}" style="font-size: 0.7rem; padding: 0.4rem 0.8rem; font-weight: 800;">Edit</button>
                                <button class="btn btn-small btn-danger delete-user-trigger" data-id="${user.id}" style="font-size: 0.7rem; padding: 0.4rem 0.8rem; background: var(--primary-red); color: white; border: none; font-weight: 800;">Delete</button>
                            </div>
                        </td>
                    `;

                    if (row) {
                        row.classList.add('row-updated');
                        row.innerHTML = rowContent;
                        setTimeout(() => row.classList.remove('row-updated'), 3000);
                    } else {
                        const newRow = document.createElement('tr');
                        newRow.id = `user-row-${user.id}`;
                        newRow.setAttribute('data-id', user.id);
                        newRow.classList.add('admin-row', 'row-new');
                        newRow.innerHTML = rowContent;
                        tbody.prepend(newRow);
                    }
                }
                userForm.reset();
                regSection.style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Helper for generating row content string to match professional styles
    function generateRowContent(user) {
        return `
            <td><span class="id-tag">#${user.id}</span></td>
            <td><div class="name-cell">${user.name}</div></td>
            <td><div class="email-cell">${user.email}</div></td>
            <td><span class="role-badge badge-${user.role}">${user.role.toUpperCase()}</span></td>
            <td><span class="date-cell">${user.created_at}</span></td>
            <td style="text-align: right;">
                <div class="action-flex" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button class="btn-small edit-user-btn" data-id="${user.id}" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase; border: 1px solid #ccc; background: white; color: #1d2a44; cursor: pointer;">Edit</button>
                    <button class="btn-small delete-user-trigger" data-id="${user.id}" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase; background: #e74c3c; color: white; border: none; cursor: pointer;">Delete</button>
                </div>
            </td>
        `;
    }
    window.generateRowContent = generateRowContent;
});
</script>
