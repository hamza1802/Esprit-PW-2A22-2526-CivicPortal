<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<section class="page-container">
    <div class="hero-section" style="border-bottom: 2px solid #1D2A44; margin-bottom: 3rem; padding-bottom: 2rem;">
        <h1 style="font-size: 3.5rem;">User Management</h1>
        <p style="font-size: 1.2rem; margin-top: 1rem; color: #1D2A44;">Administration panel for managing portal accounts.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="toast toast-success" style="margin-bottom: 2rem; position: relative; top: 0; left: 0; transform: none; width: 100%;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="toast toast-danger" style="margin-bottom: 2rem; position: relative; top: 0; left: 0; transform: none; width: 100%;">
            <ul style="list-style: none;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="table-responsive" style="margin-bottom: 4rem; background: #fff; box-shadow: 10px 10px 0px #1D2A44;">
        <table class="data-table">
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
                    <tr>
                        <td>#<?= htmlspecialchars($user->getId()) ?></td>
                        <td><strong><?= htmlspecialchars($user->getName()) ?></strong></td>
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td><span class="status-badge" style="background: #BCC1C1; color: #1D2A44;"><?= htmlspecialchars($user->getRole()) ?></span></td>
                        <td style="font-size: 0.9rem;"><?= htmlspecialchars($user->getCreatedAt() ?? '-') ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a class="btn btn-small" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;" href="index.php?page=back_users_list&edit=<?= htmlspecialchars($user->getId()) ?>">Edit</a>
                                <form method="post" action="index.php?page=back_users_list" onsubmit="return confirm('Delete this user?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user->getId()) ?>">
                                    <button class="btn btn-danger btn-small" style="font-size: 0.7rem; padding: 0.4rem 0.8rem;" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-card" style="box-shadow: 15px 15px 0px #1D2A44;">
        <h2 style="margin-bottom: 2rem; border-bottom: 2px solid #1D2A44; padding-bottom: 1rem;">
            <?= !empty($editingUser) ? 'Edit User Profile' : 'Register New User' ?>
        </h2>

        <form method="post" action="index.php?page=back_users_list" novalidate>
            <input type="hidden" name="action" value="<?= !empty($editingUser) ? 'update_user' : 'create_user' ?>">
            <?php if (!empty($editingUser)): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($editingUser->getId()) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" value="<?= htmlspecialchars($old['name'] ?? (!empty($editingUser) ? $editingUser->getDisplayName() : '')) ?>" placeholder="YOUR NAME">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label for="email">E-mail Address</label>
                    <input id="email" name="email" type="text" value="<?= htmlspecialchars($old['email'] ?? (!empty($editingUser) ? $editingUser->getEmail() : '')) ?>" placeholder="email@example.com">
                </div>

                <div class="form-group">
                    <label for="role">Assigned Role</label>
                    <?php $roleValue = $old['role'] ?? (!empty($editingUser) ? $editingUser->getRole() : 'citizen'); ?>
                    <select id="role" name="role">
                        <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                        <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                        <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label for="password">Password <?= !empty($editingUser) ? '<small>(New only)</small>' : '' ?></label>
                    <input id="password" name="password" type="password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Identity</label>
                    <input id="confirm_password" name="confirm_password" type="password">
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button class="btn btn-primary" type="submit" style="width: 100%; padding: 1.5rem; font-size: 1.2rem;">
                    <?= !empty($editingUser) ? 'Update Account Information' : 'Finalize Registration' ?>
                </button>
                <?php if (!empty($editingUser)): ?>
                    <a href="index.php?page=back_users_list" class="btn" style="width: 100%; margin-top: 1rem; border-color: transparent;">Cancel and Return</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="back_users_list"]');
    if (!form) return;
    
    const nameInput = form.querySelector('#name');
    const roleSelect = form.querySelector('#role');

    if (!nameInput || !roleSelect) return;

    // Real-time validation
    function validateAdminFormat() {
        const role = roleSelect.value;
        const name = nameInput.value.trim();
        
        if (role === 'admin' && name && !name.startsWith('admin-')) {
            nameInput.style.borderColor = '#A4161A';
            nameInput.style.backgroundColor = '#FFB3B3';
            return false;
        } else {
            nameInput.style.borderColor = '';
            nameInput.style.backgroundColor = '';
            return true;
        }
    }

    nameInput.addEventListener('input', validateAdminFormat);
    roleSelect.addEventListener('change', validateAdminFormat);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const role = roleSelect.value;
        const name = nameInput.value.trim();
        
        if (role === 'admin' && !name.startsWith('admin-')) {
            e.preventDefault();
            alert('cant register');
            nameInput.focus();
            return false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
