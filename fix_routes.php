<?php
/**
 * Add transport route cases to BackOffice controller.js handleRouting() switch.
 */
$file = 'View/BackOffice/controller.js';
$content = file_get_contents($file);

// Find the exact insertion point: after `#manage-programs` case block, before `default:`
$transportRoutes = <<<'JS'
            case '#transport-dashboard':
                if (user.role === 'admin') view.renderTransportDashboard(); else window.location.hash = '#home';
                break;
            case '#transport-types':
                if (user.role === 'admin') view.renderTransportTypes(await model.getTransportTypes() || []); else window.location.hash = '#home';
                break;
            case '#fleet':
                if (user.role === 'admin') view.renderFleet(await model.getTransports() || []); else window.location.hash = '#home';
                break;
            case '#routes':
                if (user.role === 'admin') view.renderRoutes(await model.getTrajets() || []); else window.location.hash = '#home';
                break;
            case '#admin-tickets':
                if (user.role === 'admin') view.renderAdminTickets(await model.getTickets() || []); else window.location.hash = '#home';
                break;
JS;

// Insert before `            default:`
$content = str_replace(
    "            default:\r\n                view.renderHome(user);",
    $transportRoutes . "\r\n            default:\r\n                view.renderHome(user);",
    $content
);

file_put_contents($file, $content);
echo "Transport routes added to controller.js!\n";
