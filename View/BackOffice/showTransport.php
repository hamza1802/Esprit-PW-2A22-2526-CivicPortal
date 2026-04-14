<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$transports = MainController::listTransports();
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem; flex-wrap:wrap; gap:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">Transport Fleet</h2>
            <a href="addTransport.php" class="btn btn-primary">＋ Add Vehicle</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Label</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transports) > 0): ?>
                        <?php foreach ($transports as $t): ?>
                            <tr>
                                <td style="color:var(--secondary-grey); font-size:0.78rem;"><?= htmlspecialchars($t['idTransport']) ?></td>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                                <td><?= htmlspecialchars($t['type']) ?></td>
                                <td><strong><?= htmlspecialchars($t['capacity']) ?></strong> seats</td>
                                <td><span class="status-badge"><?= htmlspecialchars($t['status']) ?></span></td>
                                <td>
                                    <div style="display:flex; gap:8px;">
                                        <a href="updateTransport.php?id=<?= $t['idTransport'] ?>" class="btn btn-small" title="Edit">✎ Edit</a>
                                        <form action="../../Verification.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            <input type="hidden" name="action" value="deleteTransport">
                                            <input type="hidden" name="idTransport" value="<?= $t['idTransport'] ?>">
                                            <button type="submit" class="btn btn-small btn-danger" title="Delete">🗑</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:30px;">🚐 No vehicles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
