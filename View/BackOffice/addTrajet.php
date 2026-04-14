<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$transports = MainController::listTransports();
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTrajet.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Add New Trajet
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST">
                <input type="hidden" name="action" value="addTrajet">
                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="departure">From (Departure)</label>
                        <input type="text" id="departure" name="departure" placeholder="e.g. Tunis" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="destination">To (Destination)</label>
                        <input type="text" id="destination" name="destination" placeholder="e.g. Marsa" required>
                    </div>
                </div>
                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="idTransport">Assign Vehicle</label>
                        <select id="idTransport" name="idTransport" required>
                            <option value="">Select vehicle</option>
                            <?php foreach ($transports as $t): ?>
                                <?php if ($t['status'] === 'Active'): ?>
                                    <option value="<?= $t['idTransport'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $t['type'] ?>, <?= $t['capacity'] ?> seats)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="departureTime">Departure Time</label>
                        <input type="datetime-local" id="departureTime" name="departureTime" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="price">Price (TND)</label>
                    <input type="number" id="price" name="price" min="0" step="0.1" placeholder="e.g. 2.500" required>
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTrajet.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Trajet</button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
