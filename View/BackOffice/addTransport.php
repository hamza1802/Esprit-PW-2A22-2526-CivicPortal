<?php require_once 'header.php'; ?>

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
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select type</option>
                            <option value="Plane">Plane</option>
                            <option value="Bus">Bus</option>
                            <option value="Train">Train</option>
                            <option value="Metro">Metro</option>
                        </select>
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
</body>
</html>
