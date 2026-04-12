<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="card">
    <h1>Mon profil</h1>
    <p>Vos informations personnelles et gestion des amis.</p>

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

    <?php if (!empty($currentUser)): ?>
        <?php $isEditMode = isset($_GET['edit']); ?>
        <div style="display: grid; gap: 2rem;">
            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1.5rem; align-items: center;">
                <div>
                    <img src="<?= htmlspecialchars($currentUserAvatar ?: 'assets/images/default-avatar.png') ?>" alt="Avatar" style="width:150px; height:150px; object-fit:cover; border-radius:50%; border:1px solid #ccc;">
                </div>
                <div>
                    <h2><?= htmlspecialchars($currentProfile->getFullName() ?: $currentUser->getName()) ?></h2>
                    <p><strong>Email :</strong> <?= htmlspecialchars($currentUser->getEmail()) ?></p>
                    <p><strong>Rôle :</strong> <?= htmlspecialchars($currentUser->getRole()) ?></p>
                    <?php if (!empty($currentProfile->getPhoneNumber())): ?>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($currentProfile->getPhoneNumber()) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($currentProfile->getDateOfBirth())): ?>
                        <p><strong>Date de naissance :</strong> <?= htmlspecialchars($currentProfile->getDateOfBirth()) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($currentProfile->getBio())): ?>
                        <p><strong>Bio :</strong> <?= htmlspecialchars($currentProfile->getBio()) ?></p>
                    <?php endif; ?>
                    <p><strong>Amis :</strong> <?= htmlspecialchars(count($friends)) ?></p>
                    <p><strong>BackOffice :</strong> <a href="index.php?page=back_users_list">Voir le CRUD utilisateurs</a></p>
                    <?php if (!$isEditMode): ?>
                        <p><a href="index.php?page=front_profile&edit=1" class="button">Modifier le profil</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isEditMode): ?>
                <form method="post" action="index.php?page=front_profile" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input id="name" name="name" type="text" value="<?= htmlspecialchars($old['name'] ?? $currentUser->getName()) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? $currentUser->getEmail()) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input id="first_name" name="first_name" type="text" value="<?= htmlspecialchars($old['first_name'] ?? $currentProfile->getFirstName()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input id="last_name" name="last_name" type="text" value="<?= htmlspecialchars($old['last_name'] ?? $currentProfile->getLastName()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Biographie</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Ajoutez une biographie..."><?= htmlspecialchars($old['bio'] ?? $currentProfile->getBio()) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Téléphone</label>
                        <input id="phone_number" name="phone_number" type="text" value="<?= htmlspecialchars($old['phone_number'] ?? $currentProfile->getPhoneNumber()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date de naissance</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="<?= htmlspecialchars($old['date_of_birth'] ?? $currentProfile->getDateOfBirth()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="avatar">Photo de profil</label>
                        <input id="avatar" name="avatar" type="file" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="password">Nouveau mot de passe (optionnel)</label>
                        <input id="password" name="password" type="password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input id="confirm_password" name="confirm_password" type="password">
                    </div>

                    <button class="button" type="submit">Mettre à jour</button>
                    <a href="index.php?page=front_profile" class="button-secondary">Annuler</a>
                </form>
            <?php endif; ?>

            <div style="margin-top: 1rem; padding: 1rem; border: 1px solid #d5d9e0; border-radius: 16px; background: #fafafa;">
                <h2>Gestion des amis</h2>

                <form method="get" action="index.php?page=front_profile" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
                    <input type="text" name="friend_search" placeholder="Rechercher un ami" value="<?= htmlspecialchars($_GET['friend_search'] ?? '') ?>" style="flex:1; min-width:220px;">
                    <button class="button" type="submit">Rechercher</button>
                </form>

                <?php if (!empty($friends)): ?>
                    <ul style="list-style:none; padding:0; margin:0 0 1rem 0;">
                        <?php foreach ($friends as $friend): ?>
                            <li style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid #ebebeb;">
                                <span><?= htmlspecialchars($friend['name']) ?> (<?= htmlspecialchars($friend['role']) ?>) — <?= htmlspecialchars($friend['email']) ?></span>
                                <form method="post" action="index.php?page=front_profile" style="margin:0;">
                                    <input type="hidden" name="action" value="remove_friend">
                                    <input type="hidden" name="friend_id" value="<?= htmlspecialchars($friend['id']) ?>">
                                    <button class="button-secondary" type="submit">Supprimer</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun ami pour le moment.</p>
                <?php endif; ?>

                <form method="post" action="index.php?page=front_profile">
                    <input type="hidden" name="action" value="add_friend">
                    <div class="form-group">
                        <label for="friend-name">Nom de l'ami</label>
                        <input id="friend-name" name="name" type="text" required>
                    </div>
                    <div class="form-group">
                        <label for="friend-email">Email de l'ami</label>
                        <input id="friend-email" name="email" type="email" required>
                    </div>
                    <div class="form-group">
                        <label for="friend-role">Rôle</label>
                        <select id="friend-role" name="role">
                            <option value="citizen">Citoyen</option>
                            <option value="agent">Agent</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button class="button" type="submit">Ajouter un ami</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <p>Utilisateur non trouvé.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
