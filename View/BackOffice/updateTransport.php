<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "ID not provided"; exit; }
$t = MainController::showTransport($id);
if (!$t) { echo "Transport not found"; exit; }
$transportTypes = MainController::listTransportTypes();
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
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($t['name']) ?>">
                </div>
                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="idTransportType">Type</label>
                        <select id="idTransportType" name="idTransportType">
                            <option value="">Select type</option>
                            <?php foreach ($transportTypes as $tt): ?>
                                <option value="<?= $tt['idTransportType'] ?>" <?= ($t['idTransportType'] == $tt['idTransportType']) ? 'selected' : '' ?>><?= htmlspecialchars($tt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="type" name="type" value="<?= htmlspecialchars($t['type']) ?>">
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="capacity">Capacity (seats)</label>
                        <input type="number" id="capacity" name="capacity" value="<?= htmlspecialchars($t['capacity']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
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
<script src="validate.js"></script>
<script>
document.getElementById('idTransportType').addEventListener('change', function() {
    document.getElementById('type').value = this.options[this.selectedIndex].text;
});
</script>
</body>
</html>
