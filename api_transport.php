<?php
/**
 * api_transport.php
 * Dedicated API router for the Transport module.
 * Session-aware: ticket queries are scoped to the logged-in user.
 * Uses the Database singleton (no legacy config.php dependency).
 */

/**
 * SECURITY improvements:
 *  • bootstrap.php provides HttpOnly/SameSite session cookies and suppresses error display.
 *  • citizenName is length-validated; idTrajet is cast and range-checked before use.
 *  • Exceptions are logged server-side; only a generic message reaches the client.
 */
require_once __DIR__ . '/bootstrap.php';
define('_CIVICPORTAL_BOOTSTRAP_', true);

header('Content-Type: application/json');

require_once __DIR__ . '/Controller/MainController.php';

function fetchUrlContent(string $url): ?string {
    if (!function_exists('curl_version')) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (CivicPortal/1.0)',
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_errno($ch);
    curl_close($ch);

    if ($body === false || $error || $status >= 400) {
        return null;
    }

    return $body;
}

function parseInternetPrices(string $html): array {
    $prices = [];

    // Match prices with thousands separators: "1,000 DT", "1.000 DT", "1000 DT", "1,50 DT", etc.
    if (preg_match_all('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?|\d+(?:[.,]\d{1,2})?)\s*(?:TND|DT|dinars?|د\.ت)/iu', $html, $matches)) {
        foreach ($matches[1] as $raw) {
            // Remove thousands separators before the decimal, keep only the decimal part
            $normalized = preg_replace('/[.,](?=\d{3}(?:[^\d]|$))/', '', $raw);
            // Replace comma/dot with dot for float conversion
            $normalized = str_replace(',', '.', $normalized);
            $prices[] = (float) $normalized;
        }
    }

    // Also try reverse pattern: "DT 1,000" or "د.ت 1000"
    if (empty($prices) && preg_match_all('/(?:TND|DT|د\.ت)\s+(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?|\d+(?:[.,]\d{1,2})?)/iu', $html, $matches)) {
        foreach ($matches[1] as $raw) {
            $normalized = preg_replace('/[.,](?=\d{3}(?:[^\d]|$))/', '', $raw);
            $normalized = str_replace(',', '.', $normalized);
            $prices[] = (float) $normalized;
        }
    }

    // Filter out unrealistic prices  
    $prices = array_filter($prices, function($p) { return $p >= 1.0 && $p <= 500; });

    return array_values(array_unique($prices));
}

function buildInternetSearchQueries(string $transportType, string $departure, string $destination): array {
    $queries = [];
    
    // French-specific queries for Tunisian context
    $transportLabel = strtolower($transportType);
    
    if (strpos($transportLabel, 'bus') !== false || strpos($transportLabel, 'autocar') !== false) {
        $queries[] = "{$departure} {$destination} bus prix 2026";
        $queries[] = "tarif bus {$departure} {$destination} Tunisie";
        $queries[] = "prix trajet autocar {$departure} {$destination}";
    } elseif (strpos($transportLabel, 'train') !== false || strpos($transportLabel, 'tgm') !== false) {
        $queries[] = "{$departure} {$destination} train prix 2026";
        $queries[] = "TGM tarif {$departure} {$destination}";
    } elseif (strpos($transportLabel, 'flight') !== false || strpos($transportLabel, 'plane') !== false) {
        // International flight queries with better coverage
        $queries[] = "flight price {$departure} {$destination} 2026";
        $queries[] = "airline ticket {$departure} to {$destination} TND";
        $queries[] = "cheapest flight {$departure} {$destination}";
        $queries[] = "vol {$departure} {$destination} prix Tunisie";
        $queries[] = "Tunisair {$departure} {$destination}";
    } elseif (strpos($transportLabel, 'ferry') !== false) {
        $queries[] = "ferry {$departure} {$destination} prix Tunisie";
    } else {
        $queries[] = "{$transportType} {$departure} {$destination} prix Tunisie";
        $queries[] = "{$transportType} {$departure} {$destination} tarif 2026";
    }
    
    return $queries;
}

function searchInternetRoutePrice(string $transportType, string $departure, string $destination): array {
    $queries = buildInternetSearchQueries($transportType, $departure, $destination);
    $allPrices = [];
    $searchSources = [];
    
    foreach ($queries as $query) {
        $url = 'https://html.duckduckgo.com/html/?q=' . urlencode($query);
        $html = fetchUrlContent($url);
        if ($html === null) {
            continue;
        }

        $prices = parseInternetPrices($html);
        if (empty($prices)) {
            continue;
        }

        $transportLabel = strtolower($transportType);
        if (strpos($transportLabel, 'flight') !== false || strpos($transportLabel, 'plane') !== false) {
            $prices = array_filter($prices, fn($p) => $p >= 30 && $p <= 500);
        } else {
            $prices = array_filter($prices, fn($p) => $p >= 1 && $p <= 500);
        }

        if (empty($prices)) {
            continue;
        }

        $allPrices = array_merge($allPrices, array_values($prices));
        $searchSources[] = htmlspecialchars($query);
        // Accept first query that yields valid prices
        break;
    }

    $allPrices = array_unique($allPrices);
    sort($allPrices);
    
    // Apply transport-type-specific minimum prices
    $transportLabel = strtolower($transportType);
    $minimumPrice = 0.5;
    
    if (strpos($transportLabel, 'flight') !== false || strpos($transportLabel, 'plane') !== false) {
        // For flights, use higher minimums - these should come from internet
        // If we found prices, use them; otherwise return null to trigger fallback
        if (!empty($allPrices)) {
            // Filter out unrealistic flight prices (flights shouldn't be < 30 TND or > 500 TND)
            $allPrices = array_filter($allPrices, function($p) { return $p >= 30 && $p <= 500; });
            $allPrices = array_values($allPrices);
            
            if (empty($allPrices)) {
                // All prices were filtered as unrealistic
                return [
                    'price' => null,
                    'prices' => [],
                    'minPrice' => null,
                    'maxPrice' => null,
                    'source' => 'DuckDuckGo (Internet search)',
                    'note' => 'No realistic flight prices found online. Please search manually.'
                ];
            }
        }
    } else if (strpos($transportLabel, 'bus') !== false) {
        $minimumPrice = 0.5;
    } else if (strpos($transportLabel, 'train') !== false || strpos($transportLabel, 'tgm') !== false) {
        $minimumPrice = 0.5;
    }
    
    if (!empty($allPrices)) {
        $minPrice = min($allPrices);
        $maxPrice = max($allPrices);
        $note = count($allPrices) === 1 
            ? "Internet price: {$minPrice} TND"
            : "Internet prices found: {$minPrice} - {$maxPrice} TND (using minimum)";
        
        return [
            'price' => $minPrice,
            'prices' => $allPrices,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'source' => 'DuckDuckGo (Internet search)',
            'note' => $note
        ];
    }

    return [
        'price' => null,
        'prices' => [],
        'minPrice' => null,
        'maxPrice' => null,
        'source' => 'DuckDuckGo (Internet search)',
        'note' => 'No price information found on the internet for this route.'
    ];
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

$action = $input['action'];

try { switch ($action) {

        // ============================================
        // TRANSPORT TYPES
        // ============================================
        case 'list_transport_types':
            $types = AppModel::listTransportTypes();
            echo json_encode(['success' => true, 'data' => $types]);
            break;

        // ============================================
        // TRANSPORT VEHICLES
        // ============================================
        case 'list_transports':
            $transports = AppModel::listTransports();
            echo json_encode(['success' => true, 'data' => $transports]);
            break;

        case 'get_transport':
            $transport = AppModel::showTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true, 'data' => $transport]);
            break;

        case 'search_route_price':
            $transportType = trim((string)($input['transportType'] ?? ''));
            $departure = trim((string)($input['departure'] ?? ''));
            $destination = trim((string)($input['destination'] ?? ''));
            if ($departure === '' || $destination === '') {
                echo json_encode(['success' => false, 'error' => 'Departure and destination are required for internet price search.']);
                break;
            }

            $searchResult = searchInternetRoutePrice($transportType, $departure, $destination);
            echo json_encode(['success' => true, 'data' => $searchResult]);
            break;

        case 'add_transport':
            $transport = [
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => (int)$input['capacity'],
                'status' => $input['status'],
                'idTransportType' => !empty($input['idTransportType']) ? (int)$input['idTransportType'] : null
            ];
            AppModel::addTransport($transport);
            echo json_encode(['success' => true]);
            break;

        case 'update_transport':
            $transport = [
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => (int)$input['capacity'],
                'status' => $input['status'],
                'idTransportType' => !empty($input['idTransportType']) ? (int)$input['idTransportType'] : null
            ];
            AppModel::updateTransport((int)$input['idTransport'], $transport);
            echo json_encode(['success' => true]);
            break;

        case 'delete_transport':
            AppModel::deleteTransport((int)($input['idTransport'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TRAJETS (ROUTES)
        // ============================================
        case 'list_all_trajets':
            $trajets  = AppModel::listTrajets();
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = AppModel::getOccupancy($t['idTrajet']);
                $enriched[] = array_merge($t, ['capacity' => $occ['capacity'], 'sold' => $occ['sold']]);
            }
            echo json_encode(['success' => true, 'data' => $enriched]);
            break;

        case 'list_trajets':
            $type   = $input['type']    ?? 'Bus';
            $sortBy = $input['sortBy']  ?? 'departure';
            $order  = $input['order']   ?? 'ASC';

            $trajets  = AppModel::listTrajetsByTypeAndSort($type, $sortBy, $order);
            $enriched = [];
            foreach ($trajets as $t) {
                $occ        = AppModel::getOccupancy($t['idTrajet']);
                $enriched[] = [
                    'idTrajet'      => $t['idTrajet'],
                    'departure'     => $t['departure'],
                    'destination'   => $t['destination'],
                    'departureTime' => $t['departureTime'],
                    'price'         => $t['price'],
                    'transportName' => $t['transportName'],
                    'capacity'      => $occ['capacity'],
                    'sold'          => $occ['sold'],
                    'depLat'        => $t['depLat']     ?? null,
                    'depLng'        => $t['depLng']     ?? null,
                    'depAddress'    => $t['depAddress'] ?? null,
                    'destLat'       => $t['destLat']    ?? null,
                    'destLng'       => $t['destLng']    ?? null,
                    'destAddress'   => $t['destAddress'] ?? null,
                ];
            }
            echo json_encode(['success' => true, 'data' => $enriched]);
            break;

        case 'add_trajet':
            $trajet = [
                'departure'     => $input['departure'],
                'destination'   => $input['destination'],
                'idTransport'   => (int)$input['idTransport'],
                'departureTime' => $input['departureTime'],
                'price'         => (float)$input['price'],
                'depLat'        => isset($input['depLat']) ? (float)$input['depLat'] : null,
                'depLng'        => isset($input['depLng']) ? (float)$input['depLng'] : null,
                'depAddress'    => $input['depAddress'] ?? null,
                'destLat'       => isset($input['destLat']) ? (float)$input['destLat'] : null,
                'destLng'       => isset($input['destLng']) ? (float)$input['destLng'] : null,
                'destAddress'   => $input['destAddress'] ?? null
            ];
            AppModel::addTrajet($trajet);
            echo json_encode(['success' => true]);
            break;

        case 'get_trajet':
            $trajet = AppModel::getTrajet((int)($input['idTrajet'] ?? 0));
            echo json_encode(['success' => true, 'data' => $trajet]);
            break;

        case 'update_trajet':
            $trajet = [
                'departure'     => $input['departure'],
                'destination'   => $input['destination'],
                'idTransport'   => (int)$input['idTransport'],
                'departureTime' => $input['departureTime'],
                'price'         => (float)$input['price'],
                'depLat'        => isset($input['depLat']) ? (float)$input['depLat'] : null,
                'depLng'        => isset($input['depLng']) ? (float)$input['depLng'] : null,
                'depAddress'    => $input['depAddress'] ?? null,
                'destLat'       => isset($input['destLat']) ? (float)$input['destLat'] : null,
                'destLng'       => isset($input['destLng']) ? (float)$input['destLng'] : null,
                'destAddress'   => $input['destAddress'] ?? null
            ];
            AppModel::updateTrajet((int)$input['idTrajet'], $trajet);
            echo json_encode(['success' => true]);
            break;

        case 'delete_trajet':
            AppModel::deleteTrajet((int)($input['idTrajet'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        // ============================================
        // TICKETS
        // ============================================
        case 'list_tickets':
            $tickets = AppModel::listTickets();
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'list_tickets_enriched':
            // Front-office: scope to logged-in citizen only
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $tickets = AppModel::listTicketsEnriched($userId);
            echo json_encode(['success' => true, 'data' => $tickets]);
            break;

        case 'book_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }

            $idTrajet    = (int)($input['idTrajet'] ?? 0);
            $citizenName = trim($input['citizenName'] ?? '');
            $user_id     = (int)$_SESSION['user_id'];

            // SECURITY: server-side validation — never trust client-supplied lengths.
            // idTrajet must be a positive integer; citizenName is bounded to DB column width.
            if ($idTrajet <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid route ID.']);
                break;
            }
            if ($citizenName === '' || mb_strlen($citizenName) > 255) {
                echo json_encode(['success' => false, 'error' => 'Invalid passenger name.']);
                break;
            }

            $occ = AppModel::getOccupancy($idTrajet);
            if ($occ['capacity'] > 0 && $occ['sold'] >= $occ['capacity']) {
                echo json_encode(['success' => false, 'error' => 'Route is sold out.']);
                break;
            }

            $data = [
                'user_id'     => $user_id,
                'citizenName' => $citizenName,
                'idTrajet'    => $idTrajet
            ];
            AppModel::addTicket($data);
            echo json_encode(['success' => true]);
            break;

        case 'cancel_ticket':
            if (empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
                break;
            }
            AppModel::cancelTicket((int)($input['idTicket'] ?? 0));
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action.']);

} } catch (Exception $e) {
    // SECURITY: log full detail; return only a safe message to the client.
    error_log('[CivicPortal][Transport] ' . $e->getMessage()
        . ' | File: ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    $clientMessage = APP_DEBUG ? $e->getMessage() : 'A server error occurred.';
    echo json_encode(['success' => false, 'error' => $clientMessage]);
}
?>
