<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$trajets = MainController::listTrajets();
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem; flex-wrap:wrap; gap:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">Trajets Management</h2>
            <a href="addTrajet.php" class="btn btn-primary">＋ Add Trajet</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Departure</th>
                        <th>Price (TND)</th>
                        <th>Occupancy</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($trajets) > 0): ?>
                        <?php foreach ($trajets as $t): 
                             $occ = MainController::getOccupancy($t['idTrajet']);
                             $pct = $occ['pct'];
                             $isFull = $occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity'];
                        ?>
                            <tr>
                                <td style="color:var(--secondary-grey); font-size:0.78rem;"><?= htmlspecialchars($t['idTrajet']) ?></td>
                                <td><strong><?= htmlspecialchars($t['departure']) ?> → <?= htmlspecialchars($t['destination']) ?></strong></td>
                                <td><?= htmlspecialchars($t['transportName'] ?? '⚠ Unassigned') ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($t['departureTime'])) ?></td>
                                <td><strong><?= number_format($t['price'], 3) ?></strong></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="progress-track" style="width:80px; margin:0;">
                                            <div class="progress-fill" style="width:<?= $pct ?>%; <?= $pct > 80 ? 'background:var(--danger);' : '' ?>"></div>
                                        </div>
                                        <span style="font-size:0.85rem; font-weight:700;"><?= $occ['sold'] ?>/<?= $occ['capacity'] ?></span>
                                        <?php if ($isFull): ?>
                                            <span class="status-badge status-rejected" style="font-size:0.7rem;">FULL</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <form action="../../Verification.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this trajet and all linked tickets?');">
                                        <input type="hidden" name="action" value="deleteTrajet">
                                        <input type="hidden" name="idTrajet" value="<?= $t['idTrajet'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger" title="Delete">🗑</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding:30px;">🗺️ No trajets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
