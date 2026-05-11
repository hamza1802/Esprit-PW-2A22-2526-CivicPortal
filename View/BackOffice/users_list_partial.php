<?php
/**
 * users_list_partial.php
 * Content for the Users tab in BackOffice.
 */
require_once __DIR__ . '/../../controller/UserController.php';

// Handle Search and Sort
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id DESC';

$usersData = UserController::getAllUsers($search, $sort);

$editingUserId = $_GET['edit'] ?? null;
$editingUser = $editingUserId ? UserController::getUserById((int)$editingUserId) : null;
?>
<section class="page-container" style="padding-top: 1rem;">
    <div class="hero-section" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; border-bottom: 2px solid var(--primary-navy); padding-bottom: 2rem;">
        <div>
            <h1 style="font-size: 3rem; color: var(--primary-navy); font-weight: 900; letter-spacing: -1.5px;">User Management</h1>
            <p style="font-size: 1.1rem; opacity: 0.8; margin-top: 10px; font-weight: 600;">Manage portal accounts and staff credentials.</p>
        </div>
        <button id="show-register-form" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-weight: 900; border-radius: 4px; box-shadow: 8px 8px 0px var(--primary-navy);">+ NEW REGISTRATION</button>
    </div>

    <!-- Search and Sort Controls -->
    <div class="controls-bar" style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
        <div style="flex-grow: 1; position: relative;">
            <i class="bi bi-search" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--primary-navy); opacity: 0.5;"></i>
            <input type="text" id="user-search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>" style="width: 100%; padding: 1.2rem 1.2rem 1.2rem 3.5rem; border: 2px solid var(--primary-navy); font-weight: 700; border-radius: 8px;">
        </div>
        <div style="width: 250px;">
            <select id="user-sort" style="width: 100%; padding: 1.2rem; border: 2px solid var(--primary-navy); font-weight: 900; text-transform: uppercase; border-radius: 8px; appearance: none; background: white url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231D2A44%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22/%3E%3C/svg%3E') no-repeat right 1rem center; background-size: 0.65rem auto;">
                <option value="u.id DESC" <?= $sort === 'u.id DESC' ? 'selected' : '' ?>>Latest First</option>
                <option value="u.username ASC" <?= $sort === 'u.username ASC' ? 'selected' : '' ?>>Full Name (A-Z)</option>
                <option value="u.username DESC" <?= $sort === 'u.username DESC' ? 'selected' : '' ?>>Full Name (Z-A)</option>
                <option value="u.email ASC" <?= $sort === 'u.email ASC' ? 'selected' : '' ?>>Email Address</option>
            </select>
        </div>
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
                        $p = $item['profile'];
                        $avatar = $p['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($u->getName()) . '&background=1D2A44&color=fff';

                        // Fix for masked avatar paths ($2y$10$ + base64)
                        if (strpos($avatar, '$2y$10$') === 0) {
                            $avatar = base64_decode(substr($avatar, 7));
                        }

                        // Check for BLOB image
                        if (!empty($item['profile']['has_pic'])) {
                            $avatar = 'get_image.php?type=profile&id=' . $u->getId() . '&t=' . $u->getId();
                        }
                    ?>
                    <tr id="user-row-<?= htmlspecialchars($u->getId()) ?>" data-id="<?= htmlspecialchars($u->getId()) ?>" class="admin-row">
                        <td style="font-weight: 800;"><span class="id-tag">#<?= htmlspecialchars($u->getId()) ?></span></td>
                        <td>
                            <div class="name-cell" style="display: flex; align-items: center; gap: 1rem;">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-navy);">
                                <span style="font-weight: 800; font-size: 1.1rem; color: var(--primary-navy);"><?= htmlspecialchars($u->getName()) ?></span>
                            </div>
                        </td>
                        <td style="font-weight: 600; opacity: 0.8;"><div class="email-cell"><?= htmlspecialchars($u->getEmail()) ?></div></td>
                        <td><span class="role-badge" style="background: transparent; color: var(--primary-navy); border: none; font-weight: 800; padding: 0; font-size: 0.9rem;"><?= strtoupper(htmlspecialchars($u->getRole())) ?></span></td>
                        <td style="font-weight: 600; opacity: 0.8;"><span class="date-cell"><?= $u->getCreatedAt() ? date('M j, Y', strtotime($u->getCreatedAt())) : '-' ?></span></td>
                        <td style="text-align: right;">
                            <div class="action-flex" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn btn-small edit-user-btn" data-id="<?= htmlspecialchars($u->getId()) ?>" style="border: 1px solid #ddd; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 800;">EDIT</button>
                                <button class="btn btn-small btn-del delete-user-trigger" data-id="<?= htmlspecialchars($u->getId()) ?>" style="border: 1px solid #ddd; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 800; background: #fff; color: #e74c3c; border-color: #e74c3c;">DELETE</button>
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

<!-- Custom Deletion Modal -->
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
    /* ------------------------------------------------------------------ */
    /* Local toast — works without the SPA view.js                         */
    /* ------------------------------------------------------------------ */
    function showLocalToast(msg, isError = false) {
        // Use the SPA toast if available
        if (window.view && typeof window.view.renderToast === 'function') {
            window.view.renderToast(msg, isError ? 'error' : 'success');
            return;
        }
        // Fallback: create a simple toast
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.style.cssText = `padding:1rem 1.5rem;background:${isError ? '#e74c3c' : '#1D2A44'};color:#fff;font-weight:800;border-radius:4px;box-shadow:6px 6px 0 rgba(0,0,0,0.15);min-width:260px;font-size:0.95rem;`;
        toast.textContent = msg;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s';
            setTimeout(() => toast.remove(), 450);
        }, 3500);
    }

    /* ------------------------------------------------------------------ */
    /* DOM refs                                                             */
    /* ------------------------------------------------------------------ */
    const regSection  = document.getElementById('user-registration-section');
    const showBtn     = document.getElementById('show-register-form');
    const cancelBtn   = document.getElementById('cancel-btn');
    const userForm    = document.getElementById('user-form');
    const tableBody   = document.querySelector('#users-table tbody');
    const submitBtn   = document.getElementById('submit-btn');
    const formTitle   = document.getElementById('form-title');
    const formAction  = document.getElementById('form-action');

    /* ------------------------------------------------------------------ */
    /* Build a table row from a user object (from User::toArray())          */
    /* ------------------------------------------------------------------ */
    function generateRowContent(user) {
        // User::toArray() gives: { id, name, email, role, avatar (url or null), ... }
        let avatar = user.avatar
            || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || user.username || '?')}&background=1D2A44&color=fff`;

        // Fix for masked avatar paths ($2y$10$ + base64)
        if (avatar && avatar.startsWith('$2y$10$')) {
            try { avatar = atob(avatar.substring(7)); } catch(e) {}
        }

        const displayName = user.name || user.username || '—';
        const role = (user.role || 'citizen').toLowerCase();

        return `
            <td><span class="id-tag">#${user.id}</span></td>
            <td>
                <div class="name-cell" style="display:flex;align-items:center;gap:1rem;">
                    <img src="${avatar}" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--primary-navy);"
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=1D2A44&color=fff'">
                    <span style="font-weight:800;font-size:1.1rem;color:var(--primary-navy);">${displayName}</span>
                </div>
            </td>
            <td><div class="email-cell" style="font-weight:600;opacity:0.8;">${user.email || '—'}</div></td>
            <td><span class="role-badge badge-${role}" style="font-weight:800;">${role.toUpperCase()}</span></td>
            <td><span class="date-cell" style="font-weight:600;opacity:0.8;">${user.created_at ? new Date(user.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</span></td>
            <td style="text-align:right;">
                <div class="action-flex" style="display:flex;gap:0.5rem;justify-content:flex-end;">
                    <button class="btn btn-small edit-user-btn" data-id="${user.id}" style="border:1px solid #ddd;padding:0.6rem 1.5rem;border-radius:8px;font-weight:800;">EDIT</button>
                    <button class="btn btn-small btn-del delete-user-trigger" data-id="${user.id}" style="border:1px solid #e74c3c;padding:0.6rem 1.5rem;border-radius:8px;font-weight:800;background:#fff;color:#e74c3c;">DELETE</button>
                </div>
            </td>
        `;
    }

    /* ------------------------------------------------------------------ */
    /* Helper: ensure the hidden user_id field exists in the form           */
    /* ------------------------------------------------------------------ */
    function setHiddenUserId(id) {
        let idInput = userForm.querySelector('input[name="id"]');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type  = 'hidden';
            idInput.name  = 'id';
            userForm.appendChild(idInput);
        }
        idInput.value = id || '';
    }

    /* ------------------------------------------------------------------ */
    /* Reset form to "create" mode                                          */
    /* ------------------------------------------------------------------ */
    function resetToCreateMode() {
        userForm.reset();
        setHiddenUserId('');
        formAction.value     = 'create_user';
        submitBtn.textContent = 'COMPLETE PORTAL REGISTRATION';
        formTitle.textContent = 'Portal Registration';
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
    }

    /* ------------------------------------------------------------------ */
    /* Show the registration panel                                          */
    /* ------------------------------------------------------------------ */
    showBtn.addEventListener('click', () => {
        resetToCreateMode();
        regSection.style.display = 'block';
        regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ------------------------------------------------------------------ */
    /* Cancel                                                               */
    /* ------------------------------------------------------------------ */
    cancelBtn.addEventListener('click', () => {
        regSection.style.display = 'none';
        resetToCreateMode();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ------------------------------------------------------------------ */
    /* Edit button — load user data via AJAX then switch form to edit mode  */
    /* ------------------------------------------------------------------ */
    document.addEventListener('click', e => {
        if (!e.target.classList.contains('edit-user-btn')) return;
        const userId = e.target.dataset.id;

        // Optimistic UI: show form immediately with loading state
        formTitle.textContent  = 'Loading…';
        submitBtn.textContent  = 'LOADING…';
        submitBtn.disabled     = true;
        regSection.style.display = 'block';
        regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');

        fetch('../../Verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_user', data: { id: parseInt(userId) } })
        })
        .then(res => res.json())
        .then(resData => {
            if (!resData.success) {
                showLocalToast(resData.error || 'Failed to load user.', true);
                regSection.style.display = 'none';
                return;
            }
            const user = resData.data;

            // Switch to edit mode
            formAction.value      = 'update_user';
            formTitle.textContent = 'UPDATE STAFF IDENTITY';
            submitBtn.textContent = 'SAVE IDENTITY CHANGES';
            submitBtn.disabled    = false;

            // Set hidden user id (name="id" matches MainController $data['id'])
            setHiddenUserId(user.id);

            document.getElementById('name').value             = user.name || user.username || '';
            document.getElementById('email').value            = user.email || '';
            document.getElementById('role').value             = (user.role || 'citizen').toLowerCase();
            document.getElementById('password').value         = '';
            document.getElementById('confirm_password').value = '';
        })
        .catch(() => {
            showLocalToast('Network error: could not load user.', true);
            regSection.style.display = 'none';
            submitBtn.disabled = false;
        });
    });

    /* ------------------------------------------------------------------ */
    /* Form submit — create or update                                       */
    /* ------------------------------------------------------------------ */
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');

        const action = formAction.value;  // 'create_user' or 'update_user'
        const formData = new FormData(userForm);

        // Client-side basic validation
        const name     = (formData.get('name') || '').trim();
        const email    = (formData.get('email') || '').trim();
        const password = formData.get('password') || '';
        const confirm  = formData.get('confirm_password') || '';
        const id       = formData.get('id') || '';

        if (!name) {
            document.getElementById('error-name').textContent = 'FULL NAME IS REQUIRED.';
            return;
        }
        if (/\d/.test(name)) {
            document.getElementById('error-name').textContent = 'NAME CANNOT CONTAIN NUMBERS.';
            return;
        }
        if (!email) {
            document.getElementById('error-email').textContent = 'EMAIL IS REQUIRED.';
            return;
        }

        // Password: required for new users, optional for edits
        if (!id) {
            if (!password || password.length < 8) {
                document.getElementById('error-password').textContent = 'PASSWORD MUST BE AT LEAST 8 CHARACTERS.';
                return;
            }
        }
        if ((password || confirm) && password !== confirm) {
            document.getElementById('error-confirm_password').textContent = 'PASSWORDS DO NOT MATCH.';
            return;
        }
        if (id && password && password.length < 8) {
            document.getElementById('error-password').textContent = 'PASSWORD MUST BE AT LEAST 8 CHARACTERS.';
            return;
        }

        // Disable submit during request
        submitBtn.disabled = true;
        const origText = submitBtn.textContent;
        submitBtn.textContent = 'PROCESSING…';

        fetch('../../Verification.php', {
            method: 'POST',
            body: formData   // multipart/form-data — action field is in the FormData
        })
        .then(res => res.json())
        .then(resData => {
            submitBtn.disabled    = false;
            submitBtn.textContent = origText;

            // Verification.php wraps: { success: true, data: { ... } }
            //                      or { success: false, error: "..." }
            if (!resData.success) {
                showLocalToast(resData.error || 'Server error occurred.', true);
                return;
            }

            const data = resData.data;   // UserController return value

            if (data && data.errors) {
                // Show field-level validation errors
                for (const [field, msg] of Object.entries(data.errors)) {
                    const el = document.getElementById(`error-${field}`);
                    if (el) el.textContent = msg.toUpperCase();
                    else if (field === 'general') showLocalToast(msg, true);
                }
                return;
            }

            if (data && data.success) {
                showLocalToast(data.success);

                const user = data.user;
                if (user) {
                    const existingRow = document.getElementById(`user-row-${user.id}`);
                    const rowHtml = generateRowContent(user);

                    if (existingRow) {
                        // Update existing row
                        existingRow.innerHTML = rowHtml;
                        existingRow.classList.add('row-updated');
                        setTimeout(() => existingRow.classList.remove('row-updated'), 2000);
                    } else {
                        // Prepend new row
                        const newRow = document.createElement('tr');
                        newRow.id        = `user-row-${user.id}`;
                        newRow.dataset.id = user.id;
                        newRow.classList.add('admin-row', 'row-new');
                        newRow.innerHTML  = rowHtml;
                        tableBody.prepend(newRow);
                    }
                }

                // Hide form and reset
                regSection.style.display = 'none';
                resetToCreateMode();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            // Unexpected response
            showLocalToast('Unexpected response from server.', true);
        })
        .catch(() => {
            submitBtn.disabled    = false;
            submitBtn.textContent = origText;
            showLocalToast('Network error: could not reach server.', true);
        });
    });

    /* ------------------------------------------------------------------ */
    /* Delete workflow                                                      */
    /* ------------------------------------------------------------------ */
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
        fetch('../../Verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_user', data: { id: parseInt(userIdToDelete) } })
        })
        .then(res => res.json())
        .then(resData => {
            confirmOverlay.style.display = 'none';
            if (resData.success) {
                const row = document.getElementById(`user-row-${userIdToDelete}`);
                if (row) {
                    row.style.transition = 'opacity 0.4s, transform 0.4s';
                    row.style.opacity    = '0';
                    row.style.transform  = 'translateX(20px)';
                    setTimeout(() => row.remove(), 420);
                }
                showLocalToast(resData.data?.success || 'User deleted successfully.');
            } else {
                showLocalToast(resData.error || 'Failed to delete user.', true);
            }
            userIdToDelete = null;
        })
        .catch(() => {
            confirmOverlay.style.display = 'none';
            showLocalToast('Network error during deletion.', true);
        });
    });

    /* ------------------------------------------------------------------ */
    /* Search / Sort filters                                                */
    /* ------------------------------------------------------------------ */
    const searchInput = document.getElementById('user-search');
    const sortSelect  = document.getElementById('user-sort');

    function applyFilters() {
        const url = new URL(window.location.href);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('sort',   sortSelect.value);
        window.location.href = url.toString();
    }

    sortSelect.addEventListener('change', applyFilters);
    searchInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') applyFilters();
    });
});
</script>

