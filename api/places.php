<?php
/**
 * Places / Locations Live Autocomplete Proxy API - Orion Advent
 * Supports:
 * 1. OpenStreetMap Photon Geocoding API (Default, 100% Free & Unlimited)
 * 2. Google Places Autocomplete API (When Google Key is enabled in Settings)
 * 3. Mapbox Geocoding API (When Mapbox Key is enabled)
 * 4. Database-Integrated Package & Hotel Availability Matching
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$searchType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'package';

$settings = getGlobalSettings();
$provider = isset($settings['location_provider']) ? $settings['location_provider'] : 'osm';
$googleKey = isset($settings['google_places_api_key']) ? trim($settings['google_places_api_key']) : '';
$mapboxKey = isset($settings['mapbox_api_key']) ? trim($settings['mapbox_api_key']) : '';
$locationiqKey = isset($settings['locationiq_api_key']) ? trim($settings['locationiq_api_key']) : '';

$pdo = getDBConnection();

// Helper: Check database package and hotel count for a place name
function checkDbAvailability($pdo, $name) {
    $res = ['packages' => 0, 'hotels' => 0];
    if (!$pdo || empty($name)) return $res;
    try {
        $term = '%' . $name . '%';
        $stmtPkg = $pdo->prepare("SELECT COUNT(*) FROM `packages` WHERE `status` = 'active' AND (`title` LIKE :term1 OR `location` LIKE :term2 OR `state_country` LIKE :term3)");
        $stmtPkg->execute([':term1' => $term, ':term2' => $term, ':term3' => $term]);
        $res['packages'] = (int)$stmtPkg->fetchColumn();

        $stmtHtl = $pdo->prepare("SELECT COUNT(*) FROM `hotels` WHERE `status` = 'active' AND (`name` LIKE :term1 OR `city` LIKE :term2 OR `state` LIKE :term3 OR `country` LIKE :term4)");
        $stmtHtl->execute([':term1' => $term, ':term2' => $term, ':term3' => $term, ':term4' => $term]);
        $res['hotels'] = (int)$stmtHtl->fetchColumn();
    } catch (Exception $e) {}
    return $res;
}

// Case A: Query is empty (< 2 chars) -> Return top popular/featured destinations
if (strlen($query) < 2) {
    $defaultDestinations = [];

    if ($pdo) {
        try {
            // Fetch distinct locations from active packages
            $sql = "SELECT id, title, location, state_country, category, badge FROM `packages` WHERE `status` = 'active'";
            if ($category === 'domestic') {
                $sql .= " AND `category` = 'domestic'";
            } elseif ($category === 'international') {
                $sql .= " AND `category` = 'international'";
            }
            $sql .= " ORDER BY `id` DESC LIMIT 6";
            $dbPkgs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbPkgs as $p) {
                // Pick primary city / place from location
                $locParts = explode(',', $p['location']);
                $primaryName = trim($locParts[0] ?? $p['title']);
                $stateParts = explode(',', $p['state_country'] ?? '');
                $country = trim(end($stateParts)) ?: ($p['category'] === 'international' ? 'International' : 'India');

                $defaultDestinations[] = [
                    'name' => $primaryName,
                    'full_address' => ($p['location'] ?: $primaryName) . ' • ' . ($p['state_country'] ?: $country),
                    'city' => $primaryName,
                    'state' => trim($stateParts[0] ?? ''),
                    'country' => $country,
                    'has_packages' => true,
                    'package_count' => 1,
                    'has_hotels' => false,
                    'hotel_count' => 0,
                    'badge' => $p['badge'] ?: 'Top Tour',
                    'source' => 'db'
                ];
            }
        } catch (Exception $e) {}
    }

    // Fallback defaults if DB is empty
    if (empty($defaultDestinations)) {
        if ($category === 'international') {
            $defaultDestinations = [
                ['name' => 'Dubai', 'full_address' => 'Dubai, United Arab Emirates', 'city' => 'Dubai', 'state' => 'Dubai', 'country' => 'United Arab Emirates', 'has_packages' => true, 'badge' => 'Bestseller', 'source' => 'default'],
                ['name' => 'Bali', 'full_address' => 'Bali, Indonesia', 'city' => 'Bali', 'state' => 'Bali', 'country' => 'Indonesia', 'has_packages' => true, 'badge' => 'Popular', 'source' => 'default'],
                ['name' => 'Thailand', 'full_address' => 'Bangkok & Phuket, Thailand', 'city' => 'Bangkok', 'state' => 'Central', 'country' => 'Thailand', 'has_packages' => false, 'badge' => 'Trending', 'source' => 'default'],
                ['name' => 'Maldives', 'full_address' => 'Malé, Maldives', 'city' => 'Malé', 'state' => 'Kaafu Atoll', 'country' => 'Maldives', 'has_packages' => false, 'badge' => 'Luxury', 'source' => 'default']
            ];
        } else {
            $defaultDestinations = [
                ['name' => 'Kashmir', 'full_address' => 'Srinagar & Gulmarg, Kashmir, India', 'city' => 'Srinagar', 'state' => 'Jammu & Kashmir', 'country' => 'India', 'has_packages' => true, 'badge' => 'Bestseller', 'source' => 'default'],
                ['name' => 'Goa', 'full_address' => 'Calangute & Panaji, Goa, India', 'city' => 'Goa', 'state' => 'Goa', 'country' => 'India', 'has_packages' => true, 'badge' => 'Beach Hub', 'source' => 'default'],
                ['name' => 'Kerala', 'full_address' => 'Munnar & Alleppey, Kerala, India', 'city' => 'Munnar', 'state' => 'Kerala', 'country' => 'India', 'has_packages' => true, 'badge' => 'Backwaters', 'source' => 'default'],
                ['name' => 'Manali', 'full_address' => 'Manali, Himachal Pradesh, India', 'city' => 'Manali', 'state' => 'Himachal Pradesh', 'country' => 'India', 'has_packages' => false, 'badge' => 'Hill Station', 'source' => 'default']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'provider' => $provider,
        'is_default' => true,
        'data' => $defaultDestinations
    ]);
    exit();
}

$results = [];
$seenKeys = [];

// Step 1: Check matching DB destinations first to ensure any DB packages surface right at top
if ($pdo) {
    try {
        $term = '%' . $query . '%';
        $sqlPkg = "SELECT id, title, location, state_country, category, badge FROM `packages` WHERE `status` = 'active' AND (`title` LIKE :t1 OR `location` LIKE :t2 OR `state_country` LIKE :t3)";
        if ($category === 'domestic') {
            $sqlPkg .= " AND `category` = 'domestic'";
        } elseif ($category === 'international') {
            $sqlPkg .= " AND `category` = 'international'";
        }
        $sqlPkg .= " LIMIT 4";
        $stmt = $pdo->prepare($sqlPkg);
        $stmt->execute([':t1' => $term, ':t2' => $term, ':t3' => $term]);
        $matchingPkgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($matchingPkgs as $mp) {
            $locParts = explode(',', $mp['location']);
            $firstLoc = trim($locParts[0] ?? $mp['title']);
            $key = strtolower($firstLoc);
            if (!isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $results[] = [
                    'name' => $firstLoc,
                    'full_address' => ($mp['location'] ?: $firstLoc) . (!empty($mp['state_country']) ? ' • ' . $mp['state_country'] : ''),
                    'city' => $firstLoc,
                    'state' => '',
                    'country' => ($mp['category'] === 'international' ? 'International' : 'India'),
                    'has_packages' => true,
                    'package_count' => 1,
                    'has_hotels' => false,
                    'hotel_count' => 0,
                    'badge' => $mp['badge'] ?: 'Package Available',
                    'source' => 'db'
                ];
            }
        }
    } catch (Exception $e) {}
}

// Step 2: Query External Location Autocomplete API

// Provider 1: Google Places API (if enabled & key supplied)
if ($provider === 'google' && !empty($googleKey)) {
    $params = [
        'input' => $query,
        'key' => $googleKey,
        'types' => '(regions)'
    ];
    if ($category === 'domestic') {
        $params['components'] = 'country:in';
    }
    $googleUrl = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?' . http_build_query($params);

    $ch = curl_init($googleUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!empty($data['predictions'])) {
        foreach ($data['predictions'] as $pred) {
            $mainText = $pred['structured_formatting']['main_text'] ?? $pred['description'];
            $key = strtolower($mainText);
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;

            $avail = checkDbAvailability($pdo, $mainText);
            $results[] = [
                'name' => $mainText,
                'full_address' => $pred['description'],
                'city' => $mainText,
                'state' => $pred['terms'][count($pred['terms'])-2]['value'] ?? '',
                'country' => $pred['terms'][count($pred['terms'])-1]['value'] ?? '',
                'has_packages' => $avail['packages'] > 0,
                'package_count' => $avail['packages'],
                'has_hotels' => $avail['hotels'] > 0,
                'hotel_count' => $avail['hotels'],
                'badge' => ($avail['packages'] > 0 ? 'Package in DB' : ($avail['hotels'] > 0 ? 'Hotel in DB' : 'Destination')),
                'source' => 'google'
            ];
            if (count($results) >= 8) break;
        }
    }
}

// Provider 2: Mapbox Geocoding API (if enabled)
if ($provider === 'mapbox' && !empty($mapboxKey) && count($results) < 8) {
    $encodedQ = urlencode($query);
    $mbUrl = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$encodedQ}.json?access_token={$mapboxKey}&types=place,locality,region,country&language=en&limit=6";
    if ($category === 'domestic') $mbUrl .= "&country=in";

    $ch = curl_init($mbUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!empty($data['features'])) {
        foreach ($data['features'] as $feat) {
            $name = $feat['text'] ?? $feat['place_name'];
            $key = strtolower($name);
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;

            $avail = checkDbAvailability($pdo, $name);
            $results[] = [
                'name' => $name,
                'full_address' => $feat['place_name'] ?? $name,
                'city' => $name,
                'state' => '',
                'country' => '',
                'has_packages' => $avail['packages'] > 0,
                'package_count' => $avail['packages'],
                'has_hotels' => $avail['hotels'] > 0,
                'hotel_count' => $avail['hotels'],
                'badge' => ($avail['packages'] > 0 ? 'Package in DB' : 'Destination'),
                'source' => 'mapbox'
            ];
            if (count($results) >= 8) break;
        }
    }
}

// Provider 3: Default & Free Fallback -> OpenStreetMap Photon API (English language, instant)
if (count($results) < 8) {
    $osmUrl = 'https://photon.komoot.io/api/?' . http_build_query([
        'q' => $query,
        'limit' => 10,
        'lang' => 'en'
    ]);

    $ch = curl_init($osmUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, getSetting('site_name', 'Orion Advent') . ' Travel Portal / 2.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['features']) && is_array($data['features'])) {
        foreach ($data['features'] as $feat) {
            $props = $feat['properties'] ?? [];
            $name = $props['name'] ?? '';
            if (empty($name)) continue;

            $city = $props['city'] ?? $props['town'] ?? $props['district'] ?? $props['state'] ?? $name;
            $state = $props['state'] ?? '';
            $country = $props['country'] ?? '';
            $countryCode = strtolower($props['countrycode'] ?? '');

            // Domestic vs International contextual filter
            if ($category === 'domestic') {
                $isIndia = false;
                if ($countryCode === 'in') {
                    $isIndia = true;
                } elseif (!empty($country) && (stripos($country, 'india') !== false || stripos($country, 'bharat') !== false)) {
                    $isIndia = true;
                } else {
                    $indianStates = [
                        'andhra pradesh', 'arunachal pradesh', 'assam', 'bihar', 'chhattisgarh', 'goa', 'gujarat',
                        'haryana', 'himachal pradesh', 'jharkhand', 'karnataka', 'kerala', 'madhya pradesh',
                        'maharashtra', 'manipur', 'meghalaya', 'mizoram', 'nagaland', 'odisha', 'punjab',
                        'rajasthan', 'sikkim', 'tamil nadu', 'telangana', 'tripura', 'uttar pradesh',
                        'uttarakhand', 'west bengal', 'delhi', 'jammu & kashmir', 'jammu and kashmir',
                        'ladakh', 'puducherry', 'andaman', 'chandigarh', 'kashmir'
                    ];
                    $lowerState = strtolower($state);
                    $lowerCity = strtolower($city);
                    foreach ($indianStates as $ist) {
                        if (str_contains($lowerState, $ist) || str_contains($lowerCity, $ist)) {
                            $isIndia = true;
                            break;
                        }
                    }
                }
                if (!$isIndia) {
                    continue;
                }
            } elseif ($category === 'international') {
                if ($countryCode === 'in' || strtolower($country) === 'india') {
                    continue;
                }
            }

            $key = strtolower($name);
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;

            $addressParts = array_filter([$name, $state, $country]);
            $fullAddress = implode(', ', array_unique($addressParts));

            $avail = checkDbAvailability($pdo, $name);

            $badge = 'Destination';
            if ($avail['packages'] > 0) {
                $badge = $avail['packages'] . ' Package' . ($avail['packages'] > 1 ? 's' : '') . ' in DB';
            } elseif ($avail['hotels'] > 0) {
                $badge = $avail['hotels'] . ' Hotel' . ($avail['hotels'] > 1 ? 's' : '') . ' in DB';
            }

            $results[] = [
                'name' => $name,
                'full_address' => $fullAddress,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'has_packages' => $avail['packages'] > 0,
                'package_count' => $avail['packages'],
                'has_hotels' => $avail['hotels'] > 0,
                'hotel_count' => $avail['hotels'],
                'badge' => $badge,
                'source' => 'osm'
            ];

            if (count($results) >= 8) break;
        }
    }
}

echo json_encode([
    'success' => true,
    'provider' => $provider,
    'query' => $query,
    'category' => $category,
    'count' => count($results),
    'data' => $results
]);
