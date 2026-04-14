<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$tickets = MainController::listTickets();
?>

<main id="app">
    <section class="page-container">
        <h2 class="reveal active">Tickets Management</h2>
        <div style="margin-bottom:2rem;">
            <div class="editorial-grid" style="border:none;">
                <div class="editorial-card" style="border: var(--border-main); padding:2rem;">
                    <h3>🎟️ <?= count($tickets) ?></h3>
                    <p style="margin-bottom:0;">Total Tickets</p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Reference</th>
                        <th>Citizen Name</th>
                        <th>User ID</th>
                        <th>Route</th>
                        <th>Issued At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tickets) > 0): ?>
                        <?php foreach ($tickets as $t): 
                            $statusClass = $t['status'] === 'Valid' ? 'status-validated' : 'status-rejected';
                        ?>
                            <tr>
                                <td style="color:var(--secondary-grey); font-size:0.78rem;"><?= htmlspecialchars($t['idTicket']) ?></td>
                                <td><span class="status-badge"><?= htmlspecialchars($t['ref']) ?></span></td>
                                <td><strong><?= htmlspecialchars($t['citizenName']) ?></strong></td>
                                <td><?= htmlspecialchars($t['idUser'] ?? 'Guest') ?></td>
                                <td><?= htmlspecialchars($t['departure']) ?> → <?= htmlspecialchars($t['destination']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($t['issuedAt'])) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                                <td>
                                    <?php if ($t['status'] === 'Valid'): ?>
                                        <form action="../../Verification.php" method="POST" style="display:inline;" onsubmit="return confirm('Cancel this ticket?');">
                                            <input type="hidden" name="action" value="cancelTicket">
                                            <input type="hidden" name="idTicket" value="<?= $t['idTicket'] ?>">
                                            <button type="submit" class="btn btn-small btn-danger">🚫 Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--secondary-grey); font-size:0.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; padding:30px;">🎟️ No tickets booked yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
