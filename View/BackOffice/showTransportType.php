<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$types = MainController::listTransportTypes();
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem; flex-wrap:wrap; gap:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">Transport Types</h2>
            <a href="addTransportType.php" class="btn btn-primary">＋ Add Type</a>
        </div>
        <div class="editorial-grid">
            <?php if (count($types) > 0): ?>
                <?php foreach ($types as $t): ?>
                    <div class="editorial-card" style="position:relative;">
                        <?php if ($t['photo_url']): ?>
                            <div style="width:100%; height:160px; overflow:hidden; border-radius:8px; margin-bottom:1rem;">
                                <img src="../assets/images/<?= htmlspecialchars($t['photo_url']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                        <?php else: ?>
                            <div style="width:100%; height:160px; background:var(--bg-accent, linear-gradient(135deg, #667eea, #764ba2)); border-radius:8px; margin-bottom:1rem; display:flex; align-items:center; justify-content:center; font-size:3rem;">
                                🚌
                            </div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($t['name']) ?></h3>
                        <p style="flex:1;"><?= htmlspecialchars($t['description'] ?? 'No description') ?></p>
                        <div style="display:flex; gap:8px; margin-top:auto; padding-top:1rem;">
                            <a href="updateTransportType.php?id=<?= $t['idTransportType'] ?>" class="btn btn-small" title="Edit">✎ Edit</a>
                            <form action="../../Verification.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this transport type? Vehicles linked to it will be unlinked.');">
                                <input type="hidden" name="action" value="deleteTransportType">
                                <input type="hidden" name="idTransportType" value="<?= $t['idTransportType'] ?>">
                                <button type="submit" class="btn btn-small btn-danger" title="Delete">🗑</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="editorial-card" style="grid-column: 1 / -1; text-align:center;">
                    <h3>No Transport Types Found</h3>
                    <p>Add your first transport type to get started.</p>
                    <a href="addTransportType.php" class="btn btn-primary">＋ Add Type</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
