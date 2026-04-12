<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="card">
    <h1>Bienvenue sur CivicPortal</h1>
    <p>Accédez à vos services municipaux en ligne.</p>

    <?php if (!empty($currentUser)): ?>
        <div style="margin-top: 1.5rem;">
            <h2>Bonjour, <?= htmlspecialchars($currentUser['name']) ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <div class="service-card">
                    <h3>Documents</h3>
                    <p>Gérer vos documents officiels.</p>
                    <a href="#" class="button">Accéder</a>
                </div>
                <div class="service-card">
                    <h3>Forum Posts</h3>
                    <p>Participer aux discussions communautaires.</p>
                    <a href="#" class="button">Accéder</a>
                </div>
                <div class="service-card">
                    <h3>Transport</h3>
                    <p>Informations et services de transport.</p>
                    <a href="#" class="button">Accéder</a>
                </div>
                <div class="service-card">
                    <h3>Programmes</h3>
                    <p>Découvrez les programmes disponibles.</p>
                    <a href="#" class="button">Accéder</a>
                </div>
                <div class="service-card">
                    <h3>Profil</h3>
                    <p>Gérer votre profil utilisateur.</p>
                    <a href="index.php?page=front_profile" class="button">Accéder</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
    .service-card {
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #d5d9e0;
        border-radius: 14px;
        text-align: center;
    }
    .service-card h3 {
        margin-top: 0;
    }
    .service-card a {
        display: inline-block;
        margin-top: 0.5rem;
    }
</style>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
