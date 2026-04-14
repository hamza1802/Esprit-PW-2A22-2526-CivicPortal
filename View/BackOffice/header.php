<?php
/**
 * BackOffice Header
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Staff Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="nav--staff">
        <div class="nav-brand nav-brand--staff">
            CivicPortal
        </div>
        <ul class="nav-links">
            <li><a href="index.php">home</a></li>
            <li><a href="showTransport.php">transports</a></li>
            <li><a href="showTrajet.php">trajets</a></li>
            <li><a href="showTicket.php">tickets</a></li>
        </ul>
        <div class="user-controls">
            <a href="../FrontOffice/index.php" class="btn btn-small" style="text-decoration:none; border-color: var(--primary-navy);">FrontOffice</a>
            <div class="user-role-badge user-role-badge--staff">admin</div>
        </div>
    </nav>
