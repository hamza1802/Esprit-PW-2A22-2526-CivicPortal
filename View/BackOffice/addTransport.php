<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';
$transportTypes = MainController::listTransportTypes();
?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTransport.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Add New Vehicle
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST">
                <input type="hidden" name="action" value="addTransport">
                <div class="form-group">
                    <label for="name">Vehicle Name / Label</label>
                    <input type="text" id="name" name="name" placeholder="e.g. Bus #001" required>
                </div>
                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="idTransportType">Type</label>
                        <select id="idTransportType" name="idTransportType" required>
                            <option value="">Select type</option>
                            <?php foreach ($transportTypes as $tt): ?>
                                <option value="<?= $tt['idTransportType'] ?>"><?= htmlspecialchars($tt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Hidden field: auto-filled by JS for backward compat with transport.type column -->
                        <input type="hidden" id="type" name="type" value="">
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="capacity">Capacity (seats)</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="500" placeholder="e.g. 50" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Retired">Retired</option>
                    </select>
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTransport.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Vehicle</button>
                </div>
            </form>
        </div>
    </section>
</main>
<script>
// Sync hidden type field with transport type name
document.getElementById('idTransportType').addEventListener('change', function() {
    document.getElementById('type').value = this.options[this.selectedIndex].text;
});
</script>
</body>
</html>
