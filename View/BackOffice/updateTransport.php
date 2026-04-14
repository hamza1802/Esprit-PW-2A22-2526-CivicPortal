<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID not provided";
    exit;
}
$t = MainController::showTransport($id);
if (!$t) {
    echo "Transport not found";
    exit;
}
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTransport.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Edit Vehicle
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST">
                <input type="hidden" name="action" value="updateTransport">
                <input type="hidden" name="idTransport" value="<?= $t['idTransport'] ?>">
                <div class="form-group">
                    <label for="name">Vehicle Name / Label</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($t['name']) ?>" required>
                </div>
                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="Plane" <?= $t['type'] === 'Plane' ? 'selected' : '' ?>>Plane</option>
                            <option value="Bus" <?= $t['type'] === 'Bus' ? 'selected' : '' ?>>Bus</option>
                            <option value="Train" <?= $t['type'] === 'Train' ? 'selected' : '' ?>>Train</option>
                            <option value="Metro" <?= $t['type'] === 'Metro' ? 'selected' : '' ?>>Metro</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="capacity">Capacity (seats)</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="500" value="<?= htmlspecialchars($t['capacity']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active" <?= $t['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Maintenance" <?= $t['status'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="Retired" <?= $t['status'] === 'Retired' ? 'selected' : '' ?>>Retired</option>
                    </select>
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTransport.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Vehicle</button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
