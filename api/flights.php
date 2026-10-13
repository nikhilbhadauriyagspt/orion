<?php
/**
 * Real-time Flight Search & Aggregator API Controller
 * Supports:
 * 1. Amadeus Flight Offers API (Live global GDS when API Key is active in settings)
 * 2. High-Fidelity Multi-Airline Schedule Engine (IndiGo, Air India, Akasa, Vistara, Emirates, SpiceJet)
 * 3. Exact MakeMyTrip / EaseMyTrip Structured JSON responses
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/flights_service.php';

$tripType = isset($_GET['trip']) ? strtolower(trim($_GET['trip'])) : 'oneway';
$origin = isset($_GET['from']) ? strtoupper(trim($_GET['from'])) : 'DEL';
$destination = isset($_GET['to']) ? strtoupper(trim($_GET['to'])) : 'BOM';
$departureDate = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d', strtotime('+3 days'));
$returnDate = isset($_GET['return_date']) ? trim($_GET['return_date']) : null;
$cabinClass = isset($_GET['class']) ? trim($_GET['class']) : 'ECONOMY';
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;

$legs = [];
if ($tripType === 'multicity') {
    if (isset($_GET['legs']) && is_string($_GET['legs'])) {
        $decoded = json_decode($_GET['legs'], true);
        if (is_array($decoded)) $legs = $decoded;
    }
    if (empty($legs)) {
        // Collect from GET query params
        $legs[] = ['from' => $origin, 'to' => $destination, 'date' => $departureDate];
        if (isset($_GET['to2']) || isset($_GET['from2'])) {
            $legs[] = [
                'from' => isset($_GET['from2']) ? strtoupper(trim($_GET['from2'])) : $destination,
                'to' => isset($_GET['to2']) ? strtoupper(trim($_GET['to2'])) : 'GOX',
                'date' => isset($_GET['date2']) ? trim($_GET['date2']) : date('Y-m-d', strtotime('+3 days', strtotime($departureDate)))
            ];
        }
        if (isset($_GET['to3']) || isset($_GET['from3'])) {
            $legs[] = [
                'from' => isset($_GET['from3']) ? strtoupper(trim($_GET['from3'])) : ($legs[1]['to'] ?? 'GOX'),
                'to' => isset($_GET['to3']) ? strtoupper(trim($_GET['to3'])) : $origin,
                'date' => isset($_GET['date3']) ? trim($_GET['date3']) : date('Y-m-d', strtotime('+6 days', strtotime($departureDate)))
            ];
        }
    }
}

$result = searchFlightsService($origin, $destination, $departureDate, $cabinClass, $adults, $tripType, $returnDate, $legs);

$response = [
    'success' => true,
    'trip_type' => $result['trip_type'],
    'count' => $result['count'],
    'search_params' => [
        'trip' => $result['trip_type'],
        'origin' => $origin,
        'destination' => $destination,
        'departure_date' => $departureDate,
        'return_date' => $returnDate,
        'class' => $cabinClass,
        'adults' => $adults
    ],
    'data' => $result['data']
];

if ($result['trip_type'] === 'roundtrip') {
    $response['outbound'] = $result['outbound'];
    $response['return'] = $result['return'];
    $response['combos'] = $result['combos'];
    $response['departure_date'] = $result['departure_date'];
    $response['return_date'] = $result['return_date'];
    $response['fromInfo'] = $result['fromInfo'];
    $response['toInfo'] = $result['toInfo'];
} elseif ($result['trip_type'] === 'multicity') {
    $response['legs'] = $result['legs'];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

