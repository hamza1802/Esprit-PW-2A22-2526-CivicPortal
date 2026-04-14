<?php
/**
 * BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal
 */
require_once '../../Model/AppModel.php';
AppModel::init();
require_once 'header.php';
?>

<main id="app">
    <div class="hero-container">
        <section class="hero-section">
            <h1>Staff Portal</h1>
            <p>Welcome back, Admin. Manage transport fleet, routes, and tickets.</p>
        </section>
    </div>
    <section class="page-container">
        <h2>Transport Management</h2>
        <div class="editorial-grid">
            <div class="editorial-card editorial-highlight">
                <h3>🚐 Fleet</h3>
                <p>View and manage the municipality's physical vehicles — planes, buses, trains, and metros.</p>
                <a href="showTransport.php" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Manage Fleet</a>
            </div>
            <div class="editorial-card">
                <h3>🗺️ Routes</h3>
                <p>Schedule trips by assigning vehicles to routes with departure times and pricing.</p>
                <a href="showTrajet.php" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Routes</a>
            </div>
            <div class="editorial-card">
                <h3>🎟️ Tickets</h3>
                <p>Review all transport tickets booked by citizens. Cancel invalid bookings.</p>
                <a href="showTicket.php" class="btn" style="align-self: flex-start; margin-top: auto;">View Tickets</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
