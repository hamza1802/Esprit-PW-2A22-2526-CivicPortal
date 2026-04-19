<?php require_once 'header.php'; ?>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTransportType.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Add Transport Type
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addTransportType">
                <div class="form-group">
                    <label for="name">Type Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g. Bus, Plane, Ferry" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Brief description of this transport type..." style="width:100%; padding:10px 14px; background:var(--bg-dark, #1a1a2e); border:var(--border-main); border-radius:8px; color:inherit; font-family:inherit; font-size:0.95rem; resize:vertical;"></textarea>
                </div>
                <div class="form-group">
                    <label for="photo">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" style="border:none; padding:1rem 0;">
                    <p style="font-size:0.8rem; color:var(--secondary-grey); margin-top:4px;">Accepted: JPG, PNG, GIF, WebP</p>
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTransportType.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Type</button>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
