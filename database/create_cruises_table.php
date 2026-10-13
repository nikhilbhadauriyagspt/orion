<?php
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

$sql = "CREATE TABLE IF NOT EXISTS `cruises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `cruise_line` varchar(100) NOT NULL DEFAULT 'Cordelia Cruises',
  `ship_name` varchar(100) NOT NULL DEFAULT 'The Empress',
  `departure_port` varchar(100) NOT NULL DEFAULT 'Mumbai',
  `destination_ports` varchar(255) NOT NULL DEFAULT 'Mumbai, High Seas, Goa',
  `duration_nights` int(11) NOT NULL DEFAULT 3,
  `duration_days` int(11) NOT NULL DEFAULT 4,
  `duration_text` varchar(50) DEFAULT '3 Nights / 4 Days',
  `starting_price` decimal(10,2) NOT NULL DEFAULT 19999.00,
  `original_price` decimal(10,2) DEFAULT 26999.00,
  `token_advance` decimal(10,2) DEFAULT 3000.00,
  `badge` varchar(50) DEFAULT 'Bestseller',
  `rating` decimal(2,1) DEFAULT 4.9,
  `reviews_count` int(11) DEFAULT 380,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` longtext DEFAULT NULL,
  `highlights` longtext DEFAULT NULL,
  `itinerary` longtext DEFAULT NULL,
  `cabins` longtext DEFAULT NULL,
  `inclusions` longtext DEFAULT NULL,
  `exclusions` longtext DEFAULT NULL,
  `policies` longtext DEFAULT NULL,
  `category` enum('domestic','international') DEFAULT 'domestic',
  `sailing_dates` varchar(255) DEFAULT 'Weekly Departures • Friday & Monday',
  `status` enum('active','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "Table `cruises` created or already exists.\n";

// Check if any cruises exist
$count = $pdo->query("SELECT COUNT(*) FROM `cruises`")->fetchColumn();
if ($count == 0) {
    echo "Inserting initial popular cruises...\n";

    // 1. Mumbai to Goa Weekend Cruise (Cordelia Empress)
    $c1_highlights = json_encode([
        ['icon' => 'fa-solid fa-water-ladder', 'title' => 'Top Deck Infinity Pools', 'desc' => 'Dual swimming pools and whirlpool jacuzzis with ocean views'],
        ['icon' => 'fa-solid fa-masks-theater', 'title' => 'Broadway Theatre & Burlesque', 'desc' => 'Nightly live musicals, magic shows & standup comedy'],
        ['icon' => 'fa-solid fa-utensils', 'title' => 'All-Inclusive Ocean Dining', 'desc' => 'Lavish global buffets, Jain cuisine & Indian culinary stations'],
        ['icon' => 'fa-solid fa-dice', 'title' => 'Casino & Nightclub Lounges', 'desc' => 'Live DJs, rock bar, casino gaming tables & stargazing lounges']
    ], JSON_UNESCAPED_UNICODE);

    $c1_itinerary = json_encode([
        ['day' => 'Day 1', 'port' => 'Mumbai (Green Gate Pier)', 'arrive' => '02:00 PM Check-in', 'depart' => '06:30 PM Sailing', 'title' => 'Embarkation at Mumbai Port & Sunset Welcome', 'desc' => 'Board The Empress at Mumbai Port. Settle into your stateroom, join the safety drill, and enjoy the sunset sail-away party on the pool deck with live DJ beats. Lavish dinner buffet follows.'],
        ['day' => 'Day 2', 'port' => 'At Sea (Arabian High Seas)', 'arrive' => 'Cruising', 'depart' => 'All Day', 'title' => 'Full Day Luxury Cruising on High Seas', 'desc' => 'Wake up to endless 360° turquoise horizons. Enjoy rock climbing, pool games, luxury spa treatments, afternoon tea, and grand gala Broadway musical at Marquee Theatre.'],
        ['day' => 'Day 3', 'port' => 'Mormugao Port (Goa)', 'arrive' => '07:30 AM', 'depart' => '05:00 PM', 'title' => 'Port of Call: Goa Excursion & Beach Vibes', 'desc' => 'Dock at Mormugao Port. Disembark for South Goa beach clubs, heritage church visits, or local spice farms. Return on board before sunset for the Bollywood themed deck party.'],
        ['day' => 'Day 4', 'port' => 'Mumbai Return', 'arrive' => '08:00 AM', 'depart' => 'Disembark', 'title' => 'Morning Arrival & Fond Memories', 'desc' => 'Enjoy breakfast as the Mumbai skyline comes into view. Complete debarkation formalities with cherished sea voyage memories.']
    ], JSON_UNESCAPED_UNICODE);

    $c1_cabins = json_encode([
        ['name' => 'Interior Stateroom', 'type' => 'Standard Cozy', 'price' => 19999, 'original_price' => 24999, 'capacity' => '2-3 Guests', 'size' => '135 sq.ft', 'perks' => 'Queen/Twin Beds, Ensuite Bathroom, 24/7 Room Service, Interactive TV', 'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ocean View Stateroom', 'type' => 'Window Panoramic', 'price' => 24999, 'original_price' => 31999, 'capacity' => '2-4 Guests', 'size' => '160 sq.ft', 'perks' => 'Picture Window Ocean Views, Sofa Bed, Mini Bar, Priority Dining', 'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Mini Suite with Private Balcony', 'type' => 'Private Verandah', 'price' => 34999, 'original_price' => 44999, 'capacity' => '2-4 Guests', 'size' => '240 sq.ft', 'perks' => 'Private Sea Balcony, Jacuzzi Bath Tub, Welcome Champagne, Butler Service', 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Chairman\'s Presidential Suite', 'type' => 'Ultra Luxury', 'price' => 59999, 'original_price' => 79999, 'capacity' => '2-5 Guests', 'size' => '450 sq.ft', 'perks' => 'Expansive Sun Deck, Dedicated 24/7 Concierge, Free Premium Spirits, VIP Lounge Access', 'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80']
    ], JSON_UNESCAPED_UNICODE);

    $c1_inclusions = json_encode([
        '3 Nights accommodation in selected luxury cabin category',
        'All standard buffet meals (Breakfast, Lunch, Evening Snacks & Dinner) across 5 specialty restaurants',
        'Access to swimming pools, fitness centre, rock climbing wall & sports court',
        'Complimentary entry to nightly Broadway theatre shows and live musical entertainment',
        'Port handling charges, fuel surcharge & mandatory baggage transfer'
    ], JSON_UNESCAPED_UNICODE);

    $c1_exclusions = json_encode([
        'Casino gaming chips and casino bar privileges',
        'Alcoholic and canned packaged beverages (Beverage packages available)',
        'Spa, wellness treatments and salon services',
        'Optional Shore Excursions at Mormugao (Goa)',
        '18% Government GST'
    ], JSON_UNESCAPED_UNICODE);

    $c1_gallery = json_encode([
        'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80'
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO `cruises` (
        `title`, `slug`, `subtitle`, `cruise_line`, `ship_name`, `departure_port`, `destination_ports`,
        `duration_nights`, `duration_days`, `duration_text`, `starting_price`, `original_price`, `token_advance`,
        `badge`, `rating`, `reviews_count`, `featured_image`, `gallery`, `highlights`, `itinerary`, `cabins`,
        `inclusions`, `exclusions`, `policies`, `category`, `sailing_dates`, `status`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");

    $stmt->execute([
        'Mumbai to Goa & Arabian High Seas Luxury Weekend Cruise',
        'mumbai-to-goa-arabian-high-seas-cruise',
        'Experience 3 nights of high-seas luxury, Broadway-style entertainment, cascading pools & oceanview dining aboard The Empress',
        'Cordelia Cruises',
        'The Empress',
        'Mumbai',
        'Mumbai, High Seas, Goa',
        3, 4, '3 Nights / 4 Days',
        19999.00, 26999.00, 3000.00,
        'Bestseller', 4.9, 480,
        'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=900&q=80',
        $c1_gallery, $c1_highlights, $c1_itinerary, $c1_cabins,
        $c1_inclusions, $c1_exclusions,
        '100% refund on cancellations requested 30 days prior to sailing. 50% refund between 15-29 days.',
        'domestic',
        'Every Friday & Monday departures'
    ]);

    // 2. Chennai to Sri Lanka Island Cruise
    $c2_highlights = json_encode([
        ['icon' => 'fa-solid fa-globe', 'title' => 'International Island Hopping', 'desc' => 'Visit Trincomalee, Hambantota and Jaffna with easy visa assistance'],
        ['icon' => 'fa-solid fa-umbrella-beach', 'title' => 'Pristine Sri Lankan Beaches', 'desc' => 'Golden sands, whale watching ports and colonial fort tours'],
        ['icon' => 'fa-solid fa-utensils', 'title' => 'Authentic Coastal & Chettinad Dining', 'desc' => 'Master chefs curating South Indian, Sri Lankan & international dishes'],
        ['icon' => 'fa-solid fa-spa', 'title' => 'Full Sea View Ayurvedic Spa', 'desc' => 'Rejuvenating ocean massages and sauna therapies at sea']
    ], JSON_UNESCAPED_UNICODE);

    $c2_itinerary = json_encode([
        ['day' => 'Day 1', 'port' => 'Chennai Port', 'arrive' => '01:00 PM', 'depart' => '05:00 PM', 'title' => 'Embarkation at Chennai & High Seas Gala', 'desc' => 'Check in at Chennai International Cruise Terminal. Board the ship and enjoy welcome cocktails. Sail into the Bay of Bengal.'],
        ['day' => 'Day 2', 'port' => 'At Sea (Bay of Bengal)', 'arrive' => 'Cruising', 'depart' => 'All Day', 'title' => 'High Seas Fun, Casino & Pool Deck Parties', 'desc' => 'Unwind by the jacuzzi, watch 3D movies at sea, take Latin dance lessons, and relish the grand captain dinner.'],
        ['day' => 'Day 3', 'port' => 'Hambantota (Sri Lanka)', 'arrive' => '08:00 AM', 'depart' => '06:00 PM', 'title' => 'Port of Call: Yala National Park Safari', 'desc' => 'Disembark for wildlife safaris in search of leopards and wild elephants, or visit local botanical gardens.'],
        ['day' => 'Day 4', 'port' => 'Trincomalee (Sri Lanka)', 'arrive' => '08:00 AM', 'depart' => '05:00 PM', 'title' => 'Port of Call: Koneswaram Temple & Pigeon Island', 'desc' => 'Explore the dramatic cliff-top temple and turquoise snorkeling lagoons.'],
        ['day' => 'Day 5', 'port' => 'Chennai Return', 'arrive' => '09:00 AM', 'depart' => 'Disembark', 'title' => 'Return to Chennai & Disembarkation', 'desc' => 'Conclude your international sea journey after a scenic morning breakfast.']
    ], JSON_UNESCAPED_UNICODE);

    $c2_cabins = json_encode([
        ['name' => 'Interior Stateroom', 'type' => 'Standard', 'price' => 28999, 'original_price' => 36000, 'capacity' => '2-3 Guests', 'size' => '140 sq.ft', 'perks' => 'Air Conditioned, LED TV, Daily Housekeeping, Ensuite Shower', 'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ocean View Stateroom', 'type' => 'Panoramic Window', 'price' => 34999, 'original_price' => 44000, 'capacity' => '2-4 Guests', 'size' => '170 sq.ft', 'perks' => 'Bay of Bengal Horizon Views, Tea/Coffee Maker, Soft Bathrobes', 'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Private Balcony Suite', 'type' => 'Balcony Luxury', 'price' => 49999, 'original_price' => 64000, 'capacity' => '2-4 Guests', 'size' => '260 sq.ft', 'perks' => 'Private Ocean Terrace, Premium Toiletries, Priority Boarding', 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80']
    ], JSON_UNESCAPED_UNICODE);

    $stmt->execute([
        'Chennai to Sri Lanka (Hambantota & Trincomalee) International Cruise',
        'chennai-to-sri-lanka-international-cruise',
        'Sail across the Bay of Bengal to the exotic shores of Sri Lanka with wildlife safaris & beach adventures',
        'Cordelia Cruises',
        'The Empress',
        'Chennai',
        'Chennai, Hambantota, Trincomalee',
        4, 5, '4 Nights / 5 Days',
        28999.00, 36000.00, 4000.00,
        'International Pass', 4.9, 310,
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
        $c1_gallery, $c2_highlights, $c2_itinerary, $c2_cabins,
        $c1_inclusions, $c1_exclusions,
        'Valid Passport required with minimum 6 months validity. Sri Lanka ETA visa assistance included.',
        'international',
        'Monthly Departures • Monday Sailing'
    ]);

    // 3. Singapore to Malaysia & Thailand Mega Cruise (Royal Caribbean)
    $c3_highlights = json_encode([
        ['icon' => 'fa-solid fa-ship', 'title' => 'World-Class Mega Ship Amenities', 'desc' => 'FlowRider surf simulator, RipCord by iFLY skydiving & bumper cars'],
        ['icon' => 'fa-solid fa-water', 'title' => 'North Star Observation Capsule', 'desc' => 'Ascend 300 feet above sea level in glass observation pod'],
        ['icon' => 'fa-solid fa-utensils', 'title' => '16 Specialty Restaurants & Bars', 'desc' => 'Bionic robotic bar, Jamie Italian & Wonderland culinary journeys'],
        ['icon' => 'fa-solid fa-music', 'title' => 'Two70 Multimedia Theater', 'desc' => 'Immersive robotic screen visual spectacles and acrobatics']
    ], JSON_UNESCAPED_UNICODE);

    $c3_itinerary = json_encode([
        ['day' => 'Day 1', 'port' => 'Singapore (Marina Bay Cruise Centre)', 'arrive' => '12:00 PM', 'depart' => '04:30 PM', 'title' => 'Depart Singapore Marina Bay', 'desc' => 'Board Spectrum of the Seas. Explore SeaPlex sports arena and enjoy Broadway-scale theater.'],
        ['day' => 'Day 2', 'port' => 'Penang (George Town, Malaysia)', 'arrive' => '02:30 PM', 'depart' => '09:00 PM', 'title' => 'Port of Call: Penang Street Food & Heritage', 'desc' => 'Dock in George Town. Taste Michelin street food, explore heritage temples and night markets.'],
        ['day' => 'Day 3', 'port' => 'Phuket (Thailand)', 'arrive' => '08:00 AM', 'depart' => '08:00 PM', 'title' => 'Port of Call: Phuket Islands & Emerald Waters', 'desc' => 'Full day in Phuket for Phi Phi speedboat trips or Patong beach excursions.'],
        ['day' => 'Day 4', 'port' => 'At Sea (Straits of Malacca)', 'arrive' => 'Cruising', 'depart' => 'All Day', 'title' => 'Full Day Sea Voyage Thrills', 'desc' => 'Enjoy FlowRider surfing, laser tag tournaments, luxury duty-free shopping and farewell gala.'],
        ['day' => 'Day 5', 'port' => 'Singapore Return', 'arrive' => '07:00 AM', 'depart' => 'Disembark', 'title' => 'Arrival at Marina Bay Singapore', 'desc' => 'Morning disembarkation in Singapore. Seamless luggage retrieval and city transfers.']
    ], JSON_UNESCAPED_UNICODE);

    $c3_cabins = json_encode([
        ['name' => 'Interior Stateroom with Virtual Balcony', 'type' => 'High-Tech Cabin', 'price' => 38999, 'original_price' => 49000, 'capacity' => '2-4 Guests', 'size' => '166 sq.ft', 'perks' => 'Real-time 80-inch HD screen projecting ocean views, Smart lighting', 'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ocean View Stateroom', 'type' => 'Panoramic Window', 'price' => 46999, 'original_price' => 58000, 'capacity' => '2-4 Guests', 'size' => '182 sq.ft', 'perks' => 'Large Picture Window, Sitting Area with Sofa, Luxury Bedding', 'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ocean Balcony Stateroom', 'type' => 'Private Verandah', 'price' => 58999, 'original_price' => 75000, 'capacity' => '2-4 Guests', 'size' => '216 sq.ft', 'perks' => '55 sq.ft Private Ocean Verandah with lounge chairs, Evening canapes', 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ultimate Family Suite', 'type' => 'Two-Story Penthouse', 'price' => 119999, 'original_price' => 150000, 'capacity' => '4-8 Guests', 'size' => '1134 sq.ft', 'perks' => 'In-suite slide, Private 3D cinema, Air hockey, Royal Genie personal butler', 'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80']
    ], JSON_UNESCAPED_UNICODE);

    $stmt->execute([
        'Singapore to Malaysia (Penang) & Thailand (Phuket) Royal Caribbean Mega Cruise',
        'singapore-malaysia-thailand-royal-caribbean-cruise',
        'Sail on Asia\'s largest quantum-class mega ship Spectrum of the Seas with robotic bars & surf simulators',
        'Royal Caribbean',
        'Spectrum of the Seas',
        'Singapore',
        'Singapore, Penang, Phuket',
        4, 5, '4 Nights / 5 Days',
        38999.00, 49000.00, 5000.00,
        'Mega Ship', 5.0, 520,
        'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80',
        $c1_gallery, $c3_highlights, $c3_itinerary, $c3_cabins,
        $c1_inclusions, $c1_exclusions,
        'Passport valid for at least 6 months required. Singapore multiple-entry visa required.',
        'international',
        'Year-round weekly sailings'
    ]);

    echo "Initial cruises inserted successfully.\n";
} else {
    echo "Cruises already populated ({$count} records).\n";
}
