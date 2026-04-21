<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "ID not provided"; exit; }
$t = MainController::showTransportType($id);
if (!$t) { echo "Transport Type not found"; exit; }
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTransportType.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Edit Transport Type
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="updateTransportType">
                <input type="hidden" name="idTransportType" value="<?= $t['idTransportType'] ?>">
                <div class="form-group">
                    <label for="name">Type Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($t['name']) ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" style="width:100%; padding:10px 14px; background:var(--bg-dark, #1a1a2e); border:var(--border-main); border-radius:8px; color:inherit; font-family:inherit; font-size:0.95rem; resize:vertical;"><?= htmlspecialchars($t['description'] ?? '') ?></textarea>
                </div>
                <?php if ($t['photo_url']): ?>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; margin-bottom:6px; font-size:0.82rem; font-weight:600; color:var(--secondary-grey); text-transform:uppercase; letter-spacing:0.3px;">Current Photo</label>
                        <img src="../assets/images/<?= htmlspecialchars($t['photo_url']) ?>" alt="Current" style="max-width:200px; border-radius:8px; border:var(--border-main);">
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="photo">Replace Photo (optional)</label>
                    <input type="file" id="photo" name="photo" style="border:none; padding:1rem 0;">
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTransportType.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </section>
</main>
<script src="validate.js"></script>
</body>
</html>
