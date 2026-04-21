<?php
require_once '../../Controller/MainController.php';
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "ID not provided"; exit; }
$trajet = MainController::showTrajet($id);
if (!$trajet) { echo "Trajet not found"; exit; }

$transports = MainController::listTransports();
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .autocomplete-list { position:absolute; z-index:9999; background:#1a1a2e; border:1px solid rgba(255,255,255,0.1); border-radius:8px; max-height:200px; overflow-y:auto; width:100%; margin-top:2px; }
    .autocomplete-list div { padding:10px 14px; cursor:pointer; color:#e0e0e0; font-size:0.9rem; border-bottom:1px solid rgba(255,255,255,0.05); }
    .autocomplete-list div:hover { background:rgba(99,102,241,0.2); }
</style>

<main id="app">
    <section class="page-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
            <h2 style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                <a href="showTrajet.php" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                Edit Trajet
            </h2>
        </div>
        <div class="form-card">
            <form action="../../Verification.php" method="POST" id="trajetForm">
                <input type="hidden" name="action" value="updateTrajet">
                <input type="hidden" name="idTrajet" value="<?= $trajet['idTrajet'] ?>">
                <input type="hidden" name="depLat" id="depLat" value="<?= htmlspecialchars($trajet['depLat'] ?? '') ?>">
                <input type="hidden" name="depLng" id="depLng" value="<?= htmlspecialchars($trajet['depLng'] ?? '') ?>">
                <input type="hidden" name="depAddress" id="depAddress" value="<?= htmlspecialchars($trajet['depAddress'] ?? '') ?>">
                <input type="hidden" name="destLat" id="destLat" value="<?= htmlspecialchars($trajet['destLat'] ?? '') ?>">
                <input type="hidden" name="destLng" id="destLng" value="<?= htmlspecialchars($trajet['destLng'] ?? '') ?>">
                <input type="hidden" name="destAddress" id="destAddress" value="<?= htmlspecialchars($trajet['destAddress'] ?? '') ?>">

                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px; position:relative;">
                        <label for="departure">From (Departure)</label>
                        <input type="text" id="departure" name="departure" value="<?= htmlspecialchars($trajet['departure']) ?>" autocomplete="off">
                        <div id="depSuggestions" class="autocomplete-list" style="display:none;"></div>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px; position:relative;">
                        <label for="destination">To (Destination)</label>
                        <input type="text" id="destination" name="destination" value="<?= htmlspecialchars($trajet['destination']) ?>" autocomplete="off">
                        <div id="destSuggestions" class="autocomplete-list" style="display:none;"></div>
                    </div>
                </div>

                <!-- Interactive Map -->
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; margin-bottom:8px; font-size:0.82rem; font-weight:600; color:var(--secondary-grey); text-transform:uppercase; letter-spacing:0.3px;">Route Preview</label>
                    <div id="map" style="width:100%; height:380px; border-radius:12px; border:var(--border-main); background:#1a1a2e;"></div>
                    <p id="mapStatus" style="font-size:0.8rem; color:var(--secondary-grey); margin-top:6px;">Loading existing route...</p>
                </div>

                <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="idTransport">Assign Vehicle</label>
                        <select id="idTransport" name="idTransport">
                            <option value="">Select vehicle</option>
                            <?php foreach ($transports as $t): ?>
                                <?php if ($t['status'] === 'Active'): ?>
                                    <option value="<?= $t['idTransport'] ?>" <?= ($trajet['idTransport'] == $t['idTransport']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?> (<?= $t['typeName'] ?? $t['type'] ?>, <?= $t['capacity'] ?> seats)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label for="departureTime">Departure Time</label>
                        <input type="datetime-local" id="departureTime" name="departureTime" value="<?= date('Y-m-d\TH:i', strtotime($trajet['departureTime'])) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="price">Price (TND)</label>
                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($trajet['price']) ?>">
                </div>
                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="showTrajet.php" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Trajet</button>
                </div>
            </form>
        </div>
    </section>
</main>

<script src="validate.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, depMarker, destMarker, routeLine;

map = L.map('map').setView([36.8065, 10.1815], 7);
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CARTO',
    maxZoom: 19
}).addTo(map);

let debounceTimer;
function setupAutocomplete(inputId, suggestionsId, latId, lngId, addressId) {
    const input = document.getElementById(inputId);
    const sugBox = document.getElementById(suggestionsId);

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        if (query.length < 3) { sugBox.style.display = 'none'; return; }

        debounceTimer = setTimeout(() => {
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=5&addressdetails=1')
                .then(r => r.json())
                .then(results => {
                    sugBox.innerHTML = '';
                    if (results.length === 0) { sugBox.style.display = 'none'; return; }
                    results.forEach(place => {
                        const div = document.createElement('div');
                        div.textContent = place.display_name;
                        div.addEventListener('click', () => {
                            input.value = place.display_name.split(',').slice(0, 2).join(',');
                            document.getElementById(latId).value = place.lat;
                            document.getElementById(lngId).value = place.lon;
                            document.getElementById(addressId).value = place.display_name;
                            sugBox.style.display = 'none';
                            drawRoute();
                        });
                        sugBox.appendChild(div);
                    });
                    sugBox.style.display = 'block';
                })
                .catch(() => { sugBox.style.display = 'none'; });
        }, 350);
    });

    document.addEventListener('click', (e) => {
        if (e.target !== input) sugBox.style.display = 'none';
    });
}

setupAutocomplete('departure', 'depSuggestions', 'depLat', 'depLng', 'depAddress');
setupAutocomplete('destination', 'destSuggestions', 'destLat', 'destLng', 'destAddress');

function drawRoute() {
    const depLat = parseFloat(document.getElementById('depLat').value);
    const depLng = parseFloat(document.getElementById('depLng').value);
    const destLat = parseFloat(document.getElementById('destLat').value);
    const destLng = parseFloat(document.getElementById('destLng').value);

    if (isNaN(depLat) || isNaN(destLat)) return;

    if (depMarker) map.removeLayer(depMarker);
    if (destMarker) map.removeLayer(destMarker);
    if (routeLine) map.removeLayer(routeLine);

    depMarker = L.marker([depLat, depLng]).addTo(map).bindPopup('A — Departure').openPopup();
    destMarker = L.marker([destLat, destLng]).addTo(map).bindPopup('B — Destination');

    fetch('https://router.project-osrm.org/route/v1/driving/' + depLng + ',' + depLat + ';' + destLng + ',' + destLat + '?overview=full&geometries=geojson')
        .then(r => r.json())
        .then(data => {
            if (data.code === 'Ok' && data.routes.length > 0) {
                const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                routeLine = L.polyline(coords, { color: '#6366f1', weight: 5, opacity: 0.85 }).addTo(map);
                map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
                const dist = (data.routes[0].distance / 1000).toFixed(1);
                const dur = Math.round(data.routes[0].duration / 60);
                document.getElementById('mapStatus').textContent = '✓ Route — ' + dist + ' km, ' + dur + ' min';
                document.getElementById('mapStatus').style.color = '#22c55e';
            } else {
                map.fitBounds([[depLat, depLng], [destLat, destLng]], { padding: [40, 40] });
                document.getElementById('mapStatus').textContent = '⚠ Route unavailable. Markers placed.';
                document.getElementById('mapStatus').style.color = '#f59e0b';
            }
        })
        .catch(() => {
            map.fitBounds([[depLat, depLng], [destLat, destLng]], { padding: [40, 40] });
            document.getElementById('mapStatus').textContent = '⚠ Routing failed. Markers placed.';
            document.getElementById('mapStatus').style.color = '#f59e0b';
        });
}

// Draw existing route on load
drawRoute();
</script>
</body>
</html>
