<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="card">
    <h1>Management - User Administration</h1>
    <p>View, edit, or delete user accounts from the portal.</p>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getId()) ?></td>
                        <td><?= htmlspecialchars($user->getName()) ?></td>
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td><span class="status-badge"><?= htmlspecialchars($user->getRole()) ?></span></td>
                        <td><?= htmlspecialchars($user->getCreatedAt() ?? '-') ?></td>
                        <td class="actions">
                            <a class="button-secondary" href="index.php?page=back_users_list&edit=<?= htmlspecialchars($user->getId()) ?>">Edit</a>
                            <form method="post" action="index.php?page=back_users_list" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user->getId()) ?>">
                                <button class="button-secondary" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <section style="margin-top: 2rem;">
        <?php if (!empty($editingUser)): ?>
            <h2>Edit User #<?= htmlspecialchars($editingUser->getId()) ?></h2>
        <?php else: ?>
            <h2>Add New User</h2>
        <?php endif; ?>

        <form method="post" action="index.php?page=back_users_list">
            <input type="hidden" name="action" value="<?= !empty($editingUser) ? 'update_user' : 'create_user' ?>">
            <?php if (!empty($editingUser)): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($editingUser->getId()) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" pattern="[A-Za-zÀ-ÿ '-]+" title="Name cannot contain numbers." required value="<?= htmlspecialchars($old['name'] ?? (!empty($editingUser) ? $editingUser->getName() : '')) ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="<?= htmlspecialchars($old['email'] ?? (!empty($editingUser) ? $editingUser->getEmail() : '')) ?>">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <?php $roleValue = $old['role'] ?? (!empty($editingUser) ? $editingUser->getRole() : 'citizen'); ?>
                <select id="role" name="role">
                    <option value="citizen" <?= $roleValue === 'citizen' ? 'selected' : '' ?>>Citizen</option>
                    <option value="agent" <?= $roleValue === 'agent' ? 'selected' : '' ?>>Agent</option>
                    <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password <?= !empty($editingUser) ? '(leave empty to keep current)' : '' ?></label>
                <input id="password" name="password" type="password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password">
            </div>

            <button class="button" type="submit"><?= !empty($editingUser) ? 'Update' : 'Add' ?> User</button>
        </form>
    </section>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
