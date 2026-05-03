/**
 * map.js
 * Handles Leaflet.js map integration with OSRM routing and Nominatim autocomplete.
 */
import { CONFIG } from './config.js';

export function initRouteMap() {
    const mapContainer = document.getElementById('route-map');
    if (!mapContainer) return;

    if (mapContainer._leaflet_id) {
        return;
    }

    const map = L.map('route-map').setView(CONFIG.MAP_DEFAULT_CENTER, CONFIG.MAP_DEFAULT_ZOOM);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        maxZoom: 19
    }).addTo(map);

    let depMarker, destMarker, routeLine;
    let debounceTimer;

    // We will add autocomplete suggestions containers dynamically right after the inputs
    function ensureSuggestionsBox(inputId, sugId) {
        const input = document.getElementById(inputId);
        if (!input) return null;
        let sugBox = document.getElementById(sugId);
        if (!sugBox) {
            sugBox = document.createElement('div');
            sugBox.id = sugId;
            sugBox.style.cssText = 'position:absolute; z-index:9999; background:#fff; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow-y:auto; width:calc(100% - 2rem); margin-top:2px; display:none; color:black;';
            input.parentNode.style.position = 'relative';
            input.parentNode.appendChild(sugBox);
        }
        return sugBox;
    }

    function setupAutocomplete(inputId, suggestionsId, latId, lngId) {
        const input = document.getElementById(inputId);
        const sugBox = ensureSuggestionsBox(inputId, suggestionsId);
        if (!input || !sugBox) return;

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
                            div.style.cssText = 'padding:8px; cursor:pointer; border-bottom:1px solid #eee; font-size:0.9rem;';
                            div.addEventListener('mouseover', () => div.style.backgroundColor = '#f0f0f0');
                            div.addEventListener('mouseout', () => div.style.backgroundColor = 'transparent');
                            div.addEventListener('click', () => {
                                input.value = place.display_name.split(',').slice(0, 2).join(',');
                                document.getElementById(latId).value = place.lat;
                                document.getElementById(lngId).value = place.lon;
                                // Set address field
                                const addressId = latId.replace('Lat', 'Address').replace('Lng', 'Address');
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
            if (e.target !== input && e.target !== sugBox) {
                sugBox.style.display = 'none';
            }
        });
    }

    function drawRoute() {
        const depLat = parseFloat(document.getElementById('depLat').value);
        const depLng = parseFloat(document.getElementById('depLng').value);
        const destLat = parseFloat(document.getElementById('destLat').value);
        const destLng = parseFloat(document.getElementById('destLng').value);

        if (isNaN(depLat) || isNaN(destLat)) return;

        if (depMarker) map.removeLayer(depMarker);
        if (destMarker) map.removeLayer(destMarker);
        if (routeLine) map.removeLayer(routeLine);

        depMarker = L.marker([depLat, depLng]).addTo(map).bindPopup('Departure').openPopup();
        destMarker = L.marker([destLat, destLng]).addTo(map).bindPopup('Destination');

        fetch('https://router.project-osrm.org/route/v1/driving/' + depLng + ',' + depLat + ';' + destLng + ',' + destLat + '?overview=full&geometries=geojson')
            .then(r => r.json())
            .then(data => {
                if (data.code === 'Ok' && data.routes.length > 0) {
                    const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    routeLine = L.polyline(coords, { color: '#6366f1', weight: 5, opacity: 0.85 }).addTo(map);
                    map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
                } else {
                    map.fitBounds([[depLat, depLng], [destLat, destLng]], { padding: [40, 40] });
                }
            })
            .catch(() => {
                map.fitBounds([[depLat, depLng], [destLat, destLng]], { padding: [40, 40] });
            });
    }

    // Set input IDs according to the new form in view.js
    const depInputId = document.querySelector('input[name="departure"]').id || 'prog-dep';
    const destInputId = document.querySelector('input[name="destination"]').id || 'prog-dest';
    document.querySelector('input[name="departure"]').id = depInputId;
    document.querySelector('input[name="destination"]').id = destInputId;

    setupAutocomplete(depInputId, 'depSuggestions', 'depLat', 'depLng');
    setupAutocomplete(destInputId, 'destSuggestions', 'destLat', 'destLng');

    setTimeout(() => {
        map.invalidateSize();
    }, 100);
}
