<?php
/**
 * fix_controller_listeners.php
 * Injects transport click + submit handlers into BackOffice/controller.js
 */
$file = 'View/BackOffice/controller.js';
$content = file_get_contents($file);

// ── 1. Add transport CLICK handlers ──────────────────────────────────────────
$clickTransport = <<<'JS'
 } else if (action === 'new-transport-type') {
                view.renderTransportTypeForm();
            } else if (action === 'edit-transport-type') {
                model.getTransportTypes().then(types => {
                    const type = types.find(t => t.idTransportType == id);
                    view.renderTransportTypeForm(type);
                });
            } else if (action === 'delete-transport-type') {
                if (confirm('Delete this transport type?')) this.handleTransportTypeDelete(id);
            } else if (action === 'new-fleet') {
                model.getTransportTypes().then(types => view.renderFleetForm(null, types));
            } else if (action === 'edit-fleet') {
                Promise.all([model.getTransports(), model.getTransportTypes()]).then(([fleets, types]) => {
                    const fleet = fleets.find(f => f.idTransport == id);
                    view.renderFleetForm(fleet, types);
                });
            } else if (action === 'delete-fleet') {
                if (confirm('Delete this vehicle?')) this.handleFleetDelete(id);
            } else if (action === 'new-route') {
                model.getTransports().then(fleets => view.renderRouteForm(null, fleets));
            } else if (action === 'edit-route') {
                Promise.all([model.getTrajets(), model.getTransports()]).then(([trajets, fleets]) => {
                    const route = trajets.find(r => r.idTrajet == id);
                    view.renderRouteForm(route, fleets);
                });
            } else if (action === 'delete-route') {
                if (confirm('Delete this route?')) this.handleRouteDelete(id);
            } else if (action === 'cancel-ticket') {
                if (confirm('Cancel this ticket?')) this.handleTicketCancel(id);
            }
        });
JS;

// Replace the closing of the click block (the `}` before the hashchange listener)
$old_click_end = "            } else if (action === 'cancel-enroll') {\r\n                const progId = actionEl.dataset.programId;\r\n                this.handleEnrollmentUpdate(id, 'cancelled', progId);\r\n            }\r\n        });";
$new_click_end = "            } else if (action === 'cancel-enroll') {\r\n                const progId = actionEl.dataset.programId;\r\n                this.handleEnrollmentUpdate(id, 'cancelled', progId);" . $clickTransport;

$content = str_replace($old_click_end, $new_click_end, $content);

// ── 2. Add transport SUBMIT handlers ─────────────────────────────────────────
$old_submit_end = "            } else if (e.target.id === 'program-form') {\r\n                this.handleProgramSave(new FormData(e.target));\r\n            }\r\n        });";
$new_submit_end = "            } else if (e.target.id === 'program-form') {\r\n                this.handleProgramSave(new FormData(e.target));\r\n            } else if (e.target.id === 'transport-type-form') {\r\n                this.handleTransportTypeSave(new FormData(e.target));\r\n            } else if (e.target.id === 'fleet-form') {\r\n                this.handleFleetSave(new FormData(e.target));\r\n            } else if (e.target.id === 'route-form') {\r\n                this.handleRouteSave(new FormData(e.target));\r\n            }\r\n        });";

$content = str_replace($old_submit_end, $new_submit_end, $content);

$written = file_put_contents($file, $content);
echo $written ? "controller.js patched successfully ($written bytes)\n" : "ERROR: nothing was written!\n";

// Verify
echo strpos($content, 'new-transport-type') !== false ? "✓ Click handlers present\n" : "✗ Click handlers MISSING\n";
echo strpos($content, 'transport-type-form') !== false ? "✓ Submit handlers present\n" : "✗ Submit handlers MISSING\n";
