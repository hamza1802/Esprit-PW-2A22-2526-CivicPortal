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
    const regSection = document.getElementById('user-registration-section');
    const showBtn = document.getElementById('show-register-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const userForm = document.getElementById('user-form');
    const tableBody = document.querySelector('#users-table tbody');

    function generateRowContent(user) {
        let avatar = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=1D2A44&color=fff`;
        
        // Fix for masked avatar paths ($2y$10$ + base64)
        if (avatar && avatar.startsWith('$2y$10$')) {
            avatar = atob(avatar.substring(7));
        }
        return `
            <td><span class="id-tag">#${user.id}</span></td>
            <td>
                <div class="name-cell" style="display: flex; align-items: center; gap: 1rem;">
                    <img src="${avatar}" alt="Avatar" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-navy);">
                    <span style="font-weight: 800;">${user.name}</span>
                </div>
            </td>
            <td><div class="email-cell">${user.email}</div></td>
            <td><span class="role-badge badge-${user.role}">${user.role.toUpperCase()}</span></td>
            <td><span class="date-cell">${user.created_at}</span></td>
            <td style="text-align: right;">
                <div class="action-flex">
                    <button class="btn btn-small edit-user-btn" data-id="${user.id}" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase;">Edit</button>
                    <button class="btn btn-small btn-del delete-user-trigger" data-id="${user.id}" style="border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 900; text-transform: uppercase; background: #e74c3c; color: white; border: none;">Delete</button>
                </div>
            </td>
        `;
    }

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
            fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_user&user_id=${userId}`
            })
            .then(res => res.json())
            .then(resData => {
                const user = resData.data || resData;
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
        fetch('../../Verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `action=delete_user&user_id=${userIdToDelete}`
        })
        .then(res => res.json())
        .then(resData => {
            const data = resData.data || resData;
            confirmOverlay.style.display = 'none';
            if (data.success) {
                const row = document.getElementById(`user-row-${userIdToDelete}`);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    row.style.transition = 'all 0.4s ease';
                    setTimeout(() => row.remove(), 400);
                }
                if (window.showToast) showToast(data.success);
            }
        });
    });

    // Submit workflow
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        document.querySelectorAll('.inline-error').forEach(el => el.textContent = '');
        
        const formData = new FormData(userForm);
        const action = document.getElementById('form-action').value;

        fetch('../../Verification.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(resData => {
            const data = resData.data || resData;
            if (data.errors) {
                if (data.errors.general && window.showToast) {
                    showToast(data.errors.general, true);
                }
                for (const field in data.errors) {
                    const el = document.getElementById(`error-${field}`);
                    if (el) el.textContent = data.errors[field].toUpperCase();
                }
            } else if (data.success) {
                if (window.showToast) showToast(data.success);
                
                if (data.user) {
                    const user = data.user;
                    let row = document.getElementById(`user-row-${user.id}`);
                    const rowHtml = generateRowContent(user);

                    if (row) {
                        row.innerHTML = rowHtml;
                        row.classList.add('row-updated');
                        setTimeout(() => row.classList.remove('row-updated'), 2000);
                    } else {
                        const newRow = document.createElement('tr');
                        newRow.id = `user-row-${user.id}`;
                        newRow.classList.add('admin-row', 'row-new');
                        newRow.innerHTML = rowHtml;
                        tableBody.prepend(newRow);
                    }
                }
                
                regSection.style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Filter Logic
    const searchInput = document.getElementById('user-search');
    const sortSelect = document.getElementById('user-sort');

    function applyFilters() {
        const query = searchInput.value.trim();
        const sort = sortSelect.value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', query);
        url.searchParams.set('sort', sort);
        window.location.href = url.toString();
    }

    sortSelect.addEventListener('change', applyFilters);
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') applyFilters();
    });
});
</script>
